/* =========================================
   SIDEBAR
========================================= */

function openSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');

    if (sidebar) {
        sidebar.classList.add('open');
    }

    if (overlay) {
        overlay.classList.add('show');
    }
}


function closeSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');

    if (sidebar) {
        sidebar.classList.remove('open');
    }

    if (overlay) {
        overlay.classList.remove('show');
    }
}


/* =========================================
   NOTIFIKASI
========================================= */

let notificationInitialized = false;
let lastNotificationId = 0;
let lastToastNotificationId = 0;
let notificationTimer = null;
let notificationInterval = null;


/*
|-------------------------------------------------------------------------- 
| ID NOTIFIKASI YANG SUDAH DILIHAT
|-------------------------------------------------------------------------- 
*/

let notificationSeenId = Number(
    localStorage.getItem(
        'perpustakaan_last_notification_id'
    ) || 0
);


/* =========================================
   TOGGLE LONCENG
========================================= */

function toggleNotifications() {

    const dropdown =
        document.getElementById(
            'notificationDropdown'
        );

    const button =
        document.getElementById(
            'notificationButton'
        );

    const dot =
        document.getElementById(
            'notificationDot'
        );


    if (!dropdown) {
        return;
    }


    const sedangTerbuka =
        dropdown.classList.contains('show');


    /*
    |-------------------------------------------------------------------------- 
    | JIKA SUDAH TERBUKA
    |-------------------------------------------------------------------------- 
    */

    if (sedangTerbuka) {

        dropdown.classList.remove('show');

        if (button) {
            button.setAttribute(
                'aria-expanded',
                'false'
            );
        }

        dropdown.setAttribute(
            'aria-hidden',
            'true'
        );

        return;
    }


    /*
    |-------------------------------------------------------------------------- 
    | JIKA BELUM TERBUKA
    |-------------------------------------------------------------------------- 
    */

    dropdown.classList.add('show');

    if (button) {
        button.setAttribute(
            'aria-expanded',
            'true'
        );
    }

    dropdown.setAttribute(
        'aria-hidden',
        'false'
    );


    /*
    |-------------------------------------------------------------------------- 
    | HILANGKAN TITIK MERAH
    |-------------------------------------------------------------------------- 
    */

    if (
        lastNotificationId >
        notificationSeenId
    ) {

        notificationSeenId =
            lastNotificationId;

        localStorage.setItem(
            'perpustakaan_last_notification_id',
            String(notificationSeenId)
        );
    }


    if (dot) {
        dot.style.display = 'none';
    }


    /*
    |-------------------------------------------------------------------------- 
    | TAMPILKAN PEMINJAMAN TERBARU
    |-------------------------------------------------------------------------- 
    */

    loadNotifications(true);
}


/* =========================================
   AMBIL NOTIFIKASI
========================================= */

function loadNotifications(showItems = false) {

    const button =
        document.getElementById(
            'notificationButton'
        );


    if (!button) {
        return;
    }


    fetch(
        '../proses/notifikasi.php?t=' +
        Date.now(),
        {
            method: 'GET',
            cache: 'no-store',
            credentials: 'same-origin'
        }
    )

    .then(function(response) {

        if (!response.ok) {

            throw new Error(
                'Gagal mengambil notifikasi'
            );

        }

        return response.json();

    })

    .then(function(data) {

        if (
            !data ||
            data.success !== true
        ) {

            return;
        }


        const items =
            Array.isArray(data.items)
                ? data.items
                : [];


        const latestId =
            Number(
                data.latest_id || 0
            );


        /*
        |-------------------------------------------------------------------------- 
        | SIMPAN ID TERBARU
        |-------------------------------------------------------------------------- 
        */

        lastNotificationId =
            latestId;


        /*
        |-------------------------------------------------------------------------- 
        | CEK NOTIFIKASI YANG BELUM DIBUKA
        |-------------------------------------------------------------------------- 
        */

        const adaPeminjamanBaru =
            latestId >
            notificationSeenId;


        /*
        |-------------------------------------------------------------------------- 
        | UPDATE TITIK MERAH
        |-------------------------------------------------------------------------- 
        */

        updateNotificationDot(
            latestId,
            notificationSeenId
        );


        /*
        |-------------------------------------------------------------------------- 
        | DATA HANYA MUNCUL SAAT LONCENG DIKLIK
        |-------------------------------------------------------------------------- 
        */

        if (showItems) {

            renderNotifications(
                items
            );

        }


        /*
        |-------------------------------------------------------------------------- 
        | PESAN OTOMATIS
        |-------------------------------------------------------------------------- 
        |
        | Kalau ada peminjaman yang belum dilihat,
        | langsung tampilkan pesan.
        |
        | Tidak menunggu klik lonceng.
        |
        */

        if (
            adaPeminjamanBaru &&
            latestId !== lastToastNotificationId
        ) {

            showNewLoanToast();

            lastToastNotificationId =
                latestId;
        }


        /*
        |-------------------------------------------------------------------------- 
        | SELESAI LOAD PERTAMA
        |-------------------------------------------------------------------------- 
        */

        if (!notificationInitialized) {

            notificationInitialized =
                true;
        }

    })

    .catch(function(error) {

        console.log(
            'Notifikasi:',
            error.message
        );

    });
}


/* =========================================
   TITIK MERAH
========================================= */

function updateNotificationDot(
    latestId,
    seenId
) {

    const dot =
        document.getElementById(
            'notificationDot'
        );


    if (!dot) {
        return;
    }


    if (
        latestId > 0 &&
        latestId > seenId
    ) {

        dot.style.display =
            'block';

    } else {

        dot.style.display =
            'none';
    }
}


/* =========================================
   TAMPILKAN DATA NOTIFIKASI
========================================= */

function renderNotifications(items) {

    const list =
        document.getElementById(
            'notificationList'
        );


    if (!list) {
        return;
    }


    if (
        !items ||
        items.length === 0
    ) {

        list.innerHTML = `
            <div class="notification-empty">
                Tidak ada peminjaman baru.
            </div>
        `;

        return;
    }


    let html = '';


    items.forEach(function(item) {

        html += `

            <a
                href="../admin/peminjaman.php"
                class="notification-item"
            >

                <strong>
                    Peminjaman Baru
                </strong>

                <span>
                    ${escapeHtml(
                        item.nama_siswa
                    )}
                    meminjam buku
                    "${escapeHtml(
                        item.nama_buku
                    )}"
                </span>

                <span>
                    Kelas:
                    ${escapeHtml(
                        item.kelas
                    )}
                </span>

                <span>
                    Jumlah:
                    ${escapeHtml(
                        item.jumlah
                    )}
                    buku
                </span>

                <small>
                    Status: Disetujui
                </small>

            </a>

        `;

    });


    list.innerHTML =
        html;
}


/* =========================================
   PESAN OTOMATIS
========================================= */

function showNewLoanToast() {

    const toast =
        document.getElementById(
            'newLoanToast'
        );


    if (!toast) {
        return;
    }


    /*
    |-------------------------------------------------------------------------- 
    | TAMPILKAN PESAN
    |-------------------------------------------------------------------------- 
    */

    toast.style.display =
        'block';


    /*
    |-------------------------------------------------------------------------- 
    | HAPUS TIMER SEBELUMNYA
    |-------------------------------------------------------------------------- 
    */

    if (notificationTimer) {

        clearTimeout(
            notificationTimer
        );
    }


    /*
    |-------------------------------------------------------------------------- 
    | PESAN TAMPIL SELAMA 2 DETIK
    |-------------------------------------------------------------------------- 
    */

    notificationTimer =
        setTimeout(function() {

            toast.style.display =
                'none';

        }, 2000);
}


/* =========================================
   ESCAPE HTML
========================================= */

function escapeHtml(value) {

    if (
        value === null ||
        value === undefined
    ) {

        return '';
    }


    return String(value)

        .replace(
            /&/g,
            '&amp;'
        )

        .replace(
            /</g,
            '&lt;'
        )

        .replace(
            />/g,
            '&gt;'
        )

        .replace(
            /"/g,
            '&quot;'
        )

        .replace(
            /'/g,
            '&#039;'
        );
}


/* =========================================
   CEK NOTIFIKASI OTOMATIS
========================================= */

function startNotificationChecker() {

    const button =
        document.getElementById(
            'notificationButton'
        );


    /*
    |-------------------------------------------------------------------------- 
    | HANYA JALAN JIKA LONCENG ADA
    |-------------------------------------------------------------------------- 
    */

    if (!button) {
        return;
    }


    /*
    |-------------------------------------------------------------------------- 
    | CEK PERTAMA
    |-------------------------------------------------------------------------- 
    */

    loadNotifications(false);


    /*
    |-------------------------------------------------------------------------- 
    | HINDARI INTERVAL GANDA
    |-------------------------------------------------------------------------- 
    */

    if (notificationInterval) {

        clearInterval(
            notificationInterval
        );
    }


    /*
    |-------------------------------------------------------------------------- 
    | CEK SETIAP 3 DETIK
    |-------------------------------------------------------------------------- 
    */

    notificationInterval =
        setInterval(function() {

            loadNotifications(false);

        }, 3000);
}


/* =========================================
   KETIKA HALAMAN SELESAI
========================================= */

document.addEventListener(
    'DOMContentLoaded',
    function() {

        const button =
            document.getElementById(
                'notificationButton'
            );


        const wrapper =
            document.getElementById(
                'notificationWrapper'
            );


        const dropdown =
            document.getElementById(
                'notificationDropdown'
            );


        /*
        |-------------------------------------------------------------------------- 
        | TOMBOL LONCENG
        |-------------------------------------------------------------------------- 
        */

        if (button) {

            button.addEventListener(
                'click',
                function(event) {

                    event.preventDefault();

                    event.stopPropagation();

                    toggleNotifications();

                }
            );
        }


        /*
        |-------------------------------------------------------------------------- 
        | KLIK DI LUAR DROPDOWN
        |-------------------------------------------------------------------------- 
        */

        document.addEventListener(
            'click',
            function(event) {

                if (
                    !wrapper ||
                    !dropdown
                ) {

                    return;
                }


                if (
                    !wrapper.contains(
                        event.target
                    )
                ) {

                    dropdown.classList.remove(
                        'show'
                    );


                    if (button) {

                        button.setAttribute(
                            'aria-expanded',
                            'false'
                        );
                    }


                    dropdown.setAttribute(
                        'aria-hidden',
                        'true'
                    );
                }

            }
        );


        /*
        |-------------------------------------------------------------------------- 
        | MULAI SISTEM NOTIFIKASI
        |-------------------------------------------------------------------------- 
        */

        startNotificationChecker();

    }
);