<?php

namespace App\Filament\Widgets\Superadmin;

use App\Models\ActivityLog;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class ActivityLogWidget extends BaseWidget
{
    protected static ?int $sort = 5;
    protected int|string|array $columnSpan = 'full';
    
    /**
     * Determina si el widget puede ser visto por el usuario actual.
     * Solo visible para superadmins (role_id = 4)
     */
    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->role_id === 4;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('📊 Registro de Actividad del Sistema')
            ->description('Monitoreo completo de todas las acciones realizadas por los usuarios')
            ->query(
                ActivityLog::query()
                    ->with('user')
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha y Hora')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->formatStateUsing(function ($state, $record) {
                        if (!$record->user) {
                            return 'Sistema';
                        }
                        return $record->user->name . ' ' . $record->user->last_name;
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('action')
                    ->label('Acción')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'login' => 'success',
                        'logout' => 'danger',
                        'create' => 'info',
                        'update' => 'warning',
                        'delete' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(50)
                    ->tooltip(fn ($record): ?string => $record->description)
                    ->searchable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('Dirección IP')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user_agent')
                    ->label('Navegador')
                    ->limit(30)
                    ->tooltip(fn ($record): ?string => $record->user_agent)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('model_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn ($state) => $state ? class_basename($state) : '-')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('model_id')
                    ->label('ID')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->options(User::all()->pluck('name', 'id'))
                    ->searchable(),
                Tables\Filters\SelectFilter::make('action')
                    ->label('Acción')
                    ->options([
                        'login' => 'Inicio de sesión',
                        'logout' => 'Cierre de sesión',
                        'create' => 'Creación',
                        'update' => 'Actualización',
                        'delete' => 'Eliminación',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Desde'),
                        DatePicker::make('created_until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100]);
    }
}
