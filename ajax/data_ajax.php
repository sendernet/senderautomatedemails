<?php
/**
 * 2010-2025 Sender.net
 *
 * Sender.net Automated Emails
 *
 * @author Sender.net <info@sender.net>
 * @copyright 2010-2025 Sender.net
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License v. 3.0 (OSL-3.0)
 * Sender.net
 */

require_once(dirname(__FILE__) . '/../../../config/config.inc.php');
require_once(dirname(__FILE__) . '/../senderautomatedemails.php');

ini_set('display_errors', '0');
header('Content-Type: application/json');

$receivedToken = Tools::getValue('token');
$expectedToken = Configuration::get('SPM_SENDERAPP_MODULE_TOKEN');

if (!$expectedToken) {
    http_response_code(403);
    die(json_encode([
        'result' => false,
        'error' => 'Missing security token.',
    ]));
}

if ($receivedToken !== $expectedToken) {
    http_response_code(403);
    die(json_encode([
        'result' => false,
        'error' => 'Invalid token',
        'received' => $receivedToken,
        'expected' => $expectedToken,
    ]));
}

$senderautomatedemails = new SenderAutomatedEmails();

switch (Tools::getValue('action')) {
    case 'addData':
        Configuration::updateValue('SPM_CUSTOMER_FIELD_' . Tools::getValue('option_name'), 1);
        die(json_encode(['result' => true]));

    case 'removeData':
        Configuration::updateValue('SPM_CUSTOMER_FIELD_' . Tools::getValue('option_name'), 0);
        die(json_encode(['result' => true]));

    case 'getIfEnabled':
        $status = Configuration::get('SPM_CUSTOMER_FIELD_' . Tools::getValue('option_name'));
        die(json_encode(['result' => $status]));

    case 'savePartnerOffers':
        if (Tools::getValue('field_id') === 0) {
            Configuration::updateValue('SPM_CUSTOMER_FIELD_PARTNER_OFFERS_ID', Tools::getValue('field_id'));
            die(json_encode(['result' => 'Partner offer field not active']));
        }
        Configuration::updateValue('SPM_CUSTOMER_FIELD_PARTNER_OFFERS_ID', Tools::getValue('field_id'));
        die(json_encode(['result' => true]));

    case 'genderField':
        Configuration::updateValue('SPM_CUSTOMER_FIELD_GENDER', Tools::getValue('field_id') == 0 ? Tools::getValue('field_id') : null);
        die(json_encode(['result' => true]));

    case 'birthdayField':
        Configuration::updateValue('SPM_CUSTOMER_FIELD_BIRTHDAY', Tools::getValue('field_id') == 0 ? Tools::getValue('field_id') : null);
        die(json_encode(['result' => true]));

    case 'languageField':
        Configuration::updateValue('SPM_CUSTOMER_FIELD_LANGUAGE', Tools::getValue('field_id') == 0 ? Tools::getValue('field_id') : null);
        die(json_encode(['result' => true]));

    case 'countryField':
        Configuration::updateValue('SPM_CUSTOMER_FIELD_COUNTRY', Tools::getValue('field_id') == 0 ? Tools::getValue('field_id') : null);
        die(json_encode(['result' => true]));

    default:
        http_response_code(400);
        die(json_encode(['result' => false, 'error' => 'Unknown action']));
}
