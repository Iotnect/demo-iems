@props(['tone' => 'neutral', 'dot' => false])
<span {{ $attributes->merge(['class' => 'badge badge-'.$tone]) }}>@if($dot)<span class="status-dot"></span>@endif{{ $slot }}</span>
