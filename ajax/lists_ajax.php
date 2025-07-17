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
    case 'saveCustomerListId':
        if (Configuration::updateValue('SPM_CUSTOMERS_LIST_ID', Tools::getValue('list_id'))) {
            Configuration::updateValue('SPM_CUSTOMERS_LIST_NAME', Tools::getValue('list_name'));
            die(json_encode(['result' => true]));
        }
        Configuration::updateValue('SPM_CUSTOMERS_LIST_ID', 0);
        die(json_encode(['result' => false]));

    case 'saveGuestListId':
        if (Configuration::updateValue('SPM_GUEST_LIST_ID', Tools::getValue('list_id'))) {
            Configuration::updateValue('SPM_GUEST_LIST_NAME', Tools::getValue('list_name'));
            die(json_encode(['result' => true]));
        }
        Configuration::updateValue('SPM_GUEST_LIST_ID', 0);
        die(json_encode(['result' => false]));

    case 'saveALlowNewSignups':
        if (Configuration::updateValue(
            'SPM_ALLOW_TRACK_NEW_SIGNUPS',
            !Configuration::get('SPM_ALLOW_TRACK_NEW_SIGNUPS')
        )) {
            die(json_encode([
                'result' => Configuration::get('SPM_ALLOW_TRACK_NEW_SIGNUPS')
            ]));
        }
        die(json_encode(['result' => false]));

    default:
        http_response_code(400);
        die(json_encode(['result' => false, 'error' => 'Unknown action']));
}

exit;
