<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar tickets – {{ $celebration->name }}</title>

    <!-- Tailwind desde CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            600: '#4f46e5',
                            700: '#4338ca',
                        },
                    },
                },
            },
        }
    </script>
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-white shadow-lg rounded-xl p-6 space-y-6">

        <!-- Título -->
        <h2 class="text-2xl font-bold text-gray-800 text-center">
            Verificar tickets – {{ $celebration->name }}
        </h2>

        <!-- Información de la celebración -->
        <div class="p-4 bg-gray-100 rounded-lg space-y-1 text-sm">
            <p><span class="font-semibold">Agencia:</span> {{ $celebration->agency->name }}</p>
            <p><span class="font-semibold">Fecha:</span> {{ $celebration->start_date }}</p>
        </div>

        <!-- Campo de texto para lectores USB -->
        <div class="space-y-2">
            <label for="manualCodeInput" class="block text-sm font-medium text-gray-700">
                Escanear código (lector USB o ingreso manual)
            </label>

            <input
                id="manualCodeInput"
                type="text"
                placeholder="Pase el lector o escriba el código y presione Enter"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-primary-600 focus:ring focus:ring-primary-600/30"
            >
        </div>

        <!-- Botones de cámara -->
        <div class="flex gap-2">
            <button
                id="openScannerBtn"
                class="flex-1 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200"
            >
                Escanear con cámara
            </button>

            <button
                id="closeScannerBtn"
                class="flex-1 py-3 bg-gray-300 rounded-lg hover:bg-gray-400 transition-colors duration-200 hidden"
            >
                Cerrar cámara
            </button>
        </div>

        <!-- Contenedor para la cámara -->
        <div id="qrReaderContainer" class="hidden mt-4">
            <div id="qr-reader"></div>
        </div>

        <!-- Resultado -->
        <div id="resultBox" class="hidden mt-6 text-center text-sm font-medium"></div>
    </div>

    <!-- Configuración global para verify.js -->
    <script>
        window.__VERIFY_CONFIG = {
            verifyUrl: "{{ route('celebrations.verify.submit', $celebration) }}",
            token: "{{ csrf_token() }}"
        };
    </script>

    <!-- Archivo JS externo con sonidos y vibración -->
    <script src="{{ asset('js/qrs/verify.js') }}"></script>
</body>
</html>
