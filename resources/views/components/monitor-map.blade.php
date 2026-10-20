@props(['aoi', 'aois', 'aoiKey', 'available' => true, 'compact' => false, 'historical' => false, 'parameter' => 'all', 'openAnomalies' => 2, 'anomalies' => [], 'selectedId' => null, 'context' => [], 'availableParameters' => ['methane', 'air', 'water']])
<div class="monitor-map {{ $compact ? 'map-compact' : '' }}" data-map data-focus-x="{{ $aoi['x'] }}" data-focus-y="{{ $aoi['y'] }}">
    <svg class="map-canvas" viewBox="0 0 1024 512" preserveAspectRatio="xMidYMid slice" role="group" aria-label="Illustrative monitoring boundaries in southern Johor, Malaysia. Geographic basemap with demonstration observation markers.">
        <defs><radialGradient id="plume-{{ $historical ? 'historic' : 'now' }}"><stop offset="0" stop-color="#e5a62d" stop-opacity=".38"/><stop offset="1" stop-color="#e5a62d" stop-opacity="0"/></radialGradient></defs>
        <g class="map-zoom-group">
            @for($x = 806; $x < 810; $x++) @for($y = 507; $y < 509; $y++)
            <image href="{{ asset('images/map/'.$x.'-'.$y.'.png') }}" x="{{ ($x - 806) * 256 }}" y="{{ ($y - 507) * 256 }}" width="256" height="256"/>
            @endfor @endfor
            <g data-map-layer="boundaries">
                @foreach($aois as $key => $area)
                <path d="M{{ $area['x'] - 58 }},{{ $area['y'] - 55 }} l72,-14 51,43 -16,62 -77,15 -45,-50 Z" fill="{{ $key === $aoiKey ? '#009786' : '#88a1a0' }}" fill-opacity="{{ $key === $aoiKey ? '.10' : '.04' }}" stroke="{{ $key === $aoiKey ? '#008a79' : '#6c9390' }}" stroke-width="{{ $key === $aoiKey ? '2.4' : '1.4' }}" stroke-dasharray="{{ $key === $aoiKey ? '6 3' : '4 5' }}"/>
                <text x="{{ $area['x'] }}" y="{{ $area['y'] - 75 }}" text-anchor="middle" fill="#315b55" font-size="11" font-weight="600" paint-order="stroke" stroke="#fff" stroke-width="4">{{ $area['short'] }} AOI</text>
                @endforeach
            </g>
            @if($available)
            <g data-map-layer="methane" @if(!in_array($parameter, ['all', 'methane']) || (!$historical && !in_array('methane', $availableParameters))) style="display:none" @endif>
                <ellipse cx="{{ $aoi['x'] + 21 }}" cy="{{ $aoi['y'] - 19 }}" rx="84" ry="49" transform="rotate(-28 {{ $aoi['x'] + 21 }} {{ $aoi['y'] - 19 }})" fill="url(#plume-{{ $historical ? 'historic' : 'now' }})"/>
                @foreach([[-40, -18], [-8, 2], [31, -30], [18, 28], [50, 9]] as $point)
                <circle cx="{{ $aoi['x'] + $point[0] }}" cy="{{ $aoi['y'] + $point[1] }}" r="{{ $historical ? 6 : 8 }}" fill="#fff" fill-opacity=".85"/><circle cx="{{ $aoi['x'] + $point[0] }}" cy="{{ $aoi['y'] + $point[1] }}" r="4" fill="#009b83"/>
                @endforeach
            </g>
            <g data-map-layer="air" @if(!in_array($parameter, ['all', 'air']) || (!$historical && !in_array('air', $availableParameters))) style="display:none" @endif>
                @foreach([[-15, -34], [42, 38], [64, -18]] as $point)<rect x="{{ $aoi['x'] + $point[0] }}" y="{{ $aoi['y'] + $point[1] }}" width="24" height="24" fill="#7aaac6" fill-opacity=".17" stroke="#6b9db8" stroke-opacity=".3"/><circle cx="{{ $aoi['x'] + $point[0] + 12 }}" cy="{{ $aoi['y'] + $point[1] + 12 }}" r="4" fill="#518cbb" stroke="#fff" stroke-width="2"/>@endforeach
            </g>
            <g data-map-layer="water" @if(!in_array($parameter, ['all', 'water']) || (!$historical && !in_array('water', $availableParameters))) style="display:none" @endif>
                @foreach([[20, 72], [-8, 87], [54, 80]] as $point)<circle cx="{{ $aoi['x'] + $point[0] }}" cy="{{ $aoi['y'] + $point[1] }}" r="5" fill="#3db5c5" stroke="#fff" stroke-width="2"/>@endforeach
            </g>
            @unless($historical)
            @foreach($anomalies as $item)
            @php($position = match($item['id']) {'ANM-026' => [38, -28], 'ANM-025' => [64, 38], 'ANM-024' => [20, 80], default => [-40, -18]})
            <a href="{{ route('iems.screen', ['screen' => 'alerts', 'anomaly' => $item['id'], 'aoi' => $aoiKey, 'period' => request('period', 'month'), 'parameter' => $parameter, 'reference' => request('reference', 'seasonal')] + $context) }}"
               @if(!in_array($parameter, ['all', $item['parameter']])) style="display:none" @endif class="map-anomaly-marker {{ $selectedId === $item['id'] ? 'is-selected' : '' }}" data-map-marker data-select-anomaly="{{ $item['id'] }}" data-anomaly-parameter="{{ $item['parameter'] }}" data-anomaly-title="{{ $item['title'] }}" data-anomaly-time="{{ $item['time'] }}" data-anomaly-value="{{ $item['value'] }}" data-anomaly-deviation="{{ $item['deviation'] }}" data-anomaly-confidence="{{ $item['confidence'] }}" data-anomaly-severity="{{ $item['severity'] }}" data-map-layer="{{ $item['parameter'] }}" aria-label="Review {{ $item['id'] }}: {{ $item['title'] }} ({{ $item['status'] }})" transform="translate({{ $aoi['x'] + $position[0] }} {{ $aoi['y'] + $position[1] }})">
                <title>{{ $item['id'] }}: {{ $item['title'] }} - {{ $item['status'] }}</title><circle class="marker-halo" r="21" fill="none" stroke="#164f59" stroke-width="3"/>
                <circle r="13" fill="#fff"/><circle r="10" fill="{{ in_array($item['status'], ['Reviewed', 'Closed']) ? '#637c85' : match($item['severity']) {'High' => '#b94635', 'Medium' => '#a96b09', default => '#3b6a96'} }}"/>
                <text y="4" fill="#fff" text-anchor="middle" font-size="13" font-weight="700">!</text>
            </a>
            @endforeach
            @endunless
            @endif
        </g>
    </svg>
    <div class="map-location"><span class="tiny-dot green"></span> {{ $aoi['region'] }} <span class="map-label-tag">{{ $historical ? 'Historical reference' : 'Illustrative AOI' }}</span></div>
    <div class="map-tools"><button type="button" data-map-zoom="in" title="Zoom in" aria-label="Zoom in"><x-icon name="plus" size="18"/></button><button type="button" data-map-zoom="out" title="Zoom out" aria-label="Zoom out"><x-icon name="minus" size="18"/></button><button type="button" data-map-reset title="Reset map" aria-label="Reset map"><x-icon name="expand" size="17"/></button></div>
    @unless($compact)
    <div class="map-layer-control"><button type="button" class="layers-button" data-toggle-layers aria-expanded="false"><x-icon name="layers" size="17"/> Layers <x-icon name="down" size="13"/></button><div class="layer-options" hidden>@foreach(['methane' => 'Methane', 'air' => 'Air quality', 'water' => 'Water quality', 'boundaries' => 'AOI boundaries'] as $key => $label)<label><input type="checkbox" data-layer-toggle="{{ $key }}" @disabled($key !== 'boundaries' && !$historical && !in_array($key, $availableParameters)) @checked($key === 'boundaries' || (in_array($parameter, ['all', $key]) && ($historical || in_array($key, $availableParameters))))>{{ $label }}</label>@endforeach</div></div>
    <div class="map-selection" data-map-selection aria-live="polite"><div class="flex items-center justify-between gap-3"><span class="eyebrow">SELECTED AREA OF INTEREST</span><span class="tiny-dot green"></span></div><strong>{{ $aoi['short'] }} AOI</strong><p>Demonstration boundary · {{ $aoi['area'] }} km²</p><div class="map-selection-stats"><span><b>{{ $available ? $aoi['coverage'].'%' : '—' }}</b> data coverage</span><span><b>{{ $available ? $openAnomalies : '—' }}</b> open anomalies</span></div><a href="{{ route('iems.screen', ['screen' => 'areas-of-interest', 'aoi' => $aoiKey, 'period' => request('period', 'month'), 'parameter' => $parameter, 'reference' => request('reference', 'seasonal')] + $context) }}">View AOI details <x-icon name="arrow" size="15"/></a></div>
    @endunless
    <div class="map-scale"><span></span><b data-map-scale>10 km</b></div>
    <div class="map-attribution">© OpenStreetMap contributors · Demo overlays</div>
</div>
