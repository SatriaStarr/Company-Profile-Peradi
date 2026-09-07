<?php
/*
 * Modal konfirmasi hapus (POST + CSRF).
 * Butuh csrf_field() -> halaman harus sudah memuat partials/admin_auth.php.
 * Pakai:  openDeleteModal(id, 'hapus_xxx.php')  [+ opsional teks]
 */
if (!function_exists('csrf_field')) {
    function csrf_field(): string { return ''; }
}
?>
<style>
    .modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.55); display: none;
        align-items: center; justify-content: center; z-index: 9999; padding: 20px; }
    .modal-box { background: #fff; padding: 26px; border-radius: 14px; width: 100%; max-width: 430px;
        text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,.25); font-family: 'Montserrat','Poppins',sans-serif; }
    .modal-box h3 { color: #1e3a8a; margin: 0 0 8px; font-size: 17px; }
    .modal-box p { color: #475569; font-size: 13px; margin: 0 0 18px; }
    .modal-actions { display: flex; justify-content: center; gap: 12px; }
    .btn-delete { background: #dc2626; color: #fff; padding: 10px 18px; border-radius: 8px;
        border: none; cursor: pointer; font-weight: 700; font-size: 12px; }
    .btn-cancel { background: #e5e7eb; color: #333; padding: 10px 18px; border-radius: 8px;
        border: none; cursor: pointer; font-weight: 700; font-size: 12px; }
</style>

<div id="confirmDeleteModal" class="modal-overlay">
    <div class="modal-box">
        <h3>Hapus Data?</h3>
        <p id="confirmDeleteText">Tindakan ini tidak dapat dibatalkan.</p>
        <form id="confirmDeleteForm" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="confirmDeleteId">
            <div class="modal-actions">
                <button type="button" onclick="closeDeleteModal()" class="btn-cancel">Batal</button>
                <button type="submit" class="btn-delete">Ya, Hapus</button>
            </div>
        </form>
    </div>
</div>

<script>
function openDeleteModal(id, actionUrl, text) {
    var f = document.getElementById('confirmDeleteForm');
    if (actionUrl) f.action = actionUrl;
    document.getElementById('confirmDeleteId').value = id;
    if (text) document.getElementById('confirmDeleteText').textContent = text;
    document.getElementById('confirmDeleteModal').style.display = 'flex';
}
function closeDeleteModal() {
    document.getElementById('confirmDeleteModal').style.display = 'none';
}
document.getElementById('confirmDeleteModal').addEventListener('click', function (e) {
    if (e.target === this) closeDeleteModal();
});
</script>
