<?php

namespace App\Modules\Cms\Filament\Admin\Resources;

use App\Modules\Cms\Filament\Admin\Resources\RedirectResource\Pages\CreateRedirect;
use App\Modules\Cms\Filament\Admin\Resources\RedirectResource\Pages\EditRedirect;
use App\Modules\Cms\Filament\Admin\Resources\RedirectResource\Pages\ListRedirects;
use App\Modules\Cms\Models\Redirect;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-uturn-right';

    protected static string|\UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $navigationLabel = 'Redirects';

    protected static ?string $recordTitleAttribute = 'source_path';

    public static function isScopedToTenant(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('source_path')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->placeholder('/old-page.html')
                    ->helperText('Enter the old path, beginning with /. Query strings are ignored.'),
                TextInput::make('destination_url')
                    ->required()
                    ->maxLength(2048)
                    ->placeholder('/new-page or https://example.com/page')
                    ->helperText('Use a site-relative path or a complete HTTP/HTTPS URL.'),
                Select::make('status_code')
                    ->options([
                        301 => '301 — Permanent redirect',
                        302 => '302 — Temporary redirect',
                        307 => '307 — Temporary redirect, preserve method',
                        308 => '308 — Permanent redirect, preserve method',
                    ])
                    ->required()
                    ->default(301),
                Toggle::make('is_enabled')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('source_path')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('destination_url')
                    ->limit(60)
                    ->searchable()
                    ->copyable(),
                TextColumn::make('status_code')
                    ->badge()
                    ->sortable(),
                IconColumn::make('is_enabled')
                    ->boolean(),
                TextColumn::make('hit_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('last_hit_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_enabled')
                    ->label('Enabled'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => ListRedirects::route('/'),
            'create' => CreateRedirect::route('/create'),
            'edit' => EditRedirect::route('/{record}/edit'),
        ];
    }
}
