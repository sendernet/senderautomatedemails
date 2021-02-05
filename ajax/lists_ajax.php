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

require_once(dirname(__FILE__) . '/../../../config/config.inc.php');
require_once(dirname(__FILE__) . '/../senderautomatedemails.php');

$senderautomatedemails = new SenderAutomatedEmails();


if (Tools::getValue('token') !== Tools::getAdminToken($senderautomatedemails->name)) {
    die(json_encode(array( 'result' => false )));
} else {
    switch (Tools::getValue('action')) {
        case 'saveCustomerListId':
            if (Configuration::updateValue('SPM_CUSTOMERS_LIST_ID', Tools::getValue('list_id'))) {
                Configuration::updateValue('SPM_CUSTOMERS_LIST_NAME', Tools::getValue('list_name'));
                die(json_encode(array( 'result' => true)));
            }
            Configuration::updateValue('SPM_CUSTOMERS_LIST_ID', 0);
            die(json_encode(array( 'result' => false )));
            // break;
        case 'saveGuestListId':
            if (Configuration::updateValue('SPM_GUEST_LIST_ID', Tools::getValue('list_id'))) {
                Configuration::updateValue('SPM_GUEST_LIST_NAME', Tools::getValue('list_name'));
                die(json_encode(array( 'result' => true)));
            }
            Configuration::updateValue('SPM_GUEST_LIST_ID', 0);
            die(json_encode(array( 'result' => false )));
            // break;
        case 'saveALlowNewSignups':
            if (Configuration::updateValue(
                'SPM_ALLOW_TRACK_NEW_SIGNUPS',
                !Configuration::get('SPM_ALLOW_TRACK_NEW_SIGNUPS')
            )) {
                die(json_encode(array(
                    'result' => Configuration::get('SPM_ALLOW_TRACK_NEW_SIGNUPS')
                )));
            }
            die(json_encode(array( 'result' => false )));
            // break;
        default:
            exit;
    }
}
exit;
