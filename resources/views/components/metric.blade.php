@props(['metric', 'available' => true, 'href'])
@php($available = $available && ($metric['available'] ?? true))
<a href="{{ $href }}" class="metric-card panel group">
    <div class="metric-top"><span class="metric-icon {{ $metric['color'] }}"><x-icon :name="$metric['icon']" /></span><span class="metric-label">{{ $metric['label'] }}</span><x-icon name="chevron" size="15" class="metric-go" /></div>
    <div class="metric-value">{{ $available ? $metric['value'] : '—' }} <span>{{ $metric['unit'] }}</span></div>
    <p class="metric-descriptor">{{ $metric['descriptor'] }}@if($metric['label'] === 'Water quality') · estimate @endif</p>
    <div class="metric-change"><span class="reference-change">{{ $available ? ($metric['delta'] > 0 ? '+' : '').$metric['delta'].'%' : 'No observations' }}</span><span>{{ $available ? 'vs. selected reference' : 'in selected period' }}</span>
    </div>
    <div class="metric-bottom"><span><span class="tiny-dot {{ !$available ? 'neutral' : ($metric['quality'] === 'High confidence' ? 'green' : 'amber') }}"></span>{{ $available ? $metric['quality'] : 'No data available' }}</span><span>{{ $available ? $metric['acquired'] : 'Selected range' }}</span></div>
</a>
