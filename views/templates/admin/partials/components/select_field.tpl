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
<div class="col-xs-12" id="{$fieldName}_tab" style="margin-top: 20px!important">
    <blockquote>
        <h4>
            {l s=$title mod='senderautomatedemails'}
        </h4>
        <div>
            {l s=$description mod='senderautomatedemails'}
            <br>
            <strong>{l s=$subtitle mod='senderautomatedemails'}</strong>
            <p>
                <a class="btn btn-lg btn-sender field-create"
                    href="{$appUrl|escape:'htmlall':'UTF-8'}/subscribers/fields" target="_blank">
                    {l s='Create new field' mod='senderautomatedemails'}
                </a>
            </p>
        </div>
    </blockquote>
    <div class="form-group">
        <select class="sender-lists sender-custom-field" id="{$fieldName}_field" name="{$fieldName}"
                value="{$fieldId|escape:'htmlall':'UTF-8'}">
            {if empty($fields)}
                <option value="0">
                    {l s=$emptyMessage|default:'No fields found' mod='senderautomatedemails'}
                </option>
            {else}
                <option value="0">
                    <b>{l s="Don't add to list" mod="senderautomatedemails"}</b>
                </option>
                {foreach $fields as $field}
                    <option {if $field->id eq $fieldId }selected="selected"{/if}
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