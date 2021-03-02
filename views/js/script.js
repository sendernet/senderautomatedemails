/**
 * 2010-2021 Sender.net
 *
 * Sender.net Automated Emails
 *
 * @author Sender.net <info@sender.net>
 * @copyright 2010-2021 Sender.net
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License v. 3.0 (OSL-3.0)
 * Sender.net
 */

(function($) {
    'use strict';
    jQuery(document).ready(function() {

        jQuery('#swToggleNewSignups').on('click', function(event) {

            var newSignupButton = jQuery('#swToggleNewSignups');
            var statusTitle     = jQuery('#swToggleNewSignupsTitle');
            var selectContainer = jQuery('#swNewSignupListContainer');

            newSignupButton.text('Saving...');
            newSignupButton.attr('disabled', true);

            jQuery.post(listsAjaxurl, { action: 'saveALlowNewSignups' }, function(response) {
                var proceed = jQuery.parseJSON(response);

                if (!proceed.result) {

                    statusTitle
                        .text('disabled')
                        .css('color', 'red');

                    newSignupButton
                        .text('Enable')
                        .removeClass('btn-danger')
                        .addClass('btn-success');

                    selectContainer.fadeOut();

                } else {

                    statusTitle
                        .text('enabled')
                        .css('color', 'green');

                    newSignupButton
                        .text('Disable')
                        .removeClass('btn-success')
                        .addClass('btn-danger');

                    selectContainer.fadeIn();
                }

                newSignupButton.removeAttr('disabled');

            });

        });

        jQuery('#swToggleCartTrack').on('click', function(event) {

            jQuery('#swToggleCartTrack').text('Saving...');
            jQuery('#swToggleCartTrack').attr('disabled', true);

            jQuery.post(cartsAjaxurl, { action: 'saveAllowCartTracking' }, function(response) {
                var proceed = jQuery.parseJSON(response);

                if (!proceed.result) {
                    jQuery('#swToggleCartTrackTitle').text('disabled');
                    jQuery('#swToggleCartTrackTitle').css('color', 'red');
                    jQuery('#swToggleCartTrack').text('Enable');
                    jQuery('#swToggleCartTrack').removeClass('btn-danger__sender');
                    jQuery('#swToggleCartTrack').addClass('btn-sender');
                    jQuery('#swToggleNewsletter').text('Enable');
                    jQuery('#swToggleNewsletter').removeClass('btn-danger__sender');
                    jQuery('#swToggleNewsletter').addClass('btn-sender');
                    jQuery('#swCustomerListSelect').attr('disabled', true);
                    jQuery('#swGuestListSelect').attr('disabled', true);
                    jQuery('#swToggleNewsletter').attr('disabled', false);

                } else {
                    jQuery('#swToggleCartTrackTitle').text('enabled');
                    jQuery('#swToggleCartTrackTitle').css('color', '#ff8d00');
                    jQuery('#swToggleCartTrack').text('Disable');
                    jQuery('#swToggleCartTrack').removeClass('btn-sender');
                    jQuery('#swToggleCartTrack').addClass('btn-danger__sender');
                    jQuery('#swToggleNewsletter').text('Disable');
                    jQuery('#swToggleNewsletter').removeClass('btn-sender');
                    jQuery('#swToggleNewsletter').addClass('btn-danger__sender');
                    jQuery('#swToggleNewsletter').attr('disabled', true);
                    jQuery('#swCustomerListSelect').removeAttr('disabled');
                    jQuery('#swGuestListSelect').removeAttr('disabled');
                }

                jQuery('#swToggleCartTrack').removeAttr('disabled');

            });

        });

        jQuery('#swToggleNewsletter').on('click', function(event) {

            jQuery('#swToggleNewsletter').text('Saving...');
            jQuery('#swToggleNewsletter').attr('disabled', true);

            jQuery.post(newsletterAjaxUrl, { action: 'saveAllowNewsletter' }, function(response) {
                var proceed = jQuery.parseJSON(response);

                if (!proceed.result) {
                    jQuery('#swToggleNewsletterTitle').text('disabled');
                    jQuery('#swToggleNewsletterTitle').css('color', 'red');
                    jQuery('#swToggleNewsletter').text('Enable');
                    jQuery('#swToggleNewsletter').removeClass('btn-danger__sender');
                    jQuery('#swToggleNewsletter').addClass('btn-sender');
                    jQuery('#swCustomerListSelect').attr('disabled', true);
                    jQuery('#swGuestListSelect').attr('disabled', true);
                    jQuery('#newsletterCheckbox').css({opacity: 0});
                    // jQuery('#newsletterCheckbox').css('visibility', 'hidden')
                } else {
                    jQuery('#swToggleNewsletterTitle').text('enabled');
                    jQuery('#swToggleNewsletterTitle').css('color', '#ff8d00');
                    jQuery('#swToggleNewsletter').text('Disable');
                    jQuery('#swToggleNewsletter').removeClass('btn-sender');
                    jQuery('#swToggleNewsletter').addClass('btn-danger__sender');
                    jQuery('#swCustomerListSelect').removeAttr('disabled');
                    jQuery('#swGuestListSelect').removeAttr('disabled');
                    jQuery('#newsletterCheckbox').css({opacity: 1, visibility: "visible"});
                }

                jQuery('#swToggleNewsletter').removeAttr('disabled');

            });

        });

        jQuery('#swToggleWidget').on('click', function(event) {

            jQuery('#swToggleWidget').text('Saving...');
            jQuery('#swToggleWidget').attr('disabled', true);

            jQuery.post(formsAjaxurl, { action: 'saveAllowForms' }, function(response) {
                var proceed = jQuery.parseJSON(response);

                if (!proceed.result) {
                    jQuery('#swToggleWidgetTitle').text('disabled');
                    jQuery('#swToggleWidgetTitle').css('color', 'red');
                    jQuery('#swToggleWidget').text('Enable');
                    jQuery('#swToggleWidget').removeClass('btn-danger__sender');
                    jQuery('#swToggleWidget').addClass('btn-sender');
                    jQuery('#swFormsSelect').attr('disabled', true);
                } else {
                    jQuery('#swToggleWidgetTitle').text('enabled');
                    jQuery('#swToggleWidgetTitle').css('color', '#ff8d00');
                    jQuery('#swToggleWidget').text('Disable');
                    jQuery('#swToggleWidget').removeClass('btn-sender');
                    jQuery('#swToggleWidget').addClass('btn-danger__sender');
                    jQuery('#swFormsSelect').removeAttr('disabled');
                }

                jQuery('#swToggleWidget').removeAttr('disabled');

            });

        });

        jQuery('#swToggleGuestCartTracking').on('click', function (event) {

            jQuery('#swToggleGuestCartTracking').text('Saving...');
            jQuery('#swToggleGuestCartTracking').attr('disabled', true);

            jQuery.post(cartsAjaxurl, {
                action: 'saveAllowGuestCartTracking'
            }, function (response) {
                var proceed = jQuery.parseJSON(response);
                console.log(proceed.result);
                if (!proceed.result) {
                    jQuery('#swToggleGuestCartTrackingTitle').text('disabled');
                    jQuery('#swToggleGuestCartTrackingTitle').css('color', 'red');
                    jQuery('#swToggleGuestCartTracking').text('Enable');
                    jQuery('#swToggleGuestCartTracking').removeClass('btn-danger');
                    jQuery('#swToggleGuestCartTracking').addClass('btn-success');
                    jQuery('#guests_lists').fadeOut();
                } else {
                    jQuery('#swToggleGuestCartTrackingTitle').text('enabled');
                    jQuery('#swToggleGuestCartTrackingTitle').css('color', '#ff8d00');
                    jQuery('#swToggleGuestCartTracking').text('Disable');
                    jQuery('#swToggleGuestCartTracking').removeClass('btn-success');
                    jQuery('#swToggleGuestCartTracking').addClass('btn-danger');
                    jQuery('#guests_lists').fadeIn();
                }

                jQuery('#swToggleGuestCartTracking').removeAttr('disabled');

            });

        });

        jQuery('#swTogglePush').on('click', function(event) {
            console.log(true)

            jQuery('#swTogglePush').text('Saving...');
            jQuery('#swTogglePush').attr('disabled', true);

            jQuery.post(pushAjaxurl, { action: 'saveAllowPush' }, function(response) {
                var proceed = jQuery.parseJSON(response);

                if (!proceed.result) {
                    jQuery('#swTogglePushTitle').text('disabled');
                    jQuery('#swTogglePushTitle').css('color', 'red');
                    jQuery('#swTogglePush').text('Enable');
                    jQuery('#swTogglePush').removeClass('btn-danger');
                    jQuery('#swTogglePush').addClass('btn-success');
                    $('#push_enabled').addClass('hidden');
                    $('#push_disabled').removeClass('hidden');
                } else {
                    jQuery('#swTogglePushTitle').text('enabled');
                    jQuery('#swTogglePushTitle').css('color', '#ff8d00');
                    jQuery('#swTogglePush').text('Disable');
                    jQuery('#swTogglePush').removeClass('btn-success');
                    jQuery('#swTogglePush').addClass('btn-danger');
                    $('#push_enabled').removeClass('hidden');
                    $('#push_disabled').addClass('hidden');
                }

                jQuery('#swTogglePush').removeAttr('disabled');

            });

        });

        jQuery('#swFormsSelect').on('change', function(event) {

            jQuery.post(formsAjaxurl, {
                action: 'saveFormId',
                form_id: jQuery('#swFormsSelect').val()
            }, function(response) {
                var proceed = jQuery.parseJSON(response);

                if (!proceed.result) {
                    console.log('save error');
                } else {
                    actionSaved($('#forms_tab').find('span'));
                }

            });

        });

        jQuery('#swGuestListSelect').on('change', function(event) {

            jQuery('#swGuestListSelect').attr('disabled', true);

            jQuery.post(listsAjaxurl, {
                action: 'saveGuestListId',
                list_id: jQuery('#swGuestListSelect').val(),
                list_name: jQuery('#swGuestListSelect option:selected').attr("id")
            }, function(response) {
                var proceed = jQuery.parseJSON(response);

                if (!proceed.result) {
                    console.log('save error');
                } else {
                    actionSaved($('#guest_list_tab').find('span'));
                }

                jQuery('#swGuestListSelect').removeAttr('disabled');

            });

        });

        jQuery('.spm-customer-data-input').on('change', function(event) {
            if($(this).is(':checked')) {
                jQuery.post(dataAjaxurl, { action: 'addData', option_name: $(this).val() }, function(response) {
                    console.log(response);
                });
            }else{
                jQuery.post(dataAjaxurl, { action: 'removeData', option_name: $(this).val() }, function(response) {
                    console.log(response);
                });
            }
        });

        jQuery('.spm-customer-data-input').each(function() {
            let elm = $(this);
            jQuery.post(dataAjaxurl, { action: 'getIfEnabled', option_name: $(this).val() }, function(response) {
                if(response == 1){
                    elm.prop( 'checked', true );
                }

            });

        });

        jQuery('#swPartnerOffers').on('change', function(event) {
            jQuery.post(dataAjaxurl, {
                action: 'savePartnerOffers',
                field_id: jQuery('#swPartnerOffers').val(),
                field_name: jQuery('#swPartnerOffers option:selected').attr("id")
            }, function(response) {
                var proceed = jQuery.parseJSON(response);

                if (!proceed.result) {
                    console.log('save error');
                } else {
                    console.log('save success');
                }
            });
        });

        jQuery('#swGenderField').on('change', function(event) {

            jQuery.post(dataAjaxurl, {
                action: 'genderField',
                field_id: jQuery('#swGenderField').val(),
            }, function(response) {
                var proceed = jQuery.parseJSON(response);
                if (!proceed.result) {
                    console.log('save error');
                } else {
                    actionSaved($('#gender_tab').find('span'));
                }
            });

        });

        jQuery('#swBirthdayField').on('change', function(event) {

            jQuery.post(dataAjaxurl, {
                action: 'birthdayField',
                field_id: jQuery('#swBirthdayField').val(),
            }, function(response) {
                var proceed = jQuery.parseJSON(response);
                if (!proceed.result) {
                    console.log('save error');
                } else {
                    actionSaved($('#birthday_tab').find('span'));
                }
            });

        });

        jQuery('#swCustomerListSelect').on('change', function(event) {
            var hello = $('#swCustomerListSelect option:selected').attr("id");
            console.log(hello);

            jQuery('#swCustomerListSelect').attr('disabled', true);

            jQuery.post(listsAjaxurl, {
                action: 'saveCustomerListId',
                list_id: jQuery('#swCustomerListSelect').val(),
                list_name: jQuery('#swCustomerListSelect option:selected').attr("id")
            }, function(response) {
                var proceed = jQuery.parseJSON(response);

                if (!proceed.result) {
                    console.log('save error');
                } else {
                    actionSaved($('#customer_list_tab').find('span'));
                }

                jQuery('#swCustomerListSelect').removeAttr('disabled');

            });

        });

        jQuery('#swExportClientToList').on('change', function(event) {
            jQuery.post(syncListAjaxUrl, {
                action: 'exportList',
                list_id: jQuery('#swExportClientToList').val(),
            }, function(response) {
                var proceed = jQuery.parseJSON(response);
                if (!proceed.result) {
                    console.log('Export list not saved');
                } else {
                    console.log('Export list saved');
                }
            });
        });

        /**
         * Tab menu change handler
         */
        jQuery('ul.spm-tabs li').click(function() {
            var tab_id = jQuery(this).data().tab;
            jQuery('ul.spm-tabs li').removeClass('spm-current').removeClass('spm-active');
            jQuery('.spm-tab-content').removeClass('spm-current');
            jQuery("#" + tab_id).addClass('spm-current');
            jQuery(this).addClass('spm-current').addClass('spm-active');
        })

        if (window.location.hash) {
            var hash = window.location.hash.substring(2);
            jQuery('[data-tab="' + hash + '"]').trigger('click');

        } else {}

        jQuery('#syncList').click(function (){
            jQuery('#syncList').html('Working on it');
            jQuery('#syncList').focusout();
            jQuery.get(syncListAjaxUrl, {
                action: 'syncList',
            }, function(response) {
                var proceed = jQuery.parseJSON(response);
                if (!proceed.result.success) {
                    jQuery('#syncList').html('Synchronize this list with Sender.net');
                    jQuery('#syncError').show().html(proceed.result.message);
                } else {
                    $('#syncError').css('display', 'none');
                    jQuery('#syncList').addClass('btn_sender_success');
                    jQuery('#syncList').html('Synchronized');
                    jQuery('#syncDate').html(proceed.result.message);
                    setTimeout(function(){
                        $('#syncList').removeClass('btn_sender_success').html('Synchronize this list with Sender.net');
                    }, 5000);
                }
            });
        });

        function actionSaved(element){
            var duration = 130;
            element.css({opacity: 0, visibility: "visible"}).animate({opacity: 1}, duration);
            setTimeout(function(){
                element.animate({opacity: 0}, duration);
            }, 2000);
        }

    });

})(jQuery);