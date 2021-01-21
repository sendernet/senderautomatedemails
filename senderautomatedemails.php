<?php
/**
 * 2010-2018 Sender.net
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
require_once 'lib/Sender/Base62.php';
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

    /**
     * Indicates whether module is in debug mode
     * @var bool
     */
    private $debug = true;

    /**
     * Sender.net API client
     * @var object
     */
    public $apiClient = null;

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
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array(
            'min' => '1.6.0.5',
            'max' => _PS_VERSION_
        );
        $this->bootstrap = true;
        $this->module_key = 'ae9d0345b98417ac768db7c8f321ff7c';

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
            'SPM_ALLOW_GUEST_TRACK' => 0,
            'SPM_CUSTOMER_FIELD_FIRSTNAME' => 0,
            'SPM_CUSTOMER_FIELD_LASTNAME' => 0,
            'SPM_CUSTOMER_FIELD_LOCATION' => 0,
            'SPM_CUSTOMER_FIELD_BIRTHDAY_ID' => 0,
            'SPM_CUSTOMER_FIELD_GENDER_ID' => 0,
            'SPM_CUSTOMER_FIELD_PARTNER_OFFERS_ID' => 0,
            'SPM_SENDERAPP_SYNC_LIST_ID' => 0,
        );
//           [ 'SPM_CUSTOMER_NEWSLETTER' => 1,
//            'SPM_CUSTOMER_GENDER' => 1,];

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
            || !$this->registerHook('actionCartSave') // Getting it on all pages
            || !$this->registerHook('displayHome')
            || !$this->registerHook('actionCustomerAccountAdd')  //Adding customer and tracking the customer track
            || !$this->registerHook('actionCustomerAccountUpdate')
            || !$this->registerHook('actionObjectCustomerUpdateAfter')
            || !$this->registerHook('displayFooterProduct')) {
            return false;
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
     * Add tab css to the BackOffice
     *
     * @return void
     */
    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCss($this->_path . 'views/css/tab.css');
    }

    /**
     * Reset all Sender.net related settings
     *
     * @return void
     */
    private function disableModule()
    {
        #Should we disable all keys when authentication is gone?
        $this->logDebug('Disable module!');
        //Disabling as per AdminSenderAutomatedEmails -> enableDefaults
        Configuration::updateValue('SPM_API_KEY', '');
        Configuration::updateValue('SPM_IS_MODULE_ACTIVE', 0);
        Configuration::updateValue('SPM_ALLOW_FORMS', '');
        Configuration::updateValue('SPM_ALLOW_IMPORT', 0);
        Configuration::updateValue('SPM_ALLOW_TRACK_NEW_SIGNUPS', 0);
        Configuration::updateValue('SPM_ALLOW_TRACK_CARTS', 0);
        Configuration::updateValue('SPM_CUSTOMER_FIELD_FIRSTNAME', 0);
        Configuration::updateValue('SPM_CUSTOMER_FIELD_LASTNAME', 0);


    }

    /**
     * Loading form when selected form is active
     */
    public function hookDisplayHome()
    {
        // Check if we should
        if (!Configuration::get('SPM_IS_MODULE_ACTIVE') || (!Configuration::get('SPM_ALLOW_FORMS'))
            || Configuration::get('SPM_FORM_ID') == $this->defaultSettings['SPM_FORM_ID']) {
            return;
        }

        $options = array(
            'showForm' => false
        );

        $form = $this->apiClient()->getFormById(Configuration::get('SPM_FORM_ID'));
        #Check if form is disabled
        if (!$form->is_active) {
            return;
        }

        $currentAccount = $this->apiClient()->getCurrentAccount();
        $resourceKey = $currentAccount ? $currentAccount->resource_key : '';
        if (empty($resourceKey)) {
            return;
        }

        if ($form->type === 'embed') {
            $embedHash = $form->settings->embed_hash;
        }

        // Add forms
        if (Configuration::get('SPM_ALLOW_FORMS')) {
            $options['formUrl'] = isset($form->settings->resource_path) ? $form->settings->resource_path : '';
            $options['resourceKey'] = $resourceKey;
            $options['showForm'] = true;
            $options['embedForm'] = isset($embedHash);
            $options['embedHash'] = isset($embedHash) ? $embedHash : '';
        }

        $this->context->smarty->assign($options);
        return $this->context->smarty->fetch($this->views_url . '/templates/front/form.tpl');
    }

    /**
     * Here we handle new signups, we fetch customer info
     * then if enabled tracking and user has opted in for
     * a newsletter we add him to the prefered list
     *
     * @param  array $context
     * @return array $context
     */
    public function hookactionCustomerAccountAdd($context)
    {
        $this->logDebug('#hookactionCustomerAccountAdd START');
        $this->logDebug('Guest on checkout filled personal information');
        // Validate if we should
        if (!Validate::isLoadedObject($context['newCustomer']) ||
            (!Configuration::get('SPM_ALLOW_TRACK_NEW_SIGNUPS') && !Configuration::get('SPM_ALLOW_GUEST_TRACK'))
            || !Configuration::get('SPM_IS_MODULE_ACTIVE')) {
            $this->logDebug('Something went wrong');
            return;
        }

        if (Configuration::get('SPM_ALLOW_TRACK_NEW_SIGNUPS') != 1){
            $this->logDebug('New customer wont be track. Tracking cart is not enable');
            return;
        }

        $isSubscriber = $this->checkSubscriberState($this->context->customer->email, $context);

        #Form the recipient or sync it
        if ($isSubscriber){
            $this->logDebug('Already exists subscriber');
            #Track cart details
            $recipient = $this->formDefaultsRecipientSubscriber($this->context->customer);
        }else{
            $this->logDebug('Forming the new subscriber');
            $recipient = $this->formDefaultsRecipient($this->context->customer);
        }

        $customFields = $this->getCustomFields($this->context);

        if (version_compare(_PS_VERSION_, '1.6.1.10', '>=')) {
            $cookie = $this->context->cookie->getAll();
        } else {
            $cookie = $context['cookie']->getFamily($context['cookie']->id);
        }

        #Creating/Update subscriber on the required list
        try {
            if ($isSubscriber){
                $tagId = Configuration::get('SPM_GUEST_LIST_ID');
                $subscriberId = $isSubscriber->id;
                $this->syncRecipient($recipient, $isSubscriber->id, $tagId);
                $this->logDebug('Subscriber sync and added to guest track list option');
            }else{
                $listToAdd = !empty(Configuration::get('SPM_GUEST_LIST_NAME')) ? [Configuration::get('SPM_GUEST_LIST_NAME')] : '';
                $newSubscriber = $this->apiClient()->addSubscriberAndList($recipient, $listToAdd);
                $subscriberId = $newSubscriber->id;
                $this->logDebug('Subscriber has been created: ' . json_encode($newSubscriber));
            }

            if (!empty($customFields)) {
                $this->apiClient()->addFields($subscriberId, $customFields);
                $this->logDebug('Adding fields to this recipient: ' . json_encode($customFields));
            }

            $this->syncCart($context['cart'], $cookie);
            $this->logDebug('#hookactionCustomerAccountAdd END');
        }catch (Exception $e){
            $this->logDebug('Error hookactionCustomer ' . json_encode($e->getMessage()));
        }
    }

    /**
     * Use this hook in order to be sure
     * whether we have captured the latest cart info
     * it fires when user uses instant checkout
     * or logged in user goes to checkout page
     *
     * @param  object $context
     * @return object $context
     */
    public function hookActionCartSummary($context)
    {
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
            || (!Configuration::get('SPM_ALLOW_GUEST_TRACK')
                && isset($cookie['is_guest']) && $cookie['is_guest'])
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
    public function hookActionCartSave($context)
    {
        $this->logDebug('hookActionCartSAve');
//        if (version_compare(_PS_VERSION_, '1.6.1.10', '>=')) {
//            $cookie = $context['cookie']->getAll();
//        } else {
//            $cookie = $context['cookie']->getFamily($context['cookie']->id);
//        }
//        // Validate if we should track
//        if (!isset($cookie['email'])
//            || !Validate::isLoadedObject($context['cart'])
//            || (!Configuration::get('SPM_ALLOW_TRACK_CARTS')
//                && isset($cookie['logged']) && $cookie['logged'])
//            || (!Configuration::get('SPM_ALLOW_GUEST_TRACK')
//                && isset($cookie['is_guest']) && $cookie['is_guest'])
//            || !Configuration::get('SPM_IS_MODULE_ACTIVE')
//            || $this->context->controller instanceof OrderController) {
//            $this->logDebug('hookActionCartSave first condition failed');
//            return;
//        }
//
//        $encodedEmail = base64_encode($cookie['email']);
//        if ($isSubscriber = $this->apiClient()->isAlreadySubscriber($encodedEmail)){
//            if ($isSubscriber->unsubscribed){
//                $this->logDebug('Subscriber is NOT active in SENDER wont track customer cart');
//                return;
//            }
//            $this->logDebug('Subscriber active in SENDER');
//        }
//
//        $this->logDebug('#hookActionCartSave START');
//
//        $this->syncCart($context['cart'], $cookie, $isSubscriber);
//
//        $this->logDebug('#hookActionCartSave END');
    }

    /**
     * Hook into order confirmation. Mark cart as converted since order is made.
     * Keep in mind that it doesn't mean that payment has been made
     *
     *
     * @param  object $context
     * @return object $context
     */
    public function hookDisplayOrderConfirmation($context)
    {
        $this->logDebug('hookDisplayOrderConfirmation');
        #First check if we should capture these details
        $this->logDebug('When the order would be finish');
        if (version_compare(_PS_VERSION_, '1.6.1.10', '>=')) {
            $order = $context['order'];
        } else {
            $order = $context['objOrder'];
        }

        try {
            // Return if cart object is not found or module is not active
//            if (!Configuration::get('SPM_IS_MODULE_ACTIVE')
//                || !Validate::isLoadedObject($order)
//                || !isset($order->id_cart)) {
//                return $context;
//            }

            $this->logDebug('#hookActionValidateOrder START');

            // Convert cart
            $converStatus = $this->apiClient()->cartConvert($order->id_cart);

            $this->logDebug('Cart convert response: '
                . json_encode($converStatus));
        }catch (Exception $e)
        {
            $this->logDebug($e->getMessage());
        }
    }

    /**
     * Here we handle customer info where he update his account
     * and we delete or add him to the prefered list
     *
     * @param  array $context
     * @return array $context
     */
    public function hookactionObjectCustomerUpdateAfter($context)
    {
        $this->logDebug('hookactionObjectCustomerUpdateAfter');
        return $this->hookactionCustomerAccountUpdate($context);
    }

    /**
     * Here we handle customer info where he update his account
     * and we delete or add him to the prefered list
     *
     * @param  array $context
     * @return array $context
     */
    public function hookactionCustomerAccountUpdate($context)
    {
        $this->logDebug('hookactionCustomerAccountUpdate');
        $this->logDebug('Updating personal details');

        $customer = $this->context->customer;

        //Validate if we should
        if (!Validate::isLoadedObject($customer)
            || !Configuration::get('SPM_IS_MODULE_ACTIVE')
            || !Configuration::get('SPM_ALLOW_TRACK_CARTS')) {
            $this->logDebug('exiting update customer');
            return $customer;
        }
        $this->logDebug('#hookactionCustomerAccountUpdate START');

        $listId = Configuration::get('SPM_CUSTOMERS_LIST_ID');
        $recipient = $this->formDefaultsRecipientSubscriber($this->context->customer);
        $isSubscriber = $this->checkSubscriberState($this->context->customer->email, $context);

        // Check if user opted in for a newsletter
        if (!$customer->newsletter && !$customer->optin) {
            $this->logDebug('Customer did not checked newsletter or optin!');
            $deleteFromListResult = $this->apiClient()->listRemove(
                $recipient,
                $listId
            );
            $this->logDebug('Delete the recipient ' .
                json_encode($recipient) . ' from the ' . json_encode($listId) . ' list is ' . json_encode($deleteFromListResult) . '.');
        } else {
            $tagId = Configuration::get('SPM_CUSTOMERS_LIST_ID');
            $addToListResult = $this->syncRecipient($recipient, $isSubscriber->id, $tagId);
            $this->logDebug('Add this recipient: ' .
                json_encode($recipient));
            $this->logDebug('Add to list response:' .
                json_encode($addToListResult));
        }
        $this->logDebug('#hookactionCustomerAccountUpdate END');
    }

    /**
     * On this hook we setup product
     * import JSON for sender to get the data
     *
     * @param  array $params
     * @return mixed string Smarty
     */
    public function hookDisplayFooterProduct($params)
    {
        $this->logDebug('hookDisplayFooterProduct');
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
            $image_url = $product['images']['0']['small']['url'];

            if ($product['images']['0']['small']['url']) {
                $image_url = $product['images']['0']['small']['url'];
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
            "quantity" => $product['quantity'],
            "discount" => $discount
        );


        $this->context->smarty->assign('product', $options);

        return $this->context->smarty->fetch($this->views_url . '/templates/front/product_import.tpl');
    }

    /**
     * @param $email
     * @param $context
     * @return false|void
     */
    public function checkSubscriberState($email, $context)
    {
        if ($isSubscriber = $this->apiClient()->isAlreadySubscriber($email)){
            if (!$isSubscriber->unsubscribed){
                $this->logDebug('Active subscriber');
                #
            }else{
                $this->logDebug('Unsubscribed subscriber');
                if (array_key_exists('newCustomer', $context) && $context['newCustomer']->newsletter){
                    #Changes over website
                    $this->apiClient()->reactivateSubscriber($isSubscriber->id);
                    $this->logDebug('context -> newCustomer');
                    $this->logDebug('Subscriber reactivated');
                }elseif (array_key_exists('object', $context) && $context['object']->newsletter) {
                    #Changes over interface
                    $this->apiClient()->reactivateSubscriber($isSubscriber->id);
                    $this->logDebug('context -> object');
                    $this->logDebug('Subscriber reactivated');
                } else{
                    return false;
                }
            }
            #Update subscriber details with shop checkout information
            return $isSubscriber;
            #Sync recipient
        }
        return false;
    }

    /**
     * @param $context
     * @return array
     */
    public function getCustomFields($context)
    {
        $fields = [];

        (Configuration::get('SPM_CUSTOMER_FIELD_BIRTHDAY_ID')) != 0 ? $fields[Configuration::get('SPM_CUSTOMER_FIELD_BIRTHDAY_ID')] = $context->customer->birthday : false;
        (Configuration::get('SPM_CUSTOMER_FIELD_GENDER_ID')) != 0 ? $fields[Configuration::get('SPM_CUSTOMER_FIELD_GENDER_ID')] = ($context->customer->id_gender == 1 ? $this->l('Male') : $this->l('Female')) : false;

        return $fields;
    }

    /**
     * Forming subscriber details (default ones - email, firstname, lastname)
     * @param $context
     * @return mixed
     */
    public function formDefaultsRecipient($context)
    {
        $this->logDebug('Forming recipient');

        $recipient = array(
            'email' => $context->email,
        );

        #Default fields
        (Configuration::get('SPM_CUSTOMER_FIELD_FIRSTNAME')) == 1 ? $recipient['firstname'] = $context->firstname : false;
        (Configuration::get('SPM_CUSTOMER_FIELD_LASTNAME')) == 1 ? $recipient['lastname'] = $context->lastname : false;

        return $recipient;
    }

    /**
     * Forming subscriber details (default ones - email, firstname, lastname)
     * @param $context
     * @return mixed
     */
    public function formDefaultsRecipientSubscriber($context)
    {
        $this->logDebug('Forming recipient suscriber');

        $recipient = array(
            'email' => $context->email,
        );

        #Default fields
        (Configuration::get('SPM_CUSTOMER_FIELD_FIRSTNAME')) == 1 ? $recipient['firstname'] = $context->firstname : false;
        (Configuration::get('SPM_CUSTOMER_FIELD_LASTNAME')) == 1 ? $recipient['lastname'] = $context->lastname : false;

        return $recipient;
    }

    /**
     * Helper method to
     * generate cart array for Sender api call
     * It also retrieves products with images
     *
     * @param  object $cart
     * @param  string $email
     * @return array
     */
    private function mapCartData($cart, $email)
    {
        $imageType = ImageType::getFormatedName('home');
        $this->logDebug($imageType);
        $cartHash = $cart->id;
        $this->logDebug('This is the cart hash ' . $cartHash);

        $data = array(
            "email" => $email,
            "external_id" => $cart->id,
            "url" => _PS_BASE_URL_ . __PS_BASE_URI__
                . 'index.php?fc=module&module='
                . $this->name
                . "&controller=recover&hash={$cartHash}", //cart_hash where formed?
            "currency" => $this->context->currency->iso_code,
            "order_total" => (string)$cart->getOrderTotal(),
            "products" => array()
        );

        $products = $cart->getProducts();

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
                    $imageType
                )
            );
            $this->logDebug(json_encode($prod));
            $data['products'][] = $prod;
        }

        return $data;
    }

    /**
     * Sync current cart with sender cart track
     *
     * @param  object $cart prestashop Cart
     * @param  array $cookie
     * @return void
     */
    public function syncCart($cart, $cookie)
    {
        // Keep recipient up to date with Sender.net list
        // Generate cart data array for api call
        $cartData = $this->mapCartData($cart, $cookie['email']);

        if (!empty($cartData['products'])) {
            $cartTrackResult = $this->apiClient()->trackCart($cartData);
            $this->logDebug('Cart track request:' . json_encode($cartData));
            $this->logDebug('Cart track response: ' . json_encode($cartTrackResult));
        } elseif (empty($cartData['products']) && isset($cookie['id_cart'])) {
            $cartDeleteResult = $this->apiClient()->cartDelete($cookie['id_cart']);
            $this->logDebug('Cart delete response:' . json_encode($cartDeleteResult));
        }
    }

    public function updateSubscriber($subscriber)
    {
        $this->apiClient()->updateSubscriber($subscriber);
    }

    /**
     * For customers that return to the site
     * Syncs recipient with the proper Sender.net list
     *
     * @return void
     */
    private function syncRecipient($recipient, $subscriberId, $tagId)
    {
        $this->logDebug('syncRecipient hook');
        // Validate if we should
        if (!Validate::isLoadedObject($this->context->customer)
            || (!Configuration::get('SPM_ALLOW_TRACK_NEW_SIGNUPS')
                && !Configuration::get('SPM_ALLOW_GUEST_TRACK'))
            || !Configuration::get('SPM_IS_MODULE_ACTIVE')) {
            return false;
        }

        $this->apiClient()->updateSubscriber($recipient, $subscriberId);
        $addToListResult = $this->apiClient()->addToList($subscriberId, $tagId);

        if (!empty($customFields)) {
            $this->apiClient()->addFields($addToListResult->id, $customFields);
            $this->logDebug('Adding fields to this recipient: ' . json_encode($customFields));
        }

        return $addToListResult;
    }

    /**
     * Generates Configuration link in modules selection view
     */
    public function getContent()
    {
        $this->logDebug('getContent');
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminSenderAutomatedEmails'));
    }

    public function syncList()
    {
        try {
            $customersRequirements = Db::getInstance()->executeS('
                SELECT email, firstname, lastname
                       FROM ' . _DB_PREFIX_ . 'customer
                WHERE newsletter = 1');
            if (!empty($customersRequirements)){
                $stringCustomers = $this->recursive_implode($customersRequirements);
                $customersExport = new CustomersExport(Configuration::get('SPM_API_KEY'));
                return $customersExport->textImport($stringCustomers, $customersRequirements);
            }
        } catch (PrestaShopDatabaseException $e) {
            return $data = [
                'success' => false,
                'message' => $e ? $e->getMessage() : 'Unexpected error',
            ];
        }
    }

    public function recursive_implode(array $array, $glue = ',', $include_keys = false, $trim_all = true)
    {
        $glued_string = '';

        // Recursively iterates array and adds key/value to glued string
        array_walk_recursive($array, function($value, $key) use ($glue, $include_keys, &$glued_string)
        {
            $include_keys and $glued_string .= $key.$glue;
//            $glued_string .= $value.$glue;
            if ($key == 'lastname'){
                $glued_string .= $value;
                $glued_string .= PHP_EOL;
            }else{
                $glued_string .= $value.$glue;
            }
        });
        // Removes last $glue from string
        strlen($glue) > 0 and $glued_string = substr($glued_string, 0, -strlen($glue));
        // Trim ALL whitespace
//        $trim_all and $glued_string = preg_replace("/(\s)/ixsm", '', $glued_string);
//        dump($glued_string);
        $result = str_replace('{"subscribers":', '', $glued_string);
        return (string) $result;
    }

    /**
     * Add Module Settings tab to the sidebar
     */
    private function addTabs()
    {

        $langs = Language::getLanguages();

        $new_tab = new Tab();
        $new_tab->class_name = "AdminSenderAutomatedEmails";
        $new_tab->module = "senderautomatedemails";
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $new_tab->icon = "mail";
        }

        $new_tab->id_parent = Tab::getIdFromClassName('CONFIGURE');
        $new_tab->active = 1;

        foreach ($langs as $l) {
            $new_tab->name[$l['id_lang']] = $this->l('Sender.net Settings');
        }
        $new_tab->save();
        return true;
    }

    /**
     * @todo  Optimize for huge lists
     *
     * Get subscribers from ps_newsletter table
     * and sync with sender
     *
     * @return string Status message
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
            $this->apiClient()->addToList(array(
                'email' => $subscriber['email'],
                'created' => $subscriber['newsletter_date_add'],
                'active' => $subscriber['active'],
                'source' => $this->l('Newsletter')
            ), $listId);
            $this->logDebug('Added newsletter subscriber: ' . $subscriber['email']);
        }

        foreach ($oldCustomers as $subscriber) {
            $this->apiClient()->addToList(array(
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
                $this->debugLogger->setFilename($this->module_path . '/log/sender_automated_emails_logs_' . date('Ymd') . '.log');
            }
            $this->debugLogger->logDebug($message);
        }
    }

    /**
     * Get Sender API Client instance
     * and make sure that everything is in order
     *
     * @todo  described bellow
     * @return object SenderApiClient
     */
    public function apiClient()
    {
        // Create new instance if there is none
        if (!$this->apiClient) {
            $this->apiClient = new SenderApiClient();
            $this->apiClient->setApiKey(Configuration::get('SPM_API_KEY'));
        }

        // Check if key is valid
        if (!$this->apiClient->checkApiKey()) {
            $this->logDebug('apiClient(): checkApiKey failed.');
            $this->logDebug('Key used: ' . Configuration::get('SPM_API_KEY'));
            // Disable module
            $this->disableModule();

            return $this->apiClient;
        }

        return $this->apiClient;
    }
}
