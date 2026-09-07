function openViewer(url, title = 'Dokumen') {
    const overlay = document.getElementById('pdfViewer');
    const frame = document.getElementById('viewerFrame');
    const header = document.getElementById('viewerTitle');

    frame.src = url;
    header.textContent = title;
    overlay.classList.add('active');
}

function closeViewer() {
    const overlay = document.getElementById('pdfViewer');
    const frame = document.getElementById('viewerFrame');

    frame.src = '';
    overlay.classList.remove('active');
}

function openImage(src, title = '') {
    const modal = document.getElementById('viewerModal');

    document.getElementById('pdfFrame').style.display = 'none';

    const img = document.getElementById('imageFrame');
    img.src = src;
    img.style.display = 'block';

    document.getElementById('viewerTitle').innerText = title;

    modal.classList.add('show');
}


/* ESC untuk tutup */
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeViewer();
});
