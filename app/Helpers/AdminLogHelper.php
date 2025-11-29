<?php

use App\Models\AdminLog;

function admin_log($type, $action, $details = [])
{
    AdminLog::create([
        'admin_id' => auth()->id(),
        'type'     => $type,
        'action'   => $action,
        'details'  => $details,
    ]);
}
