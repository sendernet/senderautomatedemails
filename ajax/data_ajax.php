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

require_once(dirname(__FILE__) . '/../../../config/config.inc.php');
require_once(dirname(__FILE__) . '/../senderautomatedemails.php');

$senderautomatedemails = new SenderAutomatedEmails();


if (Tools::getValue('token') !== Tools::getAdminToken($senderautomatedemails->name)) {
    die(json_encode(array('result' => false)));
} else {
    switch (Tools::getValue('action')) {
        case 'addData':
            Configuration::updateValue('SPM_CUSTOMER_FIELD_' . Tools::getValue('option_name'), 1);
            break;
        case 'removeData':
            Configuration::updateValue('SPM_CUSTOMER_FIELD_' . Tools::getValue('option_name'), 0);
            break;
        case 'getIfEnabled':
            $status = Configuration::get('SPM_CUSTOMER_FIELD_' . Tools::getValue('option_name'));
            echo $status;
            break;
        case 'savePartnerOffers':
            if (Tools::getValue('field_id') === 0) {
                Configuration::updateValue('SPM_CUSTOMER_FIELD_PARTNER_OFFERS_ID', Tools::getValue('field_id'));
                die(json_encode(['result' => 'Partner offer field not active']));
            }
            Configuration::updateValue('SPM_CUSTOMER_FIELD_PARTNER_OFFERS_ID', Tools::getValue('field_id'));
            break;
        case 'genderField':
            if (Tools::getValue('field_id') === 0) {
                Configuration::updateValue('SPM_CUSTOMER_FIELD_GENDER_ID', Tools::getValue('field_id'));
                die(json_encode(['result' => 'Gender field not active']));
            }
            Configuration::updateValue('SPM_CUSTOMER_FIELD_GENDER_ID', Tools::getValue('field_id'));
            break;
        case 'birthdayField':
            if (Tools::getValue('field_id') === 0) {
                Configuration::updateValue('SPM_CUSTOMER_FIELD_BIRTHDAY_ID', Tools::getValue('field_id'));
                die(json_encode(['result' => 'Birthday field not active']));
            }
            Configuration::updateValue('SPM_CUSTOMER_FIELD_BIRTHDAY_ID', Tools::getValue('field_id'));
            break;
        default:
            exit;
    }
}
exit;
