@props([
    'theme' => 'light',
])
<div {{ $attributes->class(['cag-title-rule', 'cag-title-rule--'.$theme]) }} aria-hidden="true"></div>
