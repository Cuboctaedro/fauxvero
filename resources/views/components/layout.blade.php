<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width">
  <meta name="text-scale" content="scale">
  <meta name="csrf-token" content="{{ csrf_token() }}">

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

<body class="font-mono antialiased bg-gray-200 dark:bg-gray-900 text-black dark:text-gray-100">
  <a href="#main" class="absolute left-0 right-0 -top-12 z-50 h-12 w-full flex items-center justify-start px-4 bg-black text-white focus:top-0 transition-all text-lg font-bold">
    Skip to main content
  </a>
  <div >
    <x-navigation />
    <main id="main" class="min-h-screen p-3 md:p-6 container mx-auto max-w-screen-xl ">
        {{ $slot }}
    </main>
  </div>
</body>
</html>
