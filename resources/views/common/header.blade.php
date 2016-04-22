<!-- Content Header (Page header) -->
@if (View::hasSection('content-title'))
    <section class="content-header">
        <h1>
            @yield('content-title')
            <small>@yield('content-subtitle')</small>
        </h1>
    </section>
@endif
