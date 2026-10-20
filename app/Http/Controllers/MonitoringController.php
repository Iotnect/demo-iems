<?php

namespace App\Http\Controllers;

use DateTimeImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MonitoringController extends Controller
{
    public function index(Request $request, string $screen = 'overview'): View
    {
        return view('iems.'.$screen, $this->data($request, $screen));
    }

    public function settings(Request $request): RedirectResponse
    {
        $settings = $request->validate([
            'methane_threshold' => ['required', 'numeric', 'min:1', 'max:100'],
            'air_threshold' => ['required', 'numeric', 'min:1', 'max:100'],
            'water_threshold' => ['required', 'numeric', 'min:1', 'max:100'],
            'frequency' => ['required', Rule::in(['Daily availability check', 'Every 3 days', 'Weekly'])],
            'baseline' => ['required', Rule::in(['2023–2025 seasonal reference', '2025 annual reference'])],
        ]);
        $request->session()->put('iems.settings', $settings);

        return to_route('iems.screen', ['screen' => 'administration'])->with('success', 'Demo configuration saved for this browser session.');
    }

    public function review(Request $request, string $anomaly): RedirectResponse
    {
        abort_unless(in_array($anomaly, ['ANM-026', 'ANM-025', 'ANM-024', 'ANM-023'], true), 404);
        $review = $request->validate([
            'status' => ['required', Rule::in(['New', 'Under Review', 'Reviewed', 'Closed'])],
            'note' => ['required', 'string', 'min:5', 'max:1000'],
        ]);
        $review['updated_at'] = now('Asia/Kuala_Lumpur')->format('d M Y, H:i').' MYT';
        $request->session()->put('iems.reviews.'.$anomaly, $review);

        return to_route('iems.screen', ['screen' => 'alerts', 'anomaly' => $anomaly] + $request->only('aoi', 'period', 'parameter', 'reference', 'start_date', 'end_date'))
            ->with('success', 'Anomaly review updated in this demonstration session.');
    }

    public function report(Request $request): View
    {
        $type = $request->validate(['type' => ['nullable', Rule::in(['progress', 'baseline', 'findings', 'evaluation', 'roadmap'])]]);
        $titles = ['progress' => 'Periodic Progress Report', 'baseline' => 'Environmental Baseline Assessment', 'findings' => 'Environmental Monitoring Findings', 'evaluation' => 'Final PoC Evaluation', 'roadmap' => 'IEMS Implementation Roadmap'];

        return view('iems.print-report', $this->data($request, 'reports') + ['reportTitle' => $titles[$type['type'] ?? 'findings']]);
    }

    public function export(Request $request): StreamedResponse
    {
        $data = $this->data($request, 'monitoring');

        return response()->streamDownload(function () use ($data): void {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, ['IEMS demonstration data — illustrative observations only'], ',', '"', '');
            fputcsv($stream, ['AOI', 'Acquisition (MYT)', 'Parameter', 'Value', 'Unit', 'Type', 'Confidence', 'Coverage'], ',', '"', '');
            foreach ($data['observations'] as $observation) {
                fputcsv($stream, [$data['aoi']['name'], $observation['time'], $observation['parameter'], $observation['value'], $observation['unit'], 'Satellite-derived', $observation['confidence'], $observation['coverage'].'%'], ',', '"', '');
            }
            fclose($stream);
        }, 'iems-demonstration-observations.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function data(Request $request, string $screen): array
    {
        $filters = $request->validate([
            'aoi' => ['nullable', Rule::in(['coastal', 'catchment'])],
            'period' => ['nullable', Rule::in(['month', 'week', 'empty', 'custom'])],
            'start_date' => ['exclude_unless:period,custom', 'required', 'date_format:Y-m-d'],
            'end_date' => ['exclude_unless:period,custom', 'required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'parameter' => ['nullable', Rule::in(['all', 'methane', 'air', 'water'])],
            'severity' => ['nullable', Rule::in(['all', 'High', 'Medium', 'Low'])],
            'status' => ['nullable', Rule::in(['all', 'New', 'Under Review', 'Reviewed', 'Closed'])],
            'anomaly' => ['nullable', Rule::in(['ANM-026', 'ANM-025', 'ANM-024', 'ANM-023'])],
            'detected' => ['nullable', Rule::in(['all', 'latest'])],
            'reference' => ['nullable', Rule::in(['seasonal', 'annual'])],
            'type' => ['nullable', Rule::in(['progress', 'baseline', 'findings', 'evaluation', 'roadmap'])],
        ], [
            'end_date.after_or_equal' => 'The end date must be on or after the start date.',
        ]);
        $aoiKey = $filters['aoi'] ?? 'coastal';
        $period = $filters['period'] ?? 'month';
        $parameter = $filters['parameter'] ?? 'all';
        $reference = $filters['reference'] ?? 'seasonal';
        $query = ['aoi' => $aoiKey, 'period' => $period, 'parameter' => $parameter, 'reference' => $reference];
        [$startDate, $endDate] = match ($period) {
            'custom' => [$filters['start_date'], $filters['end_date']],
            'week' => ['2026-10-14', '2026-10-20'],
            'empty' => ['2026-09-01', '2026-09-30'],
            default => ['2026-10-01', '2026-10-20'],
        };
        if ($period === 'custom') {
            $query += ['start_date' => $startDate, 'end_date' => $endDate];
        }
        $reportType = $filters['type'] ?? 'findings';
        $aois = [
            'coastal' => ['name' => 'Coastal Industrial AOI — Demo', 'short' => 'Coastal Industrial', 'region' => 'Pengerang, Johor · Malaysia', 'area' => '142.6', 'coverage' => 94, 'x' => 575, 'y' => 280, 'methane' => 1924, 'air' => 3.42, 'water' => 8.6, 'offset' => 0, 'status' => 'Attention required'],
            'catchment' => ['name' => 'River Catchment AOI — Demo', 'short' => 'River Catchment', 'region' => 'Kota Tinggi, Johor · Malaysia', 'area' => '214.8', 'coverage' => 97, 'x' => 465, 'y' => 100, 'methane' => 1854, 'air' => 2.24, 'water' => 5.8, 'offset' => -55, 'status' => 'Within reference range'],
        ];
        $aoi = $aois[$aoiKey];
        $hasData = $startDate <= '2026-10-19' && $endDate >= '2026-10-01';
        $start = new DateTimeImmutable($startDate);
        $end = new DateTimeImmutable($endDate);
        $periodLabel = $startDate === $endDate ? $start->format('j M Y') : ($start->format('Y-m') === $end->format('Y-m')
            ? $start->format('j').'–'.$end->format('j M Y')
            : $start->format('j M Y').' – '.$end->format('j M Y'));
        $metrics = [
            'methane' => ['label' => 'Methane', 'descriptor' => 'Column concentration', 'value' => number_format($aoi['methane']), 'unit' => 'ppb', 'delta' => round(($aoi['methane'] - 1850) / 1850 * 100, 1), 'baseline' => 1850, 'color' => 'teal', 'icon' => 'methane', 'quality' => 'High confidence', 'source' => 'Sentinel-5P · TROPOMI', 'acquired' => '19 Oct, 14:20 MYT', 'fullunit' => 'ppb', 'coverage' => $aoi['coverage'], 'method' => 'Quality-screened, satellite-derived dry-air column-averaged methane. Cloud-affected pixels are excluded. These columns do not represent ground-level concentrations.'],
            'air' => ['label' => 'Air quality', 'descriptor' => 'Tropospheric NO₂ column', 'value' => number_format($aoi['air'], 2), 'unit' => '×10¹⁵ molec/cm²', 'delta' => round(($aoi['air'] - 3.05) / 3.05 * 100, 1), 'baseline' => 3.05, 'color' => 'blue', 'icon' => 'wind', 'quality' => 'High confidence', 'source' => 'Sentinel-5P · TROPOMI', 'acquired' => '19 Oct, 14:20 MYT', 'fullunit' => '×10¹⁵ molecules/cm²', 'coverage' => $aoi['coverage'] - 3, 'method' => 'Satellite-derived tropospheric nitrogen dioxide column, screened for clouds and retrieval quality. This is not a ground-level air quality index or a regulatory compliance measure.'],
            'water' => ['label' => 'Water quality', 'descriptor' => 'Estimated turbidity', 'value' => number_format($aoi['water'], 1), 'unit' => 'NTU', 'delta' => round(($aoi['water'] - 9.2) / 9.2 * 100, 1), 'baseline' => 9.2, 'color' => 'cyan', 'icon' => 'droplet', 'quality' => 'Moderate confidence', 'source' => 'Sentinel-2 · MSI', 'acquired' => '17 Oct, 10:45 MYT', 'fullunit' => 'NTU · satellite-derived estimate', 'coverage' => $aoi['coverage'] - 16, 'method' => 'Illustrative turbidity estimate derived from multispectral water reflectance. Coastal adjacency and cloud contamination reduce confidence. In-situ sampling is needed for validation.'],
        ];
        if ($reference === 'annual') {
            foreach (['methane' => 1838, 'air' => 2.93, 'water' => 8.8] as $key => $value) {
                $metrics[$key]['baseline'] = $value;
                $metrics[$key]['delta'] = round(((float) str_replace(',', '', $metrics[$key]['value']) - $value) / $value * 100, 1);
            }
        }
        $observations = [];
        if ($hasData) {
            foreach ($metrics as $key => $metric) {
                foreach ([19, 18, 16, 14, 12, 9, 5, 2] as $index => $day) {
                    $acquisitionDay = $key === 'water' ? max(1, $day - 2) : $day;
                    $acquisitionDate = sprintf('2026-10-%02d', $acquisitionDay);
                    if ($acquisitionDate < $startDate || $acquisitionDate > $endDate) {
                        continue;
                    }
                    $value = (float) str_replace(',', '', $metric['value']);
                    $observations[] = ['key' => $key, 'date' => $acquisitionDate, 'parameter' => $metric['label'], 'time' => sprintf('%02d Oct 2026, %s MYT', $acquisitionDay, $key === 'water' ? '10:45' : '14:20'), 'value' => round($value * (1 + sin($index * 2) * 0.025), $key === 'methane' ? 0 : 2), 'unit' => $metric['unit'], 'confidence' => $index === 2 ? 'Low' : ($key === 'water' ? 'Moderate' : 'High'), 'coverage' => max(50, $metric['coverage'] - $index * 2), 'source' => $metric['source']];
                }
            }
        }
        foreach ($metrics as $key => &$metric) {
            $metric['series'] = array_reverse(array_values(array_filter($observations, fn (array $row): bool => $row['key'] === $key)));
            $metric['available'] = count($metric['series']) > 0;
            $latest = $metric['series'] ? $metric['series'][array_key_last($metric['series'])] : null;
            $metric['latestDate'] = $latest ? (new DateTimeImmutable($latest['date']))->format('d M Y') : 'Unavailable';
            if ($latest) {
                $metric['value'] = number_format($latest['value'], $key === 'methane' ? 0 : ($key === 'water' ? 1 : 2));
                $metric['delta'] = round(($latest['value'] - $metric['baseline']) / $metric['baseline'] * 100, 1);
                $metric['acquired'] = str_replace(' 2026', '', $latest['time']);
                $metric['quality'] = $latest['confidence'].' confidence';
                $metric['coverage'] = $latest['coverage'];
            }
        }
        unset($metric);
        $observations = array_values(array_filter($observations, fn (array $row): bool => $parameter === 'all' || $row['key'] === $parameter));
        $hasData = count($observations) > 0;
        $latestAcquisition = collect($observations)->sortByDesc('date')->first()['time'] ?? 'No acquisition in selected period';
        $availableParameters = array_keys(array_filter($metrics, fn (array $metric): bool => $metric['available']));
        $anomalies = $aoiKey === 'catchment' || ! $hasData ? [] : [
            ['id' => 'ANM-026', 'parameter' => 'methane', 'title' => 'Potential methane anomaly', 'location' => 'Northeast industrial zone', 'date' => '19 Oct 2026', 'time' => '19 Oct 2026, 14:20 MYT', 'severity' => 'High', 'status' => 'New', 'value' => '2,048 ppb', 'deviation' => '+10.7%', 'confidence' => 'High · 0.89', 'finding' => 'A cluster of elevated methane column observations is present northeast of the illustrative AOI. Wind transport and retrieval effects have not been ruled out. This is not a confirmed emission source.', 'action' => 'Review adjacent satellite acquisitions and meteorological context. Coordinate ground-level validation before attributing a source.'],
            ['id' => 'ANM-025', 'parameter' => 'air', 'title' => 'Elevated NO₂ observation', 'location' => 'Eastern coastal corridor', 'date' => '18 Oct 2026', 'time' => '18 Oct 2026, 14:05 MYT', 'severity' => 'Medium', 'status' => 'Under Review', 'value' => '3.84 ×10¹⁵ molec/cm²', 'deviation' => '+25.9%', 'confidence' => 'Moderate · 0.76', 'finding' => 'The tropospheric NO₂ column is above the seasonal reference in a coastal pixel cluster. Satellite columns are not directly comparable with ground-level regulatory limits.', 'action' => 'Compare regional transport, cloud fraction, and neighbouring acquisitions. Retain for analyst review.'],
            ['id' => 'ANM-024', 'parameter' => 'water', 'title' => 'Coastal turbidity variation', 'location' => 'Southern coastal waters', 'date' => '17 Oct 2026', 'time' => '17 Oct 2026, 10:45 MYT', 'severity' => 'Low', 'status' => 'Reviewed', 'value' => '12.4 NTU (estimate)', 'deviation' => '+34.8%', 'confidence' => 'Low · 0.58', 'finding' => 'A localised reflectance-derived turbidity estimate is above the historical reference. Rainfall, tides, and coastal adjacency may explain the variation.', 'action' => 'Review tidal and rainfall context, and request a water sample if elevated estimates persist.'],
            ['id' => 'ANM-023', 'parameter' => 'methane', 'title' => 'Historical methane variation', 'location' => 'Western monitoring sector', 'date' => '09 Oct 2026', 'time' => '09 Oct 2026, 14:15 MYT', 'severity' => 'Low', 'status' => 'Closed', 'value' => '1,902 ppb', 'deviation' => '+2.8%', 'confidence' => 'High · 0.92', 'finding' => 'Subsequent acquisitions returned within the seasonal reference band. No persistent signal was identified in the available satellite record.', 'action' => 'Continue scheduled monitoring; reopen if a repeated spatial pattern is observed.'],
        ];
        $anomalies = array_values(array_filter($anomalies, fn (array $item): bool => ($parameter === 'all' || $item['parameter'] === $parameter)
            && (new DateTimeImmutable($item['date']))->format('Y-m-d') >= $startDate
            && (new DateTimeImmutable($item['date']))->format('Y-m-d') <= $endDate
        ));
        foreach ($anomalies as &$anomaly) {
            $review = $request->session()->get('iems.reviews.'.$anomaly['id']);
            if ($review) {
                $anomaly['status'] = $review['status'];
                $anomaly['review'] = $review;
            }
        }
        unset($anomaly);
        $openAnomalies = count(array_filter($anomalies, fn (array $item): bool => ! in_array($item['status'], ['Reviewed', 'Closed'], true)));
        $priorityAnomalies = array_values(array_filter($anomalies, fn (array $item): bool => ! in_array($item['status'], ['Reviewed', 'Closed'], true)));
        $findingsSummary = ! $hasData
            ? 'No observations are available for the selected period. Environmental conditions and anomaly presence cannot be assessed.'
            : ($openAnomalies > 0
                ? 'Flagged satellite observations require analyst assessment. Compare repeat acquisitions and reference conditions before drawing conclusions.'
                : 'No open anomalies in the available observations. Continue scheduled monitoring and account for gaps in coverage.');
        $openSeverityCounts = [];
        foreach (['High', 'Medium', 'Low'] as $severity) {
            $openSeverityCounts[$severity] = count(array_filter($anomalies, fn (array $item): bool => $item['severity'] === $severity && ! in_array($item['status'], ['Reviewed', 'Closed'], true)));
        }
        $filteredAnomalies = array_values(array_filter($anomalies, fn (array $item): bool => ($parameter === 'all' || $item['parameter'] === $parameter)
            && (($filters['severity'] ?? 'all') === 'all' || $item['severity'] === $filters['severity'])
            && (($filters['status'] ?? 'all') === 'all' || $item['status'] === $filters['status'])
            && (($filters['detected'] ?? 'all') === 'all' || $item['date'] === '19 Oct 2026')
        ));
        $selectedAnomaly = collect($filteredAnomalies)->firstWhere('id', $filters['anomaly'] ?? 'ANM-026') ?? ($filteredAnomalies[0] ?? null);
        $navigation = [
            'overview' => ['Overview', 'overview'], 'areas-of-interest' => ['Areas of Interest', 'map'], 'monitoring' => ['Environmental Monitoring', 'activity'], 'historical-baseline' => ['Historical Baseline', 'history'], 'alerts' => ['Alerts & Anomalies', 'alert'], 'reports' => ['Reports', 'file'], 'poc-evaluation' => ['PoC Evaluation', 'chart'], 'administration' => ['Administration', 'settings'],
        ];
        $titles = ['overview' => 'Environmental Overview', 'areas-of-interest' => 'Areas of Interest', 'monitoring' => 'Environmental Monitoring', 'historical-baseline' => 'Historical Baseline', 'alerts' => 'Alerts & Anomalies', 'reports' => 'Reports & Insights', 'poc-evaluation' => 'PoC Evaluation & Roadmap', 'administration' => 'Administration'];
        $settings = $request->session()->get('iems.settings', ['methane_threshold' => 8, 'air_threshold' => 20, 'water_threshold' => 30, 'frequency' => 'Daily availability check', 'baseline' => '2023–2025 seasonal reference']);

        return compact('screen', 'filters', 'aoiKey', 'period', 'parameter', 'query', 'startDate', 'endDate', 'latestAcquisition', 'availableParameters', 'reference', 'reportType', 'aois', 'aoi', 'hasData', 'periodLabel', 'metrics', 'anomalies', 'filteredAnomalies', 'selectedAnomaly', 'priorityAnomalies', 'findingsSummary', 'openAnomalies', 'openSeverityCounts', 'observations', 'navigation', 'titles', 'settings');
    }
}
