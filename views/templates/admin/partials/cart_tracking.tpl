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
<div id="spm-carts" class="spm-tab-content">

    {* ALLOW CART TRACK *}
    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="zmdi zmdi-shopping-cart"></i>
            {l s='Customer cart tracking is' mod='senderautomatedemails'}
            {if not $allowCartTrack}
                <span id="swToggleCartTrackTitle" style="color:red;">
                            {l s='disabled' mod='senderautomatedemails'}
                        </span>
            {else}
                <span id="swToggleCartTrackTitle" style="color:#ff8d00;">
                            {l s='enabled' mod='senderautomatedemails'}
                        </span>
            {/if}
        </div>
        <div class="panel-body">
            <div class="spm-details-settings">
                <div class="alert alert-info">
                    <p>
                        <i class="zmdi zmdi-shopping-cart" style="font-size: 20px"></i>
                        {l s='Enable Sender track system to track your customers cart.' mod='senderautomatedemails'}
                        <a href="https://landing.sender.net/abandoned-cart-email-template"
                           target="_blank">{l s='Learn how to set up abandoned carts automation' mod='senderautomatedemails'}</a>
                    </p>
                    <p>
                        <i class="zmdi zmdi-email" style="font-size: 20px"></i>
                        {l s='Newsletter. Use this option if you are not going to use the Cart tracking feature
but still you would like to add your customers to your Sender.net application.' mod='senderautomatedemails'}
                    </p>
                </div>
                <div class="form-group">
                    {*CART-TRACKING*}
                    <table class="sender-table" style="max-width: 600px; border: none">
                        <tr style="border: none">
                            <th>{l s='Activate cart tracking' mod='senderautomatedemails'} <i
                                        class="zmdi zmdi-shopping-cart"></i></th>
                            <th>{l s='Activate newsletters' mod='senderautomatedemails'} <i class="zmdi zmdi-email"></i>
                            </th>
                        </tr>
                        <tr>
                            <td>
                                <button style="display: inline" id="swToggleCartTrack"
                                        class="btn btn-lg {if not $allowCartTrack}btn-sender{else}btn-danger__sender{/if}">
                                    {if not $allowCartTrack}
                                        {l s='Enable' mod='senderautomatedemails'}
                                    {else}
                                        {l s='Disable' mod='senderautomatedemails'}
                                    {/if}
                                </button>
                            </td>
                            <td>
                                <button id="swToggleNewsletter"
                                        class="btn btn-lg {if not $allowNewsletter}btn-sender{else}btn-danger__sender{/if}">
                                    {if not $allowNewsletter}
                                        {l s='Enable' mod='senderautomatedemails'}
                                    {else}
                                        {l s='Disable' mod='senderautomatedemails'}
                                    {/if}
                                </button>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            {*CUSTOMER LIST*}
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
                    <div class="col-xs-12" id="customer_list_tab">
                        <div id="swCustomerListSelectContainer" class="form-group">
                            <label for="swCustomerListSelect">
                                {l s='Select customer list' mod='senderautomatedemails'}
                            </label>
                            <select {if not $allowCartTrack && not $allowNewsletter}disabled{/if} class="sender-lists" id="swCustomerListSelect"
                                    value="{$formId|escape:'htmlall':'UTF-8'}">
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
                        <span style="visibility: hidden;" class="alert alert-success alert-success__sender updated-first">
                            {l s='Saved' mod='senderautomatedemails'}
                        </span>
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
                    <div class="col-xs-12" id="guest_list_tab">
                        <div id="swGuestListSelectContainer" class="form-group">
                            <label for="swGuestListSelect">
                                {l s='Select guest list' mod='senderautomatedemails'}
                            </label>
                            <select {if not $allowCartTrack && not $allowNewsletter}disabled{/if} class="sender-lists" id="swGuestListSelect"
                                    value="{$formId|escape:'htmlall':'UTF-8'}">
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
                        <span style="visibility: hidden;" class="alert alert-success alert-success__sender saved-sender">
                            {l s='Saved' mod='senderautomatedemails'}
                        </span>
                    </div>
                {/if}
            </div>
                <div class="panel-body" id="newsletterCheckbox" style="opacity: 0;">
                    <label class="">
                        <input id="newsletterCheck" class="spm-receive-newsletters-input sender-checkbox"
                               type="checkbox"
                               value="" name="">
                        <span>{l s='Add checkbox in order checkout' mod='senderautomatedemails'}</span>
                    </label>

                    <div class="alert alert-info">
                        <p>
                            <i class="zmdi zmdi-email" style="font-size: 20px"></i>
                            {l s='Choose from adding a checkbox asking for permissions to become a subscriber or
no checkbox. This would be showing up in the order checkout.' mod='senderautomatedemails'}
                        </p>
                    </div>
                </div>
        </div>
    </div>
</div>