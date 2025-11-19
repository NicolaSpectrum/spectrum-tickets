document.addEventListener('DOMContentLoaded', function () {
    const cfg = window.__VERIFY_CONFIG;
    const manualInput = document.getElementById('manualCodeInput');
    const openScannerBtn = document.getElementById('openScannerBtn');
    const closeScannerBtn = document.getElementById('closeScannerBtn');
    const qrReaderContainer = document.getElementById('qrReaderContainer');
    const resultBox = document.getElementById('resultBox');
    const qrReaderId = 'qr-reader';

    let html5QrCode = null;
    let isScanning = false;
    let successSound, errorSound;

    // --- Inicializar sonidos ---
    function initSounds() {
        successSound = new Audio('/sounds/success.mp3');
        errorSound = new Audio('/sounds/error.mp3');
        // puedes usar archivos cortos en /public/sounds o enlaces absolutos
    }

    // --- Feedback haptico / sonoro ---
    function feedback(type) {
        if (navigator.vibrate) {
            switch (type) {
                case 'success': navigator.vibrate([100, 50, 100]); break;
                case 'error': navigator.vibrate([200, 100, 200]); break;
                case 'warning': navigator.vibrate(100); break;
            }
        }
        if (type === 'success' && successSound) successSound.play().catch(() => {});
        if (type === 'error' && errorSound) errorSound.play().catch(() => {});
    }

    // --- Mostrar resultados ---
    function showResult(html) {
        resultBox.innerHTML = html;
        resultBox.classList.remove('hidden');
    }

    function clearResult() {
        resultBox.innerHTML = '';
        resultBox.classList.add('hidden');
    }

    // --- Petición de verificación ---
    async function postVerify(code) {
        clearResult();
        showResult('<div class="text-gray-700">⏳ Verificando...</div>');
        const startTime = performance.now();

        try {
            const res = await fetch(cfg.verifyUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': cfg.token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ code })
            });

            const elapsed = (performance.now() - startTime).toFixed(0);
            const data = await res.json();

            if (res.ok && data.status === 'success') {
                feedback('success');
                showResult(`
                    <div class="p-4 bg-green-50 border border-green-200 rounded animate-fadeIn">
                        <strong class="text-green-700">✔ ${data.message}</strong>
                        <div class="mt-2 text-sm text-gray-700">
                            <div><strong>Nombre:</strong> ${data.data.name}</div>
                            <div><strong>Email:</strong> ${data.data.email}</div>
                            <div><strong>Ubicación:</strong> ${data.data.seat_type ?? 'N/A'} ${data.data.seat_number ? ' - ' + data.data.seat_number : ''}</div>
                            <div><strong>Check-in:</strong> ${data.data.checked_in_at}</div>
                        </div>
                        <div class="text-xs text-gray-400 mt-1">Tiempo de verificación: ${elapsed} ms</div>
                    </div>
                `);
            } else if (res.ok && data.status === 'already') {
                feedback('warning');
                showResult(`
                    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded animate-fadeIn">
                        <strong class="text-yellow-700">⚠ ${data.message}</strong>
                        <div class="mt-2 text-sm text-gray-700">
                            <div><strong>Nombre:</strong> ${data.data.name}</div>
                            <div><strong>Asiento:</strong> ${data.data.seat_type ?? 'N/A'} ${data.data.seat_number ? ' - ' + data.data.seat_number : ''}</div>
                            <div><strong>Último check-in:</strong> ${data.data.checked_in_at}</div>
                        </div>
                    </div>
                `);
            } else {
                feedback('error');
                showResult(`
                    <div class="p-4 bg-red-50 border border-red-200 rounded animate-fadeIn">
                        <strong class="text-red-700">✖ ${data.message || 'Error al verificar'}</strong>
                    </div>
                `);
            }
        } catch (e) {
            feedback('error');
            showResult(`
                <div class="p-4 bg-red-50 border border-red-200 rounded animate-fadeIn">
                    <strong class="text-red-700">✖ Error en la petición: ${e.message}</strong>
                </div>
            `);
        }
    }

    // --- Input manual ---
    manualInput?.addEventListener('keyup', function (e) {
        if (e.key === 'Enter') {
            const code = manualInput.value.trim();
            if (code) {
                postVerify(code);
                manualInput.value = '';
            }
        }
    });

    // --- Cámara ---
    openScannerBtn?.addEventListener('click', async function () {
        if (isScanning) return;
        qrReaderContainer.classList.remove('hidden');

        if (typeof Html5Qrcode === 'undefined') {
            await loadScript('https://unpkg.com/html5-qrcode');
        }

        html5QrCode = new Html5Qrcode(qrReaderId);

        try {
            await html5QrCode.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: 250 },
                (decodedText) => {
                    postVerify(decodedText);
                    html5QrCode.stop();
                    isScanning = false;
                    qrReaderContainer.classList.add('hidden');
                }
            );
            isScanning = true;
        } catch (err) {
            feedback('error');
            showResult(`<div class="p-4 bg-red-50 border border-red-200 rounded"><strong class="text-red-700">No se pudo acceder a la cámara: ${err.message}</strong></div>`);
        }
    });

    closeScannerBtn?.addEventListener('click', async function () {
        if (html5QrCode && isScanning) {
            await html5QrCode.stop();
        }
        isScanning = false;
        qrReaderContainer.classList.add('hidden');
    });

    // --- util para cargar librería ---
    function loadScript(src) {
        return new Promise((resolve, reject) => {
            const s = document.createElement('script');
            s.src = src;
            s.onload = resolve;
            s.onerror = reject;
            document.head.appendChild(s);
        });
    }

    // --- animaciones CSS básicas ---
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn { from {opacity:0; transform:scale(0.95);} to {opacity:1; transform:scale(1);} }
        .animate-fadeIn { animation: fadeIn 0.3s ease-out; }
    `;
    document.head.appendChild(style);

    // Inicializa sonidos
    initSounds();
});
