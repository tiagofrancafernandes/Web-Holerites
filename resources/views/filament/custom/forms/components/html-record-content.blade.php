@props([
    'content' => null,
])

@php
$content = $this->evaluateContent($content);
@endphp

@if ($content ?? null)
    {{ $content }}
@endif
