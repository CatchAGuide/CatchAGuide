{{-- Stylesheets that style nothing in the first paint (popups, editors, form widgets, JS-initialised
     components) load without blocking render; <noscript> keeps them for no-JS visitors.
     $sheets: list<array{href: string, integrity?: string}> — keep them at their original position in
     the <head> so the cascade order against the theme/app CSS doesn't change. --}}
@foreach($sheets as $sheet)
<link rel="stylesheet" href="{{ $sheet['href'] }}" media="print" onload="this.media='all'"@isset($sheet['integrity']) integrity="{{ $sheet['integrity'] }}" crossorigin="anonymous"@endisset>
@endforeach
<noscript>
@foreach($sheets as $sheet)
<link rel="stylesheet" href="{{ $sheet['href'] }}"@isset($sheet['integrity']) integrity="{{ $sheet['integrity'] }}" crossorigin="anonymous"@endisset>
@endforeach
</noscript>
