<?php
/**
 * 2010-2021 Sender.net
 *
 * Sender.net Automated Emails
 *
 * @author Sender.net <info@sender.net>
 * @copyright 2010-2018 Sender.net
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License v. 3.0 (OSL-3.0)
 * Sender.net
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once 'lib/Sender/SenderApiClient.php';
require_once 'lib/Sender/CustomersExport.php';
require_once(_PS_CONFIG_DIR_ . "/config.inc.php");

class SenderAutomatedEmails extends Module
{
    /**
     * Default settings array
     * @var array
     */
    private $defaultSettings = array();

    private $debug = true;

    private $visitorId;

    /**
     * Sender.net API client
     * @var object
     */
    public $senderApiClient = null;

    /**
     * FileLogger instance
     * @var object
     */
    private $debugLogger = null;

    /**
     * Contructor function
     *
     */
    public function __construct()
    {
        $this->senderDetails();
        $this->senderDefaultSettings();
        $this->senderDirectories();

        parent::__construct();
        $this->bootstrap = true;
    }

    public function senderDetails()
    {
        $this->name = 'senderautomatedemails';
        $this->tab = 'emailing';
        $this->version = '2.0.0';
        $this->author = 'Sender.net';
        $this->author_uri = 'https://www.sender.net/';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array(
            'min' => '1.6.1.24',
            'max' => _PS_VERSION_
        );

        $this->displayName = $this->l('Sender.net Automated Emails');
        $this->description = $this->l('All you need for your email marketing in one tool.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    public function senderDefaultSettings()
    {
        $this->defaultSettings = array(
            'SPM_API_KEY' => '',
            'SPM_IS_MODULE_ACTIVE' => 0,
            'SPM_LAST_ACTIVE_ACCOUNT' => 0,
            'SPM_ALLOW_FORMS' => 0,
            'SPM_ALLOW_IMPORT' => 0,
            'SPM_ALLOW_TRACK_NEW_SIGNUPS' => 0, # Always enabled, use customers tracking instead
            'SPM_ALLOW_TRACK_CARTS' => 0, # <- Allow customers track
            'SPM_CUSTOMERS_LIST_ID' => 0,
            'SPM_CUSTOMERS_LIST_NAME' => null,
            'SPM_GUEST_LIST_ID' => 0,
            'SPM_GUEST_LIST_NAME' => null,
            'SPM_FORM_ID' => 0,
            'SPM_CUSTOMER_FIELD_LOCATION' => 0,
            'SPM_CUSTOMER_FIELD_BIRTHDAY' => 0,
            'SPM_CUSTOMER_FIELD_GENDER' => 0,
            'SPM_CUSTOMER_FIELD_PARTNER_OFFERS_ID' => 0,
            'SPM_SENDERAPP_SYNC_LIST_ID' => 0,
            'SPM_SENDERAPP_RESOURCE_KEY_CLIENT' => 0,
        );
    }

    public function senderDirectories()
    {
        $this->views_url = _PS_ROOT_DIR_ . '/' . basename(_PS_MODULE_DIR_) . '/' . $this->name . '/views';
        $this->module_url = __PS_BASE_URI__ . basename(_PS_MODULE_DIR_) . '/' . $this->name;
        $this->module_path = _PS_ROOT_DIR_ . $this->module_url;
    }

    /**
     * Handle module installation
     *
     * @return bool
     */
    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        $this->addTabs();

        if (parent::install()) {
            foreach ($this->defaultSettings as $defaultSettingKey => $defaultSettingValue) {
                if (!Configuration::updateValue($defaultSettingKey, $defaultSettingValue)) {
                    return false;
                }
            }
        }

        if (!$this->registerHook('displayBackOfficeHeader')
            || !$this->registerHook('displayOrderConfirmation')
            || !$this->registerHook('registerUnsubscribedWebhook')
            || !$this->registerHook('displayHeader')
            || !$this->registerHook('actionObjectCartUpdateAfter') // Getting it on all pages
            || !$this->registerHook('actionCustomerAccountAdd')  //Adding customer and tracking the customer track
            || !$this->registerHook('actionCustomerAccountUpdate')
            || !$this->registerHook('actionAuthentication')
            || !$this->registerHook('actionObjectNewsletterAddAfter')
            || !$this->registerHook('actionObjectCustomerUpdateAfter')
            || !$this->registerHook('displayFooterProduct')) {
            return false;
        }

        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            if (!$this->registerHook('displayFooterBefore')) {
                return false;
            }
        } else {
            if (!$this->registerHook('displayFooter')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Handle uninstall
     *
     * @return bool
     */
    public function uninstall()
    {
        if (parent::uninstall()) {
            foreach (array_keys($this->defaultSettings) as $defaultSettingKey) {
                if (!Configuration::deleteByName($defaultSettingKey)) {
                    return false;
                }
            }

            $tabsArray = array();
            $tabsArray[] = Tab::getIdFromClassName("AdminSenderAutomatedEmails");
            foreach ($tabsArray as $tabId) {
                if ($tabId) {
                    $tab = new Tab($tabId);
                    $tab->delete();
                }
            }
        }

        return true;
    }

    /**
     * Reset all Sender.net related settings
     *
     * @return void
     */
    private function disableModule()
    {
        Configuration::updateValue('SPM_API_KEY', '');
        Configuration::updateValue('SPM_IS_MODULE_ACTIVE', 0);
        Configuration::updateValue('SPM_ALLOW_FORMS', 0);
        Configuration::updateValue('SPM_ALLOW_IMPORT', 0);
        Configuration::updateValue('SPM_ALLOW_TRACK_NEW_SIGNUPS', 0);
        Configuration::updateValue('SPM_ALLOW_TRACK_CARTS', 0);
        Configuration::updateValue('SPM_CUSTOMER_FIELD_FIRSTNAME', 0);
        Configuration::updateValue('SPM_CUSTOMER_FIELD_LASTNAME', 0);
        Configuration::updateValue('SPM_SENDERAPP_RESOURCE_KEY_CLIENT', 0);
        Configuration::updateValue('SPM_FORM_ID', 0);
    }

    public function isModuleActive()
    {
        if (!Configuration::get('SPM_IS_MODULE_ACTIVE')) {
            return false;
        }
        return true;
    }

    public function hookDisplayHeader()
    {
        if (!$this->isModuleActive()) {
            return;
        }

        if ((!Configuration::get('SPM_ALLOW_TRACK_CARTS') && !Configuration::get('SPM_ALLOW_FORMS'))
            || !Configuration::get('SPM_SENDERAPP_RESOURCE_KEY_CLIENT')) {
            return;
        }

        $resourceKey = Configuration::get('SPM_SENDERAPP_RESOURCE_KEY_CLIENT');

        $html = '';
        $html .= "
			<script>
			  (function (s, e, n, d, er) {
				s['Sender'] = er;
				s[er] = s[er] || function () {
				  (s[er].q = s[er].q || []).push(arguments)
				}, s[er].l = 1 * new Date();
				var a = e.createElement(n),
					m = e.getElementsByTagName(n)[0];
				a.async = 1;
				a.src = d;
				m.parentNode.insertBefore(a, m)
			  })(window, document, 'script', 'https://cdn.sender.net/accounts_resources/universal.js', 'sender');
			  sender('$resourceKey');
			</script>
			";

        if (Configuration::get('SPM_ALLOW_TRACK_CARTS')) {
            $html .= "<script>
			  sender('trackVisitors')
			</script>";
        }
        return $html;
    }

    /**
     * Add tab css to the BackOffice
     *
     * @return void
     */
    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCss($this->_path . 'views/css/tab.css');
    }

    public function hookDisplayHome()
    {
        return $this->hookDisplayFooterBefore();
    }

    /**
     * Showing the form on all pages
     * If embed will append to before the footer
     * Option available for 1.7
     */
    public function hookDisplayFooterBefore()
    {
        if (!$this->isModuleActive()) {
            return;
        }
        return $this->senderDisplayFooter();
    }

    /**
     * Showing the form on all pages
     * If embed will append to before the footer
     */
    public function hookDisplayFooter()
    {
        if (!$this->isModuleActive()) {
            return;
        }
        return $this->senderDisplayFooter();
    }

    /**
     * Solution for validation. $_COOKIE not allowed in ps
     * @return false|string
     */
    public function getSenderCookieFromHeader()
    {
        if ($this->visitorId) {
            return $this->visitorId;
        }

        $allHeaders = getallheaders();
        if (array_key_exists('Cookie', $allHeaders)) {
            $onlyCookies = $allHeaders['Cookie'];

            $senderCookiePos = strpos($onlyCookies, 'sender_site_visitor');
            $fromSenderSiteVisitor = Tools::substr($onlyCookies, $senderCookiePos);

            $posIqual = strpos($fromSenderSiteVisitor, '=');
            $fromIqualSiteVisitor = Tools::substr($fromSenderSiteVisitor, $posIqual + 1);

            if ($visitorString = strtok($fromIqualSiteVisitor, ';')) {
                $this->visitorId = $visitorString;
                return $this->visitorId;
            }

            #When sender cookie would be last on client list
            if ($visitorString = strtok($fromIqualSiteVisitor, ' ')) {
                $this->visitorId = $visitorString;
                return $this->visitorId;
            }

            return false;
        }
    }

    public function senderDisplayFooter()
    {
        if (!Configuration::get('SPM_ALLOW_FORMS') || !Configuration::get('SPM_SENDERAPP_RESOURCE_KEY_CLIENT')) {
            return;
        }

        $options = array(
            'showForm' => false
        );

        $form = $this->senderApiClient()->getFormById(Configuration::get('SPM_FORM_ID'));
        #Check if form is disabled or pop-up
        if (!$form || !$form->is_active || $form->type != 'embed') {
            return;
        }

        if ($form->type === 'embed') {
            $embedHash = $form->settings->embed_hash;
        }
        // Add forms
        $options['formUrl'] = isset($form->settings->resource_path) ? $form->settings->resource_path : '';
        $options['showForm'] = true;
        $options['embedHash'] = isset($embedHash) ? $embedHash : '';

        $this->context->smarty->assign($options);
        return $this->context->smarty->fetch($this->views_url . '/templates/front/form.tpl');
    }

    /**
     * @param $context
     * @return void
     */
    public function hookActionCustomerAccountAdd($context)
    {
        if (!$this->isModuleActive()) {
            return;
        }

        if (!Validate::isLoadedObject($context['newCustomer'])) {
            return;
        }

        $customer = $this->context->customer;

        if (!Configuration::get('SPM_ALLOW_TRACK_CARTS')) {
            return;
        }

        try {
            if (!$this->guestCheckNoAction()) {
                $this->formVisitor($customer, true, false);
            } else {
                $this->formVisitor($customer);
            }
        } catch (Exception $e) {
            $this->logDebug('Error hookActionCustomerAccountAdd ' . json_encode($e->getMessage()));
        }
    }

    /**
     * @return void
     */
    public function hookActionAuthentication()
    {
        if (!$this->isModuleActive()) {
            return;
        }

        if (!Configuration::get('SPM_ALLOW_TRACK_CARTS')) {
            return;
        }

        $customer = $this->context->customer;
        $this->formVisitor($customer, false);
    }

    /**
     * 1.6 validation for no tracking
     * @return bool
     */
    public function guestCheckNoAction()
    {
        if ($this->context->customer->is_guest) {
            return false;
        }
        return true;
    }

    /**
     * @param $context
     * @return void
     */
    public function hookActionObjectCartUpdateAfter($context)
    {
        $this->logDebug('hookActionObjectCartUpdateAfter');
        if (!$this->isModuleActive() || !Validate::isLoadedObject($context['cart'])) {
            return;
        }

        if (!Configuration::get('SPM_ALLOW_TRACK_CARTS') || !$this->getSenderCookieFromHeader()) {
            return;
        }

        if ($this->context->cookie->__get('sender-deleted-cart') === true) {
            $this->context->cookie->__set('sender-deleted-cart', false);
            return;
        }

        if ($this->context->cookie->__isset('sender-captured-cart')
            && !empty($this->context->cookie->__get('sender-captured-cart'))) {
            if ($this->compareSenderDateTime($this->context->cookie->__get('sender-captured-cart'))) {
                return;
            }
        }

        $this->syncCart($context['cart']);
    }

    /**
     * @param $cart
     * @return void
     */
    private function syncCart($cart)
    {
        $cartData = $this->mapCartData($cart, $this->getSenderCookieFromHeader());
        if (isset($cartData) && !empty($cartData['products'])) {
            $this->senderApiClient()->trackCart($cartData);
            $this->context->cookie->__set('sender-captured-cart', strtotime(date('Y-m-d H:i:s')));
            $this->context->cookie->write();
        } else {
            $this->senderApiClient()->cartDelete(Configuration::get('SPM_SENDERAPP_RESOURCE_KEY_CLIENT'), $cart->id);
            $this->context->cookie->__set('sender-deleted-cart', true);
        }
        $this->context->cookie->write();
    }

    /**
     * @param $cart
     * @param $visitorId
     * @return array|void
     */
    private function mapCartData($cart, $visitorId)
    {
        $this->logDebug(json_encode($cart));
        $cartHash = $cart->id;
        $data = array(
            "email" => $this->context->cookie->__get('email') ? $this->context->cookie->__get('email') : '',
            'visitor_id' => $visitorId,
            "external_id" => $cartHash,
            "url" => _PS_BASE_URL_ . __PS_BASE_URI__
                . 'index.php?fc=module&module='
                . $this->name
                . "&controller=recover&hash={$cartHash}",
            "currency" => $this->context->currency->iso_code,
            "order_total" => isset($cart->total_paid_tax_incl) ?
                $cart->total_paid_tax_incl : (string)$cart->getOrderTotal(),
            "products" => array()
        );

        $products = $cart->getProducts();
        if (!$products || empty($products)) {
            return;
        }

        foreach ($products as $product) {
            $Product = new Product($product['id_product']);
            $price = $Product->getPrice(true, null, 2);
            $linkRewrite = isset($product['link_rewrite'])
                ? $product['link_rewrite'] : implode('', $Product->link_rewrite);
            $prod = array(
                'name' => isset($product['name']) ? $product['name'] : $product['product_name'],
                'sku' => $product['reference'],
                'price' => (string)$price,
                'price_display' => $price . ' ' . $this->context->currency->iso_code,
                'qty' => isset($product['cart_quantity']) ? $product['cart_quantity'] : $product['product_quantity'],
                'image' => $this->context->link->getImageLink(
                    $linkRewrite,
                    $Product->getCoverWs(),
                    ImageType::getFormatedName('home')
                )
            );
            $data['products'][] = $prod;
        }

        return $data;
    }

    /**
     * @param $customer
     * @param bool $saveFields
     * @param bool $addToList
     * @return void
     */
    private function formVisitor($customer, $saveFields = true, $addToList = true)
    {
        if ($this->context->cookie->__isset('sender-added-visitor')
            && !empty($this->context->cookie->__get('sender-added-visitor'))) {
            if ($this->compareSenderDateTime($this->context->cookie->__get('sender-added-visitor'))) {
                return;
            }
        }

        if (!$visitorId = $this->getSenderCookieFromHeader()) {
            return;
        }

        $visitorRegistration = [
            'email' => $customer->email,
            'firstname' => $customer->firstname,
            'lastname' => $customer->lastname,
            'visitor_id' => $visitorId,
            'newsletter' => $customer->newsletter
        ];

        if ($addToList) {
            $visitorRegistration['list_id'] = Configuration::get('SPM_GUEST_LIST_ID');
        }

        if ($this->checkOrderHistory($customer->id)) {
            if (Configuration::get('SPM_CUSTOMERS_LIST_ID') != $this->defaultSettings['SPM_CUSTOMERS_LIST_ID']) {
                $visitorRegistration['list_id'] = Configuration::get('SPM_CUSTOMERS_LIST_ID');
            }
        }

        $this->senderApiClient()->visitorRegistered($visitorRegistration);

        if (!$subscriber = $this->senderApiClient()->isAlreadySubscriber(strtolower($customer->email))) {
            return;
        }

        if ($saveFields) {
            if (!empty($customFields = $this->getCustomFields($customer))) {
                $this->senderApiClient()->addFields($subscriber->id, $customFields);
            }
        }

        $this->context->cookie->__set('sender-added-visitor', strtotime(date('Y-m-d H:i:s')));
        $this->context->cookie->write();
    }

    /**
     * @param $dateAdd
     * @param $duration
     * @return bool
     */
    private function compareSenderDateTime($dateAdd, $duration = 1)
    {
        $currentTime = strtotime(date('Y-m-d H:i:s'));
        $dateCartAdded = $dateAdd + $duration;

        return $dateCartAdded >= $currentTime; // true = avoid // false = should track logic
    }

    /**
     * @param $context
     * @return void
     */
    public function hookDisplayOrderConfirmation($context)
    {
        $this->logDebug(__FUNCTION__);
        #First check if we should capture these details
        if (!$this->isModuleActive()) {
            return;
        }

        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $order = $context['order'];
        } else {
            $order = $context['objOrder'];
        }

        if (!$order || !Configuration::get('SPM_ALLOW_TRACK_CARTS')
            || !$this->getSenderCookieFromHeader()) {
            return;
        }

        try {
            #Subscriber status check
            $subscriber = $this->senderApiClient()->isAlreadySubscriber(strtolower($this->context->customer->email));
            if (!$subscriber){
                return;
            }

            $dataConvert = [
                'resource_key' => Configuration::get('SPM_SENDERAPP_RESOURCE_KEY_CLIENT'),
                'email' => strtolower($this->context->customer->email),
                'firstname' => $this->context->customer->firstname,
                'lastname' => $this->context->customer->lastname,
            ];

            $list = Configuration::get('SPM_CUSTOMERS_LIST_ID');
            if ($list) {
                $dataConvert['list_id'] = $list;
            }

            $cartTracked = $this->senderApiClient()->cartConvert($dataConvert, isset($idCart) ? $idCart : $order->id_cart);;
            $this->logDebug(json_encode($cartTracked));
        } catch (Exception $e) {
            $this->logDebug($e->getMessage());
        }
    }

    /**
     * Here we handle customer info where he updates his account,
     * and we delete or add him to the preferred list
     *
     * @param $context
     * @return bool|void
     */
    public function hookActionObjectCustomerUpdateAfter($context)
    {
        if (!$this->isModuleActive()) {
            return;
        }

        $customer = $context['object'];

        return $this->hookactionCustomerAccountUpdate($customer);
    }

    /**
     * Here we handle customer info where he updates his account,
     * and we delete or add him to the preferred list
     *
     * @param $customer
     * @return bool|void
     */
    public function hookActionCustomerAccountUpdate($customer)
    {
        if (!$this->isModuleActive()) {
            return;
        }
        //Validate if we should
        if (!Validate::isLoadedObject($customer)) {
            return;
        }

        if (!Configuration::get('SPM_ALLOW_TRACK_CARTS')) {
            return;
        }

        try {
            $this->formVisitor($customer);
        } catch (Exception $e) {
            $this->logDebug('Error hook hookActionCustomerAccountUpdate' . json_encode($e->getMessage()));
        }

        return true;
    }

    /**
     * @param $customerId
     * @return bool
     */
    private function checkOrderHistory($customerId)
    {
        $customerOrders = Order::getCustomerOrders($customerId);
        if ($customerOrders && count($customerOrders) > 0) {
            return true;
        }
        return false;
    }

    /**
     * On this hook we setup product
     * import JSON for sender to get the data
     *
     * @param array $params
     * @return mixed string Smarty
     */
    public function hookDisplayFooterProduct($params)
    {
        if (!$this->isModuleActive()) {
            return;
        }

        $product = $params['product'];
        $image_url = '';

        if ($product instanceof Product /* or ObjectModel */) {
            $product = (array)$product;

            if (empty($product)
                || !Configuration::get('SPM_IS_MODULE_ACTIVE')
                || !Configuration::get('SPM_ALLOW_IMPORT')) {
                return;
            }

            // Get image
            $images = $params['product']->getWsImages();

            if (sizeof($images) > 0) {
                $image = new Image($images[0]['id']);
                $image_url = _PS_BASE_URL_ . _THEME_PROD_DIR_ . $image->getExistingImgPath() . ".jpg";
            }

            //Get price
            if (!empty($product['specificPrice'])) {
                //Get discount
                if ($product['specificPrice']['reduction_type'] == 'percentage') {
                    $discount = '-' . (($product['specificPrice']['reduction']) * 100 | round(0)) . '%';
                } elseif ($product['specificPrice']['reduction_type'] == 'amount') {
                    $discount = '-' . (($product['specificPrice']['reduction']) * 100
                            | round(0)) . $this->context->currency->iso_code;
                } else {
                    $discount = '-0%';
                }
                $price = round($params['product']->getPriceWithoutReduct(), 2);
                $special_price = round($params['product']->getPublicPrice(), 2);
            } else {
                $price = round($params['product']->getPublicPrice(), 2);
                $special_price = round($params['product']->getPublicPrice(), 2);
                $discount = '-0%';
            }
        } else {
            if (empty($product)
                || !Configuration::get('SPM_IS_MODULE_ACTIVE')
                || !Configuration::get('SPM_ALLOW_IMPORT')) {
                return;
            }

            // Get image
            $image_url = $product['images']['0']['large']['url'];

            if ($product['images']['0']['large']['url']) {
                $image_url = $product['images']['0']['large']['url'];
            }

            //Get discount
            if ($product['has_discount']) {
                if ($product['discount_type'] == 'percentage') {
                    $discount = $product['discount_percentage'];
                }
                if ($product['discount_type'] == 'amount') {
                    $discount = $product['discount_amount_to_display'];
                }
            } else {
                $discount = '-0%';
            }
            //Get price
            $price = $product['regular_price_amount'];
            $special_price = $product['price_amount'];
        }

        $options = array(
            'name' => $product['name'],
            "image" => $image_url,
            "description" => str_replace(
                PHP_EOL,
                '',
                strip_tags($product['description'])
            ),
            "price" => $price,
            "special_price" => $special_price,
            "currency" => $this->context->currency->iso_code,
            "quantity" => $product['minimal_quantity'],
            "discount" => $discount
        );


        $this->context->smarty->assign('product', $options);

        return $this->context->smarty->fetch($this->views_url . '/templates/front/product_import.tpl');
    }

    /**
     * @param string $channel
     * @param $subscriber
     * @return string|null
     */
    public function getChannelStatus($channel, $subscriber)
    {
        $this->logDebug(__FUNCTION__);
        $this->logDebug($subscriber->status);
        return $subscriber->status->$channel ? $subscriber->status->$channel : false;
    }

    /**
     * @param $customer
     * @return array
     */
    public function getCustomFields($customer)
    {
        $customerFields = [];
        $possibleFields = ['birthday', 'gender'];

        foreach ($possibleFields as $field) {
            $configValue = Configuration::get('SPM_CUSTOMER_FIELD_' . Tools::strtoupper($field));
            if ($configValue) {
                switch ($field) {
                    case 'birthday':
                        $customerFields[$configValue] = $customer->birthday;
                        break;
                    case 'gender':
                        $value = $customer->id_gender == 1 ? $this->l('Male') : $this->l('Female');
                        $customerFields[$configValue] = $value;
                        break;
                }
            }
        }

        return $customerFields;
    }

    /**
     * Generates Configuration link in modules selection view
     */
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminSenderAutomatedEmails'));
    }

    /**
     * CustomersExport
     * @return array
     */
    public function syncList()
    {
        try {
            $customersRequirements = Db::getInstance()->executeS("SELECT C.email,firstname,lastname FROM 
                " . _DB_PREFIX_ . "customer C 
                INNER JOIN " . _DB_PREFIX_ . "orders O on C.id_customer = O.id_customer
                INNER JOIN " . _DB_PREFIX_ . "order_detail OD on O.id_order = OD.id_order");

            if (!empty($customersRequirements)) {
                $stringCustomers = $this->recursiveImplode($customersRequirements);
                $customersExport = new CustomersExport(Configuration::get('SPM_API_KEY'));
                return $customersExport->textImport($stringCustomers, $customersRequirements);
            }

            return [
                'success' => true,
                'message' => 'No customers to be exported',
            ];

        } catch (PrestaShopDatabaseException $e) {
            $data = [
                'success' => false,
                'message' => $e ? $e->getMessage() : 'Unexpected error',
            ];
            return $data;
        }
    }

    /**
     * @param array $array
     * @param $glue
     * @param $include_keys
     * @return string
     */
    public function recursiveImplode(array $array, $glue = ',', $include_keys = false)
    {
        $glued_string = '';

        // Recursively iterates array and adds key/value to glued string
        array_walk_recursive($array, function ($value, $key) use ($glue, $include_keys, &$glued_string) {
            $include_keys and $glued_string .= $key . $glue;

            if ($key == 'lastname') {
                $glued_string .= $value;
                $glued_string .= PHP_EOL;
            } else {
                $glued_string .= $value . $glue;
            }
        });
        // Removes last $glue from string
        Tools::strlen($glue) > 0 and $glued_string = Tools::substr($glued_string, 0, -Tools::strlen($glue));

        $result = str_replace('{"subscribers":', '', $glued_string);
        return (string)$result;
    }

    /**
     * Add Module Settings tab to the sidebar
     */
    private function addTabs()
    {
        $langs = Language::getLanguages();

        $newTab = new Tab();
        $newTab->class_name = "AdminSenderAutomatedEmails";
        $newTab->module = "senderautomatedemails";
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $newTab->icon = "mail";
        }

        $newTab->position = 1;

        $newTab->id_parent = Tab::getIdFromClassName('CONFIGURE');
        $newTab->active = 1;
        foreach ($langs as $l) {
            $newTab->name[$l['id_lang']] = $this->l('Sender.net');
        }
        $newTab->save();
        return true;
    }

    /**
     * This method handles debug message logging
     * to a file
     *
     * @param string $message
     */
    public function logDebug($message)
    {
        if ($this->debug) {
            if (!$this->debugLogger) {
                $this->debugLogger = new FileLogger(0);
                $logFolder = '/log/sender_automated_emails_logs_';
                $this->debugLogger->setFilename($this->module_path . $logFolder . date('Ymd') . '.log');
            }
            $this->debugLogger->logDebug($message);
        }
    }

    /**
     * Get Sender API Client instance
     * and make sure that everything is in order
     *
     * @return object SenderApiClient
     */
    public function senderApiClient()
    {
        // Create new instance if there is none
        if (!$this->senderApiClient) {
            $this->senderApiClient = new SenderApiClient();
            $this->senderApiClient->setApiKey(Configuration::get('SPM_API_KEY'));
        }

        // Check if key is valid
        if (!$this->senderApiClient->checkApiKey()) {
            // Disable module
            $this->disableModule();

            return $this->senderApiClient;
        }

        return $this->senderApiClient;
    }
}
