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
    die(json_encode(array('result' => false)));
} else {
    switch (Tools::getValue('action')) {
        case 'syncList':
            try {
                $response = $senderautomatedemails->syncList();
                die(json_encode(['result' => $response]));
            } catch (Exception $e) {
                die(json_encode(['result' => $response]));
            }
            //no break
        case 'exportList':
            if (Tools::getValue('list_id') === 0) {
                Configuration::updateValue('SPM_SENDERAPP_SYNC_LIST_ID', Tools::getValue('list_id'));
                die(json_encode(['result' => 'No export list selected, will import without saving to list']));
            }
            Configuration::updateValue('SPM_SENDERAPP_SYNC_LIST_ID', Tools::getValue('list_id'));
            die(json_encode(['result' => 'Updated']));
        default:
            exit;
    }
}
