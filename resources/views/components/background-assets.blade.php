@php
    $backgroundFile = 'images/system-background-selected.png';
    $backgroundUrl = asset($backgroundFile).'?v='.filemtime(public_path($backgroundFile));
@endphp
<link rel="preload" as="image" type="image/png" href="{{ $backgroundUrl }}" fetchpriority="high">
<style>
    :root { --campus-background: url("{{ $backgroundUrl }}"); }
</style>
