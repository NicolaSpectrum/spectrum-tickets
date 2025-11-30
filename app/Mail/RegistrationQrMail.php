<?php

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage; 

class RegistrationQrMail extends Mailable
{
    use Queueable, SerializesModels;

    public Registration $registration;

    public function __construct(Registration $registration)
    {
        $this->registration = $registration;
    }

    public function build()
    {
        // Leer archivo desde storage/app/public
        $fileData = Storage::disk('public')->get($this->registration->qr_path);

        // Convertir a Base64
        $base64 = base64_encode($fileData);
        $dataUri = "data:image/png;base64,$base64";

        return $this->subject('Tu Ticket De Entrada')
            ->view('emails.registration')
            ->with([
                'registration' => $this->registration,
                'qrDataUri' => $dataUri,
            ]);
    }
}
