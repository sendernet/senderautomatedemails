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
<div id="spm-customer-data" class="spm-tab-content">
    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="zmdi zmdi-accounts-alt"></i>
            {l s='Customer data add to list' mod='senderautomatedemails'}
        </div>
        <div class="panel-body">
            <blockquote>
                <p>
                    {l s='Select which customer data you would like to add to subscriber a list (email is added by default)' mod='senderautomatedemails'}
                </p>
            </blockquote>
            <input class="spm-customer-data-input" type="checkbox"
                   value="FIRSTNAME">{l s='Firstname' mod='senderautomatedemails'}<br>
            <input class="spm-customer-data-input" type="checkbox"
                   value="LASTNAME">{l s='Lastname' mod='senderautomatedemails'}<br>
            {*Mapping*}
            {*Gender*}
            <br><br>
            <div class="col-xs-12">
                <blockquote>
                    <h4>
                        {l s='GENDER' mod='senderautomatedemails'}
                    </h4>
                    <div>
                        {l s='Please select the custom field, which saves gender in your Sender mailinglist or create a new field.' mod='senderautomatedemails'}
                        <p>
                            <a class="btn btn-lg btn-info field-create"
                               href="{$appUrl|escape:'htmlall':'UTF-8'}/subscribers/fields" target="_blank">
                                {l s='Create new field' mod='senderautomatedemails'}
                            </a>
                        </p>
                    </div>
                </blockquote>
                <div class="form-group">
                    <select class="sender-lists" id="swGenderField" name="swGenderField"
                            value="{$genderFieldId|escape:'htmlall':'UTF-8'}">
                        <option value=""
                                disabled>{l s='Select the gender custom field' mod='senderautomatedemails'}</option>
                        {if empty($customFields)}
                            <option value="0">
                                {l s='No fields created in Sender app' mod='senderautomatedemails'}
                            </option>
                        {else}
                            <option value="0">
                                <b>{l s="Don't add to list" mod="senderautomatedemails"}</b>
                            </option>
                            {foreach $customFields as $field}
                                <option {if $field->id eq $genderFieldId }selected="selected"{/if}
                                        value="{$field->id|escape:'htmlall':'UTF-8'}">
                                    {$field->title|escape:'htmlall':'UTF-8'}
                                </option>
                            {/foreach}
                        {/if}
                    </select>
                </div>
                <div style="visibility: hidden;" style="margin-top: 5px" class="alert alert-success updated-first">
                    {l s='Saved' mod='senderautomatedemails'}
                </div>
            </div>
            {*Birthday*}
            <br><br>
            <div class="col-xs-12" style="margin-top: 20px!important">
                <blockquote>
                    <h4>
                        {l s='BIRTHDAY' mod='senderautomatedemails'}
                    </h4>
                    <div>
                        {l s='Please select the custom field, which saves birthday in your Sender mailinglist or create a new field.' mod='senderautomatedemails'}
                        <p>
                            <a class="btn btn-lg btn-info field-create"
                               href="{$appUrl|escape:'htmlall':'UTF-8'}/subscribers/fields" target="_blank">
                                {l s='Create new field' mod='senderautomatedemails'}
                            </a>
                        </p>
                    </div>
                </blockquote>
                <div class="form-group">
                    <select class="sender-lists" id="swBirthdayField" name="swBirthdayField"
                            value="{$birthdayFieldId|escape:'htmlall':'UTF-8'}">
                        <option value=""
                                disabled>{l s='Select the gender custom field' mod='senderautomatedemails'}</option>
                        {if empty($customFields)}
                            <option value="0">
                                {l s='No fields created in Sender app' mod='senderautomatedemails'}
                            </option>
                        {else}
                            <option value="0">
                                <b>{l s="Don't add to list" mod="senderautomatedemails"}</b>
                            </option>
                            {foreach $customFields as $field}
                                <option {if $field->id eq $birthdayFieldId }selected="selected"{/if}
                                        value="{$field->id|escape:'htmlall':'UTF-8'}">
                                    {$field->title|escape:'htmlall':'UTF-8'}
                                </option>
                            {/foreach}
                        {/if}
                    </select>
                </div>
                <div style="visibility: hidden;" class="alert alert-success updated-second">
                    {l s='Saved' mod='senderautomatedemails'}
                </div>
            </div>
            {*            {/if}*}
        </div>

        <div class="panel-body">
            <div style="margin-top: 30px" class="alert alert-info">
                {l s='This data would be save in your Sender.net application as subscriber information' mod='senderautomatedemails'}
            </div>
        </div>
    </div>

</div>