<?php

namespace App\Modules\Leads\Filament\Admin\Resources\LeadResource\Pages;

use App\Modules\Leads\Filament\Admin\Resources\LeadResource;
use Filament\Resources\Pages\ListRecords;

class ListLeads extends ListRecords
{
    protected static string $resource = LeadResource::class;
}
