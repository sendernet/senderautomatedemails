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
        case 'saveAllowForms':
            if (Configuration::updateValue('SPM_ALLOW_FORMS', !Configuration::get('SPM_ALLOW_FORMS'))) {
                die(json_encode(array(
                    'result' => Configuration::get('SPM_ALLOW_FORMS')
                )));
            }
            die(json_encode(array( 'result' => false )));
            // break;
        case 'saveFormId':
            if (Configuration::updateValue('SPM_FORM_ID', Tools::getValue('form_id'))) {
                die(json_encode(array( 'result' => true)));
            }
            die(json_encode(array( 'result' => false )));
            // break;
        default:
            exit;
    }
}
exit;
