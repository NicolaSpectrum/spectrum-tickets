<?php

namespace App\Http\Controllers;

use App\Models\Celebration;
use App\Models\Registration;
use Illuminate\Http\Request;
use Filament\Pages\Page;

class VerifyCelebrationController extends Controller
{
    // Mostrar la vista de verificación para la celebración específica
    public function show(Celebration $celebration)
    {
        // Verificar que el verificador pertenece a la misma agency (solo si aplica)
        $user = auth()->user();
        if ($user->hasRole('verifier') && $user->agency_id && $celebration->agency_id !== $user->agency_id) {
            abort(403, 'No autorizado para verificar este evento');
        }

        return view('celebrations.verify', [
            'celebration' => $celebration,
        ]);
    }

    // Endpoint que recibe el payload del QR (token o JSON) y realiza el check-in
    public function verify(Request $request, Celebration $celebration)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

         $rawCode = $request->input('code');
         $data = json_decode($rawCode, true);
         $token = $data['token'] ?? $rawCode;

         $registration = Registration::where('token', $token)
            ->where('celebration_id', $celebration->id)
            ->first();

        if (! $registration) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ticket no encontrado para este evento'
            ], 404);
        }

        // Validación de agencia por seguridad extra
        $user = auth()->user();
        if ($user->hasRole('verifier') && $user->agency_id && $registration->celebration->agency_id !== $user->agency_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autorizado para verificar tickets de otra agencia'
            ], 403);
        }

        // Si ya se hizo check-in
        if ($registration->checked_in) {
            return response()->json([
                'status' => 'already',
                'message' => 'Este ticket ya fue usado',
                'data' => [
                    'name' => $registration->name,
                    'seat_type' => $registration->seat_type,
                    'seat_number' => $registration->seat_number,
                    'checked_in_at' => $registration->checked_in_at,
                    'verified_by' => $registration->verified_by,
                ],
            ], 200);
        }

        // Marcar check-in
        $registration->update([
            'checked_in' => true,
            'checked_in_at' => now(),
            'verified_by' => $user->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Acceso permitido',
            'data' => [
                'name' => $registration->name,
                'email' => $registration->email,
                'seat_type' => $registration->seat_type,
                'seat_number' => $registration->seat_number,
                'checked_in_at' => $registration->checked_in_at,
            ],
        ], 200);
    }
}
