<?php

namespace App\Modules\Cms\Filament\Admin\Resources;

use App\Modules\Cms\Filament\Admin\Resources\PageRevisionResource\Pages\ListPageRevisions;
use App\Modules\Cms\Models\PageRevision;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PageRevisionResource extends Resource
{
    protected static ?string $model = PageRevision::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static string|\UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $navigationLabel = 'Page Revisions';

    protected static ?string $recordTitleAttribute = 'id';

    public static function isScopedToTenant(): bool
    {
        return false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('page.title')
                    ->label('Page')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('snapshot.page.title')
                    ->label('Revision title')
                    ->placeholder('Untitled revision'),
                TextColumn::make('user.name')
                    ->label('Saved by')
                    ->placeholder('System'),
                TextColumn::make('created_at')
                    ->label('Saved at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('restored_at')
                    ->dateTime()
                    ->placeholder('Not restored')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('page_id')
                    ->relationship('page', 'title')
                    ->label('Page'),
            ])
            ->recordActions([
                Action::make('restore')
                    ->label('Restore')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->modalHeading('Restore this page revision?')
                    ->modalDescription('The current page and sections will be saved as a new revision before this snapshot is restored.')
                    ->action(function (PageRevision $record): void {
                        $record->restore(auth()->id());

                        Notification::make()
                            ->title('Page revision restored')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => ListPageRevisions::route('/'),
        ];
    }
}
