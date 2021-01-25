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
{if $showForm and $formUrl}
    <div>
        <div class="col-xs-4" id="senderFormContainer">
            {if $embedForm}
                <div class="sender-form-field" data-sender-form-id="{$embedHash|escape:'htmlall':'UTF-8'}"></div>
            {/if}
            <script>
                (function (s, e, n, d, er) {
                    s['Sender'] = er;
                    s[er] = s[er] || function () {
                        (s[er].q = s[er].q || []).push(arguments)
                    }, s[er].l = 1 * new Date();
                    var a = e.createElement(n),
                        m = e.getElementsByTagName(n)[0];
                    a.async = 1;
                    a.src = d;
                    m.parentNode.insertBefore(a, m)
                })(window, document, 'script', 'https://cdn.sender.net/accounts_resources/universal.js', 'sender');
                sender('{$resourceKey|escape:'htmlall':'UTF-8'}')
            </script>
        </div>
    </div>
{/if}
