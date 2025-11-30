<?php

namespace App\Filament\Resources\RegistrationResource\Pages;

use App\Filament\Resources\RegistrationResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegistrationQrMail;
use App\Services\QrService;

class CreateRegistration extends CreateRecord
{
    protected static string $resource = RegistrationResource::class;
    
    public function getTitle(): string
    {
        return 'Crear Ticket';
    }
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()->label('Guardar'),
            $this->getCreateAnotherFormAction()->label('Guardar y crear otro evento'),
            $this->getCancelFormAction()->label('Cancelar'),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generamos token único
        $data['token'] = Str::uuid();
        return $data;
    }

    protected function afterCreate(): void
    {
        $registration = $this->record;

        /** @var QrService $qrService */
        $qrService = app(QrService::class);

        // Generar QR enriquecido en PNG
        $qrPng = $qrService->generateRegistrationQr($registration);

        // Guardarlo físicamente (opcional pero recomendado)
        $path = 'qrs/' . $registration->token . '.png';
        Storage::disk('public')->put($path, $qrPng);

        // Guardar la ruta en DB
        $registration->update([
            'qr_path' => $path
        ]);

        // Enviar correo
        Mail::to($registration->email)->send(
            new RegistrationQrMail($registration)
        );
    }


}
