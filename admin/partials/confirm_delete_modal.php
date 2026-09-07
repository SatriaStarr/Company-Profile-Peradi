<style>
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .modal-box {
        background: #fff;
        padding: 25px;
        border-radius: 12px;
        width: 100%;
        max-width: 420px;
        text-align: center;
        box-shadow: 0 10px 25px rgba(0,0,0,0.25);
    }

    .modal-actions {
        margin-top: 20px;
        display: flex;
        justify-content: center;
        gap: 12px;
    }

    .btn-delete {
        background: #dc2626;
        color: #fff;
        padding: 8px 16px;
        border-radius: 8px;
        text-decoration: none;
    }

    .btn-cancel {
        background: #e5e7eb;
        padding: 8px 16px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
    }
</style>

<div id="confirmDeleteModal" class="modal-overlay">
    <div class="modal-box">
        <h3>Hapus Data?</h3>
        <p>Tindakan ini tidak dapat dibatalkan.</p>

        <div class="modal-actions">
            <a href="#" id="confirmDeleteYes" class="btn-delete">Hapus</a>
            <button onclick="closeDeleteModal()" class="btn-cancel">Batal</button>
        </div>
    </div>
</div>
