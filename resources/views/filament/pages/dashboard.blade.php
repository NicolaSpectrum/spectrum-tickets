<x-filament-panels::page>
    <div class="flex flex-col gap-6">
        
        {{-- Header Premium --}}
        <div class="p-6 rounded-2xl bg-gradient-to-br from-indigo-600 via-indigo-500 to-indigo-700 text-white shadow-lg">
            <h1 class="text-3xl font-bold">Panel de Control</h1>
            <p class="text-indigo-100 mt-1">
                Bienvenido de nuevo, {{ auth()->user()->name }}.
            </p>
        </div>

        {{-- Contenido de widgets --}}

    </div>
</x-filament-panels::page>
