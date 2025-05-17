<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Forms;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Auth;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Auditoría';
    protected static ?string $navigationGroup = 'Gerencia';
    protected static ?int $navigationSort = 20;

    public static function shouldRegisterNavigation(): bool
    {
        // Solo visible para Gerencia, Admin y SuperAdmin
        return Auth::check() && in_array(Auth::user()->role_id, [3, 4, 2]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            // Solo lectura, no edición
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('causer.name')->label('Usuario'),
                Tables\Columns\TextColumn::make('description')->label('Acción'),
                Tables\Columns\TextColumn::make('subject_type')->label('Modelo'),
                Tables\Columns\TextColumn::make('event')->label('Evento'),
                Tables\Columns\TextColumn::make('properties')->label('Detalles')->limit(50),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('causer_id')
                    ->label('Usuario')
                    ->options(fn () => \App\Models\User::pluck('name', 'id')->toArray()),
                Tables\Filters\SelectFilter::make('subject_type')
                    ->label('Modelo')
                    ->options(Activity::query()->distinct()->pluck('subject_type', 'subject_type')->toArray()),
                Tables\Filters\SelectFilter::make('event')
                    ->label('Evento')
                    ->options(Activity::query()->distinct()->pluck('event', 'event')->toArray()),
            ])
            ->actions([])
            ->bulkActions([
                Tables\Actions\ExportBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\ActivityResource\Pages\ListActivities::route('/'),
        ];
    }
}
