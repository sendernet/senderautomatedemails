<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_3_6_5($object)
{
    $object->logDebug(__FUNCTION__);
    return ($object->registerHook('actionOrderHistoryAddAfter'));
}
