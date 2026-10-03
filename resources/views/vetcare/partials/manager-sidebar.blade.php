<aside class="sidebar">


    {{-- ===============================
         BRAND
    ================================ --}}

    <div class="sidebar-brand-new">

        <div class="sidebar-logo">
            🐾
        </div>

        <div class="sidebar-brand-text">

            <h2>
                VETCARE
            </h2>

            <p>
                Animal Clinic Management
            </p>

        </div>

    </div>



    {{-- ===============================
         ROLE
    ================================ --}}

    <div class="sidebar-role manager-role">

        ผู้จัดการคลินิก

    </div>



    {{-- ===============================
         MENU
    ================================ --}}

    <ul class="sidebar-menu-new">


        <li>

            <a
                href="{{ route('vetcare.manager.dashboard') }}"
                class="{{ request()->routeIs('vetcare.manager.dashboard')
                    ? 'active'
                    : ''
                }}"
            >

                

                <span>
                    แดชบอร์ด
                </span>

            </a>

        </li>



        <li>

            <a
                href="{{ route('vetcare.manager.users') }}"
                class="{{ request()->routeIs('vetcare.manager.users')
                    ? 'active'
                    : ''
                }}"
            >

               

                <span>
                    จัดการบุคลากร
                </span>

            </a>

        </li>



        <li>

            <a
                href="{{ route('vetcare.manager.medicines') }}"
                class="{{ request()->routeIs('vetcare.manager.medicines')
                    ? 'active'
                    : ''
                }}"
            >

                <span>
                    คลังยา
                </span>

            </a>

        </li>



        <li>

            <a
                href="{{ route('vetcare.manager.invoices') }}"
                class="{{ request()->routeIs('vetcare.manager.invoices')
                    ? 'active'
                    : ''
                }}"
            >

                <span>
                    ตรวจสอบใบเสร็จ
                </span>

            </a>

        </li>


    </ul>



    {{-- ===============================
         ACCOUNT
    ================================ --}}

    <div class="sidebar-bottom">


        <div class="sidebar-user">


            <div class="sidebar-avatar">

                ส

            </div>


            <div class="sidebar-user-info">

                <strong>
                    สมชาย มีสุข
                </strong>

                <span>
                    Manager / Admin
                </span>

            </div>


        </div>



        {{-- ===============================
             LOGOUT
        ================================ --}}

        <a
            href="{{ route('vetcare.login') }}"
            class="sidebar-logout"
        >

            <span>
                ↪
            </span>

            ออกจากระบบ

        </a>


    </div>


</aside>