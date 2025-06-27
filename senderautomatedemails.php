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
require_once 'lib/Sender/SenderExport.php';
require_once(_PS_CONFIG_DIR_ . "/config.inc.php");

class SenderAutomatedEmails extends Module
{
    const CART_STATE_CONFIRMED = "confirmed";
    const CART_STATE_UPDATED = "updated";
    const ORDER_PAID = 'paid';
    const ORDER_UNPAID = 'unpaid';
    const ORDER_SHIPPED = 'shipped';

    /**
     * Default settings array
     * @var array
     */
    private $defaultSettings = [
        'SPM_API_KEY' => '',
        'SPM_IS_MODULE_ACTIVE' => 0,
        'SPM_LAST_ACTIVE_ACCOUNT' => 0,
        'SPM_ALLOW_FORMS' => 0,
        'SPM_ALLOW_IMPORT' => 0,
        'SPM_ALLOW_TRACK_NEW_SIGNUPS' => 0,
        'SPM_ALLOW_TRACK_CARTS' => 0,
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
        'SPM_SENDERAPP_STORE_ID' => null
    ];

    private $debug = false;
    public $senderApiClient = null;

    public $views_url;
    public $module_url;
    public $module_path;

    /**
     * Contructor function
     *
     */
    public function __construct()
    {
        $this->senderDetails();
        $this->senderDirectories();

        parent::__construct();
        $this->bootstrap = true;
    }

    public function senderDetails()
    {
        $this->name = 'senderautomatedemails';
        $this->tab = 'emailing';
        $this->version = '3.7.5';
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

    public function senderDirectories()
    {
        $this->views_url = _PS_ROOT_DIR_ . '/' . basename(_PS_MODULE_DIR_) . '/' . $this->name . '/views';
        $this->module_url = __PS_BASE_URI__ . basename(_PS_MODULE_DIR_) . '/' . $this->name;
        $this->module_path = _PS_ROOT_DIR_ . $this->module_url;
    }

    /**
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

        if (
            !$this->registerHook('displayBackOfficeHeader')
            || !$this->registerHook('displayOrderConfirmation')
            || !$this->registerHook('registerUnsubscribedWebhook')
            || !$this->registerHook('displayHeader')
            || !$this->registerHook('actionObjectCartUpdateAfter') // Getting it on all pages
            || !$this->registerHook('actionCustomerAccountAdd')  //Adding customer and tracking the customer track
            || !$this->registerHook('actionCustomerAccountUpdate')
            || !$this->registerHook('actionAuthentication')
            || !$this->registerHook('actionObjectNewsletterAddAfter')
            || !$this->registerHook('actionObjectCustomerUpdateAfter')
            || !$this->registerHook('displayFooterProduct')
            || !$this->registerHook('actionOrderHistoryAddAfter')
        ) {
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
     * @return bool
     */
    public function uninstall()
    {
        if (parent::uninstall()) {
            $this->senderApiClient()->removeStore();
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
        return (bool) Configuration::get('SPM_IS_MODULE_ACTIVE');
    }

    public function hookDisplayHeader()
    {
        if (!$this->isModuleActive()) {
            return;
        }

        if ((!Configuration::get('SPM_ALLOW_TRACK_CARTS') && !Configuration::get('SPM_ALLOW_FORMS'))
            || !Configuration::get('SPM_SENDERAPP_RESOURCE_KEY_CLIENT')
        ) {
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

        if (isset($this->context->cookie->visitorData)) {
            $visitorData = json_decode($this->context->cookie->visitorData, true);
            $this->context->smarty->assign('visitorData', $visitorData);
            $html .= $this->context->smarty->fetch($this->views_url . '/templates/front/trackVisitors.tpl');

            $currentTime = strtotime(date('Y-m-d H:i:s'));
            $dateVisitorAdded = $visitorData['visitor_added_time'] + 1;
            if ($dateVisitorAdded <= $currentTime) {
                $this->context->cookie->__unset('visitorData');
            }
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
        if (!$this->isModuleActive() || $this->isAdminContext()) {
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
        if (!$this->isModuleActive() || $this->isAdminContext()) {
            return;
        }
        return $this->senderDisplayFooter();
    }

    public function getConnectedClient()
    {
        $context = Context::getContext();
        if (isset($context->customer) && !empty($context->customer->email)){
            return true;
        }

        return false;
    }

    protected function isAdminContext()
    {
        return Tools::getValue('controller') && stripos(Tools::getValue('controller'), 'admin') !== false;
    }

    public function senderDisplayFooter()
    {
        $options = [
            'showForm' => false,
            'formUrl' => '',
            'embedHash' => ''
        ];

        if (!Configuration::get('SPM_ALLOW_FORMS') || !Configuration::get('SPM_SENDERAPP_RESOURCE_KEY_CLIENT')) {
            return;
        }

        $cacheTTL = 86400;
        $cachedForm = Configuration::get('SPM_CACHED_FORM');
        $lastUpdated = (int) Configuration::get('SPM_CACHED_FORM_UPDATED');
        $cacheIsExpired = !$cachedForm || !$lastUpdated || (time() - $lastUpdated > $cacheTTL);

        if ($cacheIsExpired) {
            $form = $this->senderApiClient()->getFormById(Configuration::get('SPM_FORM_ID'));

            if (!$form || !$form->is_active || $form->type !== 'embed') {
                return;
            }

            $cachedData = [
                'formUrl' => isset($form->settings->resource_path) ? $form->settings->resource_path : '',
                'embedHash' => isset($form->settings->embed_hash) ? $form->settings->embed_hash : ''
            ];

            Configuration::updateValue('SPM_CACHED_FORM', json_encode($cachedData));
            Configuration::updateValue('SPM_CACHED_FORM_UPDATED', time());
        } else {
            $cachedData = json_decode($cachedForm, true);

            if (!is_array($cachedData)) {
                $this->resetSenderFormCache();
                return;
            }
        }

        if (!isset($cachedData['formUrl']) || !isset($cachedData['embedHash'])) {
            return;
        }

        $options['showForm'] = true;
        $options['formUrl'] = $cachedData['formUrl'];
        $options['embedHash'] = $cachedData['embedHash'];

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
        if (!$this->isModuleActive() || !Validate::isLoadedObject($context['cart'])) {
            return;
        }

        if (!Configuration::get('SPM_ALLOW_TRACK_CARTS') || !$this->getConnectedClient()) {
            return;
        }

        if ($this->context->cookie->__get('sender-deleted-cart') === true) {
            $this->context->cookie->__set('sender-deleted-cart', false);
            return;
        }

        if (
            $this->context->cookie->__isset('sender-captured-cart')
            && !empty($this->context->cookie->__get('sender-captured-cart'))
        ) {
            if ($this->compareSenderDateTime($this->context->cookie->__get('sender-captured-cart'))) {
                return;
            }
        }

        $this->syncCart($context['cart']);
    }

    /**
     * Order status update hook
     * 
     * @param array $context
     * 
     * e.g
     * ```php
     * $context = ['order_history' => OrderHistory]
     * ```
     */
    public function hookActionOrderHistoryAddAfter($context)
    {
        if(!Configuration::get('SPM_ALLOW_TRACK_CARTS')) {
            return;
        }

        try {
            $order_id = isset($context["order_history"]->id_order) ? $context["order_history"]->id_order : null;

            if(!$order_id) {
                $this->logDebug("No order_id");
                return;
            }

            $order = new Order($order_id);

            if(!$order) {
                $this->logDebug("Order not found $order_id");
                return;
            }

            $cartStatus = $order->hasBeenShipped()
                ? self::ORDER_SHIPPED
                : ($order->hasBeenPaid() ? self::ORDER_PAID : self::ORDER_UNPAID);

            $data = [
                'resource_key' => Configuration::get('SPM_SENDERAPP_RESOURCE_KEY_CLIENT'),
                'order_id' => (string)$order_id,
                'cart_status' => $cartStatus
            ];

            $res = $this->senderApiClient()->cartUpdateStatus($data, $order->id_cart);

            $this->logDebug("CART_STATUS_UPDATE: " . json_encode(["data" => $data, "response" => $res]));

        } catch (Exception $e) {
            $this->logDebug($e->getMessage());
        }
    }

    /**
     * @param $cart
     * @return void
     */
    private function syncCart($cart)
    {
        $cartData = $this->mapCartData($cart);
        if (isset($cartData) && !empty($cartData['products'])) {

            $response = $this->senderApiClient()->trackCart($cartData);
            $responseMessage = $response && is_string($response) ? $response : json_encode($response ?: []);

            $this->logDebug("SyncCart: " . $responseMessage);

            $this->context->cookie->__set('sender-captured-cart', strtotime(date('Y-m-d H:i:s')));
            $this->context->cookie->write();
        } else {
            $this->senderApiClient()->cartDelete($cart->id);
            $this->context->cookie->__set('sender-deleted-cart', true);
        }
        $this->context->cookie->write();
    }

    /**
     * @param $cart
     * @return array|void
     */
    private function mapCartData($cart)
    {
        $this->logDebug(json_encode($cart));
        $cartHash = $cart->id;
        $data = array(
            "email" => $this->context->cookie->__get('email') ? $this->context->cookie->__get('email') : '',
            "external_id" => $cartHash,
            "url" => _PS_BASE_URL_ . __PS_BASE_URI__
                . 'index.php?fc=module&module='
                . $this->name
                . "&controller=recover&hash={$cartHash}",
            "currency" => $this->context->currency->iso_code,
            "order_total" => isset($cart->total_paid_tax_incl) ?
                $cart->total_paid_tax_incl : (string)$cart->getOrderTotal(),
            "store_id" => Configuration::get('SPM_SENDERAPP_STORE_ID'),
            "products" => array(),
            "resource_key" => Configuration::get('SPM_SENDERAPP_RESOURCE_KEY_CLIENT'),
        );

        $products = $cart->getProducts();
        if (!$products || empty($products)) {
            return;
        }
        $data['products'] = $this->mapProducts($products);

        return $data;
    }

    /**
     * Map Prestashop product into Sender compatible
     * 
     * @param array $products
     */
    private function mapProducts($products)
    {
        $result = [];

        foreach ($products as $product) {
            $Product = new Product($product['id_product']);
            $price = $Product->getPrice(true, null, 2);
            $linkRewrite = isset($product['link_rewrite'])
                ? $product['link_rewrite'] : implode('', $Product->link_rewrite);
            $imageType = method_exists("ImageType", "getFormattedName") ? ImageType::getFormattedName('home') : ImageType::getFormatedName('home');

            $prod = array(
                'name' => isset($product['name']) ? $product['name'] : $product['product_name'],
                'sku' => $product['reference'],
                'price' => (string)$price,
                'price_display' => $price . ' ' . $this->context->currency->iso_code,
                'qty' => isset($product['cart_quantity']) ? $product['cart_quantity'] : $product['product_quantity'],
                'image' => $this->context->link->getImageLink(
                    $linkRewrite,
                    $Product->getCoverWs(),
                    $imageType
                ),
                'description' => strip_tags($product['description_short']),
            );
            $result[] = $prod;
        }

        return $result;
    }

    /**
     * @param $customer
     * @param bool $saveFields
     * @param bool $addToList
     * @return void
     */
    private function formVisitor($customer, $saveFields = true, $addToList = true)
    {
        if (
            $this->context->cookie->__isset('sender-added-visitor')
            && !empty($this->context->cookie->__get('sender-added-visitor'))
        ) {
            if ($this->compareSenderDateTime($this->context->cookie->__get('sender-added-visitor'))) {
                return;
            }
        }

        $visitorRegistration = [
            'email' => $customer->email,
            'firstname' => $customer->firstname,
            'lastname' => $customer->lastname,
            'newsletter' => $customer->newsletter,
            'resource_key' => Configuration::get('SPM_SENDERAPP_RESOURCE_KEY_CLIENT'),
            'store_id' => Configuration::get('SPM_SENDERAPP_STORE_ID'),
        ];

        if ($addToList) {
            $visitorRegistration['list_id'] = Configuration::get('SPM_GUEST_LIST_ID');
        }

        if ($this->checkOrderHistory($customer->id)) {
            if (Configuration::get('SPM_CUSTOMERS_LIST_ID') != $this->defaultSettings['SPM_CUSTOMERS_LIST_ID']) {
                $visitorRegistration['list_id'] = Configuration::get('SPM_CUSTOMERS_LIST_ID');
            }
        }

        $this->senderApiClient()->createSubscriber($visitorRegistration);

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

        $this->context->cookie->__set('visitorData', json_encode([
            'email' => $customer->email,
            'resource_key' => Configuration::get('SPM_SENDERAPP_RESOURCE_KEY_CLIENT'),
            'visitor_added_time' => strtotime(date('Y-m-d H:i:s')),
        ]));
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

        if (
            !$order || !Configuration::get('SPM_ALLOW_TRACK_CARTS')
            || !$this->getConnectedClient()
        ) {
            return;
        }

        try {
            #Subscriber status check
            $subscriber = $this->senderApiClient()->isAlreadySubscriber(strtolower($this->context->customer->email));
            if (!$subscriber) {
                return;
            }

            $orderDetails = [
                'tax' => number_format(isset($order->total_paid_tax_incl) ? $order->total_paid_tax_incl - $order->total_paid_tax_excl : 0, 2),
                'total' => number_format(isset($order->total_paid_tax_incl) ? $order->total_paid_tax_incl : 0, 2),
                'discount' => number_format(isset($order->total_discounts) ? $order->total_discounts : 0, 2),
                'subtotal' => number_format(isset($order->total_products) ? $order->total_products : 0, 2),
                'order_date' => isset($order->date_add) ? date('d/m/Y', strtotime($order->date_add)) : null,
            ];

            $billingAddress = new Address((int)(isset($order->id_address_invoice) ? $order->id_address_invoice : 0));
            $billing = [
                'zip' => isset($billingAddress->postcode) ? $billingAddress->postcode : '',
                'city' => isset($billingAddress->city) ? $billingAddress->city : '',
                'state' => isset($billingAddress->state) ? $billingAddress->state : '',
                'address' => (isset($billingAddress->address1) ? $billingAddress->address1 : '') . ' ' .
                    (isset($billingAddress->address2) ? $billingAddress->address2 : ''),
                'country' => isset($billingAddress->country) ? $billingAddress->country : '',
                'last_name' => isset($billingAddress->lastname) ? $billingAddress->lastname : '',
                'first_name' => isset($billingAddress->firstname) ? $billingAddress->firstname : '',
            ];

            $shippingAddress = new Address((int)(isset($order->id_address_delivery) ? $order->id_address_delivery : 0));
            $shipping = [
                'zip' => isset($shippingAddress->postcode) ? $shippingAddress->postcode : '',
                'city' => isset($shippingAddress->city) ? $shippingAddress->city : '',
                'state' => isset($shippingAddress->state) ? $shippingAddress->state : '',
                'address' => (isset($shippingAddress->address1) ? $shippingAddress->address1 : '') . ' ' .
                    (isset($shippingAddress->address2) ? $shippingAddress->address2 : ''),
                'country' => isset($shippingAddress->country) ? $shippingAddress->country : '',
                'last_name' => isset($shippingAddress->lastname) ? $shippingAddress->lastname : '',
                'first_name' => isset($shippingAddress->firstname) ? $shippingAddress->firstname : '',
                'payment_method' => isset($order->payment) ? $order->payment : '',
                'shipping_charge' => number_format(isset($order->total_shipping) ? $order->total_shipping : 0, 2),
            ];


            $dataConvert = [
                'resource_key' => Configuration::get('SPM_SENDERAPP_RESOURCE_KEY_CLIENT'),
                'email' => strtolower($this->context->customer->email),
                'firstname' => $this->context->customer->firstname,
                'lastname' => $this->context->customer->lastname,
                'order_details' => $orderDetails,
                'shipping' => $shipping,
                'billing' => $billing,
                'order_id' => (string)$order->id,
                'phone' => !empty($billingAddress->phone_mobile) ? $billingAddress->phone_mobile : $billingAddress->phone,
            ];

            $list = Configuration::get('SPM_CUSTOMERS_LIST_ID');
            if ($list) {
                $dataConvert['list_id'] = $list;
            }

            $cartID = isset($idCart) ? $idCart : $order->id_cart;
            $dataConvert['store_id'] = Configuration::get('SPM_SENDERAPP_STORE_ID');

            $cartTracked = $this->senderApiClient()->cartConvert($dataConvert, $cartID);

            $this->logDebug("CONVERT_RES: " . json_encode($cartTracked));

            $cartTrackableData = $dataConvert;
            $cartTrackableData['external_id'] = $cartID;

            $options = [
                'cartState' => self::CART_STATE_CONFIRMED,
                'cartTrackableData' => $cartTrackableData
            ];
            
            $this->context->smarty->assign($options);
            return $this->context->smarty->fetch($this->views_url . '/templates/front/cart.tpl');
        } catch (Exception $e) {
            $this->logDebug("FAILED_CONVERT: " . $e->getMessage());
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

            if (
                empty($product)
                || !Configuration::get('SPM_IS_MODULE_ACTIVE')
                || !Configuration::get('SPM_ALLOW_IMPORT')
            ) {
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
            if (
                empty($product)
                || !Configuration::get('SPM_IS_MODULE_ACTIVE')
                || !Configuration::get('SPM_ALLOW_IMPORT')
            ) {
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
            $results = [];

            $results['customers'] = $this->syncCustomers();
            $results['products'] = $this->syncProducts();
            $results['orders'] = $this->syncOrders();

            $success = true;
            $messages = [];
            $totals = [];

            foreach ($results as $type => $result) {
                $success = $success && (!empty($result['success']) && $result['success']);
                $messages[] = $result['message'] ?? ucfirst($type) . ' export completed.';
                $totals[$type] = $result['total'] ?? 0;
            }

            return [
                'success' => $success,
                'message' => implode(' | ', $messages),
                'totals' => $totals,
            ];

        } catch (PrestaShopDatabaseException $e) {
            return [
                'success' => false,
                'message' => $e ? $e->getMessage() : 'Unexpected error',
                'totals' => [
                    'customers' => 0,
                    'products' => 0,
                    'orders' => 0,
                ],
            ];
        }
    }

    private function syncCustomers()
    {
        $customerTableName = _DB_PREFIX_ . "customer";
        $genderTableName = _DB_PREFIX_ . "gender_lang";
        $addressTableName = _DB_PREFIX_ . "address";
        $countryTableName = _DB_PREFIX_ . "country";
        $zoneTableName = _DB_PREFIX_ . "zone";
        $languageTableName = _DB_PREFIX_ . "lang";
        $limit = 100;

        $exporter = new SenderExport(Configuration::get('SPM_API_KEY'));

        $fields = ["email", "newsletter"];
        $fieldsMap = [
            'gender' => Configuration::get('SPM_CUSTOMER_FIELD_GENDER'),
            'birthday' => Configuration::get('SPM_CUSTOMER_FIELD_BIRTHDAY'),
            'language' => Configuration::get('SPM_CUSTOMER_FIELD_LANGUAGE'),
            'country' => Configuration::get('SPM_CUSTOMER_FIELD_COUNTRY')
        ];

        if (Configuration::get('SPM_CUSTOMER_FIELD_FIRSTNAME')) {
            $fields[] = 'firstname';
        }
        if (Configuration::get('SPM_CUSTOMER_FIELD_LASTNAME')) {
            $fields[] = 'lastname';
        }
        if ($fieldsMap["gender"]) {
            $fields[] = $genderTableName . '.name AS gender';
        }
        if ($fieldsMap["birthday"]) {
            $fields[] = 'birthday';
        }
        if ($fieldsMap["country"]) {
            $fields[] = $zoneTableName . '.name AS country';
        }
        if ($fieldsMap["language"]) {
            $fields[] =  $languageTableName . '.name AS language';
        }

        $tagId = Configuration::get('SPM_SENDERAPP_SYNC_LIST_ID');
        $lastId = 0;
        $totalSynced = 0;
        $lastResult = ['success' => true, 'message' => 'No customers to be exported'];

        while (true) {
            $sql = "SELECT C.id_customer as id," . implode(",", $fields) . " FROM " . $customerTableName . " C ";

            if ($fieldsMap["gender"]) {
                $sql .= "LEFT JOIN " . $genderTableName . " ON " . $genderTableName . ".id_gender = C.id_gender ";
            }

            if ($fieldsMap["language"]) {
                $sql .= "LEFT JOIN " . $languageTableName . " ON " . $languageTableName . ".id_lang = C.id_lang ";
            }

            if ($fieldsMap["country"]) {
                $sql .= "LEFT JOIN " . $zoneTableName . " ON " . $zoneTableName . ".id_zone = (
                        SELECT id_zone FROM " . $countryTableName . " 
                        WHERE id_country = (
                            SELECT id_country FROM " . $addressTableName . " 
                            WHERE id_customer = C.id_customer AND active = 1 LIMIT 1
                        ) LIMIT 1
                    ) ";
            }

            $sql .= "WHERE C.id_customer > " . (int)$lastId . " 
                 ORDER BY C.id_customer ASC 
                 LIMIT " . (int)$limit;

            $customers = Db::getInstance()->executeS($sql);

            if (empty($customers)) {
                break;
            }

            array_walk($customers, function (&$customer) use ($fieldsMap, $tagId) {
                $customer['fields'] = [];

                foreach($fieldsMap as $column => $id) {
                    if (!$id) continue;
                    $customer['fields'][$id] = $customer[$column];
                    unset($customer[$column]);
                }

                if ($tagId) {
                    $customer['tags'] = [$tagId];
                }
            });

            $lastResult = $exporter->export(['customers' => $customers]);
            $totalSynced += count($customers);

            $lastCustomer = end($customers);
            $lastId = isset($lastCustomer['id']) ? (int)$lastCustomer['id'] : $lastId;
        }

        return [
            'success' => $lastResult['success'] ?? false,
            'message' => $lastResult['message'] ?? 'Customer sync completed',
            'total' => $totalSynced ?: 0
        ];
    }

    private function syncProducts()
    {
        $limit = 100;
        $offset = 0;
        $totalExported = 0;
        $lastResult = ['success' => true, 'message' => 'No products to export'];

        $sender = new SenderExport(Configuration::get('SPM_API_KEY'));

        while (true) {
            $sql = 'SELECT p.id_product, pl.name, p.price, sa.quantity, pl.description_short
                FROM '._DB_PREFIX_.'product p
                LEFT JOIN '._DB_PREFIX_.'product_lang pl ON p.id_product = pl.id_product AND pl.id_lang = '.(int)Context::getContext()->language->id.'
                LEFT JOIN '._DB_PREFIX_.'stock_available sa ON sa.id_product = p.id_product
                ORDER BY p.id_product ASC
                LIMIT '.(int)$limit.' OFFSET '.(int)$offset;

            $products = Db::getInstance()->executeS($sql);

            if (empty($products)) {
                break;
            }

            $formatted = array_map(function ($product) {
                return [
                    'product_id' => $product['id_product'],
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'quantity' => $product['quantity'],
                    'description' => strip_tags($product['description_short']),
                ];
            }, $products);

            $lastResult = $sender->export(['products' => $formatted]);
            $totalExported += count($formatted);
            $offset += $limit;
        }

        return [
            'success' => $lastResult['success'] ?? false,
            'message' => $lastResult['message'] ?? 'Product sync completed',
            'total' => $totalExported,
        ];
    }

    private function syncOrders()
    {
        $limit = 100;
        $offset = 0;
        $totalExported = 0;
        $lastResult = ['success' => true, 'message' => 'No orders to export'];

        $sender = new SenderExport(Configuration::get('SPM_API_KEY'));

        while (true) {
            $sql = 'SELECT o.id_order, o.total_paid, o.date_add, c.email
                FROM '._DB_PREFIX_.'orders o
                LEFT JOIN '._DB_PREFIX_.'customer c ON c.id_customer = o.id_customer
                ORDER BY o.id_order ASC
                LIMIT '.(int)$limit.' OFFSET '.(int)$offset;

            $orders = Db::getInstance()->executeS($sql);

            if (empty($orders)) {
                break;
            }

            $formatted = array_map(function ($order) {
                return [
                    'order_id' => $order['id_order'],
                    'total_paid' => $order['total_paid'],
                    'order_date' => $order['date_add'],
                    'customer_email' => $order['email'],
                ];
            }, $orders);

            $lastResult = $sender->export(['orders' => $formatted]);
            $totalExported += count($formatted);
            $offset += $limit;
        }

        return [
            'success' => $lastResult['success'] ?? false,
            'message' => $lastResult['message'] ?? 'Order sync completed',
            'total' => $totalExported,
        ];
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
            $this->senderApiClient()->logDebug($message);
        }
    }

    /**
     * Get Sender API Client instance
     * and make sure that everything is in order
     *
     * @return SenderApiClient
     */
    public function senderApiClient()
    {
        // Create new instance if there is none
        if (!$this->senderApiClient) {
            $this->senderApiClient = new SenderApiClient();
            $this->senderApiClient->setApiKey(Configuration::get('SPM_API_KEY'));
        }

        return $this->senderApiClient;
    }
}
