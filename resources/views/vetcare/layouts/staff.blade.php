<!DOCTYPE html>

<html lang="th">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'VetCare')
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


    {{-- VetCare Global CSS --}}

    <link
        rel="stylesheet"
        href="{{ asset('css/vetcare/common.css') }}?v=10"
    >

    <link
        rel="stylesheet"
        href="{{ asset('css/vetcare/layout.css') }}?v=10"
    >


    {{-- CSS เฉพาะแต่ละหน้า --}}

    @stack('styles')

</head>


<body>


    <div class="app-layout">


        {{-- =========================
             STAFF SIDEBAR
        ========================== --}}

        @include('vetcare.partials.staff-sidebar')



        {{-- =========================
             MAIN CONTENT
        ========================== --}}

        <div class="main-area">


            {{--
                ไม่เรียก navbar.blade.php แล้ว

                เพราะแต่ละหน้าของ VetCare
                มี Header ของตัวเองอยู่แล้ว
            --}}


            <main class="page-content

                {{ request()->routeIs('vetcare.staff.dashboard')
                    ? 'dashboard-main'
                    : ''
                }}

                {{ request()->routeIs('vetcare.staff.owners')
                    ? 'owners-main'
                    : ''
                }}

                {{ request()->routeIs('vetcare.staff.appointments')
                    ? 'appointments-main'
                    : ''
                }}

                {{ request()->routeIs('vetcare.staff.treatments')
                    ? 'treatments-main'
                    : ''
                }}

                {{ request()->routeIs('vetcare.staff.billing')
                    ? 'billing-main'
                    : ''
                }}

                {{ request()->routeIs('vetcare.staff.inventory')
                    ? 'inventory-main'
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