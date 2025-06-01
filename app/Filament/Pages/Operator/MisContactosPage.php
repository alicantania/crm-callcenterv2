<?php

namespace App\Filament\Pages\Operator;

use App\Models\Call;
use App\Models\Company;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\CompanyResource;
use App\Enums\CompanyStatus;

class MisContactosPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Mis Contactos';
    protected static ?string $navigationGroup = 'Operador';
    protected static ?int $navigationSort = 20;
    protected static string $view = 'filament.pages.operator.mis-contactos';
    /**
     * Override the page title to remove 'Page' suffix and use Spanish.
     */
    protected static ?string $title = 'Mis contactos';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Company::query()
                    ->where('assigned_operator_id', Auth::id())
                    ->whereIn('status', [
                        CompanyStatus::Contactada->value,
                        CompanyStatus::Seguimiento->value,
                    ])
            )
            ->defaultSort('updated_at', 'desc')
            // Hacer que las filas sean clicables y redirijan a la página de llamada manual
            ->recordUrl(fn (Company $record): string => route('filament.dashboard.pages.llamada-manual-page', ['empresa_id' => $record->id]))
            ->columns([
                Tables\Columns\TextColumn::make('cif')
                    ->label('CIF')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        CompanyStatus::Contactada->value => 'info',
                        CompanyStatus::Seguimiento->value => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                    
                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(),
                    

                Tables\Columns\TextColumn::make('follow_up_date')
                    ->label('Fecha seguimiento')
                    ->date()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última actualización')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        CompanyStatus::Contactada->value => 'Contactada',
                        CompanyStatus::Seguimiento->value => 'Seguimiento',
                    ]),
                Tables\Filters\Filter::make('follow_up_date')
                    ->label('Fecha de seguimiento')
                    ->form([
                        Forms\Components\DatePicker::make('follow_up_from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('follow_up_until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['follow_up_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('follow_up_date', '>=', $date),
                            )
                            ->when(
                                $data['follow_up_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('follow_up_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([])
            ->bulkActions([])
            ->paginated([10, 25, 50])
            ->deferLoading();
    }
    
    private function renderHistorialLlamadas(Company $company): string
    {
        $calls = Call::where('company_id', $company->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        if ($calls->isEmpty()) {
            return '<div class="p-4 text-center text-gray-500">No hay llamadas registradas para esta empresa.</div>';
        }
        
        $html = '<div class="space-y-4">';
        
        foreach ($calls as $call) {
            $fecha = $call->created_at->format('d/m/Y H:i');
            $operador = $call->operator?->name ?? 'Desconocido';
            $resultado = ucfirst($call->result);
            $comentarios = $call->notes ?? 'Sin comentarios';
            
            $html .= <<<HTML
            <div class="p-4 rounded-lg border border-gray-200">
                <div class="flex justify-between">
                    <div class="font-semibold text-gray-700">$fecha</div>
                    <div class="text-sm text-gray-500">Operador: $operador</div>
                </div>
                <div class="mt-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        $resultado
                    </span>
                </div>
                <div class="mt-2 text-sm text-gray-600">
                    $comentarios
                </div>
            </div>
            HTML;
        }
        
        $html .= '</div>';
        
        return $html;
    }
}
