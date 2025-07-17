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
    case 'saveAllowForms':
        if (Configuration::updateValue('SPM_ALLOW_FORMS', !Configuration::get('SPM_ALLOW_FORMS'))) {
            $senderautomatedemails->resetSenderFormCache();
            die(json_encode(['result' => Configuration::get('SPM_ALLOW_FORMS')]));
        }
        die(json_encode(['result' => false]));

    case 'saveFormId':
        $formId = Tools::getValue('form_id');

        if (!empty($formId)) {
            if (Configuration::updateValue('SPM_FORM_ID', $formId)) {
                $senderautomatedemails->resetSenderFormCache();
                die(json_encode(['result' => true]));
            }
        }

        die(json_encode(['result' => false]));

    default:
        http_response_code(400);
        die(json_encode(['result' => false, 'error' => 'Unknown action']));
}
