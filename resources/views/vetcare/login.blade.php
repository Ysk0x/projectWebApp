<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>VetCare - Login</title>

    <link rel="stylesheet"
          href="{{ asset('bootstrap/css/bootstrap.min.css') }}">
    <script src="{{ asset('bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <link rel="stylesheet"
          href="{{ asset('css/vetcare/login.css') }}">
</head>

<body>

<div class="container-fluid login-page">
    <div class="row min-vh-100">

        
        <section class="col-lg-6 login-left">

    <header class="left-header">

        <div class="brand-icon">
            🐾
        </div>

        <div class="brand-info">

            <h1>VETCARE</h1>

            <p>
                Animal Clinic Management System
            </p>

        </div>

    </header>

    <div class="left-content">

        <h2>
            ระบบจัดการคลินิกสัตว์ครบวงจร
        </h2>

        <p>
            ระบบสำหรับบริหารจัดการภายในคลินิก
            ช่วยให้การดูแลสัตว์ การนัดหมาย การรักษา
            และการจัดการข้อมูล เป็นเรื่องง่ายและเป็นระบบ
        </p>

    </div>

    <footer class="left-footer">

        ระบบจัดการสำหรับใช้งานภายในคลินิกเท่านั้น

    </footer>

</section>


      
        <section class="col-lg-6 login-right">

            <div class="login-card">

                
                <div class="login-header">
                    <p class="welcome-text">ยินดีต้อนรับ</p>

                    <h2>เข้าสู่ระบบ</h2>

                    <p class="login-description">
                        เลือกประเภทผู้ใช้งานและกรอกข้อมูลเพื่อเข้าสู่ระบบ
                    </p>
                </div>


                <div class="role-section">

                    <label class="form-label fw-bold">
                        ประเภทผู้ใช้งาน
                    </label>

                    <div class="row g-3">

                        <div class="col-md-6">

                            <input
                                type="radio"
                                class="btn-check"
                                name="role"
                                id="staff"
                                value="staff"
                                checked>

                            <label class="role-card w-100" for="staff">

                                <span class="role-icon">
                                    👤
                                </span>

                                <span>
                                    <strong>พนักงานคลินิก</strong>
                
                                </span>

                            </label>

                        </div>


                       
                        <div class="col-md-6">

                            <input
                                type="radio"
                                class="btn-check"
                                name="role"
                                id="manager"
                                value="manager">

                            <label class="role-card w-100" for="manager">

                                <span class="role-icon">
                                    🛡️
                                </span>

                                <span>
                                    <strong>ผู้จัดการระบบ</strong>
                                    
                                </span>

                            </label>

                        </div>

                    </div>

                </div>

                <form method="POST" action="{{ route('my.login.submit') }}" class="flex flex-col gap-6">
                    @csrf


                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">
                            อีเมล
                        </label>
                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            placeholder="กรอก Email">
                    </div>

                    <div class="mb-3">

                        <label for="password"
                               class="form-label fw-bold">
                            รหัสผ่าน
                        </label>

                        <input
                            type="password"
                            class="form-control"
                            id="password"
                            name="password"
                            placeholder="กรอกรหัสผ่าน">

                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">

                        <div class="form-check">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="remember">

                            <label
                                class="form-check-label"
                                for="remember">

                                จำการเข้าสู่ระบบ

                            </label>

                        </div>

                        <a href="#" class="forgot-link">
                            ลืมรหัสผ่าน?
                        </a>

                    </div>


                    <button
                        type="submit"
                        class="btn btn-primary w-100 login-button">

                        เข้าสู่ระบบ

                    </button>

                </form>


                <div class="login-footer">
                    VetCare © 2026 — ระบบจัดการคลินิกสัตว์
                </div>

            </div>

        </section>

    </div>
</div>

</body>

</html>