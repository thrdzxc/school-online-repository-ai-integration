@props(['name','size'=>20])
@php
$paths = [
'home' => '<path d="M3 10.8 12 3l9 7.8"/><path d="M5 9.8V21h14V9.8"/><path d="M9 21v-7h6v7"/>',
'rooms' => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M8 4v16M16 4v16M3 10h18"/>',
'activity' => '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>',
'plus' => '<path d="M12 5v14M5 12h14"/>',
'star' => '<path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.3L5.8 21 7 14.2l-5-4.9 6.9-1z"/>',
'chat' => '<path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/><path d="M8 9h8M8 13h5"/>',
'report' => '<path d="M4 19.5V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14.5"/><path d="M8 8h8M8 12h8M8 16h5M2 21h20"/>',
'repository' => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 3v18M8 8h8M8 12h8M8 16h5"/>',
'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
'subjects' => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><path d="M9 7h7M9 11h7"/>',
'submission' => '<path d="M12 3v12"/><path d="m7 8 5-5 5 5"/><path d="M5 13v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6"/>',
'monitor' => '<rect x="3" y="4" width="18" height="13" rx="2"/><path d="M8 21h8M12 17v4"/><path d="m7 12 2-2 2 2 4-4 2 2"/>',
'settings' => '<path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1v.09H9.6V21a1.7 1.7 0 0 0-1.1-1.6 1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.2 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1-.4h-.09V9.6h.09A1.7 1.7 0 0 0 4.2 8.5a1.7 1.7 0 0 0-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 0 0 8.5 4.2a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1V2.5h4v.09A1.7 1.7 0 0 0 15 4.2a1.7 1.7 0 0 0 1.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0 0 19.4 8.5c.14.35.35.67.6 1 .27.27.63.4 1 .4h.09v4H21c-.38 0-.73.14-1 .4-.26.26-.47.58-.6.7z"/>',
'audit' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h6"/>',
'profile' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
'bell' => '<path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/>',
'logout' => '<path d="M10 17l5-5-5-5"/><path d="M15 12H3"/><path d="M21 19V5a2 2 0 0 0-2-2h-6"/>',
'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
'collapse' => '<path d="m15 18-6-6 6-6"/>',
'expand' => '<path d="m9 18 6-6-6-6"/>',
'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
'lock' => '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
'eye' => '<path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
'eyeoff' => '<path d="m3 3 18 18"/><path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 4.2A10.8 10.8 0 0 1 12 4c6.5 0 10 8 10 8a18 18 0 0 1-3.1 4.2M6.7 6.7C3.7 8.5 2 12 2 12s3.5 8 10 8c1.5 0 2.9-.4 4.1-1"/>',
'camera' => '<path d="M14.5 4 16 7h3a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h3l1.5-3z"/><circle cx="12" cy="13" r="4"/>',
'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
'chevron' => '<path d="m9 18 6-6-6-6"/>',
];
$svg = $paths[$name] ?? $paths['home'];
@endphp
<svg {{ $attributes->merge(['class'=>'ui-icon','width'=>$size,'height'=>$size,'viewBox'=>'0 0 24 24','fill'=>'none','stroke'=>'currentColor','stroke-width'=>'1.9','stroke-linecap'=>'round','stroke-linejoin'=>'round','aria-hidden'=>'true']) }}>{!! $svg !!}</svg>
