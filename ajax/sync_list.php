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
                $response = $senderautomatedemails->exportDataToSender();
                die(json_encode(['result' => $response]));
            } catch (Exception $e) {
                die(json_encode(['result' => $response]));
            }
            //no break
        case 'exportList':
            $listId = Tools::getValue('list_id');

            if ($listId === '0') {
                $listId = 0;
            }
            Configuration::updateValue('SPM_SENDERAPP_SYNC_LIST_ID', $listId);

            die(json_encode(['result' => 'Updated']));
        default:
            exit;
    }
}
