<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width">
  <meta name="text-scale" content="scale">

  <title>{{ $metaTitle }}</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <meta name="description" content="{{ $metaDescription }}">
  <meta property="og:image" content="{{ $metaImage }}">
  <meta property="og:url" content="{{ config('app.url') . $pagePath }}">

  @if($metaRobots)
    <meta name="robots" content="index, follow">
  @else
    <meta name="robots" content="noindex, nofollow">
  @endif
  
</head>

<body>
    <x-navigation />
        {{ $slot }}
</body>
</html>
