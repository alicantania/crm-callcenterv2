<?php

namespace App\Filament\Widgets\Superadmin;

use App\Models\ActivityLog;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LoginHistoryWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('🔐 Historial de Inicios de Sesión')
            ->description('Monitoreo de todos los inicios de sesión en el sistema')
            ->query(
                ActivityLog::query()
                    ->where('action', 'login')
                    ->with('user')
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha y Hora')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->formatStateUsing(function ($state, $record) {
                        if (!$record->user) {
                            return 'Desconocido';
                        }
                        return $record->user->name . ' ' . $record->user->last_name;
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.role.name')
                    ->label('Rol')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Operador' => 'success',
                        'Supervisor' => 'warning',
                        'Admin' => 'danger',
                        'Superadmin' => 'purple',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('Dirección IP')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-globe-alt'),
                Tables\Columns\TextColumn::make('user_agent')
                    ->label('Navegador/Dispositivo')
                    ->limit(40)
                    ->tooltip(fn ($record): ?string => $record->user_agent)
                    ->searchable(),
                Tables\Columns\TextColumn::make('properties')
                    ->label('Detalles')
                    ->formatStateUsing(function ($state) {
                        if (!$state || !is_array($state)) {
                            return '-';
                        }
                        
                        $details = [];
                        foreach ($state as $key => $value) {
                            if ($key !== 'ip' && $key !== 'user_agent') {
                                $details[] = ucfirst($key) . ': ' . $value;
                            }
                        }
                        
                        return implode(', ', $details);
                    })
                    ->tooltip(function ($record) {
                        if (!$record->properties || !is_array($record->properties)) {
                            return null;
                        }
                        
                        $details = [];
                        foreach ($record->properties as $key => $value) {
                            $details[] = ucfirst($key) . ': ' . $value;
                        }
                        
                        return implode("\n", $details);
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->options(User::all()->pluck('name', 'id'))
                    ->searchable(),
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
