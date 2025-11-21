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
        successSound = new Audio('/sounds/success.wav');
        errorSound = new Audio('/sounds/error.mp3');
        warnigSound = new Audio('/sounds/warning.wav');
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
        if (type === 'warning' && warnigSound) warnigSound.play().catch(() => {});
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
                     <div class="p-5 bg-green-50 border border-green-300 rounded-xl shadow-sm animate-fadeIn">
                        <!-- Título / estado -->
                        <div class="flex justify-center gap-2 text-green-700 font-bold text-lg items-center">
                            <span>${data.message}</span>
                        </div>


                        <!-- Ubicación destacada -->
                        <div class="mt-4 flex flex-col items-center">
                            <div class="text-xs text-gray-600 uppercase tracking-wide">
                                Ubicación asignada
                            </div>

                            <div class="mt-1 px-5 py-2 bg-green-600 text-white rounded-lg text-xl font-semibold shadow">
                                ${data.data.seat_type ?? 'General'}
                                ${data.data.seat_number ? ' - ' + data.data.seat_number : ''}
                            </div>
                        </div>

                        <div class="mt-4 text-gray-800 text-2xl font-semibold text-center">
                            ${data.data.name}
                        </div>

                        <!-- Datos del usuario -->
                        <div class="mt-5 text-gray-700 text-sm space-y-2">
                            <div><span class="font-medium">Email:</span> ${data.data.email}</div>
                            <div><span class="font-medium">Check-in:</span> ${data.data.checked_in_at}</div>
                        </div>

                        <!-- Tiempo -->
                        <div class="text-xs text-gray-400 mt-3 text-right">
                            Tiempo: ${elapsed}ms
                        </div>
                    </div>
                `);
            } else if (res.ok && data.status === 'already') {
                feedback('warning');
                showResult(`
                     <div class="p-5 bg-yellow-50 border border-yellow-300 rounded-xl shadow-sm animate-fadeIn">
                    <!-- Título del warning -->
                    <div class="flex items-center gap-2 text-yellow-700 font-bold text-lg justify-center">
                        <span class="text-2xl">⚠</span>
                        <span>${data.message}</span>
                    </div>

                    <!-- Nombre destacado -->
                    <div class="mt-4 text-gray-800 text-xl font-semibold text-center">
                        ${data.data.name}
                    </div>

                    <!-- Información -->
                    <div class="mt-3 text-gray-800 text-sm text-center space-y-1">
                        <div>
                            <span class="font-semibold">Asiento:</span>
                            ${data.data.seat_type ?? 'N/A'}${data.data.seat_number ? ' - ' + data.data.seat_number : ''}
                        </div>

                        <div>
                            <span class="font-semibold">Último check-in:</span>
                            ${data.data.checked_in_at ?? '—'}
                        </div>
                    </div>

                    <!-- Línea fina separadora -->
                    <div class="mt-4 border-t border-yellow-200"></div>

                    <div class="text-xs text-gray-400 mt-2 text-center">
                        Este registro ya había ingresado anteriormente.
                    </div>
                </div>
                `);
            } else {
                feedback('error');
                showResult(`
                    <div class="p-5 bg-red-50 border border-red-300 rounded-xl shadow-sm animate-fadeIn">
                    <!-- Header -->
                    <div class="flex items-center gap-3 justify-center text-red-700 font-bold text-lg">
                        <span class="text-2xl">✖</span>
                        <span>${data.message || 'Error al verificar'}</span>
                    </div>

                    <!-- Mensaje secundario -->
                    <div class="mt-3 text-sm text-red-700 text-center">
                        El ticket no es válido o no pertenece a este evento.
                    </div>

                    <!-- Información adicional si existe -->
                    ${data.data && (data.data.error_detail || data.data.hint) ? `
                        <div class="mt-4 text-xs text-gray-600 text-center">
                            ${data.data.error_detail ? `<div><strong>Detalle:</strong> ${data.data.error_detail}</div>` : ''}
                            ${data.data.hint ? `<div class="mt-1">${data.data.hint}</div>` : ''}
                        </div>
                    ` : ''}

                    <div class="mt-4 text-xs text-gray-400 text-center">
                        Verifique nuevamente el código.
                    </div>

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
