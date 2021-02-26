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
<div id="spm-newsletter" class="spm-tab-content">

    {* NEWSLETTER *}
    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="zmdi zmdi-email"></i>
            {l s='Newsletter' mod='senderautomatedemails'}
            {if not $allowNewsletter}
                <span id="swToggleNewsletterTitle" style="color:red;">
                            {l s='disabled' mod='senderautomatedemails'}
                        </span>
            {else}
                <span id="swToggleNewsletterTitle" style="color:green;">
                            {l s='enabled' mod='senderautomatedemails'}
                        </span>
            {/if}
        </div>
        <div class="panel-body">
            <div class="spm-details-settings">
                <div class="alert alert-addons">
                    {l s='Newsletter. Use this option if you are not going to use the Cart tracking feature
but still you would like to add your customers to your Sender.net application.' mod='senderautomatedemails'}
                </div>
                <button id="swToggleNewsletter" class="btn btn-lg {if not $allowNewsletter}btn-success{else}btn-danger{/if}">
                    {if not $allowNewsletter}
                        {l s='Enable' mod='senderautomatedemails'}
                    {else}
                        {l s='Disable' mod='senderautomatedemails'}
                    {/if}
                </button>
            </div>
            <div class="panel-body">
                {if empty($allLists)}
                    <div class="alert alert-warning">
                        {l s='To track customers carts you must have at least one list at your Sender.net`s account' mod='senderautomatedemails'}
                    </div>
                    <p>
                        <a class="btn btn-lg btn-info" href="{$baseUrl|escape:'htmlall':'UTF-8'}/v2/tags">
                            {l s='Create a new list' mod='senderautomatedemails'}
                        </a>
                    </p>
                {else}
                    <blockquote>
                        <p>
                            {l s='Select to which list save customers whose carts were tracked.' mod='senderautomatedemails'}
                        </p>
                    </blockquote>
                    <div class="col-xs-12">
                        <div id="swCustomerListSelectContainer" class="form-group">
                            <label for="swCustomerListSelect">
                                {l s='Select customer list' mod='senderautomatedemails'}
                            </label>
                            <select {if not $allowNewsletter}disabled{/if} class="sender-lists" id="swCustomerListSelectNewsletter">
                                <option value="0">
                                    {l s='No list' mod='senderautomatedemails'}
                                </option>
                                {foreach $allLists as $list}
                                    <option id="{$list->title|escape:'htmlall':'UTF-8'}"
                                            {if $list->id eq $customerListId}selected="selected"{/if}
                                            value="{$list->id|escape:'htmlall':'UTF-8'}">
                                        {$list->title|escape:'htmlall':'UTF-8'}
                                    </option>
                                {/foreach}
                            </select>
                        </div>
                        <div style="visibility: hidden;" class="alert alert-success updated-first">
                            {l s='Saved' mod='senderautomatedemails'}
                        </div>
                    </div>
                {/if}
            </div>
            {*GUEST LIST*}
            <div class="panel-body">
                {if empty($allLists)}
                    <div class="alert alert-warning">
                        {l s='To track customers carts you must have at least one list at your Sender.net`s account.' mod='senderautomatedemails'}
                    </div>
                    <p>
                        <a class="btn btn-lg btn-info" href="{$baseUrl|escape:'htmlall':'UTF-8'}/mailinglists/add">
                            {l s='Create a new list' mod='senderautomatedemails'}
                        </a>
                    </p>
                {else}
                    <blockquote>
                        <p>
                            {l s='Select to which list save guests or new signups whose carts were tracked.' mod='senderautomatedemails'}
                        </p>
                    </blockquote>
                    <div class="col-xs-12">
                        <div id="swGuestListSelectContainer" class="form-group">
                            <label for="swGuestListSelect">
                                {l s='Select guest list' mod='senderautomatedemails'}
                            </label>
                            <select {if not $allowNewsletter}disabled{/if} class="sender-lists" id="swGuestListSelectNewsletter">
                                <option value="0">
                                    {l s='No list' mod='senderautomatedemails'}
                                </option>
                                {foreach $allLists as $list}
                                    <option id="{$list->title|escape:'htmlall':'UTF-8'}"
                                            {if $list->id eq $guestListId}selected="selected"{/if}
                                            value="{$list->id|escape:'htmlall':'UTF-8'}">
                                        {$list->title|escape:'htmlall':'UTF-8'}
                                    </option>
                                {/foreach}
                            </select>
                        </div>
                        <div style="visibility: hidden;" class="alert alert-success updated-second">
                            {l s='Saved' mod='senderautomatedemails'}
                        </div>
                    </div>
                {/if}
            </div>
        </div>

        <div class="panel-body">
            <div style="margin-top: 30px" class="alert alert-info">
                {l s='When Cart tracking feature would be enable, this feature would be obsolete' mod='senderautomatedemails'}
            </div>
        </div>
    </div>
</div>