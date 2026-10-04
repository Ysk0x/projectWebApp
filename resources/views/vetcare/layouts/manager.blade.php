<!DOCTYPE html>

<html lang="th">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'VetCare Manager')
    </title>


    {{-- Noto Sans Thai --}}

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >


    {{-- Bootstrap --}}

    <link
        rel="stylesheet"
        href="{{ asset('bootstrap/css/bootstrap.min.css') }}"
    >


    {{-- VetCare CSS --}}

    <link
        rel="stylesheet"
        href="{{ asset('css/vetcare/common.css') }}?v=10"
    >

    <link
        rel="stylesheet"
        href="{{ asset('css/vetcare/layout.css') }}?v=10"
    >


    @stack('styles')

</head>


<body>


    <div class="app-layout">


        {{-- =========================
             MANAGER SIDEBAR
        ========================== --}}

        @include('vetcare.partials.manager-sidebar')



        {{-- =========================
             MAIN
        ========================== --}}

        <div class="main-area">


            {{--
                Manager ทุกหน้ามี Header
                ของตัวเองอยู่แล้ว

                จึงไม่เรียก navbar.blade.php
            --}}


            <main class="page-content

                {{ request()->routeIs('vetcare.manager.dashboard')
                    ? 'manager-dashboard-main'
                    : ''
                }}

                {{ request()->routeIs('vetcare.manager.users')
                    ? 'manager-users-main'
                    : ''
                }}

                {{ request()->routeIs('vetcare.manager.medicines')
                    ? 'manager-medicines-main'
                    : ''
                }}

                {{ request()->routeIs('vetcare.manager.invoices')
                    ? 'manager-invoices-main'
                    : ''
                }}

            ">

                @yield('content')

            </main>


        </div>


    </div>


    <script
        src="{{ asset('bootstrap/js/bootstrap.bundle.min.js') }}">
    </script>


</body>

</html>