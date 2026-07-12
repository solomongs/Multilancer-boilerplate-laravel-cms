<?php

namespace App\Modules\Cms\Filament\Admin\Resources;

use App\Modules\Cms\Filament\Admin\Resources\PageSectionResource\Pages\CreatePageSection;
use App\Modules\Cms\Filament\Admin\Resources\PageSectionResource\Pages\EditPageSection;
use App\Modules\Cms\Filament\Admin\Resources\PageSectionResource\Pages\ListPageSections;
use App\Modules\Cms\Models\PageSection;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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

class PageSectionResource extends Resource
{
    protected static ?string $model = PageSection::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $navigationLabel = 'Page Sections';

    protected static ?string $recordTitleAttribute = 'section_name';

    public static function isScopedToTenant(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('page_id')
                    ->relationship('page', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('section_type')
                    ->options([
                        'hero' => 'Hero',
                        'rich_text' => 'Rich Text',
                        'image_text' => 'Image and Text',
                        'services' => 'Services',
                        'portfolio' => 'Portfolio',
                        'courses' => 'Courses',
                        'hosting_plans' => 'Hosting Plans',
                        'features' => 'Features',
                        'statistics' => 'Statistics',
                        'process' => 'Process',
                        'testimonials' => 'Testimonials',
                        'team' => 'Team',
                        'partners' => 'Partners',
                        'faq' => 'FAQ',
                        'gallery' => 'Gallery',
                        'video' => 'Video',
                        'newsletter' => 'Newsletter',
                        'contact_form' => 'Contact Form',
                        'call_to_action' => 'Call to Action',
                    ])
                    ->searchable()
                    ->required(),
                TextInput::make('section_name')
                    ->maxLength(255)
                    ->helperText('Internal label used by editors.'),
                Textarea::make('content')
                    ->rows(12)
                    ->rule('json')
                    ->formatStateUsing(fn (mixed $state): string => json_encode($state ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}')
                    ->dehydrateStateUsing(function (mixed $state): array {
                        $decoded = json_decode(is_string($state) ? $state : '{}', true);

                        return is_array($decoded) ? $decoded : [];
                    })
                    ->helperText('Structured JSON content consumed by the section Blade template.'),
                Textarea::make('settings')
                    ->rows(8)
                    ->rule('json')
                    ->formatStateUsing(fn (mixed $state): string => json_encode($state ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}')
                    ->dehydrateStateUsing(function (mixed $state): array {
                        $decoded = json_decode(is_string($state) ? $state : '{}', true);

                        return is_array($decoded) ? $decoded : [];
                    })
                    ->helperText('Layout, spacing, background and display settings as JSON.'),
                TextInput::make('sort_order')
                    ->numeric()
                    ->minValue(0)
                    ->required()
                    ->default(0),
                Toggle::make('is_enabled')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('page.title')
                    ->label('Page')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('section_name')
                    ->label('Section')
                    ->searchable()
                    ->placeholder('Unnamed section'),
                TextColumn::make('section_type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->sortable(),
                IconColumn::make('is_enabled')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('page_id')
                    ->relationship('page', 'title')
                    ->label('Page'),
                SelectFilter::make('section_type')
                    ->options([
                        'hero' => 'Hero',
                        'rich_text' => 'Rich Text',
                        'services' => 'Services',
                        'portfolio' => 'Portfolio',
                        'courses' => 'Courses',
                        'hosting_plans' => 'Hosting Plans',
                        'testimonials' => 'Testimonials',
                        'faq' => 'FAQ',
                        'call_to_action' => 'Call to Action',
                    ]),
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
            ->defaultSort('sort_order');
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => ListPageSections::route('/'),
            'create' => CreatePageSection::route('/create'),
            'edit' => EditPageSection::route('/{record}/edit'),
        ];
    }
}
