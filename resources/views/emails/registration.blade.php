    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
        <title>Tu Entrada Digital</title>

        <style>
            @media only screen and (max-width: 480px) {
                h1 { font-size: 22px !important; }
                h2 { font-size: 22px !important; }
                .content-padding { padding: 25px !important; }
                .details-table td { 
                    display: block !important;
                    width: 100% !important;
                    padding-bottom: 15px !important;
                }
                .qr-box { padding: 20px !important; }
            }
        </style>
    </head>

    <body style="margin:0; padding:0; background:#f1f5f9; font-family:Arial, sans-serif;">

    <span style="display:none;color:transparent;height:0;overflow:hidden;">
        Tu entrada digital para {{ $registration->celebration->name }}
    </span>

    <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#f1f5f9">
        <tr>
            <td align="center" style="padding:40px 0;">

                <!-- MAIN WRAPPER -->
                <table width="100%" cellspacing="0" cellpadding="0" 
                    style="max-width:650px;width:100%;background:#ffffff;
                        border-radius:20px;overflow:hidden;
                        box-shadow:0 4px 20px rgba(0,0,0,0.08);">

                    <!-- HEADER -->
                    <tr>
                        <td align="center"
                            style="background:linear-gradient(135deg,#4a00ff,#001f6b);
                                padding:40px 25px;color:#ffffff;">
                            <h1 style="margin:0;font-size:28px;font-weight:800;letter-spacing:0.5px;">
                                SPECTRUM TICKETS
                            </h1>
                        </td>
                    </tr>

                    <!-- CONTENT -->
                    <tr>
                        <td class="content-padding" style="padding:40px 30px;">

                            <h2 style="margin:0;font-size:28px;color:#0f172a;font-weight:800;text-align:center;">
                                {{ strtoupper($registration->celebration->name) }}
                            </h2>

                            <p style="text-align:center;margin-top:10px;font-size:16px;color:#475569;">
                                Entrada registrada para:<br>
                                <strong style="font-size:26px;color:#0f172a;">{{ $registration->name }}</strong>
                            </p>

                            <div style="width:100%;height:1px;background:#e2e8f0;margin:30px 0;"></div>

                            <!-- DETAILS -->
                            <table class="details-table" width="100%" cellspacing="0" cellpadding="0"
                                style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:16px;">
                                
                                <tr>
                                    <td style="padding:15px;width:50%;">
                                        <p style="margin:0;font-size:12px;color:#94a3b8;text-transform:uppercase;">
                                            Nombre del asistente
                                        </p>
                                        <p style="margin:2px 0 0;font-size:18px;font-weight:600;color:#1e293b;">
                                            {{ $registration->name }}
                                        </p>
                                    </td>

                                    <td style="padding:15px;width:50%;">
                                        <p style="margin:0;font-size:12px;color:#94a3b8;text-transform:uppercase;">
                                            Ubicación asignada
                                        </p>
                                        <span style="display:inline-block;padding:6px 12px;
                                                    background:#e8eeff;color:#001b86;font-weight:700;
                                                    border-radius:8px;font-size:18px;margin-top:4px;">
                                            {{ $registration->seat_type ?? 'General' }}
                                            @if($registration->seat_number)
                                                — {{ $registration->seat_number }}
                                            @endif
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:15px;width:50%;">
                                        <p style="margin:0;font-size:12px;color:#94a3b8;text-transform:uppercase;">
                                            Lugar del evento
                                        </p>
                                        <p style="margin:2px 0 0;font-size:18px;font-weight:600;color:#1e293b;">
                                            {{ $registration->celebration->location }}
                                        </p>
                                    </td>

                                    <td style="padding:15px;width:50%;">
                                        <p style="margin:0;font-size:12px;color:#94a3b8;text-transform:uppercase;">
                                            Fecha y hora
                                        </p>
                                        <p style="margin:2px 0 0;font-size:18px;font-weight:600;color:#1e293b;">
                                            {{ \Carbon\Carbon::parse($registration->celebration->start_date)->format('d M Y • h:i A') }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- QR -->
                            <table width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center">
                                        <table class="qr-box" cellspacing="0" cellpadding="20"
                                            style="margin:45px auto 20px;background:#ffffff;
                                                    border:1px solid #e2e8f0;border-radius:18px;
                                                    max-width:360px;">
                                            <tr>
                                                <td align="center">
                                                    <img src="{{ $message->embed(Storage::disk('public')->path($registration->qr_path)) }}" 
                                                        alt="Código QR" 
                                                        style="max-width:260px;width:100%;height:auto;display:block;margin-bottom:15px;">
                                                    <p style="margin:0;font-size:14px;color:#475569;">
                                                        Presenta este código QR para ingresar al evento
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <p style="text-align:center;font-size:13px;color:#64748b;margin-top:25px;">
                                ID del Ticket:
                                <strong style="color:#334155;">{{ $registration->token }}</strong>
                            </p>

                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td align="center"
                            style="background:#0f172a;color:#e2e8f0;padding:20px 10px;font-size:12px;
                                border-bottom-left-radius:20px;border-bottom-right-radius:20px;">
                            {{ config('app.name') }} <br>
                            Boletería virtual con gestión de accesos a eventos<br>
                            WhatsApp: 3164054975
                            <div style="margin-top:6px;font-size:11px;opacity:.7;">
                                Este es un correo automático. Por favor, no responder.
                            </div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

    </body>
    </html>
