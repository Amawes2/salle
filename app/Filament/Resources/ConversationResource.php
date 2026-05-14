<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConversationResource\Pages;
use App\Models\Conversation;
use App\Support\CurrentGymResolver;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ConversationResource extends Resource
{
    protected static ?string $model = Conversation::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    protected static ?string $navigationLabel = 'Conversations';

    protected static ?int $navigationSort = 80;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Components\Section::make('Détails de la conversation')
                    ->schema([
                        Components\TextInput::make('title')
                            ->label('Titre')
                            ->required()
                            ->maxLength(255),
                        Components\Select::make('gym_id')
                            ->label('Salle de gym')
                            ->relationship('gym', 'name')
                            ->required()
                            ->disabled(fn (string $operation) => $operation === 'edit'),
                        Components\Select::make('super_admin_id')
                            ->label('Super Admin')
                            ->relationship('superAdmin', 'name')
                            ->searchable()
                            ->preload(),
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
                Tables\Columns\TextColumn::make('gym.name')
                    ->label('Salle de gym')
                    ->searchable(),
                Tables\Columns\TextColumn::make('superAdmin.name')
                    ->label('Super Admin')
                    ->searchable()
                    ->placeholder('Non assigné'),
                Tables\Columns\TextColumn::make('messages_count')
                    ->label('Messages')
                    ->counts('messages')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('latestMessage.created_at')
                    ->label('Dernier message')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gym')
                    ->label('Salle de gym')
                    ->relationship('gym', 'name'),
                Tables\Filters\SelectFilter::make('super_admin')
                    ->label('Super Admin')
                    ->relationship('superAdmin', 'name'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
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
            'index' => Pages\ListConversations::route('/'),
            'create' => Pages\CreateConversation::route('/create'),
            'edit' => Pages\EditConversation::route('/{record}/edit'),
            'view' => Pages\ViewConversation::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('gym_id', app(CurrentGymResolver::class)->resolve()?->id);
    }
}
