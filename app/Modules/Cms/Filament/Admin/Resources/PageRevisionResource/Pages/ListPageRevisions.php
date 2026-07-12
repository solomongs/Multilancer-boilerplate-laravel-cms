<?php

namespace App\Modules\Cms\Filament\Admin\Resources\PageRevisionResource\Pages;

use App\Modules\Cms\Filament\Admin\Resources\PageRevisionResource;
use Filament\Resources\Pages\ListRecords;

class ListPageRevisions extends ListRecords
{
    protected static string $resource = PageRevisionResource::class;
}
