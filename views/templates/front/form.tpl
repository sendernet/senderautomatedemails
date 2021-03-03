{*
 * 2010-2021 Sender.net
 *
 * Sender.net Automated Emails
 *
 * @author Sender.net <info@sender.net>
 * @copyright 2010-2021 Sender.net
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License v. 3.0 (OSL-3.0)
 * Sender.net
 *}
{if $showForm and $formUrl}
    <div>
        <div class="col-xs-4" id="senderFormContainer">
            <div class="sender-form-field" data-sender-form-id="{$embedHash|escape:'htmlall':'UTF-8'}"></div>
        </div>
    </div>
{/if}
