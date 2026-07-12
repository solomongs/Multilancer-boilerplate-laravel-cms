<?php

namespace App\Modules\Cms;

use App\Modules\BaseModule;
use Illuminate\Support\Facades\Log;

class CmsModule extends BaseModule
{
    protected function onInstall(): void
    {
        Log::info('CMS module installed');
    }

    protected function onEnable(): void
    {
        Log::info('CMS module enabled');
    }

    protected function onDisable(): void
    {
        Log::info('CMS module disabled');
    }

    protected function onUninstall(): void
    {
        Log::info('CMS module uninstalled');
    }
}
