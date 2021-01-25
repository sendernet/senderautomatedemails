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
                <small style="vertical-align: bottom;">v{$moduleVersion|escape:'htmlall':'UTF-8'}</small>
            </span>
        </div>
    </div>
    <div class="panel panel-default col-sm-2 col-xs-12" style="margin-top: 15px;">
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
                        {l s='Export customers' mod='senderautomatedemails'}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>


    <div class="col-sm-10 col-xs-12 sender-prestashop-content">
        {* HOME TAB *}
        <div id="spm-home" class="spm-tab-content spm-current">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="zmdi zmdi-notifications-active"></i>
                    {l s='Plugin status is' mod='senderautomatedemails'}
                    <span style="color:green;">{l s='ACTIVE' mod='senderautomatedemails'}</span>
                </div>
                <div class="panel-body">
                    <div class="spm-details-settings">
                        <table class="table" style="margin-bottom: 25px;">
                            <tr>
                                <td>
                                    {l s='Account:' mod='senderautomatedemails'}
                                </td>
                                <td>
                                    <strong>{$connectedAccount->title|escape:'htmlall':'UTF-8'}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    {l s='User email:' mod='senderautomatedemails'}
                                </td>
                                <td>
                                    <strong>{$connectedUser->email|escape:'htmlall':'UTF-8'}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    {l s='Username:' mod='senderautomatedemails'}
                                </td>
                                <td>
                                    <strong>{$connectedUser->username|escape:'htmlall':'UTF-8'}</strong>
                                </td>
                            </tr>
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
                        <a href="{$disconnectUrl|escape:'htmlall':'UTF-8'}" class="btn btn-lg btn-danger">
                            {l s='Disconnect' mod='senderautomatedemails'}
                        </a>
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