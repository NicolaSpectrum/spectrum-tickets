<x-filament-panels::page>

    <div class="space-y-6">

        {{-- Selección de celebración --}}
        <div class="space-y-2">
            <label class="text-sm font-medium text-gray-700">
                Selecciona la celebración
            </label>

            <select
                id="celebrationSelect"
                class="w-full px-4 py-2 border rounded-lg focus:border-primary-500 focus:ring-primary-500"
            >
                <option value="">Seleccione una celebración...</option>

                @foreach ($celebrations as $celebration)
                    <option value="{{ $celebration->id }}">
                        {{ $celebration->name }} — {{ \Carbon\Carbon::parse($celebration->start_date)->format('d M Y H:i') }}
                    </option>
                @endforeach
            </select>
        </div>

        <h2 class="text-xl font-bold text-gray-800">
            Verificación de QR
        </h2>

        {{-- INPUT manual --}}
        <div class="space-y-2">
            <label class="text-sm font-medium text-gray-700">
                Escanear código (lector USB o manual)
            </label>

            <input
                id="manualCodeInput"
                type="text"
                placeholder="Enfoca el lector o escribe el código"
                class="w-full px-4 py-2 border rounded-lg focus:border-primary-500 focus:ring-primary-500"
                onkeyup="handleManualInput(event)"
            >
        </div>

        {{-- BOTÓN activar cámara --}}
        <button
            onclick="startCameraScanner()"
            class="w-full py-3 mt-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700"
        >
            Escanear con cámara
        </button>

        <div id="qr-reader" class="mt-4 hidden"></div>

        <div
            id="scanResult"
            class="mt-6 p-4 text-center text-lg font-semibold text-gray-800 border rounded-lg hidden">
        </div>
    </div>

    {{-- Scripts (DENTRO del componente, único root) --}}
    <script src="https://unpkg.com/html5-qrcode"></script>

    <script>
        let isScanning = false;
        let html5QrCode = null;

        function handleManualInput(e) {
            if (e.key === 'Enter') {
                verifyCode(e.target.value.trim());
            }
        }

        function startCameraScanner() {
            if (isScanning) return;

            document.getElementById('qr-reader').classList.remove('hidden');

            html5QrCode = new Html5Qrcode("qr-reader");

            html5QrCode.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                qrCodeMessage => {
                    verifyCode(qrCodeMessage);
                    stopCameraScanner();
                }
            ).then(() => isScanning = true)
             .catch(() => alert("No se pudo acceder a la cámara."));
        }

        function stopCameraScanner() {
            if (!html5QrCode) return;

            html5QrCode.stop().then(() => {
                isScanning = false;
                document.getElementById('qr-reader').classList.add('hidden');
            });
        }

        async function verifyCode(code) {
            const celebrationId = document.getElementById('celebrationSelect').value;

            if (!celebrationId) {
                alert('Selecciona una celebración antes de escanear.');
                return;
            }

            try {
                const res = await fetch("{{ route('verify.qr') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        code,
                        celebration_id: celebrationId
                    })
                });

                const data = await res.json();
                const resultBox = document.getElementById('scanResult');
                resultBox.classList.remove('hidden');

                if (data.status === "success") {
                    resultBox.innerHTML = `
                        <div class="text-green-700">✅ ${data.message}</div>
                        <div class="mt-2 text-sm text-gray-600">
                            <strong>Nombre:</strong> ${data.data.name} <br>
                            <strong>Tipo:</strong> ${data.data.seat_type ?? 'N/A'} <br>
                            <strong>Asiento:</strong> ${data.data.seat_number ?? 'N/A'} <br>
                            <strong>Check-in:</strong> ${data.data.checked_in_at ?? 'N/A'}
                        </div>
                    `;
                } else {
                    resultBox.innerHTML = `<div class="text-red-700">❌ ${data.message}</div>`;
                }
            } catch (e) {
                alert('Error en la petición: ' + e.message);
            }
        }
    </script>

</x-filament-panels::page>
