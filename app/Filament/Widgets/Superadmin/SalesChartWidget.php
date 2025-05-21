<?php
namespace App\Filament\Widgets\Superadmin;

use Filament\Widgets\Widget;
use App\Models\Sale;
use Illuminate\Support\Carbon;

class SalesChartWidget extends Widget
{
    protected static string $view = 'filament.widgets.superadmin.sales-chart-widget';

    public $period = 'week';

    public function getViewData(): array
    {
        $period = $this->period;
        $query = Sale::query();
        $labels = [];
        $data = [];
        if ($period === 'day') {
            $labels = range(0, 23);
            foreach ($labels as $hour) {
                $data[] = $query->whereHour('sale_date', $hour)->count();
            }
        } elseif ($period === 'week') {
            $labels = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];
            foreach (range(0, 6) as $i) {
                $data[] = $query->whereDate('sale_date', Carbon::now()->startOfWeek()->addDays($i))->count();
            }
        } else {
            $labels = range(1, 31);
            foreach ($labels as $day) {
                $data[] = $query->whereDay('sale_date', $day)->count();
            }
        }
        return compact('labels', 'data', 'period');
    }
}
