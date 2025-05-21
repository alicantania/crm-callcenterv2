<?php
namespace App\Filament\Widgets\Superadmin;

use Filament\Widgets\Widget;
use App\Models\Call;
use Illuminate\Support\Carbon;

class CallsChartWidget extends Widget
{
    protected static string $view = 'filament.widgets.superadmin.calls-chart-widget';

    public $period = 'week';

    public function getViewData(): array
    {
        $period = $this->period;
        $query = Call::query();
        $labels = [];
        $data = [];
        if ($period === 'day') {
            $labels = range(0, 23);
            foreach ($labels as $hour) {
                $data[] = $query->whereHour('call_time', $hour)->count();
            }
        } elseif ($period === 'week') {
            $labels = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];
            foreach (range(0, 6) as $i) {
                $data[] = $query->whereDate('call_date', Carbon::now()->startOfWeek()->addDays($i))->count();
            }
        } else {
            $labels = range(1, 31);
            foreach ($labels as $day) {
                $data[] = $query->whereDay('call_date', $day)->count();
            }
        }
        return compact('labels', 'data', 'period');
    }
}
