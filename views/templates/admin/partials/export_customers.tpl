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
<div id="spm-export-customers" class="spm-tab-content">

    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="zmdi zmdi-import-export"></i>
            {l s='Export all your customers to a Sender.net group' mod='senderautomatedemails'}
        </div>
        <div class="panel-body">
            <div class="panel-body">
                {if empty($allLists)}
                    <div class="alert alert-warning">
                        {l s='To save customers you must have at least one list at your Sender.net`s account' mod='senderautomatedemails'}
                    </div>
                    <p>
                        <a class="btn btn-lg btn-info" href="{$baseUrl|escape:'htmlall':'UTF-8'}/v2/tags">
                            {l s='Create a new list' mod='senderautomatedemails'}
                        </a>
                    </p>
                {else}
                    <blockquote>
                        <p>
                            {l s='Select to which list export customers as subscribers to Sender.net' mod='senderautomatedemails'}
                        </p>
                    </blockquote>
                    <div id="swExportClientToListContainer" class="form-group">
                        <label for="swExportClientToList">
                            {l s='Select list' mod='senderautomatedemails'}
                        </label>
                        <select class="sender-lists" id="swExportClientToList" name="swExportListSelect"
                                value="{$exportListId|escape:'htmlall':'UTF-8'}">
                            {if empty($allLists)}
                            <option value="0">
                                {l s='No lists created in Sender app' mod='senderautomatedemails'}
                            </option>
                            {else}
                            <option value="0">
                                {l s='Select a list' mod='senderautomatedemails'}
                            </option>
                            {foreach $allLists as $list}
                                <option id="{$list->title|escape:'htmlall':'UTF-8'}"
                                        {if $list->id eq $exportListId}selected="selected"{/if}
                                        value="{$list->id|escape:'htmlall':'UTF-8'}">
                                    {$list->title|escape:'htmlall':'UTF-8'}
                                </option>
                            {/foreach}
                            {/if}
                        </select>
                    </div>
                {/if}
                <br><br>
                <blockquote>
                    <p>
                        {l s='Would be exporting all your customers to Sender.net' mod='senderautomatedemails'}
                        <br><br>
                        {l s='If a subscriber already exists it would get updated according to your customer information.' mod='senderautomatedemails'}
                        <br><br>
                        {l s='Additionally, all your products and order history will be exported to Sender.net' mod='senderautomatedemails'}
                    </p>
                </blockquote>

                <div class="panel-body">
                    <button id="syncList"
                            class="btn btn-lg btn-sender">
                        {l s='Synchronize with Sender.net' mod='senderautomatedemails'}
                    </button>

                    <p style="margin-top: 15px;" id="syncDataParent">
                        <small id="syncTime">
                            Last time synchronized:
                            <span id="syncDate">{$syncedList|escape:'htmlall':'UTF-8'}</span>
                        </small>
                    </p>

                    <a href="https://app.sender.net/settings/connected-stores"
                       target="_blank"
                       class="btn btn-default"
                       style="margin-top: 10px;">
                        {l s='View exported data in Sender.net' mod='senderautomatedemails'}
                    </a>

                    <div id="responseMessage"
                         class="alert alert-success"
                         style="display: none; margin-top: 10px; margin-bottom: 0;">
                    </div>

                    <div id="syncError"
                         class="alert alert-danger"
                         style="display: none; margin-top: 10px;">
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>