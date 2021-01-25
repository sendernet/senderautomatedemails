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
            <i class="zmdi zmdi-shopping-cart"></i>
            {l s='User data add to list' mod='senderautomatedemails'}
        </div>
        <div class="panel-body">
            <blockquote>
                <h3 style="padding-left: 10px!important">
                    {l s='Select which customer data add to subsriber list (email is added by default)' mod='senderautomatedemails'}
                </h3>
            </blockquote>
            <input class="spm-customer-data-input" type="checkbox" value="FIRSTNAME" >{l s='Firstname' mod='senderautomatedemails'}<br>
            <input class="spm-customer-data-input" type="checkbox" value="LASTNAME" >{l s='Lastname' mod='senderautomatedemails'}<br>

            {*Mapping*}

            {*Partner offers*}
{*            <br><br>*}
{*            <blockquote>*}
{*                <h4>*}
{*                    {l s='PARTNER OFFERS' mod='senderautomatedemails'}*}
{*                </h4>*}
{*                <p>*}
{*                    {l s='Please select the custom field, which saves partner offers option in your Sender mailinglist' mod='senderautomatedemails'}*}
{*                </p>*}
{*            </blockquote>*}

{*            <select id="swPartnerOffers" name="swPartnerOffers"*}
{*                    value="{$partnerOfferId|escape:'htmlall':'UTF-8'}">*}
{*                {if empty($customFields)}*}
{*                <option value="0">*}
{*                    {l s='No field created in Sender app' mod='senderautomatedemails'}*}
{*                </option>*}
{*                {else}*}
{*                <option value="0">*}
{*                    {l s='Select the partner offer custom field' mod='senderautomatedemails'}*}
{*                </option>*}
{*                {foreach $customFields as $field}*}
{*                    <option {if $field->id eq $partnerOfferId }selected="selected"{/if}*}
{*                            value="{$field->id|escape:'htmlall':'UTF-8'}">*}
{*                        {$field->title|escape:'htmlall':'UTF-8'}*}
{*                    </option>*}
{*                {/foreach}*}
{*                {/if}*}
{*            </select>*}
{*            {if empty($customFields)}*}
{*                </div>*}
{*                <div class="panel-body">*}
{*                    <div class="alert alert-warning">*}
{*                        {l s='No subscriber fields created on your Sender.net account. Please add new fields and refresh this page' mod='senderautomatedemails'}*}
{*                    </div>*}
{*                    <p>*}
{*                        <a class="btn btn-lg btn-info" href="{$appUrl|escape:'htmlall':'UTF-8'}/subscribers/fields" target="_blank">*}
{*                            {l s='Create field' mod='senderautomatedemails'}*}
{*                        </a>*}
{*                    </p>*}
{*                </div>*}
{*            {else}*}
            {*Gender*}
            <br><br>
                <blockquote>
                    <h4>
                        {l s='GENDER' mod='senderautomatedemails'}
                    </h4>
                    <p>
                        {l s='Please select the custom field, which saves gender in your Sender mailinglist or create a new field' mod='senderautomatedemails'}
                    </p>
                </blockquote>
                <select id="swGenderField" name="swGenderField"
                        value="{$genderFieldId|escape:'htmlall':'UTF-8'}">
                    {if empty($customFields)}
                    <option value="0">
                        {l s='No fields created in Sender app' mod='senderautomatedemails'}
                    </option>
                    {else}
                    <option value="0">
                        {l s='Select the gender custom field' mod='senderautomatedemails'}
                    </option>
                    {foreach $customFields as $field}
                        <option {if $field->id eq $genderFieldId }selected="selected"{/if} value="{$field->id|escape:'htmlall':'UTF-8'}">
                            {$field->title|escape:'htmlall':'UTF-8'}
                        </option>
                    {/foreach}
                    {/if}
                </select>
                <p class="field-create">
                    <a class="btn btn-lg btn-info" href="{$appUrl|escape:'htmlall':'UTF-8'}/subscribers/fields" target="_blank">
                        {l s='Create new field' mod='senderautomatedemails'}
                    </a>
                </p>
                {*Birthday*}
                <br><br>
                <blockquote>
                    <h4>
                        {l s='BIRTHDAY' mod='senderautomatedemails'}
                    </h4>
                    <p>
                        {l s='Please select the custom field, which saves birthday in your Sender mailinglist or create a new field' mod='senderautomatedemails'}
                    </p>
                </blockquote>
                <select id="swBirthdayField" name="swBirthdayField"
                        value="{$birthdayFieldId|escape:'htmlall':'UTF-8'}">
                    {if empty($customFields)}
                    <option value="0">
                        {l s='No fields created in Sender app' mod='senderautomatedemails'}
                    </option>
                    {else}
                    <option value="0">
                        {l s='Select the birthday custom field' mod='senderautomatedemails'}
                    </option>
                    {foreach $customFields as $field}
                        <option {if $field->id eq $birthdayFieldId }selected="selected"{/if}
                                value="{$field->id|escape:'htmlall':'UTF-8'}">
                            {$field->title|escape:'htmlall':'UTF-8'}
                        </option>
                    {/foreach}
                    {/if}
                </select>

                <p class="field-create">
                    <a class="btn btn-lg btn-info" href="{$appUrl|escape:'htmlall':'UTF-8'}/subscribers/fields" target="_blank">
                        {l s='Create new field' mod='senderautomatedemails'}
                    </a>
                </p>
{*            {/if}*}
        </div>

        <div class="panel-body">
            <div style="margin-top: 30px" class="alert alert-info">
                {l s='This data would be save in your Sender.net application as subscriber information' mod='senderautomatedemails'}
            </div>
        </div>
    </div>

</div>