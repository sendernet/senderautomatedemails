<div id="spm-carts" class="spm-tab-content">

    {* ALLOW CART TRACK *}
    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="zmdi zmdi-shopping-cart"></i>
            {l s='Customer cart tracking is' mod='senderautomatedemails'}
            {if not $allowCartTrack}
                <span id="swToggleCartTrackTitle" style="color:red;">
                            {l s='disabled' mod='senderautomatedemails'}
                        </span>
            {else}
                <span id="swToggleCartTrackTitle" style="color:green;">
                            {l s='enabled' mod='senderautomatedemails'}
                        </span>
            {/if}
        </div>
        <div class="panel-body">
            <div class="spm-details-settings">
                <button id="swToggleCartTrack"
                        class="btn btn-lg {if not $allowCartTrack}btn-success{else}btn-danger{/if}">
                    {if not $allowCartTrack}
                        {l s='Enable' mod='senderautomatedemails'}
                    {else}
                        {l s='Disable' mod='senderautomatedemails'}
                    {/if}
                </button>
            </div>
            <div class="panel-body">
                {if empty($customersLists)}
                    <div class="alert alert-warning">
                        {l s='To track customers carts you must have at least one list at your Sender.net`s account' mod='senderautomatedemails'}
                    </div>
                    <p>
                        <a class="btn btn-lg btn-info" href="{$baseUrl|escape:'htmlall':'UTF-8'}/v2/tags">
                            {l s='Create a new list' mod='senderautomatedemails'}
                        </a>
                    </p>
                {else}
                    <blockquote>
                        <p>
                            {l s='Select to which list save customers whose carts were tracked' mod='senderautomatedemails'}
                        </p>
                    </blockquote>
                    <div id="swCustomerListSelectContainer" class="form-group">
                        <label for="swCustomerListSelect">
                            {l s='Select list' mod='senderautomatedemails'}
                        </label>
                        <select id="swCustomerListSelect" value="{$formId|escape:'htmlall':'UTF-8'}">
                            <option value="0">
                                {l s='Select a list' mod='senderautomatedemails'}
                            </option>
                            {foreach $customersLists as $customerList}
                                <option id="{$customerList->title|escape|lower:'htmlall':'UTF-8'}"
                                        {if $customerList->id eq $customerListId}selected="selected"{/if}
                                        value="{$customerList->id|escape:'htmlall':'UTF-8'}">
                                    {$customerList->title|escape:'htmlall':'UTF-8'}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                {/if}
            </div>
        </div>
    </div>

    {* ALLOW GUEST TRACKING PANEL *}
    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="zmdi zmdi-shopping-cart"></i>
            {l s='Guest cart tracking is' mod='senderautomatedemails'}
            {if not $allowGuestCartTracking}
                <span id="swToggleGuestCartTrackingTitle" style="color:red;">
                            {l s='disabled' mod='senderautomatedemails'}
                        </span>
            {else}
                <span id="swToggleGuestCartTrackingTitle" style="color:green;">
                            {l s='enabled' mod='senderautomatedemails'}
                        </span>
            {/if}
        </div>
        <div class="panel-body">
            {if empty($guestsLists)}
                <div class="alert alert-warning">
                    {l s='To track guest user carts you must have at least one list at your Sender.net`s account' mod='senderautomatedemails'}
                </div>
                <p>
                    <a class="btn btn-lg btn-info" href="{$baseUrl|escape:'htmlall':'UTF-8'}/mailinglists/add">
                        {l s='Create a new list' mod='senderautomatedemails'}
                    </a>
                </p>
            {else}
                <div class="spm-details-settings">
                    <button id="swToggleGuestCartTracking"
                            class="btn btn-lg {if not $allowGuestCartTracking}btn-success{else}btn-danger{/if}">
                        {if not $allowGuestCartTracking}
                            {l s='Enable' mod='senderautomatedemails'}
                        {else}
                            {l s='Disable' mod='senderautomatedemails'}
                        {/if}
                    </button>
                </div>
                <div class="panel-body">
                    {if empty($guestsLists)}
                        <div class="alert alert-warning">
                            {l s='To track customers carts you must have at least one list at your Sender.net`s account' mod='senderautomatedemails'}
                        </div>
                        <p>
                            <a class="btn btn-lg btn-info" href="{$baseUrl|escape:'htmlall':'UTF-8'}/mailinglists/add">
                                {l s='Create a new list' mod='senderautomatedemails'}
                            </a>
                        </p>
                    {else}
                        <blockquote>
                            <p>
                                {l s='Select to which list save guests or new signups whose carts were tracked' mod='senderautomatedemails'}
                            </p>
                        </blockquote>
                        <div id="swGuestListSelectContainer" class="form-group">
                            <label for="swGuestListSelect">
                                {l s='Select list' mod='senderautomatedemails'}
                            </label>
                            <select id="swGuestListSelect" value="{$formId|escape:'htmlall':'UTF-8'}">
                                <option value="0">
                                    {l s='Select a list' mod='senderautomatedemails'}
                                </option>
                                {foreach $guestsLists as $guestsList}
                                    <option id="{$guestsList->title|escape|lower:'htmlall':'UTF-8'}"
                                            {if $guestsList->id eq $guestListId}selected="selected"{/if}
                                            value="{$guestsList->id|escape:'htmlall':'UTF-8'}">
                                        {$guestsList->title|escape:'htmlall':'UTF-8'}
                                    </option>
                                {/foreach}
                            </select>
                        </div>
                    {/if}
                </div>
            {/if}
        </div>

    </div>

</div>