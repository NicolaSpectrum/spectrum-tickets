<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Boarding Pass</title>
</head>
<body style="margin:0; padding:0; background:#eef1f5; font-family: 'Arial', sans-serif;">

    <div style="max-width:650px; margin:auto; padding:30px 0;">

        <!-- CARD -->
        <div style="
            background:white;
            border-radius:18px;
            overflow:hidden;
            box-shadow:0 4px 20px rgba(0,0,0,0.08);
        ">

            <!-- HEADER -->
            <div style="
                background:linear-gradient(135deg, #3b82f6, #1e3a8a);
                padding:25px;
                color:white;
                text-align:center;
            ">
                <h1 style="margin:0; font-size:26px; letter-spacing:1px;">
                    SPECTRUM TICKETS
                </h1>
                <p style="margin:8px 0 0; opacity:0.9;">
                    Entradas digitales para tus eventos
                </p>
            </div>

            <!-- CONTENT -->
            <div style="padding:30px;">

                <!-- EVENT NAME -->
                <h2 style="margin:0; font-size:22px; color:#1e3a8a; text-align:center;">
                    {{ $registration->celebration->name }}
                </h2>

                <p style="text-align:center; margin-top:5px; font-size:14px; color:#64748b;">
                    Pase de entrada para {{ $registration->name }}
                </p>

                <!-- DIVIDER -->
                <hr style="margin:25px 0; border:0; border-top:1px dashed #cbd5e1;">

                <!-- DETAILS SECTION -->
                <div style="display:flex; justify-content:space-between; flex-wrap:wrap;">

                    <div style="width:48%; margin-bottom:18px;">
                        <p style="margin:0; font-size:12px; color:#94a3b8;">Nombre</p>
                        <p style="margin:0; font-size:16px; font-weight:bold; color:#334155;">
                            {{ $registration->name }}
                        </p>
                    </div>

                    <div style="width:48%; margin-bottom:18px;">
                        <p style="margin:0; font-size:12px; color:#94a3b8;">Email</p>
                        <p style="margin:0; font-size:16px; font-weight:bold; color:#334155;">
                            {{ $registration->email }}
                        </p>
                    </div>

                    <div style="width:48%; margin-bottom:18px;">
                        <p style="margin:0; font-size:12px; color:#94a3b8;">Fecha del evento</p>
                        <p style="margin:0; font-size:16px; font-weight:bold; color:#334155;">
                            {{ $registration->celebration->start_date }}
                        </p>
                    </div>

                    <div style="width:48%; margin-bottom:18px;">
                        <p style="margin:0; font-size:12px; color:#94a3b8;">Ubicación</p>
                        <p style="margin:0; font-size:16px; font-weight:bold; color:#334155;">
                            {{ $registration->celebration->location }}
                        </p>
                    </div>

                </div>

                <!-- QR SECTION -->
                <div style="
                    margin:40px auto;
                    text-align:center;
                    padding:20px;
                    border-radius:12px;
                    border:2px dashed #cbd5e1;
                    background:#f8fafc;
                ">
                    <img src="{{ $qrDataUri }}" alt="QR Code"
                         style="width:220px; height:auto; margin-bottom:10px;">
                    <p style="margin:0; font-size:13px; color:#475569;">
                        Presenta este codigo QR en la Entrada
                    </p>
                </div>

                <!-- FOOTER LINE -->
                <div style="margin-top:30px; text-align:center; font-size:12px; color:#94a3b8;">
                    Ticket ID: <strong>{{ $registration->token }}</strong>
                </div>

            </div>

            <!-- BOTTOM STRIP -->
            <div style="
                background:#1e293b;
                color:white;
                text-align:center;
                padding:12px;
                font-size:12px;
                letter-spacing:0.5px;
            ">
                {{ config('app.name') }} — Digital Event Access
            </div>
        </div>
    </div>

</body>
</html>
