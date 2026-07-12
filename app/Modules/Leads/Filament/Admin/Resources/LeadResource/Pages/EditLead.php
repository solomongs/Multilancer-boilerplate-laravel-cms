<?php

namespace App\Modules\Leads\Filament\Admin\Resources\LeadResource\Pages;

use App\Modules\Leads\Filament\Admin\Resources\LeadResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLead extends EditRecord
{
    protected static string $resource = LeadResource::class;

    /** @return array<int, DeleteAction> */
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
