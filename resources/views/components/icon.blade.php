@props(['name', 'size' => 20])
<svg {{ $attributes->merge(['class' => 'icon']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($name)
    @case('overview') <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/> @break
    @case('map') <path d="m3 6 6-3 6 3 6-3v15l-6 3-6-3-6 3Z"/><path d="M9 3v15M15 6v15"/> @break
    @case('activity') <path d="M3 12h4l3-8 4 16 3-8h4"/> @break
    @case('history') <path d="M3 10a9 9 0 1 1 2 8M3 4v6h6M12 7v5l3 2"/> @break
    @case('alert') <path d="m10.3 4-8 14a2 2 0 0 0 1.7 3h16a2 2 0 0 0 1.7-3l-8-14a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4M12 17h.01"/> @break
    @case('file') <path d="M14 2H5v20h14V7Z"/><path d="M14 2v6h5M8 12h8M8 16h6"/> @break
    @case('chart') <path d="M3 3v18h18M7 16l4-5 4 2 6-8"/> @break
    @case('settings') <path d="m9 3-.8 3-2.7 1L3 6l-2 3 2.3 2v3L1 16l2 3 2.5-1 2.7 1 .8 3h4l.8-3 2.7-1 2.5 1 2-3-2.3-2v-3L21 9l-2-3-2.5 1-2.7-1-.8-3Z" transform="translate(1 -1) scale(.95)"/><circle cx="12" cy="12" r="3"/> @break
    @case('methane') <circle cx="12" cy="12" r="3"/><circle cx="5" cy="5" r="2"/><circle cx="19" cy="5" r="2"/><circle cx="5" cy="19" r="2"/><circle cx="19" cy="19" r="2"/><path d="m7 7 3 3m4 4 3 3m-7-3-3 3M17 7l-3 3"/> @break
    @case('wind') <path d="M3 8h12a3 3 0 1 0-3-3M2 12h17a3 3 0 1 1-3 3M4 16h5a3 3 0 1 1-3 3"/> @break
    @case('droplet') <path d="M12 2S4 10 4 15a8 8 0 0 0 16 0c0-5-8-13-8-13Z"/><path d="M8 15a4 4 0 0 0 4 4"/> @break
    @case('bell') <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/> @break
    @case('chevron') <path d="m9 5 7 7-7 7"/> @break
    @case('down') <path d="m6 9 6 6 6-6"/> @break
    @case('calendar') <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18M8 15h2M14 15h2"/> @break
    @case('filter') <path d="M4 6h16M7 12h10M10 18h4"/> @break
    @case('download') <path d="M12 3v12m-5-5 5 5 5-5M4 16v5h16v-5"/> @break
    @case('arrow') <path d="M5 12h14m-5-5 5 5-5 5"/> @break
    @case('clock') <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/> @break
    @case('check') <path d="m5 12 4 4L19 6"/> @break
    @case('check-circle') <circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/> @break
    @case('info') <circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7h.01"/> @break
    @case('pin') <path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/> @break
    @case('layers') <path d="m12 3 10 6-10 6L2 9Zm-9 11 9 5 9-5M3 19l9 4 9-4"/> @break
    @case('expand') <path d="M8 3H3v5m13-5h5v5M3 16v5h5m13-5v5h-5"/> @break
    @case('plus') <path d="M12 5v14M5 12h14"/> @break
    @case('minus') <path d="M5 12h14"/> @break
    @case('close') <path d="m6 6 12 12M6 18 18 6"/> @break
    @case('satellite') <path d="m5 3 5 5-5 5-5-5Zm14 8 5 5-5 5-5-5ZM8 10l6 6m-2-9 5 5m-4-6 5 5m-9 7-5 5M3 15a6 6 0 0 1 6 6"/> @break
    @case('search') <circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/> @break
    @case('help') <circle cx="12" cy="12" r="9"/><path d="M9 9a3 3 0 1 1 4 3c-1 .5-1 1-1 2M12 17h.01"/> @break
    @case('leaf') <path d="M19 3C5 2 2 10 8 16s14 2 11-13Z"/><path d="m4 21 11-12M8 16v-6m0 6h6"/> @break
    @case('users') <circle cx="9" cy="8" r="3"/><path d="M2 21v-3a7 7 0 0 1 14 0v3M17 5a3 3 0 0 1 0 6m2 4a5 5 0 0 1 3 5"/> @break
    @case('shield') <path d="m12 2 9 4v6c0 6-9 10-9 10S3 18 3 12V6Z"/><path d="m8 12 3 3 5-6"/> @break
    @case('menu') <path d="M3 6h18M3 12h18M3 18h18"/> @break
    @case('spark') <path d="m12 2 3 7 7 3-7 3-3 7-3-7-7-3 7-3Z"/> @break
    @case('print') <path d="M6 9V2h12v7M6 17H3V9h18v8h-3M6 14h12v8H6ZM17 12h1"/> @break
    @case('lock') <rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/> @break
    @case('user') <circle cx="12" cy="7" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/> @break
    @case('eye') <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/> @break
    @case('eye-off') <path d="m3 3 18 18M10.6 5.1A12 12 0 0 1 12 5c6.5 0 10 7 10 7a20 20 0 0 1-3.1 4M6.5 6.5A21 21 0 0 0 2 12s3.5 7 10 7a12 12 0 0 0 5.5-1.5M10 10a3 3 0 0 0 4 4"/> @break
    @case('logout') <path d="M9 3H4v18h5M9 12h12m-5-5 5 5-5 5"/> @break
    @default <circle cx="12" cy="12" r="8"/>
@endswitch
</svg>
