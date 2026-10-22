{{-- Shrinks big banner/logo photos in the browser before upload, so replacing them never fails on size limits. --}}
<script>
(function () {
    const LIMITS = { banner_image: 1800, form_logo_image: 700 };
    const KEEP = ['image/svg+xml', 'image/gif'];

    function shrink(input) {
        const file = input.files && input.files[0];
        const note = input.parentElement.querySelector('[data-shrink-note]') || (() => {
            const n = document.createElement('div');
            n.dataset.shrinkNote = '1';
            n.className = 'small mt-1';
            input.insertAdjacentElement('afterend', n);
            return n;
        })();
        note.textContent = '';
        if (!file || KEEP.includes(file.type) || !file.type.startsWith('image/')) return;

        const max = LIMITS[input.name] || 1600;
        const url = URL.createObjectURL(file);
        const img = new Image();
        img.onload = function () {
            URL.revokeObjectURL(url);
            const scale = Math.min(1, max / Math.max(img.width, img.height));
            if (scale === 1 && file.size <= 1.5 * 1024 * 1024) { note.textContent = ''; return; }
            const canvas = document.createElement('canvas');
            canvas.width = Math.round(img.width * scale);
            canvas.height = Math.round(img.height * scale);
            const ctx = canvas.getContext('2d');
            const keepAlpha = file.type === 'image/png' && input.name !== 'banner_image';
            if (!keepAlpha) { ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, canvas.width, canvas.height); }
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            const type = keepAlpha ? 'image/png' : 'image/jpeg';
            canvas.toBlob(function (blob) {
                if (!blob || blob.size >= file.size) return;
                const ext = keepAlpha ? 'png' : 'jpg';
                const out = new File([blob], file.name.replace(/\.[^.]+$/, '') + '.' + ext, { type });
                const dt = new DataTransfer();
                dt.items.add(out);
                input.files = dt.files;
                note.className = 'small mt-1 text-success';
                note.textContent = 'Optimized: ' + Math.round(file.size / 1024) + ' KB → ' + Math.round(blob.size / 1024) + ' KB';
            }, type, 0.86);
        };
        img.onerror = function () { URL.revokeObjectURL(url); note.className = 'small mt-1 text-danger'; note.textContent = 'This file could not be read as an image.'; };
        img.src = url;
    }

    document.addEventListener('change', function (e) {
        const t = e.target;
        if (t && t.type === 'file' && LIMITS[t.name]) shrink(t);
    });
})();
</script>
