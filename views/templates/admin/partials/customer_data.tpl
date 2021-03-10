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
        {*Details*}
        <div class="panel-body">
            <div class="spm-details-settings">
                <div class="alert alert-info">
                    {l s='In order to save your customers information to your Sender.net account, you would need to
enable feature Cart tracking.' mod='senderautomatedemails'}
                </div>
            </div>
            <blockquote>
                <p>
                    {l s='Select which customer data you would like to add (email is added by default).' mod='senderautomatedemails'}
                </p>
            </blockquote>
            <div>
                <label class="FIRSTNAME">
                    <input class="spm-customer-data-input sender-checkbox" type="checkbox"
                           value="FIRSTNAME" name="FIRSTNAME">
                    <span>{l s='Firstname' mod='senderautomatedemails'}</span>
                </label>
            </div>
            <div>
                <label class="LASTNAME">
                    <input class="spm-customer-data-input sender-checkbox" type="checkbox"
                           value="LASTNAME" name="LASTNAME">
                    <span>{l s='Lastname' mod='senderautomatedemails'}</span>
                </label>
            </div>
            <br>
            {*Gender*}
            <br><br>
            <div class="col-xs-12" id="gender_tab">
                <blockquote>
                    <h4>
                        {l s='GENDER' mod='senderautomatedemails'}
                    </h4>
                    <div>
                        {l s='Please select the custom field, which saves gender in your Sender mailinglist or create a new field.' mod='senderautomatedemails'}
                        <br>
                        <strong>{l s='Showing only text field type' mod='senderautomatedemails'}</strong>
                        <p>
                            <a class="btn btn-lg btn-sender field-create"
                               href="{$appUrl|escape:'htmlall':'UTF-8'}/subscribers/fields" target="_blank">
                                {l s='Create new field' mod='senderautomatedemails'}
                            </a>
                        </p>
                    </div>
                </blockquote>
                <div class="form-group">
                    <select class="sender-lists" id="swGenderField" name="swGenderField"
                            value="{$genderFieldId|escape:'htmlall':'UTF-8'}">
                        {if empty($customFieldsText)}
                            <option value="0">
                                {l s='No fields of type text created in Sender app' mod='senderautomatedemails'}
                            </option>
                        {else}
                            <option value="0">
                                <b>{l s="Don't add to list" mod="senderautomatedemails"}</b>
                            </option>
                            {foreach $customFieldsText as $field}
                                <option {if $field->id eq $genderFieldId }selected="selected"{/if}
                                        value="{$field->id|escape:'htmlall':'UTF-8'}">
                                    {$field->title|escape:'htmlall':'UTF-8'}
                                </option>
                            {/foreach}
                        {/if}
                    </select>
                </div>
                <span style="visibility: hidden;" style="margin-top: 5px" class="alert alert-success alert-success__sender saved-sender">
                    {l s='Saved' mod='senderautomatedemails'}
                </span>
            </div>
            {*Birthday*}
            <br><br>
            <div class="col-xs-12" id="birthday_tab" style="margin-top: 20px!important">
                <blockquote>
                    <h4>
                        {l s='BIRTHDAY' mod='senderautomatedemails'}
                    </h4>
                    <div>
                        {l s='Please select the custom field, which saves birthday in your Sender mailinglist or create a new field.' mod='senderautomatedemails'}
                        <br>
                        <strong>{l s='Showing datetime field type' mod='senderautomatedemails'}</strong>
                        <p>
                            <a class="btn btn-lg btn-sender field-create"
                               href="{$appUrl|escape:'htmlall':'UTF-8'}/subscribers/fields" target="_blank">
                                {l s='Create new field' mod='senderautomatedemails'}
                            </a>
                        </p>
                    </div>
                </blockquote>
                <div class="form-group">
                    <select class="sender-lists" id="swBirthdayField" name="swBirthdayField"
                            value="{$birthdayFieldId|escape:'htmlall':'UTF-8'}">
                        {if empty($customFieldsDatetime)}
                            <option value="0">
                                {l s='No fields of type datetime created in Sender app' mod='senderautomatedemails'}
                            </option>
                        {else}
                            <option value="0">
                                <b>{l s="Don't add to list" mod="senderautomatedemails"}</b>
                            </option>
                            {foreach $customFieldsDatetime as $field}
                                <option {if $field->id eq $birthdayFieldId }selected="selected"{/if}
                                        value="{$field->id|escape:'htmlall':'UTF-8'}">
                                    {$field->title|escape:'htmlall':'UTF-8'}
                                </option>
                            {/foreach}
                        {/if}
                    </select>
                </div>
                <span style="visibility: hidden;" class="alert alert-success alert-success__sender saved-sender">
                    {l s='Saved' mod='senderautomatedemails'}
                </span>
            </div>
        </div>
        {*About*}
        <div class="panel-body">
            <div style="margin-top: 30px" class="alert alert-info">
                {l s='This data would be save in your Sender.net application as subscriber information' mod='senderautomatedemails'}
            </div>
        </div>
    </div>

</div>