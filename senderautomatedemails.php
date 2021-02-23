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

    /**
     * Indicate here the functions which are not longer available on newest versions
     * @var array
     */
    private $deprecatedFunctions = array();

    private $debug = true;

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
        $this->name = 'senderautomatedemails';
        $this->tab = 'emailing';
        $this->version = '2.0.0';
        $this->author = 'Sender.net';
        $this->author_uri = 'https://www.sender.net/';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array(
            'min' => '1.6.0.5',
            'max' => _PS_VERSION_
        );
        $this->bootstrap = true;
        $this->module_key = 'ae9d0345b98417ac768db7c8f321ff7c'; //Got after validating the module

        $this->views_url = _PS_ROOT_DIR_ . '/' . basename(_PS_MODULE_DIR_) . '/' . $this->name . '/views';
        $this->module_url = __PS_BASE_URI__ . basename(_PS_MODULE_DIR_) . '/' . $this->name;
        $this->images_url = $this->module_url . '/views/img/';
        $this->module_path = _PS_ROOT_DIR_ . $this->module_url;

        parent::__construct();

        $this->displayName = $this->l('Sender.net Automated Emails');
        $this->description = $this->l('All you need for your email marketing in one tool.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        $this->loadDefaultSettings();

        #deprecated_function => new_function_to_use
        #Issue not giving error on deprecated, functions still available
        $this->deprecatedFunctions = [
            'getIdFromClassName' => 'findOneIdByClassName',
            'getFormatedName' => 'getFormattedName'
        ];
//        $tabsArray[] = Tab::getIdFromClassName("AdminSenderAutomatedEmails");
    }

    public function loadDefaultSettings()
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
            'SPM_CUSTOMER_FIELD_FIRSTNAME' => 0,
            'SPM_CUSTOMER_FIELD_LASTNAME' => 0,
            'SPM_CUSTOMER_FIELD_LOCATION' => 0,
            'SPM_CUSTOMER_FIELD_BIRTHDAY_ID' => 0,
            'SPM_CUSTOMER_FIELD_GENDER_ID' => 0,
            'SPM_CUSTOMER_FIELD_PARTNER_OFFERS_ID' => 0,
            'SPM_SENDERAPP_SYNC_LIST_ID' => 0,
            'SPM_SENDERAPP_RESOURCE_KEY_CLIENT' => 0,
        );
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
            || !$this->registerHook('actionCartSummary')
            || !$this->registerHook('displayHeader')
            || !$this->registerHook('actionObjectCartUpdateAfter') // Getting it on all pages
            || !$this->registerHook('actionCustomerAccountAdd')  //Adding customer and tracking the customer track
            || !$this->registerHook('actionCustomerAccountUpdate')
            || !$this->registerHook('actionAuthentication')
            || !$this->registerHook('actionObjectCustomerUpdateAfter')
            || !$this->registerHook('displayFooterProduct')) {
            return false;
        }

        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            if (!$this->registerHook('displayFooterBefore')
                || !$this->registerHook('additionalCustomerFormFields')
            ) {
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
        $this->logDebug('UNISTALLING');
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
     * Reset all Sender.net related settings
     *
     * @return void
     */
    private function disableModule()
    {
        Configuration::updateValue('SPM_API_KEY', '');
        Configuration::updateValue('SPM_IS_MODULE_ACTIVE', 0);
        Configuration::updateValue('SPM_ALLOW_FORMS', '');
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
        if (!Configuration::get('SPM_IS_MODULE_ACTIVE')){
            $this->logDebug('Module not active');
            return false;
        }
        $this->logDebug('Module is active');
        return true;
    }

    public function senderDisplayFooter()
    {
        if ((!Configuration::get('SPM_ALLOW_FORMS'))
            || Configuration::get('SPM_FORM_ID') == $this->defaultSettings['SPM_FORM_ID']) {
            return;
        }

        $options = array(
            'showForm' => false
        );

        $form = $this->senderApiClient()->getFormById(Configuration::get('SPM_FORM_ID'));
        #Check if form is disabled or pop-up
        if (!$form->is_active || $form->type != 'embed') {
            return;
        }

        if ($form->type === 'embed') {
            $embedHash = $form->settings->embed_hash;
        }
        // Add forms
        if (Configuration::get('SPM_ALLOW_FORMS')) {
            $options['formUrl'] = isset($form->settings->resource_path) ? $form->settings->resource_path : '';
            $options['showForm'] = true;
            $options['embedForm'] = isset($embedHash);
            $options['embedHash'] = isset($embedHash) ? $embedHash : '';
        }

        $this->context->smarty->assign($options);
        return $this->context->smarty->fetch($this->views_url . '/templates/front/form.tpl');
    }

    /**
     * Showing the form on all pages
     * If embed will append to before the footer
     * Option available for 1.7
     */
    public function hookDisplayFooterBefore()
    {
        $this->logDebug('hookDisplayFooterBefore');
        if (!$this->isModuleActive()){
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
        $this->logDebug('hookDisplayFooter');
        if (!$this->isModuleActive()){
            return;
        }
        return $this->senderDisplayFooter();
    }

    public function hookDisplayHeader()
    {
        $this->logDebug('hookDisplayHeader');
        if (!$this->isModuleActive()){
            return;
        }

        if (!Configuration::get('SPM_API_KEY') || !Configuration::get('SPM_SENDERAPP_RESOURCE_KEY_CLIENT')) {
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
			  sender('{$resourceKey}');
			</script>
			";

        $html .= "<script>
			  sender('trackVisitors')
			</script>";
        return $html;
    }

    /**
     * Here we handle new signups, we fetch customer info
     * then if enabled tracking and user has opted in for
     * a newsletter we add him to the prefered list
     *
     * @param array $context
     * @return array $context
     */
    public function hookactionCustomerAccountAdd($context)
    {
        $this->logDebug('#hookactionCustomerAccountAdd');
        if (!$this->isModuleActive()){
            return;
        }

        if (!Validate::isLoadedObject($context['newCustomer'])) {
            $this->logDebug('object customer not loaded');
            return;
        }

        $customer = $this->context->customer;

        #Checking if we should go forward
        if (!$customer->newsletter) {
            if (!Configuration::get('SPM_ALLOW_TRACK_CARTS')) {
                $this->logDebug('No action required');
                return;
            }
        }

        try {
            $visitorRegistration = [
                'email' => $customer->email,
                'firstname' => $customer->firstname,
                'lastname' => $customer->lastname,
                'visitor_id' => $_COOKIE['sender_site_visitor'],
                'list_id' => Configuration::get('SPM_GUEST_LIST_ID'),
            ];
            if (Configuration::get('SPM_GUEST_LIST_ID') != $this->defaultSettings['SPM_GUEST_LIST_ID']) {
                $visitorRegistration['list_id'] = Configuration::get('SPM_GUEST_LIST_ID');
            }
            $this->senderApiClient()->visitorRegistered($visitorRegistration);

            #Checking the status of the subscriber. On unsubscribed we wont continue
            $subscriber = $this->checkSubscriberState($customer->email);

            #Handling subscriber deleted
            if (!$subscriber) {
                $this->logDebug('NO subscriber');
                return;
            }

            $customFields = $this->getCustomFields($customer);

            if (!empty($customFields)) {
                $this->senderApiClient()->addFields($subscriber->id, $customFields);
                $this->logDebug('Adding fields to this recipient: ' . json_encode($customFields));
            }

            $this->logDebug('#hookactionCustomerAccountAdd END');
        } catch (Exception $e) {
            $this->logDebug('Error hookactionCustomer ' . json_encode($e->getMessage()));
        }
    }

    public function hookActionAuthentication()
    {
        $this->logDebug('hookActionAuthentication');
        if (!$this->isModuleActive()){
            return;
        }

        $customer = $this->context->customer;
        $this->formVisitor($customer);
    }

    /**
     * Add an extra FormField to ask for newsletter registration.
     *
     * @param $params
     *
     * @return bool
     */
    public function hookAdditionalCustomerFormFields($params)
    {
        $this->logDebug('hookAdditionalCustomerFormFields');
        if (!$this->isModuleActive()){
            return;
        }

        if (!Configuration::get('SPM_ALLOW_TRACK_CARTS')){
            $this->logDebug('track carts not active');
            return;
        }

        if (Module::isEnabled('ps_emailsubscription')) {
            $this->logDebug('Using the newsletter checkbox from newsletter plugin');
            return;
        }
        $label = $this->trans(
            'Sign up for our newsletter[1][2]%conditions%[/2]',
            array(
                '[1]' => '<br>',
                '[2]' => '<em>',
                '%conditions%' => Configuration::get('NW_CONDITIONS', $this->context->language->id),
                '[/2]' => '</em>',
            ),
            'Modules.Emailsubscription.Shop'
        );

        return array(
            (new FormField())
                ->setName('newsletter')
                ->setType('checkbox')
                ->setLabel($label),);
    }

    /**
     * Use this hook in order to be sure
     * whether we have captured the latest cart info
     * it fires when user uses instant checkout
     * or logged in user goes to checkout page
     *
     * @param object $context
     * @return object $context
     */
    public function hookActionCartSummary($context)
    {
        $this->logDebug('hookActionCartSummary');

        if (!$this->isModuleActive()){
            return;
        }

        // Validate if we should
        if (!Configuration::get('SPM_ALLOW_TRACK_NEW_SIGNUPS') || !Configuration::get('SPM_ALLOW_TRACK_CARTS')) {
            $this->logDebug('Track cart option is not enable for Guest/New customers');
            return;
        }

        if (version_compare(_PS_VERSION_, '1.6.1.10', '>=')) {
            $cookie = $context['cookie']->getAll();
        } else {
            $cookie = $context['cookie']->getFamily($context['cookie']->id);
        }

        // Validate if we should track
        if (!isset($cookie['email'])
            || !Validate::isLoadedObject($context['cart'])
            || (!Configuration::get('SPM_ALLOW_TRACK_CARTS')
                && isset($cookie['logged']) && $cookie['logged'])
            || (isset($cookie['is_guest']) && $cookie['is_guest'])
            || !Configuration::get('SPM_IS_MODULE_ACTIVE')
            || $this->context->controller instanceof OrderController != true) {
            $this->logDebug('hookActionCartSummary stop');
            return;
        }

        $this->logDebug('#hookActionCartSummary START');
        $this->syncCart($context['cart'], $cookie);

        $this->logDebug('#hookActionCartSummary END');

        return $context;
    }

    /**
     * Use this hook only if we have customer email
     * @return object
     */
    public function hookActionObjectCartUpdateAfter($context)
    {
        $this->logDebug('hookActionObjectCartUpdateAfter');

        if (!$this->isModuleActive()){
            return;
        }

        if (!Validate::isLoadedObject($context['cart']) || !Configuration::get('SPM_ALLOW_TRACK_CARTS')
            || !isset($_COOKIE['sender_site_visitor'])) {
            $this->logDebug('Cart object not loaded || Module not active || Cart tracking not active 
            || Cookies not set up');
            return;
        }

        if (version_compare(_PS_VERSION_, '1.6.1.10', '>=')) {
            $cookie = $context['cookie']->getAll();
        } else {
            $cookie = $context['cookie']->getFamily($context['cookie']->id);
        }

        if ($this->context->cookie->__get('deleted-cart') === true){
            $this->context->cookie->__set('deleted-cart', false);
            return;
        }

        if ($this->context->cookie->__isset('captured-cart') && !empty($this->context->cookie->__get('captured-cart'))) {
            if ($this->compareCartDateTime($this->context->cookie->__get('captured-cart'))) {
                $this->logDebug('Avoiding duplicating logic of prestashop');
                return;
            }
            $this->logDebug('comparasion of cart time should not stop');
        }

        $this->syncCart($context['cart'], $cookie);
    }

    /**
     * Sync current cart with sender cart track
     * @param $cart
     * @param $cookie
     */
    private function syncCart($cart, $cookie)
    {
        $this->logDebug('SYNC-CART');

        if (isset($cookie['email']) && !empty($cookie['email'])){
            $email = $cookie['email'];
        }else{
            $email = '';
        }

        $cartData = $this->mapCartData($cart, $email, $_COOKIE['sender_site_visitor']);

        if (isset($cartData) && !empty($cartData['products'])){
            $a = $this->senderApiClient()->trackCart($cartData);
            $this->context->cookie->__set('captured-cart', strtotime(date('Y-m-d H:i:s')));
            $this->context->cookie->write();
            $this->logDebug(json_encode($a));
        }else{
            $b = $this->senderApiClient()->cartDelete(Configuration::get('SPM_SENDERAPP_RESOURCE_KEY_CLIENT'), $cart->id);
            $this->context->cookie->__set('deleted-cart', true);
            $this->context->cookie->write();
            $this->logDebug(json_encode($b));
        }
    }

    /**
     * Helper method to
     * generate cart array for Sender api call
     * It also retrieves products with images
     *
     * @param object $cart
     * @param string $email
     * @param $visitorId
     * @return array
     */
    private function mapCartData($cart, $email, $visitorId)
    {
        $this->logDebug('MAP-CART-DATA');
        $cartHash = $cart->id;

        $data = array(
            "email" => $email,
            'visitor_id' => $visitorId,
            "external_id" => $cartHash,
            "url" => _PS_BASE_URL_ . __PS_BASE_URI__
                . 'index.php?fc=module&module='
                . $this->name
                . "&controller=recover&hash={$cartHash}",
            "currency" => $this->context->currency->iso_code,
            "order_total" => (string)$cart->getOrderTotal(),
            "products" => array()
        );

        $products = $cart->getProducts();
        if (!$products || empty($products)){
            return;
        }
        foreach ($products as $product) {
            $Product = new Product($product['id_product']);

            $price = $Product->getPrice(true, null, 2);

            $prod = array(
                'name' => $product['name'],
                'sku' => $product['reference'],
                'price' => (string)$price,
                'price_display' => $price . ' ' . $this->context->currency->iso_code,
                'qty' => $product['cart_quantity'],
                'image' => $this->context->link->getImageLink(
                    $product['link_rewrite'],
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
     * @return false
     */
    private function formVisitor($customer)
    {
        $this->logDebug('FORM-VISITOR');
        $visitorRegistration = [
            'email' => $customer->email,
            'firstname' => $customer->firstname,
            'lastname' => $customer->lastname,
            'visitor_id' => $_COOKIE['sender_site_visitor'],
            'list_id' => Configuration::get('SPM_GUEST_LIST_ID'),
        ];

        #Check if has any orders
        if($this->checkOrderHistory($customer->id)) {
            if (Configuration::get('SPM_CUSTOMERS_LIST_ID') != $this->defaultSettings['SPM_CUSTOMERS_LIST_ID']) {
                $visitorRegistration['list_id'] = Configuration::get('SPM_CUSTOMERS_LIST_ID');
            }
        }else{
            $visitorRegistration['list_id'] = Configuration::get('SPM_GUEST_LIST_ID');
        }

        $this->senderApiClient()->visitorRegistered($visitorRegistration);

        #Checking the status of the subscriber. On unsubscribed we wont continue
        $subscriber = $this->checkSubscriberState($customer->email);

        #Handling subscriber deleted
        if (!$subscriber) {
            $this->logDebug('NO subscriber');
            return false;
        }

        $customFields = $this->getCustomFields($customer);

        if (!empty($customFields)) {
                $this->senderApiClient()->addFields($subscriber->id, $customFields);
                $this->logDebug('Adding fields to this recipient: ' . json_encode($customFields));
            }

        return $subscriber;
    }

    private function compareCartDateTime($dateAdd, $duration = 1)
    {
        $this->logDebug('compareCartDateTime');
        $currentTime = strtotime(date('Y-m-d H:i:s'));
        $dateCartAdded = $dateAdd + $duration;

        return $dateCartAdded >= $currentTime; // true = avoid // false = should track logic
    }

    /**
     * Hook into order confirmation. Mark cart as converted since order is made.
     * Keep in mind that it doesn't mean that payment has been made
     * @param object $context
     * @return object $context
     */
    public function hookDisplayOrderConfirmation($context)
    {
        $this->logDebug('hookDisplayOrderConfirmation');
        #First check if we should capture these details
        if (!$this->isModuleActive()){
            return;
        }

        if (!Validate::isLoadedObject($context['cart']) || !Configuration::get('SPM_ALLOW_TRACK_CARTS')
            || !isset($_COOKIE['sender_site_visitor'])) {
            $this->logDebug('Cart object not loaded || Module not active || Cart tracking not active 
            || Cookies not set up');
            return;
        }

        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $order = $context['order'];
        } else {
            $order = $context['objOrder'];
        }

        try {
            $this->logDebug('#hookActionValidateOrder START');
            $dataConvert = [
                'resource_key' => Configuration::get('SPM_SENDERAPP_RESOURCE_KEY_CLIENT'),
                'email' => $this->context->customer->email,
                'firstname' => $this->context->customer->firstname,
                'lastname' => $this->context->customer->lastname,
            ];

            if (Configuration::get('SPM_CUSTOMERS_LIST_ID') != $this->defaultSettings['SPM_CUSTOMERS_LIST_ID']) {
                $dataConvert['list_id'] = Configuration::get('SPM_CUSTOMERS_LIST_ID');
            }

            $convertCart = $this->senderApiClient()->cartConvert($dataConvert, $order->id_cart);

            $this->logDebug('Cart convert response: '
                . json_encode($convertCart));
        } catch (Exception $e) {
            $this->logDebug($e->getMessage());
        }
    }

    /**
     * Here we handle customer info where he update his account
     * and we delete or add him to the prefered list
     *
     * @param array $context
     * @return array $context
     */
    public function hookactionObjectCustomerUpdateAfter($context)
    {
        $this->logDebug('hookactionObjectCustomerUpdateAfter');

        if (!$this->isModuleActive()){
            return;
        }

        $customer = $context['object'];

        return $this->hookactionCustomerAccountUpdate($customer);
    }

    /**
     * Here we handle customer info where he update his account
     * and we delete or add him to the prefered list
     *
     * @param array $context
     * @param bool $interface
     * @return array $context
     */
    public function hookactionCustomerAccountUpdate($customer)
    {
        $this->logDebug('hookactionCustomerAccountUpdate');
        if (!$this->isModuleActive()){
            return;
        }
        //Validate if we should
        if (!Validate::isLoadedObject($customer)) {
            $this->logDebug('Exiting update customer');
            return true;
        }
        #Checking if we should go forward
        if (!$customer->newsletter) {
            if (!Configuration::get('SPM_ALLOW_TRACK_CARTS')) {
                $this->logDebug('No action required');
                return;
            }
        }
        #Registered customer coming to site
        #Set up the visitorRegistration thing
        try {
            $visitorRegistration = [
                'email' => $customer->email,
                'firstname' => $customer->firstname,
                'lastname' => $customer->lastname,
                'visitor_id' => $_COOKIE['sender_site_visitor'],
            ];

            #Check if has any orders
            if($this->checkOrderHistory($customer->id)) {
                if (Configuration::get('SPM_CUSTOMERS_LIST_ID') != $this->defaultSettings['SPM_CUSTOMERS_LIST_ID']) {
                    $visitorRegistration['list_id'] = Configuration::get('SPM_CUSTOMERS_LIST_ID');
                }
            }else{
                $visitorRegistration['list_id'] = Configuration::get('SPM_GUEST_LIST_ID');
            }

            $this->senderApiClient()->visitorRegistered($visitorRegistration);

            $newsletter = $customer->newsletter ? true : false;
            $subscriber = $this->checkSubscriberState($customer->email, $newsletter);

            $this->logDebug('Subscriber variable');
            $this->logDebug(json_encode($subscriber));
            #Handling subscriber deleted
            if (!$subscriber) {
                $this->logDebug('Subscriber was deleted');
                return;
            }

            $customFields = $this->getCustomFields($customer);

            if (!empty($customFields)) {
                $this->senderApiClient()->addFields($subscriber->id, $customFields);
                $this->logDebug('Adding fields to this recipient: ' . json_encode($customFields));
            }
        } catch (Exception $e) {
            $this->logDebug('Error hook hookactionCustomerAccountUpdate' . json_encode($e->getMessage()));
        }

        $this->logDebug('#hookactionCustomerAccountUpdate END');
        return true;
    }

    private function checkOrderHistory($customerId)
    {
        $customerOrders = Order::getCustomerOrders($customerId);
        if ($customerOrders && count($customerOrders) > 0){
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
        $this->logDebug('hookDisplayFooterProduct');
        if (!$this->isModuleActive()){
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
     * @param $email
     * @param false $reactivate
     * @return false
     */
    private function checkSubscriberState($email, $reactivate = false)
    {
        if ($isSubscriber = $this->senderApiClient()->isAlreadySubscriber($email)) {
            if (!$isSubscriber->unsubscribed) {
                return $isSubscriber;
            } else {
                if ($reactivate) {
                    $this->senderApiClient()->reactivateSubscriber($isSubscriber->id);
                    $this->logDebug('Subscriber reactivated');
                    return $isSubscriber;
                }
                $isSubscriber->onlyUpdateFields = true;
                return $isSubscriber;
            }
        }
        return false;
    }

    /**
     * @param $context
     * @return array
     */
    public function getCustomFields($customer)
    {
        $fields = [];

        (Configuration::get('SPM_CUSTOMER_FIELD_BIRTHDAY_ID')) != 0
            ? $fields[Configuration::get('SPM_CUSTOMER_FIELD_BIRTHDAY_ID')] = $customer->birthday : false;
        (Configuration::get('SPM_CUSTOMER_FIELD_GENDER_ID')) != 0
            ? $fields[Configuration::get('SPM_CUSTOMER_FIELD_GENDER_ID')] =
            ($customer->id_gender == 1 ? $this->l('Male') : $this->l('Female')) : false;

        return $fields;
    }

    public function updateSubscriber($subscriber)
    {
        $this->senderApiClient()->updateSubscriber($subscriber);
    }

    /**
     * For customers that return to the site
     * Syncs recipient with the proper Sender.net list
     *
     * @param $recipient
     * @param $subscriberId
     * @param $tagId
     * @return void
     */
    private function syncRecipient($recipient, $subscriberId, $tagId)
    {
        $this->logDebug('syncRecipient hook');
        // Validate if we should
        if (!Validate::isLoadedObject($this->context->customer)
            || (!Configuration::get('SPM_ALLOW_TRACK_NEW_SIGNUPS')
                && !Configuration::get('SPM_ALLOW_TRACK_CARTS'))
            || !Configuration::get('SPM_IS_MODULE_ACTIVE')) {
            return false;
        }

        $this->senderApiClient()->updateSubscriber($recipient, $subscriberId);
        $addToListResult = $this->senderApiClient()->addToList($subscriberId, $tagId);

        return $addToListResult;
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
    private function syncList()
    {
        $this->logDebug('syncList');
        try {
            $customersRequirements = Db::getInstance()->executeS('
                SELECT email, firstname, lastname
                       FROM ' . _DB_PREFIX_ . 'customer
                WHERE newsletter = 1');
            if (!empty($customersRequirements)) {
                $stringCustomers = $this->recursiveImplode($customersRequirements);
                $customersExport = new CustomersExport(Configuration::get('SPM_API_KEY'));
                return $customersExport->textImport($stringCustomers, $customersRequirements);
            }
        } catch (PrestaShopDatabaseException $e) {
            $data = [
                'success' => false,
                'message' => $e ? $e->getMessage() : 'Unexpected error',
            ];
            return $data;
        }
    }

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
        $this->logDebug(json_encode($newTab));
        foreach ($langs as $l) {
            $newTab->name[$l['id_lang']] = $this->l('Sender.net Settings');
        }
        $newTab->save();
        return true;
    }

    /**
     * @return string Status message
     * @todo  Optimize for huge lists
     *
     * Get subscribers from ps_newsletter table
     * and sync with sender
     *
     */
    public function syncOldNewsletterSubscribers($listId)
    {
        $error = $this->l("We couldn't find any subscribers @newsletterblock module.");

        if (!Configuration::get('SPM_IS_MODULE_ACTIVE')) {
            return $error;
        }

        $oldSubscribers = array();

        // We cannot be sure whether the table exists
        try {
            $oldSubscribers = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'newsletter');
            $oldCustomers = Db::getInstance()->executeS('
                SELECT email, firstname, lastname, date_add, newsletter, optin 
                FROM ' . _DB_PREFIX_ . 'customer 
                WHERE newsletter = 1 OR optin = 1');
        } catch (PrestaShopDatabaseException $e) {
            $this->logDebug('PDO Exception: '
                . json_encode($e));
            return $error;
        }

        $this->logDebug('Syncing old newsletter subscribers');
        $this->logDebug('Selected list: ' . $listId);

        if (empty($oldSubscribers)) {
            return $error;
        }

        foreach ($oldSubscribers as $subscriber) {
            $this->senderApiClient()->addToList(array(
                'email' => $subscriber['email'],
                'created' => $subscriber['newsletter_date_add'],
                'active' => $subscriber['active'],
                'source' => $this->l('Newsletter')
            ), $listId);
            $this->logDebug('Added newsletter subscriber: ' . $subscriber['email']);
        }

        foreach ($oldCustomers as $subscriber) {
            $this->senderApiClient()->addToList(array(
                'email' => $subscriber['email'],
                'firstname' => $subscriber['firstname'],
                'lastname' => $subscriber['lastname'],
                'created' => $subscriber['date_add'],
                'active' => 1,
                'source' => $this->l('Customer')
            ), $listId);
            $this->logDebug('Added newsletter subscriber: ' . $subscriber['email']);
        }

        $this->logDebug('Sync finished.');
        return $this->l('Successfully synced!');
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
     * @return object SendersenderApiClient
     * @todo  described bellow
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
            $this->logDebug('senderApiClient(): checkApiKey failed.');
            $this->logDebug('Key used: ' . Configuration::get('SPM_API_KEY'));
            // Disable module
            $this->disableModule();

            return $this->senderApiClient;
        }

        return $this->senderApiClient;
    }
}
