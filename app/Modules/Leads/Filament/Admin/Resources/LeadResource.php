<?php

namespace App\Modules\Leads\Filament\Admin\Resources;

use App\Modules\Leads\Filament\Admin\Resources\LeadResource\Pages\EditLead;
use App\Modules\Leads\Filament\Admin\Resources\LeadResource\Pages\ListLeads;
use App\Modules\Leads\Models\Lead;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static string|\UnitEnum|null $navigationGroup = 'Business';

    protected static ?string $navigationLabel = 'Leads';

    protected static ?string $recordTitleAttribute = 'name';

    public static function isScopedToTenant(): bool
    {
        return false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('status')
                    ->options(config('leads.statuses', []))
                    ->required(),
                Select::make('assigned_to')
                    ->relationship('assignee', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Select::make('source')
                    ->options(config('leads.sources', []))
                    ->required(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(120),
                TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(50),
                TextInput::make('company')
                    ->maxLength(255),
                TextInput::make('subject')
                    ->maxLength(255),
                TextInput::make('page_url')
                    ->url()
                    ->maxLength(2048),
                Textarea::make('message')
                    ->required()
                    ->rows(8)
                    ->maxLength(5000),
                Textarea::make('internal_notes')
                    ->rows(8),
                DateTimePicker::make('contacted_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->placeholder('No email'),
                TextColumn::make('phone')
                    ->searchable()
                    ->copyable()
                    ->placeholder('No phone'),
                TextColumn::make('subject')
                    ->limit(40)
                    ->placeholder('General enquiry'),
                TextColumn::make('source')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('assignee.name')
                    ->label('Assigned to')
                    ->placeholder('Unassigned'),
                TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(config('leads.statuses', [])),
                SelectFilter::make('source')
                    ->options(config('leads.sources', [])),
                SelectFilter::make('assigned_to')
                    ->relationship('assignee', 'name')
                    ->label('Assigned to'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => ListLeads::route('/'),
            'edit' => EditLead::route('/{record}/edit'),
        ];
    }
}
