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

    <div class="sidebar-role">

        พนักงานคลินิก

    </div>


    <ul class="sidebar-menu-new">


        <li>

            <a
                href="{{ route('vetcare.staff.dashboard') }}"
                class="{{ request()->routeIs('vetcare.staff.dashboard')
                    ? 'active'
                    : ''
                }}"


                <span>
                    ภาพรวมคลินิก
                </span>

            </a>

        </li>



        <li>

            <a
                href="{{ route('vetcare.staff.owners') }}"
                class="{{ request()->routeIs('vetcare.staff.owners')
                    ? 'active'
                    : ''
                }}"
            >

                <span>
                    เจ้าของและสัตว์เลี้ยง
                </span>

            </a>

        </li>



        <li>

            <a
                href="{{ route('vetcare.staff.appointments') }}"
                class="{{ request()->routeIs('vetcare.staff.appointments')
                    ? 'active'
                    : ''
                }}"
            >

            
                <span>
                    ตารางนัดหมาย
                </span>

            </a>

        </li>



        <li>

            <a
                href="{{ route('vetcare.staff.treatments') }}"
                class="{{ request()->routeIs('vetcare.staff.treatments')
                    ? 'active'
                    : ''
                }}"
            >

                

                <span>
                    การรักษาและจ่ายยา
                </span>

            </a>

        </li>



        <li>

            <a
                href="{{ route('vetcare.staff.billing') }}"
                class="{{ request()->routeIs('vetcare.staff.billing')
                    ? 'active'
                    : ''
                }}"
            >

                <span>
                    ชำระเงิน
                </span>

            </a>

        </li>



        <li>

            <a
                href="{{ route('vetcare.staff.inventory') }}"
                class="{{ request()->routeIs('vetcare.staff.inventory')
                    ? 'active'
                    : ''
                }}"
            >



                <span>
                    คลังยา
                </span>

            </a>

        </li>


    </ul>



    <div class="sidebar-bottom">


        <div class="sidebar-user">


            <div class="sidebar-avatar">

                ว

            </div>


            <div class="sidebar-user-info">

                <strong>
                    วิภา รักดี
                </strong>

                <span>
                    Staff
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