<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Vehicles In Veranda')</title>
    <meta name="description"content="@yield('description', 'Vehicles In Veranda — quality vehicles with complete documentation and trusted service.')">
    <link rel="stylesheet" href="{{ asset('showroom/css/showroom.css') }}">

    @stack('styles')
</head>

<body>

    @include('showroom.partials.header')

    <main>
        @yield('content')
    </main>

    @include('showroom.partials.footer')

    <script src="{{ asset('showroom/js/layout.js') }}"></script>

    @stack('scripts')

</body>
</html>
