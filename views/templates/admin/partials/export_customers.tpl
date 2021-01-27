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

    {* ALLOW CART TRACK *}
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
                        <select id="swExportClientToList" name="swExportListSelect"
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
                        {l s='Would be migrating all your customers to Sender.net which opted to receive newsletter information.' mod='senderautomatedemails'}
                        <br><br>
                        {l s='if a subscriber already exists it would get updated according to your customer information.' mod='senderautomatedemails'}
                    </p>
                </blockquote>

                <div class="panel-body">
                    <button id="syncList"
                            class="btn btn-lg btn-warning">{l s='Synchronize this list with Sender' mod='senderautomatedemails'}</button>
                    <p style="margin-top: 15px">
                        <small>Last time synchronized: <span id="syncDate">{$syncedList|escape:'htmlall':'UTF-8'}</span></small>
                    </p>
                    <p style="margin-top: 15px">
                        <small class="alert alert-danger" id="syncError" style="display: none"></small>
                    </p>
                </div>
            </div>
        </div>
    </div>

</div>