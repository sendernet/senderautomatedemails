<?php


//hoookAddAccount
//Check if user opted in for a newsletter
//Think that with this disable gives error
//        if (!$context['newCustomer']->newsletter
//            && !$context['newCustomer']->optin) {
//            $this->logDebug('Customer did not checked newsletter or optin!');
//            $encodedEmail = base64_encode($this->context->customer->email);
//            if (!$isSubscriber = $this->apiClient()->isAlreadySubscriber($encodedEmail)) {
//                return;
//            }
//            $this->context->customer->newsletter = 1;
//            $recipient = $this->formDefaultsRecipient($isSubscriber);
//            #Check if already is a subscriber on a list as active
//        } else {
//            $recipient = $this->formDefaultsRecipient($this->context->customer);
//        }

#Maybe this on separated function
//        switch ($context['newCustomer']->is_guest) {
//            case true:
//                if (!Configuration::get('SPM_ALLOW_GUEST_TRACK') === false) {
//                    $this->logDebug('Adding to guest list: ' . $listToAdd);
//                    $listToAdd = Configuration::get('SPM_GUEST_LIST_NAME');
////                    $this->syncCart($context['cart'], $cookie);
//                    break;
//                } else {
//                    $this->logDebug('SPM_ALLOW_GUEST_TRACK is not enabled, wont create new customer details');
//                    return;
//                }
//            case false:
//                if (!Configuration::get('SPM_ALLOW_TRACK_CARTS') === false) {
//                    $this->logDebug('Adding to customers lists: ' . json_encode($listToAdd));
//                    break;
//                } else {
//                    $this->logDebug('SPM_ALLOW_TRACK_CARTS is not enabled, wont create new customer details');
//                    return;
//                }
//        }