{*
 * 2010-2018 Sender.net
 *
 * Sender.net Automated Emails
 *
 * @author Sender.net <info@sender.net>
 * @copyright 2010-2018 Sender.net
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License v. 3.0 (OSL-3.0)
 * Sender.net
 *}

<div class="row" style="text-align: center;">
	<div class="well col-lg-6 col-lg-offset-3">
		<div class="row">
			<div class="col-xs-12">
		    	<img src="{$imageUrl|escape:'htmlall':'UTF-8'}" alt="Sender Logo" />
		    	<span>
		    		<small style="vertical-align:bottom;">
		    			v{$moduleVersion|escape:'htmlall':'UTF-8'}
		    		</small>
		    	</span>
		    	<hr>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12">
			    <h2>
			        {l s='Thank you for choosing Sender.net`s Integration module!' mod='senderautomatedemails'}
			    </h2>
			    <p>
			        {l s='First you must authenticate yourself with Sender.net' mod='senderautomatedemails'}
			    </p>
				<p>
					{l s='You can do so, by generating an Api access token' mod='senderautomatedemails'}
					<a style="background-color: #ffffff;" href="https://app.sender.net/settings/tokens" target="_blank" rel="noopener">here</a>
				</p>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12" style="padding: 10px;">
				<form action="{$link->getAdminLink('AdminSenderAutomatedEmails')|escape:'htmlall':'utf-8'}"
					  method="post">
					<label for="apiKey">Api access token:</label>
					<input type="text" id="apiKey" name="apiKey" placeholder="{l s='Paste here the copied Api token' mod='senderautomatedemails'}"><br><br>
					<input type="submit" value="{l s='Authenticate' mod='senderautomatedemails'}"
						   name="actionApiKey" class="btn btn-lg" style="background-color: #009587; color: #fff;"
					>
				</form>
			</div>
		</div>
	</div>
</div>
<h2 id="someId"></h2>