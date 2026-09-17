<?php

require 'config/database.php';

session_start();


/*
|--------------------------------------------------------------------------
| Fungsi keamanan
|--------------------------------------------------------------------------
*/

function e($text)
{
    return htmlspecialchars(
        $text ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}


$error = '';


/*
|--------------------------------------------------------------------------
| PROSES REGISTRASI
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim(
        $_POST['username'] ?? ''
    );

    $password = trim(
        $_POST['password'] ?? ''
    );

    $konfirmasiPassword = trim(
        $_POST['konfirmasi_password'] ?? ''
    );


    /*
    |--------------------------------------------------------------------------
    | Validasi kosong
    |--------------------------------------------------------------------------
    */

    if (
        $username === '' ||
        $password === '' ||
        $konfirmasiPassword === ''
    ) {

        $error =
            'Username, password, dan konfirmasi password wajib diisi.';

    }


    /*
    |--------------------------------------------------------------------------
    | Validasi username
    |--------------------------------------------------------------------------
    */

    elseif (strlen($username) < 3) {

        $error =
            'Username minimal 3 karakter.';

    }


    /*
    |--------------------------------------------------------------------------
    | Validasi password
    |--------------------------------------------------------------------------
    */

    elseif (strlen($password) < 4) {

        $error =
            'Password minimal 4 karakter.';

    }


    /*
    |--------------------------------------------------------------------------
    | Validasi konfirmasi password
    |--------------------------------------------------------------------------
    */

    elseif ($password !== $konfirmasiPassword) {

        $error =
            'Password dan konfirmasi password tidak sama.';

    }


    /*
    |--------------------------------------------------------------------------
    | Cek username
    |--------------------------------------------------------------------------
    */

    else {

        $cek = $pdo->prepare("
            SELECT id
            FROM users
            WHERE username = ?
            LIMIT 1
        ");

        $cek->execute([
            $username
        ]);


        if ($cek->fetch()) {

            $error =
                'Username sudah digunakan.';

        }


        /*
        |--------------------------------------------------------------------------
        | SIMPAN AKUN
        |--------------------------------------------------------------------------
        */

        else {

            $stmt = $pdo->prepare("
                INSERT INTO users
                (
                    nama,
                    username,
                    email,
                    password,
                    kelas,
                    foto,
                    profil_lengkap,
                    role,
                    status
                )
                VALUES
                (
                    '',
                    ?,
                    NULL,
                    ?,
                    NULL,
                    NULL,
                    0,
                    'siswa',
                    'aktif'
                )
            ");


            $stmt->execute([
                $username,
                $password
            ]);


            /*
            |--------------------------------------------------------------------------
            | Setelah berhasil langsung ke LOGIN
            |--------------------------------------------------------------------------
            */

            header(
                'Location: login.php?daftar=berhasil'
            );

            exit;
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

    <title>
        Daftar Siswa - Perpustakaan SMK
    </title>


    <style>

        * {

            margin: 0;

            padding: 0;

            box-sizing: border-box;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

        }


        body {

            min-height: 100vh;

            background:
                linear-gradient(
                    135deg,
                    #0f172a,
                    #1e3a8a
                );

            display: flex;

            justify-content: center;

            align-items: center;

            padding: 30px 15px;

        }


        .register-container {

            width: 100%;

            max-width: 450px;

        }


        .register-box {

            background: white;

            border-radius: 22px;

            padding: 35px;

            box-shadow:
                0 20px 50px
                rgba(0, 0, 0, 0.25);

        }


        /* LOGO */

        .logo {

            width: 80px;

            height: 80px;

            margin:
                0 auto 18px;

            border-radius: 50%;

            background:
                #2563eb;

            display: flex;

            justify-content: center;

            align-items: center;

            color: white;

            font-size: 28px;

            font-weight: bold;

        }


        /* JUDUL */

        h1 {

            text-align: center;

            color: #172033;

            margin-bottom: 8px;

        }


        .subtitle {

            text-align: center;

            color: #64748b;

            font-size: 14px;

            margin-bottom: 25px;

        }


        /* INFO */

        .info {

            background:
                #eff6ff;

            color:
                #1e40af;

            padding:
                13px 15px;

            border-radius:
                10px;

            margin-bottom:
                20px;

            font-size:
                14px;

            line-height:
                1.5;

        }


        /* ERROR */

        .alert {

            padding:
                13px 15px;

            border-radius:
                10px;

            margin-bottom:
                18px;

            font-size:
                14px;

        }


        .alert-danger {

            background:
                #fee2e2;

            color:
                #b91c1c;

        }


        /* FORM */

        .form-group {

            margin-bottom:
                18px;

        }


        label {

            display:
                block;

            margin-bottom:
                8px;

            font-weight:
                bold;

            color:
                #334155;

        }


        /* INPUT BIASA */

        input {

            width:
                100%;

            padding:
                13px;

            border:
                1px solid #cbd5e1;

            border-radius:
                9px;

            font-size:
                14px;

            outline:
                none;

        }


        input:focus {

            border-color:
                #2563eb;

        }


        /* PASSWORD WRAPPER */

        .password-wrapper {

            position:
                relative;

            width:
                100%;

        }


        .password-wrapper input {

            padding-right:
                48px;

        }


        /* TOMBOL MATA */

        .toggle-password {

            position:
                absolute;

            right:
                10px;

            top:
                50%;

            transform:
                translateY(-50%);

            width:
                34px;

            height:
                34px;

            border:
                none;

            background:
                transparent;

            cursor:
                pointer;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            color:
                #64748b;

            font-size:
                18px;

            padding:
                0;

        }


        .toggle-password:hover {

            color:
                #2563eb;

        }


        /* TOMBOL DAFTAR */

        .btn {

            width:
                100%;

            padding:
                13px;

            border:
                none;

            border-radius:
                9px;

            background:
                #2563eb;

            color:
                white;

            font-size:
                15px;

            font-weight:
                bold;

            cursor:
                pointer;

        }


        .btn:hover {

            background:
                #1d4ed8;

        }


        /* LINK LOGIN */

        .login-link {

            text-align:
                center;

            margin-top:
                20px;

            color:
                #64748b;

            font-size:
                14px;

        }


        .login-link a {

            color:
                #2563eb;

            text-decoration:
                none;

            font-weight:
                bold;

        }


        /* FOOTER */

        .footer {

            text-align:
                center;

            color:
                rgba(255,255,255,0.8);

            font-size:
                12px;

            margin-top:
                18px;

        }


        /* MOBILE */

        @media (max-width: 500px) {

            .register-box {

                padding:
                    25px 20px;

            }

        }

    </style>

</head>


<body>


<div class="register-container">


    <div class="register-box">


        <!-- LOGO -->

        <div class="logo">

            SMK

        </div>


        <!-- JUDUL -->

        <h1>

            Daftar Siswa

        </h1>


        <p class="subtitle">

            Buat akun untuk menggunakan
            Perpustakaan SMK

        </p>


        <!-- ERROR -->

        <?php if ($error !== ''): ?>

            <div class="alert alert-danger">

                <?= e($error) ?>

            </div>

        <?php endif; ?>


        <!-- INFORMASI -->

        <div class="info">

            Setelah berhasil mendaftar,
            Anda akan langsung diarahkan
            ke halaman login.

            <br><br>

            Setelah login, lengkapi profil
            terlebih dahulu sebelum
            melakukan peminjaman buku.

        </div>


        <!-- FORM -->

        <form
            method="POST"
            id="registerForm"
        >


            <!-- USERNAME -->

            <div class="form-group">

                <label for="username">

                    Username

                </label>


                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Buat username"
                    value="<?= e($_POST['username'] ?? '') ?>"
                    minlength="3"
                    required
                >

            </div>


            <!-- PASSWORD -->

            <div class="form-group">

                <label for="password">

                    Password

                </label>


                <div class="password-wrapper">

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Buat password"
                        minlength="4"
                        required
                    >


                    <button
                        type="button"
                        class="toggle-password"
                        onclick="togglePassword(
                            'password',
                            this
                        )"
                        aria-label="Tampilkan password"
                    >

                        <span>
                            ◉
                        </span>

                    </button>

                </div>

            </div>


            <!-- KONFIRMASI PASSWORD -->

            <div class="form-group">

                <label for="konfirmasi_password">

                    Konfirmasi Password

                </label>


                <div class="password-wrapper">

                    <input
                        type="password"
                        id="konfirmasi_password"
                        name="konfirmasi_password"
                        placeholder="Ulangi password"
                        minlength="4"
                        required
                    >


                    <button
                        type="button"
                        class="toggle-password"
                        onclick="togglePassword(
                            'konfirmasi_password',
                            this
                        )"
                        aria-label="Tampilkan konfirmasi password"
                    >

                        <span>
                            ◉
                        </span>

                    </button>

                </div>

            </div>


            <!-- TOMBOL -->

            <button
                type="submit"
                class="btn"
            >

                Daftar Sekarang

            </button>


        </form>


        <!-- LOGIN -->

        <div class="login-link">

            Sudah punya akun?

            <a href="login.php">

                Login di sini

            </a>

        </div>


    </div>


    <div class="footer">

        Sistem Perpustakaan Digital SMK

    </div>


</div>


<script>

/*
|--------------------------------------------------------------------------
| TAMPILKAN / SEMBUNYIKAN PASSWORD
|--------------------------------------------------------------------------
*/

function togglePassword(
    inputId,
    button
) {

    const input =
        document.getElementById(inputId);


    if (!input) {

        return;

    }


    if (input.type === 'password') {

        input.type = 'text';

        button.innerHTML =
            '<span>○</span>';

        button.setAttribute(
            'aria-label',
            'Sembunyikan password'
        );

    } else {

        input.type = 'password';

        button.innerHTML =
            '<span>◉</span>';

        button.setAttribute(
            'aria-label',
            'Tampilkan password'
        );

    }

}


/*
|--------------------------------------------------------------------------
| CEK PASSWORD
|--------------------------------------------------------------------------
*/

document
    .getElementById('registerForm')
    .addEventListener(
        'submit',
        function(event) {

            const password =
                document.getElementById(
                    'password'
                ).value;


            const konfirmasi =
                document.getElementById(
                    'konfirmasi_password'
                ).value;


            if (password !== konfirmasi) {

                event.preventDefault();

                alert(
                    'Password dan konfirmasi password tidak sama.'
                );

                return false;

            }

        }
    );

</script>


</body>

</html>