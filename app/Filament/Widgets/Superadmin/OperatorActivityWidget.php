<?php

namespace App\Filament\Widgets\Superadmin;

use App\Models\User;
use App\Models\Call;
use App\Models\Sale;
use App\Models\ActivityLog;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class OperatorActivityWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('👥 Actividad de Operadores')
            ->description('Monitoreo detallado de la actividad de los operadores en el sistema')
            ->query(
                User::query()
                    ->where('role_id', 1) // Solo operadores
                    ->withCount(['calls', 'sales'])
                    // No usamos comisiones ya que la columna no existe
                    ->with('role')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->formatStateUsing(fn ($record) => $record->name . ' ' . $record->last_name)
                    ->searchable(['name', 'last_name', 'email']),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('calls_count')
                    ->label('Llamadas')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('sales_count')
                    ->label('Ventas')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.')),
                // Quitamos la columna de comisiones ya que no existe en la base de datos
                // Tables\Columns\TextColumn::make('total_commission')
                //     ->label('Comisiones')
                //     ->sortable()
                //     ->money('EUR'),
                Tables\Columns\TextColumn::make('last_login')
                    ->label('Último acceso')
                    ->formatStateUsing(function ($record) {
                        try {
                            $lastLogin = ActivityLog::where('user_id', $record->id)
                                ->where('action', 'login')
                                ->latest('created_at')
                                ->first();
                            
                            return $lastLogin 
                                ? $lastLogin->created_at->format('d/m/Y H:i:s')
                                : 'Nunca';
                        } catch (\Exception $e) {
                            return 'N/A';
                        }
                    }),
                Tables\Columns\TextColumn::make('last_ip')
                    ->label('Última IP')
                    ->formatStateUsing(function ($record) {
                        try {
                            $lastLogin = ActivityLog::where('user_id', $record->id)
                                ->where('action', 'login')
                                ->latest('created_at')
                                ->first();
                            
                            return $lastLogin 
                                ? $lastLogin->ip_address
                                : '-';
                        } catch (\Exception $e) {
                            return '-';
                        }
                    })
                    ->copyable(),
                Tables\Columns\TextColumn::make('active')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Activo' : 'Inactivo')
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('today_calls')
                    ->label('Llamadas hoy')
                    ->formatStateUsing(function ($record) {
                        try {
                            return Call::where('user_id', $record->id)
                                ->whereDate('call_date', now())
                                ->count();
                        } catch (\Exception $e) {
                            return 0;
                        }
                    }),
                Tables\Columns\TextColumn::make('today_sales')
                    ->label('Ventas hoy')
                    ->formatStateUsing(function ($record) {
                        try {
                            return Sale::where('user_id', $record->id)
                                ->whereDate('created_at', now())
                                ->count();
                        } catch (\Exception $e) {
                            return 0;
                        }
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('active')
                    ->label('Estado')
                    ->options([
                        '1' => 'Activo',
                        '0' => 'Inactivo',
                    ]),
                Tables\Filters\Filter::make('high_performers')
                    ->label('Alto rendimiento')
                    ->toggle()
                    ->query(fn (Builder $query) => $query->having('sales_count', '>=', 10)),
                Tables\Filters\Filter::make('low_performers')
                    ->label('Bajo rendimiento')
                    ->toggle()
                    ->query(fn (Builder $query) => $query->having('sales_count', '<', 5)),
            ])
            ->actions([
                Tables\Actions\Action::make('view_details')
                    ->label('Ver detalles')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => '/dashboard/resources/users/' . $record->id)
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('view_activity')
                    ->label('Ver actividad')
                    ->icon('heroicon-o-clock')
                    ->action(function ($record) {
                        // Esta acción se implementará en la interfaz de Filament
                    }),
            ])
            ->bulkActions([])
            ->defaultSort('sales_count', 'desc')
            ->paginated([10, 25, 50, 100]);
    }
}
