<?php

namespace App\Modules\Cms\Filament\Admin\Resources;

use App\Modules\Cms\Filament\Admin\Resources\PageResource\Pages\CreatePage;
use App\Modules\Cms\Filament\Admin\Resources\PageResource\Pages\EditPage;
use App\Modules\Cms\Filament\Admin\Resources\PageResource\Pages\ListPages;
use App\Modules\Cms\Models\Page;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-duplicate';

    protected static string|\UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $navigationLabel = 'Pages';

    protected static ?string $recordTitleAttribute = 'title';

    public static function isScopedToTenant(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->alphaDash()
                    ->maxLength(255),
                Textarea::make('excerpt')
                    ->rows(3)
                    ->maxLength(1000),
                Select::make('template')
                    ->options(config('cms.templates', []))
                    ->required()
                    ->default(config('cms.default_template', 'default')),
                Select::make('status')
                    ->options(config('cms.statuses', []))
                    ->required()
                    ->default('draft'),
                Toggle::make('is_homepage')
                    ->label('Use as homepage')
                    ->default(false),
                DateTimePicker::make('published_at')
                    ->label('Publish at')
                    ->helperText('Leave empty to publish immediately when status is Published.'),
                TextInput::make('meta_title')
                    ->maxLength(255),
                Textarea::make('meta_description')
                    ->rows(3)
                    ->maxLength(320),
                TextInput::make('canonical_url')
                    ->url()
                    ->maxLength(255),
                Select::make('robots')
                    ->options([
                        'index,follow' => 'Index and follow',
                        'noindex,follow' => 'Do not index, follow links',
                        'index,nofollow' => 'Index, do not follow links',
                        'noindex,nofollow' => 'Do not index or follow links',
                    ])
                    ->required()
                    ->default('index,follow'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('template')
                    ->badge(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                IconColumn::make('is_homepage')
                    ->label('Home')
                    ->boolean(),
                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(config('cms.statuses', [])),
                SelectFilter::make('template')
                    ->options(config('cms.templates', [])),
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
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }
}
