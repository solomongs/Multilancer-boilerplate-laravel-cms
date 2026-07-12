<?php

namespace App\Modules\Leads;

use App\Modules\BaseModule;
use Illuminate\Support\Facades\Log;

class LeadsModule extends BaseModule
{
    protected function onInstall(): void
    {
        Log::info('Leads module installed');
    }

    protected function onEnable(): void
    {
        Log::info('Leads module enabled');
    }

    protected function onDisable(): void
    {
        Log::info('Leads module disabled');
    }

    protected function onUninstall(): void
    {
        Log::info('Leads module uninstalled');
    }
}
