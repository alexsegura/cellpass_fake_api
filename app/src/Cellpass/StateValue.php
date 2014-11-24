<?php

namespace Cellpass;

class StateValue
{
    const STATE_VALUE_UNKNOWN                       = 'Error unknown';
    const STATE_VALUE_NOT_EXISTS                    = 'This does not exist or does not belong to the right editor, site or service';
    const STATE_VALUE_DEACTIVATED                   = 'This is not activated';
    const STATE_VALUE_NO_OFFER                      = 'No offer found';
    const STATE_VALUE_INVALID_URL                   = 'Url is invalid';
    const STATE_VALUE_CLIENT_CANCEL                 = 'Client cancel the billing';
    const STATE_VALUE_CLIENT_ALREADY_SUBSCRIBE      = 'This User has already subscribed to this service';
    const STATE_VALUE_CLIENT_NOT_AUTHORIZED         = 'Client is not authorized to purchase this offer';
    const STATE_VALUE_CLIENT_SUBSCRIPTION_DATA      = 'Subscription of the user is in an invalid state';
    const STATE_VALUE_OPERATOR                      = 'An error occurs on Operator side';
    const STATE_VALUE_CELLPASS                      = 'An error occurs on Cellpass side';
    const STATE_VALUE_CONFIGURATION                 = 'This service seems to not be correctly configured';
    const STATE_VALUE_UNEXPECTED_BEHAVIOUR          = 'Unexpected client behaviour';
    const STATE_VALUE_TOO_OLD                       = 'This transaction is too old';
    const STATE_VALUE_INCOMPATIBLE                  = 'The service or the offer chosen is not compatible with this End-User';
    const STATE_VALUE_BILL_KO                       = 'Client does not complete billing';

    public static function toArray() {
        $reflectClass = new \ReflectionClass(__CLASS__);

        $constants = [];
        foreach ($reflectClass->getConstants() as $name => $value) {
            $constants[str_replace('STATE_VALUE_', '', $name)] = $value;
        }

        return $constants;
    }

    public static function getValue($code)
    {
        $values = self::toArray();
        return $values[$code];
    }
}
