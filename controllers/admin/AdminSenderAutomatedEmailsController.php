<?php
/**
 * 2010-2021 Sender.net
 *
 * Sender.net Automated Emails
 *
 * @author Sender.net <info@sender.net>
 * @copyright 2010-2021 Sender.net
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License v. 3.0 (OSL-3.0)
 * Sender.net
 */

require_once(dirname(__FILE__) . '/../../lib/Sender/SenderApiClient.php');

/**
 * Class AdminSenderAutomatedEmailsController
 */
class AdminSenderAutomatedEmailsController extends ModuleAdminController
{
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
     * @return string|void
     */
    public function renderOptions()
    {
        $shouldDisconnect = Tools::getValue('disconnect', null);
        if ($shouldDisconnect == 'true') {
            $this->disconnect();
        }

        if (!empty(Configuration::get('SPM_API_KEY'))) {
            return $this->renderConfigurationMenu();
        }

        $senderApiKey = Tools::getValue('apiKey');
        if (!$senderApiKey) {
            return $this->renderAuth();
        } else {
            $this->connect($senderApiKey);
        }

        return $this->renderAuth();
    }

    /**
     * @param $apiKey
     */
    private function connect($apiKey)
    {
        $this->module->apiClient = new SenderApiClient();
        $this->module->apiClient->setApiKey($apiKey);

        if ($this->module->apiClient->checkApiKey()) {
            $this->module->logDebug('Connected to Sender. Got key: ' . $apiKey);
            $this->enableDefaults($apiKey);
            // Redirect back to module admin page
            $this->redirectToAdminMenu('&conf=200');
        } else {
            if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
                $this->redirectToAdminMenu('&error=100');
            } else {
                $this->errors[] = Tools::displayError($this->_error[100]);
            }
        }
    }

    /**
     * Handles the form submission for connecting Sender account
     * @return string
     */
    public function postProcess()
    {
        if (Tools::isSubmit('actionApiKey')) {
            if (Tools::getIsset('apiKey') && Tools::getValue('apiKey')) {
                $this->renderOptions();
            } else {
                if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
                    $this->redirectToAdminMenu('&error=101');
                } else {
                    $this->errors[] = Tools::displayError($this->_error[101]);
                }
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
        $options = array(
            'moduleVersion' => $this->module->version,
            'imageUrl' => $this->module->getPathUri() . 'views/img/sender_logo.png',
            'link' => $this->context->link,
        );

        $this->context->controller->addCSS($this->module->views_url . '/css/auth.css');
        $this->context->smarty->assign($options);

        return $this->context->smarty->fetch($this->module->views_url . '/templates/admin/auth.tpl');
    }

    /**
     * Loading the sender menu settings if authenticated
     * @todo  Use proper method like renderConfiguration instead!
     */
    public function renderConfigurationMenu()
    {
        if (version_compare(_PS_VERSION_, '1.6.0.0', '>=')) {
            $disconnectUrl = $this->context->link->getAdminLink('AdminSenderAutomatedEmails') . '&disconnect=true';
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

        $customFields = $this->module->senderApiClient()->getCustomFields();

        #Removing the default fields
        $customFieldsToHide = ['email', 'firstname', 'lastname'];
        foreach ($customFields as $key => $field) {
            if (in_array(Tools::strtolower(str_replace(' ', '', $field->title)), $customFieldsToHide)) {
                unset($customFields[$key]);
            }
        }


        $this->context->smarty->assign(array(
            'imageUrl' => $this->module->getPathUri() . 'views/img/sender_logo.png',
            //Which user show here as auth been done over apiKey, no user involve
            'connectedAccount' => $this->module->senderApiClient()->getCurrentAccount(),
            'connectedUser' => $this->module->senderApiClient()->getCurrentUser(),
            'apiKey' => $this->module->senderApiClient()->getApiKey(),
            'disconnectUrl' => $disconnectUrl,
            'baseUrl' => $this->module->senderApiClient()->getBaseUrl(),
            'appUrl' => $this->module->senderApiClient()->getAppUrl(),
            'moduleVersion' => $this->module->version,
            'allForms' => $this->module->senderApiClient()->getAllForms(),
            'allLists' => $this->module->senderApiClient()->getAllLists(),
            'allowNewSignups' => Configuration::get('SPM_ALLOW_TRACK_NEW_SIGNUPS'),
            'allowCartTrack' => Configuration::get('SPM_ALLOW_TRACK_CARTS'),
            'allowNewsletter' => Configuration::get('SPM_ALLOW_NEWSLETTERS'),
            'allowForms' => Configuration::get('SPM_ALLOW_FORMS'),
            'cartsAjaxurl' => $this->module->module_url . '/ajax/carts_ajax.php?token='
                . Tools::getAdminToken($this->module->name),
            'formsAjaxurl' => $this->module->module_url . '/ajax/forms_ajax.php?token='
                . Tools::getAdminToken($this->module->name),
            'listsAjaxurl' => $this->module->module_url . '/ajax/lists_ajax.php?token='
                . Tools::getAdminToken($this->module->name),
            'dataAjaxurl' => $this->module->module_url . '/ajax/data_ajax.php?token='
                . Tools::getAdminToken($this->module->name),
            'syncListAjaxUrl' => $this->module->module_url . '/ajax/sync_list.php?token='
                . Tools::getAdminToken($this->module->name),
            'newsletterAjaxUrl' => $this->module->module_url . '/ajax/newsletter_ajax.php?token='
                . Tools::getAdminToken($this->module->name),
            'formId' => Configuration::get('SPM_FORM_ID'),
            'partnerOfferId' => Configuration::get('SPM_CUSTOMER_FIELD_PARTNER_OFFERS_ID'),
            'guestListId' => Configuration::get('SPM_GUEST_LIST_ID'),
            'customerListId' => Configuration::get('SPM_CUSTOMERS_LIST_ID'),
            'exportListId' => Configuration::get('SPM_SENDERAPP_SYNC_LIST_ID'),
            'genderFieldId' => Configuration::get('SPM_CUSTOMER_FIELD_GENDER_ID'),
            'birthdayFieldId' => Configuration::get('SPM_CUSTOMER_FIELD_BIRTHDAY_ID'),
            'customFields' => $customFields,
            'syncedList' => Configuration::get('SPM_SENDERAPP_SYNC_LIST_DATE')
                ? Configuration::get('SPM_SENDERAPP_SYNC_LIST_DATE') : '',
        ));
        #loading templates
        $output .= $this->context->smarty->fetch($this->module->views_url . '/templates/admin/view.tpl');

        return $output;
    }

    /**
     * Enabling default values for prestashop
     * @param $apiKey
     */
    private function enableDefaults($apiKey)
    {
        Configuration::updateValue('SPM_API_KEY', $apiKey);
        Configuration::updateValue('SPM_IS_MODULE_ACTIVE', true);
        Configuration::updateValue('SPM_ALLOW_IMPORT', true);
        Configuration::updateValue('SPM_ALLOW_TRACK_NEW_SIGNUPS', true);
        Configuration::updateValue('SPM_CUSTOMER_FIELD_FIRSTNAME', true);
        Configuration::updateValue('SPM_CUSTOMER_FIELD_LASTNAME', true);

        $resourceKey = $this->getResourceKey();
        Configuration::updateValue('SPM_SENDERAPP_RESOURCE_KEY_CLIENT', $resourceKey);
    }

    public function getResourceKey()
    {
        $currentAccount = $this->module->apiClient->getCurrentAccount();
        $resourceKey = $currentAccount ? $currentAccount->resource_key : '';
        if (empty($resourceKey)) {
            return;
        }
        return $resourceKey;
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
        $this->module->logDebug('Removing api key');
        Configuration::deleteByName('SPM_API_KEY');
        Configuration::deleteByName('SPM_SENDERAPP_RESOURCE_KEY_CLIENT');
        $this->removeSenderKeys();
        // Redirect back to module admin page
        $this->redirectToAdminMenu();
    }

    private function removeSenderKeys()
    {
        Configuration::updateValue('SPM_IS_MODULE_ACTIVE', 0);
        Configuration::updateValue('SPM_ALLOW_FORMS', '');
        Configuration::updateValue('SPM_ALLOW_IMPORT', 0);
        Configuration::updateValue('SPM_ALLOW_TRACK_NEW_SIGNUPS', 0);
        Configuration::updateValue('SPM_ALLOW_TRACK_CARTS', 0);
        Configuration::updateValue('SPM_CUSTOMER_FIELD_FIRSTNAME', 0);
        Configuration::updateValue('SPM_CUSTOMER_FIELD_LASTNAME', 0);
        Configuration::updateValue('SPM_FORM_ID', 0);
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

        if ($message) {
            Tools::redirect($url . $message);
        }

        Tools::redirect($url);
    }
}
