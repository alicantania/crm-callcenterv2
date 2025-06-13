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
use Carbon\Carbon;

class OperatorActivityWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $today = Carbon::today();
        
        return $table
            ->heading('👥 Actividad de Operadores')
            ->description('Monitoreo detallado de la actividad de los operadores en el sistema')
            ->query(
                User::query()
                    ->where('role_id', 1) // Solo operadores
                    ->withCount([
                        'calls', 
                        'sales',
                        'calls as today_calls_count' => function ($query) use ($today) {
                            $query->whereDate('call_date', $today);
                        },
                        'sales as today_sales_count' => function ($query) use ($today) {
                            $query->whereDate('created_at', $today);
                        }
                    ])
                    ->with(['role'])
                    ->addSelect([
                        'total_commission' => Sale::selectRaw('COALESCE(SUM(commission_amount), 0)')
                            ->whereColumn('operator_id', 'users.id'),
                        'last_login_at' => ActivityLog::select('created_at')
                            ->whereColumn('user_id', 'users.id')
                            ->where('action', 'login')
                            ->latest('created_at')
                            ->limit(1),
                        'last_login_ip' => ActivityLog::select('ip_address')
                            ->whereColumn('user_id', 'users.id')
                            ->where('action', 'login')
                            ->latest('created_at')
                            ->limit(1)
                    ])
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
                    
                Tables\Columns\TextColumn::make('total_commission')
                    ->label('Comisiones')
                    ->sortable()
                    ->money('EUR'),
                    
                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Último acceso')
                    ->date('d/m/Y H:i:s')
                    ->placeholder('Nunca'),
                    
                Tables\Columns\TextColumn::make('last_login_ip')
                    ->label('Última IP')
                    ->placeholder('-')
                    ->copyable(),
                    
                Tables\Columns\TextColumn::make('active')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Activo' : 'Inactivo')
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
                    
                Tables\Columns\TextColumn::make('today_calls_count')
                    ->label('Llamadas hoy')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('today_sales_count')
                    ->label('Ventas hoy')
                    ->sortable(),
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
                    
                Tables\Filters\Filter::make('with_activity_today')
                    ->label('Con actividad hoy')
                    ->toggle()
                    ->query(fn (Builder $query) => $query
                        ->having('today_calls_count', '>', 0)
                        ->orHaving('today_sales_count', '>', 0)
                    ),
            ])
            // ->actions([
            //     Tables\Actions\Action::make('view_details')
            //         ->label('Ver detalles')
            //         ->icon('heroicon-o-eye')
            //         ->url(fn ($record) => '/dashboard/resources/users/' . $record->id)
            //         ->openUrlInNewTab(),
                    
            //     Tables\Actions\ViewAction::make('view_activity')
            //         ->label('Ver actividad')
            //         ->icon('heroicon-o-clock')
            //         ->modalHeading(fn ($record) => 'Actividad de ' . $record->name)
            //         ->modalContent(fn ($record) => view('filament.modals.user-activity', ['user' => $record])),
            // ])
            ->bulkActions([])
            ->defaultSort('sales_count', 'desc')
            ->paginated([10, 25, 50, 100])
            ->deferLoading();
    }
}
