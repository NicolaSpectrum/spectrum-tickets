<?php

namespace App\Filament\Resources\RegistrationResource\Pages;

use App\Filament\Resources\RegistrationResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use App\Mail\RegistrationQrMail;
use Illuminate\Support\Facades\Mail;

class CreateRegistration extends CreateRecord
{
    protected static string $resource = RegistrationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Crear token
        $data['token'] = Str::uuid();

        return $data;
    }

    protected function afterCreate(): void
    {
        $registration = $this->record;

        // Crear QR que contiene el token
        $qr = QrCode::format('png')
            ->size(300)
            ->generate($registration->token);

        // Guardar QR
        $path = 'qrs/' . $registration->token . '.png';
        Storage::disk('public')->put($path, $qr);

        $registration->update(['qr_path' => $path]);

        // Enviar email con QR adjunto
        Mail::to($registration->email)->send(new RegistrationQrMail($registration));
    }
}
