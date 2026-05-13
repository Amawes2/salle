<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\AlertResource\Pages;
use App\Models\Alert;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class AlertResource extends Resource
{
    protected static ?string $model = Alert::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static string|\UnitEnum|null $navigationGroup = 'Gestion';

    protected static ?string $navigationLabel = 'Alertes';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations de l\'alerte')
                    ->schema([
                        Forms\Components\Select::make('gym_id')
                            ->label('Salle de gym')
                            ->relationship('gym', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('type')
                            ->label('Type d\'alerte')
                            ->options([
                                Alert::TYPE_SUBSCRIPTION_EXPIRY => 'Expiration d\'abonnement',
                                Alert::TYPE_LOW_SESSIONS => 'Séances restantes faibles',
                                Alert::TYPE_PAYMENT_DUE => 'Paiement dû',
                                Alert::TYPE_SYSTEM_NOTIFICATION => 'Notification système',
                                Alert::TYPE_NEW_MESSAGE => 'Nouveau message',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('title')
                            ->label('Titre')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('content')
                            ->label('Contenu')
                            ->required()
                            ->rows(3),
                        Forms\Components\Select::make('user_id')
                            ->label('Utilisateur destinataire')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Tous les utilisateurs du gym'),
                        Forms\Components\KeyValue::make('data')
                            ->label('Données supplémentaires')
                            ->addActionLabel('Ajouter une donnée'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Alert::TYPE_SUBSCRIPTION_EXPIRY => 'danger',
                        Alert::TYPE_LOW_SESSIONS => 'warning',
                        Alert::TYPE_PAYMENT_DUE => 'warning',
                        Alert::TYPE_SYSTEM_NOTIFICATION => 'info',
                        Alert::TYPE_NEW_MESSAGE => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Alert::TYPE_SUBSCRIPTION_EXPIRY => 'Expiration abonnement',
                        Alert::TYPE_LOW_SESSIONS => 'Séances faibles',
                        Alert::TYPE_PAYMENT_DUE => 'Paiement dû',
                        Alert::TYPE_SYSTEM_NOTIFICATION => 'Notification système',
                        Alert::TYPE_NEW_MESSAGE => 'Nouveau message',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('gym.name')
                    ->label('Salle de gym')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Destinataire')
                    ->searchable()
                    ->placeholder('Tous'),
                Tables\Columns\IconColumn::make('is_read')
                    ->label('Lu')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        Alert::TYPE_SUBSCRIPTION_EXPIRY => 'Expiration abonnement',
                        Alert::TYPE_LOW_SESSIONS => 'Séances faibles',
                        Alert::TYPE_PAYMENT_DUE => 'Paiement dû',
                        Alert::TYPE_SYSTEM_NOTIFICATION => 'Notification système',
                        Alert::TYPE_NEW_MESSAGE => 'Nouveau message',
                    ]),
                Tables\Filters\SelectFilter::make('gym')
                    ->label('Salle de gym')
                    ->relationship('gym', 'name'),
                Tables\Filters\TernaryFilter::make('is_read')
                    ->label('Lu')
                    ->placeholder('Tous')
                    ->trueLabel('Lu')
                    ->falseLabel('Non lu'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('mark_as_read')
                    ->label('Marquer comme lu')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Alert $record) => ! $record->is_read)
                    ->action(fn (Alert $record) => $record->markAsRead()),
                Action::make('mark_as_unread')
                    ->label('Marquer comme non lu')
                    ->icon('heroicon-o-x-mark')
                    ->color('gray')
                    ->visible(fn (Alert $record) => $record->is_read)
                    ->action(fn (Alert $record) => $record->markAsUnread()),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('mark_as_read')
                        ->label('Marquer comme lu')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->markAsRead();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Marquer les alertes comme lues')
                        ->modalDescription('Êtes-vous sûr de vouloir marquer ces alertes comme lues ?')
                        ->modalSubmitActionLabel('Marquer comme lu'),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAlerts::route('/'),
            'create' => Pages\CreateAlert::route('/create'),
            'edit' => Pages\EditAlert::route('/{record}/edit'),
            'view' => Pages\ViewAlert::route('/{record}'),
        ];
    }
}
