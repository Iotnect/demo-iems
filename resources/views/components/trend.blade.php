@props(['metric', 'parameter' => 'methane', 'available' => true, 'historical' => false, 'offset' => 0, 'compact' => false])
@php
    $available = $available && ($historical || ($metric['available'] ?? true));
    $baseline = (float) $metric['baseline'];
    $center = (float) str_replace(',', '', $metric['value']);
    $spread = $parameter === 'methane' ? 150 : ($parameter === 'air' ? 1.1 : 5);
    $low = $baseline - $spread * .55;
    $high = max($baseline + $spread, $center + $spread * .25);
    $points = [];
    $rawValues = [];
    $series = $metric['series'] ?? [];
    if ($historical) {
        $series = [];
        $annual = request('reference') === 'annual';
        for ($i = 0; $i < ($annual ? 12 : 36); $i++) {
            $series[] = ['value' => $baseline + sin($i * .52) * $spread * .18 + cos($i * 1.8) * $spread * .07, 'time' => date('M Y', strtotime(($annual ? '2025' : '2023').'-01-01 +'.$i.' months'))];
        }
    }
    $values = array_column($series, 'value');
    $low = min($low, ($values ? min($values) : $center) - $spread * .15);
    $high = max($high, ($values ? max($values) : $center) + $spread * .15);
    $firstDate = $series ? strtotime(substr($series[0]['time'], 0, $historical ? 8 : 11)) : 0;
    $lastDate = $series ? strtotime(substr($series[array_key_last($series)]['time'], 0, $historical ? 8 : 11)) : 0;
    foreach ($series as $i => $observation) {
        $value = (float) $observation['value'];
        $date = strtotime(substr($observation['time'], 0, $historical ? 8 : 11));
        $x = 52 + ($date - $firstDate) / max(1, $lastDate - $firstDate) * 650;
        $y = 185 - (($value - $low) / ($high - $low)) * 145;
        $points[] = round($x, 1).','.round($y, 1);
        $rawValues[] = ['x' => round($x, 1), 'y' => round($y, 1), 'value' => round($value, $parameter === 'methane' ? 0 : 2), 'date' => $observation['time']];
    }
    $baselineY = 185 - (($baseline - $low) / ($high - $low)) * 145;
@endphp
<div class="trend-chart {{ $compact ? 'compact' : '' }}" data-chart>
    @if(!$available)
        <div class="empty-state"><x-icon name="satellite" size="32"/><strong>No observations available</strong><p>Choose an October period to view demonstration observations.</p></div>
    @else
    <span class="chart-unit">{{ $metric['fullunit'] }}</span>
    <svg viewBox="0 0 740 222" role="img" aria-label="{{ $metric['label'] }} observations compared with a historical baseline. Illustrative values.">
        <defs><linearGradient id="fill-{{ $parameter }}-{{ $historical ? 'history' : 'current' }}" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#009b88" stop-opacity=".15"/><stop offset="100%" stop-color="#009b88" stop-opacity="0"/></linearGradient></defs>
        @foreach([40, 88, 136, 185] as $i => $y)
        <line x1="52" y1="{{ $y }}" x2="710" y2="{{ $y }}" stroke="#e9edef" stroke-dasharray="3 4"/>
        <text x="39" y="{{ $y + 4 }}" text-anchor="end" fill="#526b74" font-size="13">{{ number_format($high - $i * ($high - $low) / 3, $parameter === 'methane' ? 0 : 1) }}</text>
        @endforeach
        <rect x="52" y="{{ $baselineY - 16 }}" width="658" height="32" fill="#eff3f2"/>
        <line x1="52" y1="{{ $baselineY }}" x2="710" y2="{{ $baselineY }}" stroke="#9ba8a5" stroke-dasharray="5 5" stroke-width="1.2"/>
        <path d="M52,185 L{{ implode(' L', $points) }} L702,185 Z" fill="url(#fill-{{ $parameter }}-{{ $historical ? 'history' : 'current' }})"/>
        <polyline points="{{ implode(' ', $points) }}" fill="none" stroke="#008f7c" stroke-width="2.5" stroke-linejoin="round"/>
        @foreach($rawValues as $point)
        <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="4" fill="#fff" stroke="#008f7c" stroke-width="2" class="chart-point" tabindex="0" data-chart-value="{{ $point['date'] }} · {{ $point['value'] }} {{ $metric['unit'] }}"><title>{{ $point['date'] }}: {{ $point['value'] }} {{ $metric['unit'] }}</title></circle>
        @endforeach
        @php($axisIndexes = $series ? array_unique([0, (int) round((count($series) - 1) / 4), (int) round((count($series) - 1) / 2), (int) round((count($series) - 1) * .75), count($series) - 1]) : [])
        @foreach($axisIndexes as $index)<text x="{{ $rawValues[$index]['x'] }}" y="212" fill="#526b74" text-anchor="{{ $loop->first ? 'start' : 'middle' }}" font-size="13">{{ date($historical ? 'M y' : 'd M', strtotime(substr($series[$index]['time'], 0, $historical ? 8 : 11))) }}</text>@endforeach
    </svg>
    <div class="chart-tooltip" role="status"></div>
    <div class="chart-legend"><span><i class="legend-line"></i> {{ $historical ? 'Historical reconstruction' : 'Latest observations' }}</span><span><i class="legend-line dashed"></i> {{ request('reference') === 'annual' ? 'Annual reference' : 'Seasonal reference' }}</span><span><i class="legend-band"></i> Reference range</span></div>
    @endif
</div>
