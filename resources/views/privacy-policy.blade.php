<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Privacy Policy - Centrooh</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-gradient-to-br from-[#FDFDFC] to-[#fff2f2] dark:from-[#0a0a0a] dark:to-[#1D0002] text-[#1b1b18] flex flex-col min-h-screen font-sans">
    <section class="flex-1 flex flex-col items-center justify-center text-center px-6 py-24 md:py-32">
        <h1 class="text-4xl font-extrabold mb-6 text-[#1b1b18] dark:text-[#EDEDEC]">Privacy Policy</h1>
        <p class="text-xl text-[#706f6c] dark:text-[#A1A09A]">Work In Progress</p>
    </section>
</body>
</html>
