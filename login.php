<?php

require 'config/database.php';
session_start();

function e($text)
{
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}


/* ==========================================
   JIKA SUDAH LOGIN
========================================== */

if (isset($_SESSION['user'])) {

    if ($_SESSION['user']['role'] === 'admin') {

        header('Location: admin/dashboard.php');

    } else {

        header('Location: siswa/dashboard.php');

    }

    exit;
}


$error = '';


/* ==========================================
   PROSES LOGIN
========================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');


    if ($username === '' || $password === '') {

        $error = 'Username dan password wajib diisi.';

    } else {

        $stmt = $pdo->prepare("
            SELECT *
            FROM users
            WHERE username = ?
            AND status = 'aktif'
            LIMIT 1
        ");

        $stmt->execute([$username]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);


        /* ==========================================
           CEK PASSWORD
           Password biasa tanpa hash
        ========================================== */

        if ($user && $password === $user['password']) {

            unset($user['password']);

            $_SESSION['user'] = $user;


            /* ==========================================
               LOGIN ADMIN
            ========================================== */

            if ($user['role'] === 'admin') {

                /*
                 * Pesan akan ditampilkan di
                 * Dashboard Admin.
                 */

                $_SESSION['login_success'] = 'admin';

                header('Location: admin/dashboard.php');

                exit;

            }


            /* ==========================================
               LOGIN SISWA
            ========================================== */

            $_SESSION['login_success'] = 'siswa';

            header('Location: siswa/dashboard.php');

            exit;

        } else {

            $error = 'Username atau password salah.';

        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login | Perpustakaan SMK</title>


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }


        /* =====================================
           BODY
        ===================================== */

        body {

            min-height: 100vh;

            background: #ffffff;

            display: flex;

            justify-content: center;

            align-items: center;

            padding: 30px;
        }


        /* =====================================
           CONTAINER
        ===================================== */

        .login-container {

            width: 100%;

            max-width: 440px;
        }


        /* =====================================
           LOGIN BOX
        ===================================== */

        .login-box {

            background: #ffffff;

            border: 1px solid #e2e8f0;

            border-radius: 24px;

            padding: 42px 38px;

            box-shadow:
                0 15px 40px rgba(15, 23, 42, 0.10);

            animation: muncul 0.5s ease;
        }


        @keyframes muncul {

            from {

                opacity: 0;

                transform: translateY(20px);
            }

            to {

                opacity: 1;

                transform: translateY(0);
            }
        }


        /* =====================================
           LOGO
        ===================================== */

        .logo-wrapper {

            display: flex;

            justify-content: center;

            margin-bottom: 22px;
        }


        .logo {

            width: 105px;

            height: 105px;

            border-radius: 50%;

            background: linear-gradient(
                135deg,
                #1976d2,
                #0d47a1
            );

            display: flex;

            justify-content: center;

            align-items: center;

            box-shadow:
                0 12px 30px rgba(25, 118, 210, 0.25);
        }


        .logo svg {

            width: 65px;

            height: 65px;
        }


        /* =====================================
           TITLE
        ===================================== */

        .title {

            text-align: center;

            color: #142d5c;

            font-size: 29px;

            font-weight: 800;

            letter-spacing: 0.5px;

            margin-bottom: 8px;
        }


        .subtitle {

            text-align: center;

            color: #64748b;

            font-size: 14px;

            margin-bottom: 24px;
        }


        /* =====================================
           GARIS BAWAH JUDUL
        ===================================== */

        .title-line {

            width: 75px;

            height: 4px;

            background: #1976d2;

            border-radius: 10px;

            margin: 0 auto 30px;
        }


        /* =====================================
           ALERT
        ===================================== */

        .alert {

            padding: 13px 15px;

            border-radius: 10px;

            margin-bottom: 20px;

            font-size: 14px;

            text-align: center;
        }


        .alert-danger {

            color: #b91c1c;

            background: #fee2e2;

            border: 1px solid #fecaca;
        }


        /* =====================================
           FORM
        ===================================== */

        .form-group {

            margin-bottom: 20px;
        }


        .form-group label {

            display: block;

            color: #334155;

            font-size: 14px;

            font-weight: 700;

            margin-bottom: 8px;
        }


        /* =====================================
           INPUT
        ===================================== */

        .input-wrapper {

            position: relative;
        }


        .input-wrapper input {

            width: 100%;

            height: 52px;

            border: 1px solid #cbd5e1;

            border-radius: 11px;

            padding: 0 48px 0 15px;

            background: #f8fafc;

            color: #1e293b;

            font-size: 14px;

            outline: none;

            transition: 0.2s;
        }


        .input-wrapper input:focus {

            border-color: #1976d2;

            background: #ffffff;

            box-shadow:
                0 0 0 3px rgba(25, 118, 210, 0.10);
        }


        .input-wrapper input::placeholder {

            color: #94a3b8;
        }


        /* =====================================
           TOMBOL MATA
        ===================================== */

        .password-toggle {

            position: absolute;

            right: 12px;

            top: 50%;

            transform: translateY(-50%);

            width: 34px;

            height: 34px;

            border: none;

            background: transparent;

            cursor: pointer;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 8px;

            color: #64748b;
        }


        .password-toggle:hover {

            background: #eaf2ff;

            color: #1976d2;
        }


        .password-toggle svg {

            width: 20px;

            height: 20px;

            fill: none;

            stroke: currentColor;

            stroke-width: 2;

            stroke-linecap: round;

            stroke-linejoin: round;
        }


        /* =====================================
           BUTTON LOGIN
        ===================================== */

        .btn-login {

            width: 100%;

            height: 52px;

            border: none;

            border-radius: 11px;

            background: linear-gradient(
                135deg,
                #1976d2,
                #1565c0
            );

            color: #ffffff;

            font-size: 15px;

            font-weight: 700;

            cursor: pointer;

            margin-top: 5px;

            box-shadow:
                0 8px 20px rgba(25, 118, 210, 0.20);

            transition: 0.2s;
        }


        .btn-login:hover {

            transform: translateY(-2px);

            box-shadow:
                0 12px 25px rgba(25, 118, 210, 0.28);
        }


        .btn-login:active {

            transform: translateY(0);
        }


        /* =====================================
           REGISTER
        ===================================== */

        .register-area {

            text-align: center;

            margin-top: 25px;

            padding-top: 22px;

            border-top: 1px solid #e2e8f0;

            color: #64748b;

            font-size: 14px;
        }


        .register-area a {

            display: block;

            margin-top: 12px;

            width: 100%;

            height: 46px;

            line-height: 46px;

            border: 1px solid #1976d2;

            border-radius: 10px;

            color: #1976d2;

            text-decoration: none;

            font-weight: 700;

            transition: 0.2s;
        }


        .register-area a:hover {

            background: #1976d2;

            color: #ffffff;
        }


        /* =====================================
           FOOTER
        ===================================== */

        .footer {

            text-align: center;

            color: #94a3b8;

            font-size: 12px;

            margin-top: 18px;
        }


        /* =====================================
           RESPONSIVE
        ===================================== */

        @media (max-width: 480px) {

            body {

                padding: 15px;
            }


            .login-box {

                padding: 32px 22px;

                border-radius: 20px;
            }


            .logo {

                width: 90px;

                height: 90px;
            }


            .logo svg {

                width: 56px;

                height: 56px;
            }


            .title {

                font-size: 24px;
            }

        }

    </style>

</head>


<body>


<div class="login-container">


    <div class="login-box">


        <!-- LOGO -->

        <div class="logo-wrapper">

            <div class="logo">

                <svg
                    viewBox="0 0 100 100"
                    xmlns="http://www.w3.org/2000/svg"
                >

                    <path
                        d="M15 28
                        C28 21 40 23 50 30
                        L50 78
                        C39 71 27 70 15 76
                        Z"
                        fill="white"
                    />

                    <path
                        d="M85 28
                        C72 21 60 23 50 30
                        L50 78
                        C61 71 73 70 85 76
                        Z"
                        fill="#dbeafe"
                    />

                    <path
                        d="M50 30 L50 78"
                        stroke="#1976d2"
                        stroke-width="3"
                        fill="none"
                    />

                    <path
                        d="M22 37
                        C32 34 40 36 46 41"
                        stroke="#1976d2"
                        stroke-width="3"
                        fill="none"
                    />

                    <path
                        d="M78 37
                        C68 34 60 36 54 41"
                        stroke="#1976d2"
                        stroke-width="3"
                        fill="none"
                    />

                    <circle
                        cx="50"
                        cy="18"
                        r="7"
                        fill="white"
                    />

                </svg>

            </div>

        </div>


        <!-- JUDUL -->

        <h1 class="title">
            PERPUSTAKAAN SMK
        </h1>


        <p class="subtitle">
            Sistem Perpustakaan Digital
        </p>


        <div class="title-line"></div>


        <!-- ERROR -->

        <?php if ($error !== ''): ?>

            <div class="alert alert-danger">
                <?= e($error) ?>
            </div>

        <?php endif; ?>


        <!-- FORM -->

        <form method="POST">


            <!-- USERNAME -->

            <div class="form-group">

                <label for="username">
                    Username
                </label>

                <div class="input-wrapper">

                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Masukkan username"
                        autocomplete="username"
                        required
                    >

                </div>

            </div>


            <!-- PASSWORD -->

            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <div class="input-wrapper">

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Masukkan password"
                        autocomplete="current-password"
                        required
                    >


                    <!-- TOMBOL MATA -->

                    <button
                        type="button"
                        class="password-toggle"
                        id="passwordToggle"
                        aria-label="Lihat password"
                    >

                        <svg
                            id="eyeIcon"
                            viewBox="0 0 24 24"
                        >

                            <path
                                d="M2 12s3.5-7 10-7
                                10 7 10 7
                                -3.5 7-10 7
                                -10-7-10-7z"
                            />

                            <circle
                                cx="12"
                                cy="12"
                                r="3"
                            />

                        </svg>

                    </button>

                </div>

            </div>


            <!-- LOGIN -->

            <button
                type="submit"
                class="btn-login"
            >
                Masuk ke Perpustakaan
            </button>


        </form>


        <!-- REGISTER -->

        <div class="register-area">

            Belum mempunyai akun?

            <a href="register.php">
                Daftar sebagai Siswa
            </a>

        </div>


    </div>


    <div class="footer">
        2026 Perpustakaan SMK
    </div>


</div>


<script>

    const passwordInput =
        document.getElementById('password');

    const passwordToggle =
        document.getElementById('passwordToggle');

    const eyeIcon =
        document.getElementById('eyeIcon');


    passwordToggle.addEventListener('click', function () {

        if (passwordInput.type === 'password') {

            passwordInput.type = 'text';

            passwordToggle.setAttribute(
                'aria-label',
                'Sembunyikan password'
            );

            eyeIcon.innerHTML = `
                <path d="M3 3L21 21"></path>

                <path
                    d="M10.6 10.6
                    A2 2 0 0 0
                    13.4 13.4">
                </path>

                <path
                    d="M9.9 4.2
                    C10.6 4.1
                    11.3 4
                    12 4
                    C18.5 4
                    22 12
                    22 12
                    C21.2 13.8
                    20.1 15.3
                    18.8 16.5">
                </path>

                <path
                    d="M6.1 6.1
                    C3.5 8.2
                    2 12
                    2 12
                    C2 12
                    5.5 20
                    12 20
                    C13.5 20
                    14.9 19.6
                    16.2 19">
                </path>
            `;

        } else {

            passwordInput.type = 'password';

            passwordToggle.setAttribute(
                'aria-label',
                'Lihat password'
            );

            eyeIcon.innerHTML = `
                <path
                    d="M2 12s3.5-7 10-7
                    10 7 10 7
                    -3.5 7-10 7
                    -10-7-10-7z">
                </path>

                <circle
                    cx="12"
                    cy="12"
                    r="3">
                </circle>
            `;
        }

    });

</script>


</body>

</html>