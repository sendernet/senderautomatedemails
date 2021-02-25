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
<div id="spm-forms" class="spm-tab-content">
    {if empty($allForms)}
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="zmdi zmdi-format-list-bulleted"></i>
                {l s='Form widget information' mod='senderautomatedemails'}
            </div>
            <div class="panel-body">
                <div class="alert alert-warning">
                    {l s='There was no form found on your Sender.net`s account. Please create a new form and refresh this page' mod='senderautomatedemails'}
                </div>
                <p>
                    <a class="btn btn-lg btn-info" href="{$appUrl|escape:'htmlall':'UTF-8'}/forms" target="_blank">
                        {l s='Create a form' mod='senderautomatedemails'}
                    </a>
                </p>
            </div>
        </div>
    {else}
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="zmdi zmdi-format-list-bulleted"></i>
                {l s='Widget is ' mod='senderautomatedemails'}
                {if not $allowForms}
                    <span id="swToggleWidgetTitle" style="color:red;">
                            {l s='disabled' mod='senderautomatedemails'}
                        </span>
                {else}
                    <span id="swToggleWidgetTitle" style="color:green;">
                            {l s='enabled' mod='senderautomatedemails'}
                        </span>
                {/if}
            </div>
            <div class="panel-body">
                <div class="spm-details-settings">
                    <button id="swToggleWidget"
                            class="btn btn-lg {if not $allowForms}btn-success{else}btn-danger{/if}">
                        {if not $allowForms}
                            {l s='Enable' mod='senderautomatedemails'}
                        {else}
                            {l s='Disable' mod='senderautomatedemails'}
                        {/if}
                    </button>
                </div>
                <blockquote>
                    <p>
                        {l s='When enabled, a Sender.net form widget will appear in the customization menu.
                             It allows you to insert your Sender.net form.' mod='senderautomatedemails'}
                    </p>
                </blockquote>
                <div class="col-xs-12" id="forms_tab">
                    <div class="form-group">
                        <label for="swFormsSelect">
                            {l s='Select form' mod='senderautomatedemails'}
                        </label>
                        <select {if not $allowForms}disabled{/if} class="sender-lists" id="swFormsSelect" name="swFormsSelect">
                            <option value="0">
                                {l s='Select a form' mod='senderautomatedemails'}
                            </option>
                            {foreach $allForms as $form}
                                {if $form->type === 'embed'}
                                <option {if $form->id eq $formId}selected="selected"{/if}value="{$form->id|escape:'htmlall':'UTF-8'}"
                                        {if !$form->is_active} disabled{/if}>
                                    {$form->title|escape:'htmlall':'UTF-8'} {if !$form->is_active} <strong>| {l s='Form not active' mod='senderautomatedemails'}</strong>  {/if}
                                </option>
                                {/if}
                            {/foreach}
                        </select>
                    </div>
                    <div style="visibility: hidden;"  class="alert alert-success updated-first">
                        {l s='Saved' mod='senderautomatedemails'}
                    </div>
                </div>
            </div>
            <div class="panel-body">
                <blockquote>
                    <p>
                        {l s='To avoid pop-up forms from showing, they must be hidden to show on this website. To hide pop-up forms, go to your Sender account, Forms.' mod='senderautomatedemails'}
                    </p>
                </blockquote>
            </div>
            <div class="panel-body">
                <a class="btn btn-lg btn-info" href="{$appUrl|escape:'htmlall':'UTF-8'}/forms" target="_blank"
                   rel="help">
                    {l s='Manage your forms in Sender.net' mod='senderautomatedemails'}
                </a>

                <div style="margin-top: 30px" class="alert alert-info">
                    <h4>{l s='About the form location.' mod='senderautomatedemails' }</h4>
                    <p>
                        {l s='Initiallink-differently the embed form would be hook to "DisplayFooterBefore" or "DisplayFooter" (ps-1.7, ps-1.6). Using Prestashop functionalities it
can be transplant to "DisplayHome", this will allow you to move your form, from your website bottom to show only on the homepage.' mod='senderautomatedemails'}
                        <a class="link-different" href="http://doc.prestashop.com/display/PS17/Positions" target="_blank">{l s='Prestashop documentation' mod='senderautomatedemails'}</a>
                    </p>
                </div>
            </div>
        </div>
    {/if}
</div>
