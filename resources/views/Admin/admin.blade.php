@include('partials.header')

<body class="sb-nav-fixed">
    @include('partials.nav')

    <div id="layoutSidenav">
        @include('partials.sidebar')
        <div id="layoutSidenav_content">
            @include('partials.content')
            @include('partials.footer')
        </div>
    </div>

    @include('partials.scripts')
</body>
</html>
