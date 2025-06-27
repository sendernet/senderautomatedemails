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
<script>
    var cartsAjaxurl = "{$cartsAjaxurl|escape:'htmlall':'UTF-8'}";
    var formsAjaxurl = "{$formsAjaxurl|escape:'htmlall':'UTF-8'}";
    var listsAjaxurl = "{$listsAjaxurl|escape:'htmlall':'UTF-8'}";
    var dataAjaxurl = "{$dataAjaxurl|escape:'htmlall':'UTF-8'}";
    var syncListAjaxUrl = "{$syncListAjaxUrl|escape:'htmlall':'UTF-8'}";
</script>
<div class="sender-prestashop-card">
    <div class="sender-prestashop-header">
        <div class="spm-text-left">
            <img src="{$imageUrl|escape:'htmlall':'UTF-8'}" alt="Sender Logo">
            <span>
                <small id="current-version" style="vertical-align: bottom;">v{$moduleVersion|escape:'htmlall':'UTF-8'}</small>
                <a id="update-link" style="vertical-align: bottom;display: none;" href="https://help.sender.net/knowledgebase/the-documentation-of-our-prestashop-plugin/" title="New version available" target="_blank">
                    <span><small><img style="max-width: 15px" src="https://img.icons8.com/pulsar-color/48/null/logout-rounded-up.png"/></small></span>
                </a>
            </span>
        </div>
    </div>
    <div class="panel panel-default col-sm-3 col-xs-12" style="margin-top: 15px;">
        <div class="panel-heading">
            <i class="zmdi zmdi-notifications-active"></i>
            {l s='Menu' mod='senderautomatedemails'}
        </div>
        <div class="panel-body" style="padding: 0px;">
            <div class="">
                <ul class="spm-tabs spm-main-menu">
                    <li class="tab-link spm-current spm-active" data-tab="spm-home">
                        <a href="#!spm-home">
                            <i class="zmdi zmdi-home"></i>
                            {l s='Home as' mod='senderautomatedemails'}
                        </a>
                    </li>
                    <li class="tab-link" data-tab="spm-forms">
                        <a href="#!spm-forms">
                            <i class="zmdi zmdi-format-list-bulleted"></i>
                            {l s='Forms' mod='senderautomatedemails'}
                        </a>
                    </li>
                    <li class="tab-link" data-tab="spm-carts">
                        <a href="#!spm-carts">
                            <i class="zmdi zmdi-shopping-cart"></i>
                            {l s='Cart tracking' mod='senderautomatedemails'}
                        </a>
                    </li>
                    <li class="tab-link" data-tab="spm-customer-data">
                        <a href="#!spm-customer-data"">
                        <i class="zmdi zmdi-accounts-alt"></i>
                        {l s='Customer Data' mod='senderautomatedemails'}
                        </a>
                    </li>
                    <li class="tab-link" data-tab="spm-export-customers">
                        <a href="#!spm-export-customers"">
                        <i class="zmdi zmdi-import-export"></i>
                        {l s='Export data to sender app' mod='senderautomatedemails'}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>


    <div class="col-sm-9 col-xs-12 sender-prestashop-content">
        {* HOME TAB *}
        <div id="spm-home" class="spm-tab-content spm-current">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="zmdi zmdi-notifications-active"></i>
                    {l s='Plugin status is' mod='senderautomatedemails'}
                    {if $integration_status}
                        <span style="color:#ff8d00;">{l s='ACTIVE' mod='senderautomatedemails'}</span>
                    {else}
                        <span style="color:#ff0000;">{l s='INACTIVE' mod='senderautomatedemails'}</span>
                    {/if}
                </div>

                <div class="panel-body">
                    <div class="spm-details-settings">
                        <table class="table" style="margin-bottom: 25px;">
                            {if !empty($connectedAccount)}
                                <tr>
                                    <td>
                                        {l s='Account:' mod='senderautomatedemails'}
                                    </td>
                                    <td>
                                        <strong>{$connectedAccount->title|escape:'htmlall':'UTF-8'}</strong>
                                    </td>
                                </tr>
                            {/if}
                            {if !empty($connectedUser)}
                                <tr>
                                    <td>
                                        {l s='User email:' mod='senderautomatedemails'}
                                    </td>
                                    <td>
                                        <strong>{$connectedUser->email|escape:'htmlall':'UTF-8'}</strong>
                                    </td>
                                </tr>
                            {/if}
                            <tr>
                                <td>
                                    {l s='Api key:' mod='senderautomatedemails'}
                                </td>
                                <td>
                                    <span>
                                        <strong>{$apiKey|escape:'htmlall':'UTF-8'}</strong>
                                    </span>
                                </td>
                            </tr>
                        </table>
                        {if $integration_status}
                        <a href="{$disconnectUrl|escape:'htmlall':'UTF-8'}" class="btn btn-lg btn-sender">
                            {l s='Disconnect' mod='senderautomatedemails'}
                        </a>
                        {else}
                            <span style="color:#ff0000;">{l s='INACTIVE' mod='senderautomatedemails'}</span>
                            <!-- Show reconnect form when INACTIVE -->
                            <div class="row">
                                <div class="col-xs-12" style="padding: 10px;">
                                    <form action="{$link->getAdminLink('AdminSenderAutomatedEmails')|escape:'htmlall':'UTF-8'}"
                                          method="post" style="margin-bottom: 20px;">
                                        <div class="form-group">
                                            <label for="apiKey">{l s='API access token:' mod='senderautomatedemails'}</label>
                                            <input type="hidden" name="sender_reconnect" value="true">
                                            <input type="text" id="apiKey" name="apiKey"
                                                   placeholder="{l s='Paste here the new API token to reconnect' mod='senderautomatedemails'}"
                                                   required class="form-control">
                                        </div>
                                        <input type="submit" value="{l s='Reconnect' mod='senderautomatedemails'}"
                                               name="actionApiKey" class="btn btn-lg btn-sender" style="color: #fff;">
                                    </form>
                                    <div class="row" style="margin-top: 20px;">
                                        <div class="col-xs-12">
                                            <p>{l s='To disconnect the sender store, click the button below:' mod='senderautomatedemails'}</p>
                                            <a href="{$disconnectUrl|escape:'htmlall':'UTF-8'}"
                                               class="btn btn-lg btn-sender">
                                                {l s='Disconnect' mod='senderautomatedemails'}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
        {* FORM Settings tab *}
        {include file='././partials/forms.tpl'}

        {* CART TRACKING Tab *}
        {include file='././partials/cart_tracking.tpl'}

        {*Custom data - Fields partial*}
        {include file='././partials/customer_data.tpl'}

        {*Export customers*}
        {include file='././partials/export_customers.tpl'}
    </div>