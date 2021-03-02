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
        case 'saveAllowNewsletter':
            if (Configuration::updateValue('SPM_ALLOW_NEWSLETTERS', !Configuration::get('SPM_ALLOW_NEWSLETTERS'))) {
                die(json_encode(array('result' => Configuration::get('SPM_ALLOW_NEWSLETTERS'))));
            }
            die(json_encode(array( 'result' => false )));
        case 'addCheckboxNewsletters':
            Configuration::updateValue('SPM_SHOW_NEWSLETTER_CHECKBOX', 1);
            break;
        case 'removeCheckboxNewsletters':
            Configuration::updateValue('SPM_SHOW_NEWSLETTER_CHECKBOX', 0);
            break;
        case 'getIfEnabledNewsletterCheckbox':
            $status = Configuration::get('SPM_SHOW_NEWSLETTER_CHECKBOX');
            echo $status;
            break;
        default:
            exit;
    }
}
exit;
