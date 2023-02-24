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
            {include file="./components/select_field.tpl" fieldId=$genderFieldId fieldName="gender" title="GENDER" description="Please select the custom field, which saves gender in your Sender mailinglist or create a new field." subtitle="Showing only text field type" fields=$customFieldsText}
            {*Birthday*}
            <br><br>
            {include file="./components/select_field.tpl" fieldId=$birthdayFieldId fieldName="birthday" title="BIRTHDAY" description="Please select the custom field, which saves birthday in your Sender mailinglist or create a new field." subtitle="Showing datetime field type" fields=$customFieldsDatetime}
            {*Language*}
            <br><br>
            {include file="./components/select_field.tpl" fieldId=$languageFieldId fieldName="language" title="LANGUAGE" description="Please select the custom field, which saves language in your Sender mailinglist or create a new field." subtitle="Showing only text field type" fields=$customFieldsText}
            {*Country*}
            <br><br>
            {include file="./components/select_field.tpl" fieldId=$countryFieldId fieldName="country" title="COUNTRY" description="Please select the custom field, which saves country in your Sender mailinglist or create a new field." subtitle="Showing only text field type" fields=$customFieldsText}
        </div>
        {*About*}
        <div class="panel-body">
            <div style="margin-top: 30px" class="alert alert-info">
                {l s='This data would be save in your Sender.net application as subscriber information' mod='senderautomatedemails'}
            </div>
        </div>
    </div>

</div>