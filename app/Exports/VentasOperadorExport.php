<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Carbon\Carbon;

class VentasOperadorExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    protected $reporte;
    protected $mes;
    protected $anio;
    protected $mostrarDetalle;

    public function __construct($reporte, $mes, $anio, $mostrarDetalle = true)
    {
        $this->reporte = $reporte;
        $this->mes = $mes;
        $this->anio = $anio;
        $this->mostrarDetalle = $mostrarDetalle;
    }

    public function collection()
    {
        $data = collect();
        
        foreach ($this->reporte as $row) {
            // Añadir fila de resumen del operador
            $data->push([
                'tipo' => 'operador',
                'operador_nombre' => $row['operador']->name . ' ' . ($row['operador']->last_name ?? ''),
                'operador_email' => $row['operador']->email,
                'total_ventas' => $row['total_ventas'],
                'total_dinero' => $row['total_dinero'],
                'total_comision' => $row['total_comision'],
            ]);
            
            if ($this->mostrarDetalle) {
                foreach ($row['detalle'] as $linea => $detalleLinea) {
                    // Añadir fila de resumen de línea de negocio
                    $data->push([
                        'tipo' => 'linea',
                        'linea_nombre' => $detalleLinea['linea'],
                        'total_ventas' => $detalleLinea['total_ventas'],
                        'total_dinero' => $detalleLinea['total_dinero'],
                        'total_comision' => $detalleLinea['total_comision'],
                    ]);
                    
                    // Añadir filas de ventas individuales
                    foreach ($detalleLinea['ventas'] as $venta) {
                        $data->push([
                            'tipo' => 'venta',
                            'empresa' => $venta['empresa'],
                            'cif' => $venta['cif'],
                            'producto' => $venta['producto'],
                            'fecha' => $venta['fecha'],
                            'importe' => $venta['importe'],
                            'comision' => $venta['comision'],
                            'status' => $venta['status'],
                            'contabilizar' => $venta['contabilizar'],
                        ]);
                    }
                }
            }
        }
        
        return $data;
    }

    public function headings(): array
    {
        return [
            'Tipo',
            'Operador/Línea/Empresa',
            'Email/CIF',
            'Producto',
            'Fecha',
            'Ventas',
            'Importe (€)',
            'Comisión (€)',
            'Estado',
            'Contabiliza',
        ];
    }

    public function map($row): array
    {
        if ($row['tipo'] === 'operador') {
            return [
                'OPERADOR',
                $row['operador_nombre'],
                $row['operador_email'],
                '',
                '',
                $row['total_ventas'],
                $row['total_dinero'],
                $row['total_comision'],
                '',
                '',
            ];
        } elseif ($row['tipo'] === 'linea') {
            return [
                'LÍNEA',
                $row['linea_nombre'],
                '',
                '',
                '',
                $row['total_ventas'],
                $row['total_dinero'],
                $row['total_comision'],
                '',
                '',
            ];
        } else {
            $contabiliza = '';
            switch ($row['contabilizar']) {
                case 1:
                    $contabiliza = 'SUMA';
                    break;
                case -1:
                    $contabiliza = 'RESTA';
                    break;
                default:
                    $contabiliza = 'STANDBY';
                    break;
            }
            
            return [
                'Venta',
                $row['empresa'],
                $row['cif'],
                $row['producto'],
                Carbon::parse($row['fecha'])->format('d/m/Y'),
                '1',
                $row['importe'],
                $row['comision'],
                $row['status'],
                $contabiliza,
            ];
        }
    }

    public function title(): string
    {
        $nombreMes = Carbon::createFromDate($this->anio, $this->mes, 1)->locale('es')->monthName;
        return "Ventas " . ucfirst($nombreMes) . " " . $this->anio;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $styleArray = [];
        
        // Estilo para la fila de encabezados
        $styleArray[1] = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4F46E5']],
        ];
        
        // Aplicar estilos a las filas según su tipo
        for ($i = 2; $i <= $lastRow; $i++) {
            $tipo = $sheet->getCell('A' . $i)->getValue();
            $estado = $sheet->getCell('I' . $i)->getValue();
            $contabiliza = $sheet->getCell('J' . $i)->getValue();
            
            if ($tipo === 'OPERADOR') {
                $styleArray[$i] = [
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E5E7EB']],
                ];
            } elseif ($tipo === 'LÍNEA') {
                $styleArray[$i] = [
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F3F4F6']],
                ];
            } elseif ($tipo === 'Venta') {
                // Colorear según el estado
                $colorRgb = 'FFFFFF'; // Blanco por defecto
                
                switch (strtolower($estado)) {
                    case 'tramitada':
                    case 'completada':
                    case 'procesada':
                        $colorRgb = 'DCFCE7'; // Verde claro
                        break;
                    case 'anulada':
                    case 'cancelada':
                        $colorRgb = 'FEE2E2'; // Rojo claro
                        break;
                    case 'incidentada':
                        $colorRgb = 'FFEDD5'; // Naranja claro
                        break;
                    case 'pendiente':
                    default:
                        $colorRgb = 'FEF3C7'; // Amarillo claro
                        break;
                }
                
                $styleArray[$i] = [
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $colorRgb]],
                ];
            }
        }
        
        return $styleArray;
    }
}
