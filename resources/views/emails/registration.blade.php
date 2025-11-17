<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tu Entrada Digital</title>
</head>

<body style="margin:0; padding:0; background:#f3f4f6; font-family: Arial, sans-serif;">

    <div style="max-width:650px; margin:auto; padding:40px 0;">

        <!-- CARD -->
        <div style="
            background:white;
            border-radius:20px;
            overflow:hidden;
            box-shadow:0 8px 28px rgba(0,0,0,0.10);
        ">

            <!-- HEADER -->
            <div style="
                background:linear-gradient(135deg,#6366f1,#312e81);
                padding:35px 25px;
                color:white;
                text-align:center;
            ">
                <h1 style="margin:0; font-size:30px; font-weight:bold; letter-spacing:.8px;">
                     SPECTRUM TICKETS
                </h1>
                <p style="margin:10px 0 0; font-size:14px; opacity:0.9;">
                    Tu boleta digital se generó con éxito
                </p>
            </div>

            <!-- CONTENT -->
            <div style="padding:35px 30px 40px;">

                <!-- EVENT NAME -->
                <h2 style="
                    margin:0;
                    font-size:26px;
                    color:#1e293b;
                    text-align:center;
                    font-weight:bold;
                ">
                    {{ $registration->celebration->name }}
                </h2>

                <p style="
                    text-align:center;
                    margin-top:8px;
                    font-size:15px;
                    color:#64748b;
                ">
                    Entrada registrada para <strong>{{ $registration->name }}</strong>
                </p>

                <!-- DIVIDER -->
                <div style="width:100%; height:1px; background:#e2e8f0; margin:30px 0;"></div>

                <!-- DETAILS GRID -->
                <div style="display:flex; justify-content:space-between; flex-wrap:wrap;">

                    <div style="width:48%; margin-bottom:20px;">
                        <p style="margin:0; font-size:12px; color:#94a3b8;">Nombre del asistente</p>
                        <p style="margin:0; font-size:17px; font-weight:600; color:#1e293b;">
                            {{ $registration->name }}
                        </p>
                    </div>

                    @if($registration->seat_type || $registration->seat_number)
                    <div style="width:48%; margin-bottom:20px;">
                        <p style="margin:0; font-size:12px; color:#94a3b8;">Ubicación asignada</p>
                        <p style="margin:0; font-size:17px; font-weight:600; color:#1e293b;">
                            {{ $registration->seat_type ?? 'General' }}
                            @if($registration->seat_number)
                                — {{ $registration->seat_number }}
                            @endif
                        </p>
                    </div>
                    @endif

                    <div style="width:48%; margin-bottom:20px;">
                        <p style="margin:0; font-size:12px; color:#94a3b8;">Fecha y hora</p>
                        <p style="margin:0; font-size:17px; font-weight:600; color:#1e293b;">
                            {{ \Carbon\Carbon::parse($registration->celebration->start_date)->format('d M Y • h:i A') }}
                        </p>
                    </div>

                    <div style="width:48%; margin-bottom:20px;">
                        <p style="margin:0; font-size:12px; color:#94a3b8;">Lugar del evento</p>
                        <p style="margin:0; font-size:17px; font-weight:600; color:#1e293b;">
                            {{ $registration->celebration->location }}
                        </p>
                    </div>

                </div>

                <!-- QR BOX -->
                <div style="
                    margin:45px auto;
                    text-align:center;
                    padding:25px 20px;
                    border-radius:16px;
                    border:2px dashed #cbd5e1;
                    background:#f8fafc;
                    max-width:350px;
                ">
                    <img src="{{ $qrDataUri }}" alt="QR Code"
                         style="width:230px; height:auto; margin-bottom:10px;">
                    <p style="margin:0; font-size:14px; color:#475569;">
                        Presenta este código QR al ingresar al evento
                    </p>
                </div>

                <!-- EXTRA -->
                <p style="text-align:center; font-size:13px; color:#64748b; margin-top:25px;">
                    Ticket ID: <strong>{{ $registration->token }}</strong>
                </p>

            </div>

            <!-- FOOTER -->
            <div style="
                background:#0f172a;
                color:#cbd5e1;
                text-align:center;
                padding:18px 10px;
                font-size:12px;
                letter-spacing:.4px;
            ">
                {{ config('app.name') }} • Acceso Digital a Eventos  
                <div style="margin-top:6px; font-size:11px; opacity:.7;">
                    Por favor no respondas a este correo. Se generó automáticamente.
                </div>
            </div>

        </div>
    </div>

</body>
</html>
