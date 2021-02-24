<?php
/**
 * 2010-2018 Sender.net
 *
 * Sender.net Automated Emails
 *
 * @author Sender.net <info@sender.net>
 * @copyright 2010-2018 Sender.net
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License v. 3.0 (OSL-3.0)
 * Sender.net
 */

class SenderAutomatedEmailsRecoverModuleFrontController extends ModuleFrontController
{
    /**
     * Handle cart recover
     *
     * @return void
     */
    public function display()
    {
        if (!Configuration::get('SPM_IS_MODULE_ACTIVE')
            || !Tools::getIsset('hash')) {
            Tools::redirect($this->context->link->getPageLink('index'));
            $this->module->logDebug('Recover validation failed.');
            return;
        }
        
        // Here we retrieve the cart from Sender
        $cart = $this->module->senderApiClient()->cartGet(Tools::getValue('hash', 'NULL'));

        $this->module->logDebug('Cart get by hash: ' . Tools::getValue('hash', 'NULL'));
        $this->module->logDebug('Cart data: ' . json_encode($cart));

        if (!isset($cart->cart_id)) {
            Tools::redirect($this->context->link->getPageLink('index'));
            return;
        }

        // Assign cart for the user
        $this->context->cart = new Cart($cart->cart_id);
        $this->context->cookie->id_cart = $cart->cart_id;

        // Redirect
        Tools::redirect($this->context->link->getPageLink('index'));
    }
}
