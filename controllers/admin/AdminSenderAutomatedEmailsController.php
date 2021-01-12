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

require_once(dirname(__FILE__) . '/../../lib/Sender/SenderApiClient.php');

use GuzzleHttp\Client;

class AdminSenderAutomatedEmailsController extends ModuleAdminController
{
    private $customFields = [];

    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
        $this->customMessages();
    }

    /**
     * Add here custom messages
     */
    public function customMessages()
    {
        $this->_error[100] = $this->l('Could not authenticate. Incorrect api access token, please try again');
        $this->_error[101] = $this->l('No Api access token provided');
        $this->_conf[200] = $this->l('Sender module activated. Please configure it to match your requirements');
    }

    // Do not init Header
    public function initPageHeaderToolbar()
    {
        return true;
    }

    /**
     * Render options
     * Checks if user is authenticated (valid api key)
     * Handle connect and disconnect actions
     *
     * @return  string
     */
    public function renderOptions()
    {
        $shouldDisconnect = Tools::getValue('disconnect', null);
        if ($shouldDisconnect == 'true') {
            $this->disconnect();
        }

        $senderApiKey = Tools::getValue('apiKey', null);
        if ($senderApiKey) {
            $this->connect($senderApiKey);
        }

        if (!$this->module->apiClient()->checkApiKey()) {
            // User is NOT authenticated
            return $this->renderAuth();
        } else {
            // Use proper function
            // If not connect maybe use SENDER_PLUGIN_ENABLED to
            // check if show configuration
            return $this->renderConfigurationMenu();
        }
    }

    /**
     * Handles the form submission
     * @return string
     */
    public function postProcess()
    {
        if (Tools::isSubmit('actionApiKey')) {
            if (isset($_POST['apiKey']) && !empty($_POST['apiKey'])) {
                $this->renderOptions();
            } else {
                $this->redirectToAdminMenu('&error=101');
            }
        }
    }

    /**
     * It renders authentication view
     *
     * @return string
     */
    public function renderAuth()
    {
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $returnUrl = $this->context->link->getAdminLink('AdminSenderAutomatedEmails');
        } else {
            $returnUrl = $this->context->shop->getBaseUrl()
                . basename(_PS_ADMIN_DIR_)
                . DIRECTORY_SEPARATOR
                . $this->context->link->getAdminLink('AdminSenderAutomatedEmails');
        }

        $authUrl = SenderApiClient::generateAuthUrl($this->context->shop->getBaseUrl(), $returnUrl);

        $options = array(
//            'authUrl'       => $authUrl, //Not in use
            'moduleVersion' => $this->module->version,
            'imageUrl'      => $this->module->getPathUri() . 'views/img/sender_logo.png',
            //'baseUrl'       => $this->module->apiClient()->getBaseUrl(), //Not in use
        );

        $this->context->smarty->assign($options);

        return $this->context->smarty->fetch($this->module->views_url . '/templates/admin/auth.tpl');
    }

    /**
     * TEMPORARY!
     * Loading the sender menu settings if authenticated
     * @todo  Use proper methot like renderConfiguration instead!
     */
    public function renderConfigurationMenu()
    {
        if (version_compare(_PS_VERSION_, '1.6.0.0', '>=')) {
            $disconnectUrl = $this->context->link->getAdminLink('AdminSenderAutomatedEmails').'&disconnect=true';
        } else {
            $disconnectUrl = $this->context->shop->getBaseUrl()
                . basename(_PS_ADMIN_DIR_)
                . DIRECTORY_SEPARATOR
                . $this->context->link->getAdminLink('AdminSenderAutomatedEmails')
                . '&disconnect=true';
        }

        $output = '';

        // Add dependencies
        $this->context->controller->addJquery();
        $this->context->controller->addJS($this->module->views_url . '/js/script.js');
        $this->context->controller->addJS($this->module->views_url . '/js/sp-vendor-table-sorter.js');
        $this->context->controller->addCSS($this->module->views_url . '/css/style.css');
        $this->context->controller->addCSS($this->module->views_url . '/css/material-font.css');

        $customFields = $this->module->apiClient->getCustomFields();

        #Removing the default fields
        $customFieldsToHide = ['email', 'firstname', 'lastname'];
        foreach ($customFields as $key => $field){
            if (in_array(strtolower(str_replace(' ','', $field->title)), $customFieldsToHide)){
                unset($customFields[$key]);
            }
        }


        $this->context->smarty->assign(array(
            'imageUrl'               => $this->module->getPathUri() . 'views/img/sender_logo.png',
            //Which user show here as auth been done over apiKey, no user involve
            'connectedAccount'       => $this->module->apiClient()->getCurrentAccount(),
            'apiKey'                 => $this->module->apiClient()->getApiKey(),
            'disconnectUrl'          => $disconnectUrl,
            'baseUrl'                => $this->module->apiClient()->getBaseUrl(),
            'appUrl'                 => $this->module->apiClient()->getAppUrl(),
            'moduleVersion'          => $this->module->version,
            'formsList'              => $this->module->apiClient()->getAllForms(),
            'guestsLists'            => $this->module->apiClient()->getAllLists(),
            'customersLists'         => $this->module->apiClient()->getAllLists(),
            'allowNewSignups'        => Configuration::get('SPM_ALLOW_TRACK_NEW_SIGNUPS'),
            'allowCartTrack'         => Configuration::get('SPM_ALLOW_TRACK_CARTS'),
            'allowForms'             => Configuration::get('SPM_ALLOW_FORMS'),
            'allowGuestCartTracking' => Configuration::get('SPM_ALLOW_GUEST_TRACK'),
            'allowCartTracking'      => Configuration::get('SPM_ALLOW_TRACK_CARTS'),
            'cartsAjaxurl'           => $this->module->module_url . '/ajax/carts_ajax.php?token=' . Tools::getAdminToken($this->module->name),
            'formsAjaxurl'           => $this->module->module_url . '/ajax/forms_ajax.php?token=' . Tools::getAdminToken($this->module->name),
            'listsAjaxurl'           => $this->module->module_url . '/ajax/lists_ajax.php?token=' . Tools::getAdminToken($this->module->name),
            'dataAjaxurl'            => $this->module->module_url . '/ajax/data_ajax.php?token=' . Tools::getAdminToken($this->module->name),
            'syncListAjaxUrl'            => $this->module->module_url . '/ajax/sync_list.php?token=' . Tools::getAdminToken($this->module->name),
            'formId'                 => Configuration::get('SPM_FORM_ID'),
            'partnerOfferId'         => Configuration::get('SPM_CUSTOMER_FIELD_PARTNER_OFFERS_ID'),
            'guestListId'            => Configuration::get('SPM_GUEST_LIST_ID'),
            'customerListId'         => Configuration::get('SPM_CUSTOMERS_LIST_ID'),
            'genderFieldId'          => Configuration::get('SPM_CUSTOMER_FIELD_GENDER_ID'),
            'birthdayFieldId'          => Configuration::get('SPM_CUSTOMER_FIELD_BIRTHDAY_ID'),
            'customFields'           => $customFields,
            'syncedList'             => Configuration::get('SPM_SENDERAPP_SYNC_LIST_DATE') ? Configuration::get('SPM_SENDERAPP_SYNC_LIST_DATE') : ''
        ));

        #loading templates
        $output .= $this->context->smarty->fetch($this->module->views_url . '/templates/admin/view.tpl');

        dump($this->context->link->getAdminLink('AdminSenderAutomatedEmails'));


        return $output;
    }
    function recursive_implode(array $array, $glue = ',', $include_keys = false, $trim_all = true)
    {
        $glued_string = '';

        // Recursively iterates array and adds key/value to glued string
        array_walk_recursive($array, function($value, $key) use ($glue, $include_keys, &$glued_string)
        {
            $include_keys and $glued_string .= $key.$glue;
            if ($key == 'lastname'){
                $glued_string .= $value.'\n';
            }else{
                $glued_string .= $value.$glue;
            }
        });

        // Removes last $glue from string
        strlen($glue) > 0 and $glued_string = substr($glued_string, 0, -strlen($glue));

        // Trim ALL whitespace
        $trim_all and $glued_string = preg_replace("/(\s)/ixsm", '', $glued_string);

        return (string) $glued_string;
    }


    /**
     * Tries to store api key returned from
     * Sender.net
     *
     * @param  string $apiKey
     * @return void
     * @todo Throw an error if something goes wrong
     */
    private function connect($apiKey)
    {
        if (!$apiKey) {
            return;
        }

        $apiClient = new SenderApiClient();

        $apiClient->setApiKey($apiKey);

        if ($apiClient->checkApiKey()) {
            $this->module->logDebug('Connected to Sender. Got key: ' . $apiKey);
            $this->enableDefaults($apiKey);
            unset($apiClient);
            // Redirect back to module admin page
            $this->redirectToAdminMenu('&conf=200');
        } else {
            $this->redirectToAdminMenu('&error=100');
//            $this->errors[] = Tools::displayError($this->l('Could not authenticate. Please try again.'));
        }
    }

    private function enableDefaults($apiKey)
    {
        Configuration::updateValue('SPM_API_KEY', $apiKey);
        Configuration::updateValue('SPM_IS_MODULE_ACTIVE', true);
        Configuration::updateValue('SPM_ALLOW_FORMS', true);
        Configuration::updateValue('SPM_ALLOW_IMPORT', true);
        Configuration::updateValue('SPM_ALLOW_TRACK_NEW_SIGNUPS', true);
        Configuration::updateValue('SPM_ALLOW_TRACK_CARTS', true);
        Configuration::updateValue('SPM_CUSTOMER_FIELD_FIRSTNAME', true);
        Configuration::updateValue('SPM_CUSTOMER_FIELD_LASTNAME', true);
    }

    /**
     * Remove stored api key from module settings
     * Disable plugin
     *
     * @return void
     * @todo  Throw an error if something goes wrong
     */
    private function disconnect()
    {
        $this->module->logDebug('Disconnected');
        Configuration::deleteByName('SPM_API_KEY');
        // Redirect back to module admin page
        $this->redirectToAdminMenu();
    }

    /**
     * Helper method to redirect user back to
     * module admin menu
     *
     * @return void
     */
    private function redirectToAdminMenu($message = null)
    {
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $url = $this->context->link->getAdminLink('AdminSenderAutomatedEmails');
        } else {
            $url = $this->context->shop->getBaseUrl()
                . basename(_PS_ADMIN_DIR_)
                . DIRECTORY_SEPARATOR
                . $this->context->link->getAdminLink('AdminSenderAutomatedEmails');
        }

        if ($message){
            Tools::redirect($url . $message);
        }

        Tools::redirect($url);
    }
}
