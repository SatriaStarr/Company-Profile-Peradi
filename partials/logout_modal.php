<?php
/*
|--------------------------------------------------------------------------
| Modal konfirmasi logout (dipakai admin & user)
|--------------------------------------------------------------------------
| Cara pakai:
|   1. include file ini sekali di halaman (sebelum </body>).
|   2. Set atribut pada link/tombol logout:
|         <a href="logout.php" data-logout>Keluar</a>
|      (boleh beberapa elemen; semua akan dicegat).
|
| Tidak mengubah mekanisme logout — hanya menampilkan konfirmasi lalu
| meneruskan ke href / action elemen aslinya.
*/
?>
<div id="logoutModal" class="logout-modal" aria-hidden="true">
    <div class="logout-modal__box" role="dialog" aria-modal="true" aria-labelledby="logoutModalTitle">
        <div class="logout-modal__icon"><i class="fa-solid fa-right-from-bracket"></i></div>
        <h3 id="logoutModalTitle">Yakin ingin keluar?</h3>
        <p>Anda akan keluar dari akun dan harus login kembali untuk masuk.</p>
        <div class="logout-modal__actions">
            <button type="button" class="logout-modal__btn logout-modal__btn--cancel" id="logoutCancel">Batal</button>
            <a href="#" class="logout-modal__btn logout-modal__btn--confirm" id="logoutConfirm">
                <i class="fa-solid fa-right-from-bracket"></i> Ya, Logout
            </a>
        </div>
    </div>
</div>

<style>
.logout-modal{position:fixed;inset:0;background:rgba(15,23,42,.55);display:none;
    align-items:center;justify-content:center;z-index:10000;padding:20px;
    font-family:'Montserrat','Poppins',system-ui,sans-serif}
.logout-modal.is-open{display:flex}
.logout-modal__box{background:#fff;border-radius:16px;max-width:400px;width:100%;
    padding:30px 26px;text-align:center;box-shadow:0 24px 60px rgba(0,0,0,.28);
    animation:logoutPop .16s ease-out}
@keyframes logoutPop{from{transform:scale(.94);opacity:0}to{transform:scale(1);opacity:1}}
.logout-modal__icon{width:60px;height:60px;border-radius:50%;background:#fef2f2;color:#dc2626;
    display:flex;align-items:center;justify-content:center;font-size:24px;margin:0 auto 16px}
.logout-modal__box h3{color:#1e3a8a;font-size:19px;font-weight:800;margin:0 0 8px}
.logout-modal__box p{color:#64748b;font-size:13px;line-height:1.6;margin:0 0 22px}
.logout-modal__actions{display:flex;gap:10px;justify-content:center}
.logout-modal__btn{flex:1;padding:12px 14px;border-radius:9px;border:none;cursor:pointer;
    font-weight:700;font-size:13px;text-decoration:none;display:inline-flex;
    align-items:center;justify-content:center;gap:7px;font-family:inherit}
.logout-modal__btn--cancel{background:#e5e7eb;color:#334155}
.logout-modal__btn--cancel:hover{background:#d1d5db}
.logout-modal__btn--confirm{background:#dc2626;color:#fff}
.logout-modal__btn--confirm:hover{background:#b91c1c}
</style>

<script>
(function () {
    var modal   = document.getElementById('logoutModal');
    var confirm = document.getElementById('logoutConfirm');
    var cancel  = document.getElementById('logoutCancel');
    var pending = null;

    function open(target) {
        pending = target;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        cancel.focus();
    }
    function close() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        pending = null;
    }

    document.querySelectorAll('[data-logout]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            open(el);
        });
    });

    confirm.addEventListener('click', function (e) {
        e.preventDefault();
        if (!pending) { return; }
        if (pending.tagName === 'FORM') { pending.submit(); return; }
        var href = pending.getAttribute('href');
        if (href && href !== '#') { window.location.href = href; }
        else if (pending.form) { pending.form.submit(); }
    });

    cancel.addEventListener('click', close);
    modal.addEventListener('click', function (e) { if (e.target === modal) { close(); } });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) { close(); }
    });
})();
</script>
