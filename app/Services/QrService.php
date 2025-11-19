<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrService
{
    
    public function generateRegistrationQr($registration)
    {
        // Construimos la data del QR
        $payload = [
            'token'        => $registration->token,
            'registration' => [
                'name'       => $registration->name,
                'seat_type'  => $registration->seat_type,
                'seat_number'=> $registration->seat_number,
            ],
            'celebration' => [
                'id'          => $registration->celebration->id,
                'name'        => $registration->celebration->name,
                'date'        => $registration->celebration->start_date,
                'location'    => $registration->celebration->location,
            ],
            'issued_at' => now()->toDateTimeString(),
        ];

        // Convertimos a JSON
        $jsonPayload = json_encode($payload);

        // Generamos el QR
        return QrCode::format('png')
            ->size(300)
            ->margin(2)
            ->generate($jsonPayload);
    }
}
