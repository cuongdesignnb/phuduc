<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        @if(filled($rootSite['favicon_url'] ?? null))
            <link rel="icon" href="{{ $rootSite['favicon_url'] }}">
            <link rel="shortcut icon" href="{{ $rootSite['favicon_url'] }}">
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        @php
            $headingFont = $rootSite['fonts']['heading'] ?? 'Rajdhani';
            $bodyFont = $rootSite['fonts']['body'] ?? 'Inter';
            $fonts = collect([$headingFont, $bodyFont])->unique()->map(fn($f) => str_replace(' ', '+', $f) . ':wght@300;400;500;600;700;800;900')->implode('&family=');
            $primaryColor = $rootSite['primary_color'] ?? null;
        @endphp
        <link href="https://fonts.googleapis.com/css2?family={{ $fonts }}&display=swap" rel="stylesheet" />
        <style>:root { --font-sans: '{{ $bodyFont }}', sans-serif; --font-display: '{{ $headingFont }}', sans-serif; }</style>

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead

        {{-- Primary color: AFTER @vite so it overrides app.css defaults --}}
        @php
            $pc = ($primaryColor && preg_match('/^#[0-9A-Fa-f]{6}$/', $primaryColor)) ? $primaryColor : '#ffd400';
            $r = hexdec(substr($pc, 1, 2)); $g = hexdec(substr($pc, 3, 2)); $b = hexdec(substr($pc, 5, 2));
            $rf = $r/255; $gf = $g/255; $bf = $b/255;
            $mx = max($rf, $gf, $bf); $mn = min($rf, $gf, $bf);
            $ll = ($mx + $mn) / 2;
            if ($mx == $mn) { $hh = 0; $ss = 0; }
            else {
                $dd = $mx - $mn;
                $ss = $ll > 0.5 ? $dd / (2 - $mx - $mn) : $dd / ($mx + $mn);
                if ($mx == $rf) $hh = (($gf - $bf) / $dd + ($gf < $bf ? 6 : 0)) / 6;
                elseif ($mx == $gf) $hh = (($bf - $rf) / $dd + 2) / 6;
                else $hh = (($rf - $gf) / $dd + 4) / 6;
            }
            $hDeg = $hh * 360; $sPct = $ss * 100;
            $hsl2rgb = function($h, $s, $l) {
                $h /= 360; $s /= 100; $l /= 100;
                if ($s == 0) { $rv = $gv = $bv = $l; }
                else {
                    $h2r = function($p, $q, $t) {
                        if ($t < 0) $t++; if ($t > 1) $t--;
                        if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
                        if ($t < 1/2) return $q;
                        if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
                        return $p;
                    };
                    $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
                    $p = 2 * $l - $q;
                    $rv = $h2r($p, $q, $h + 1/3); $gv = $h2r($p, $q, $h); $bv = $h2r($p, $q, $h - 1/3);
                }
                return round($rv*255).' '.round($gv*255).' '.round($bv*255);
            };
            $voltShades = [
                50=>[min($sPct*0.35,100),96],100=>[min($sPct*0.45,100),91],200=>[min($sPct*0.55,100),83],
                300=>[min($sPct*0.65,100),72],400=>[min($sPct*0.85,100),58],500=>[$sPct,45],
                600=>[min($sPct*1.05,100),37],700=>[min($sPct*0.95,100),30],800=>[min($sPct*0.85,100),24],
                900=>[min($sPct*0.75,100),20],950=>[min($sPct*0.7,100),10],
            ];
        @endphp
        <style>:root { @foreach($voltShades as $shade => [$sv, $lv]) --volt-{{ $shade }}: {{ $hsl2rgb($hDeg, $sv, $lv) }}; @endforeach }</style>
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
