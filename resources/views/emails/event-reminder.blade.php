<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Przypomnienie</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background-color: #f4f4f5; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 15px; color: #18181b; }
        .wrapper { max-width: 600px; margin: 32px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .header { background-color: #18181b; padding: 28px 36px; }
        .header h1 { color: #ffffff; font-size: 20px; font-weight: 600; }
        .body { padding: 32px 36px; }
        .greeting { font-size: 17px; font-weight: 600; margin-bottom: 12px; }
        .intro { color: #52525b; line-height: 1.6; margin-bottom: 24px; }
        .event-card { border: 1px solid #e4e4e7; border-radius: 8px; padding: 20px 24px; margin-bottom: 24px; }
        .event-title { font-size: 17px; font-weight: 700; margin-bottom: 14px; }
        .detail { display: flex; gap: 10px; margin-bottom: 8px; align-items: flex-start; }
        .detail-label { color: #71717a; font-size: 13px; min-width: 110px; }
        .detail-value { font-size: 14px; color: #18181b; font-weight: 500; }
        .btn { display: inline-block; padding: 12px 24px; background-color: #18181b; color: #ffffff; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 600; }
        .footer { padding: 20px 36px 28px; }
        .divider { border: none; border-top: 1px solid #f1f1f1; margin-bottom: 20px; }
        .footer p { color: #a1a1aa; font-size: 12px; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Event Platform</h1>
        </div>
        <div class="body">
            <p class="greeting">Cześć, {{ $registration->first_name }}!</p>
            <p class="intro">
                @if($variant === '24h')
                    To przypomnienie: jutro o tej porze (w przybliżeniu) odbędzie się wydarzenie, na które zapisałeś się wcześniej.
                @else
                    Za około godzinę rozpocznie się wydarzenie, na które jesteś zapisany. Nie zapomnij o bilecie z kodem QR.
                @endif
            </p>

            <div class="event-card">
                <p class="event-title">{{ $registration->event->title }}</p>
                <div class="detail">
                    <span class="detail-label">Start</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($registration->event->start_date)->format('d.m.Y, H:i') }}</span>
                </div>
                <div class="detail">
                    <span class="detail-label">Miejsce</span>
                    <span class="detail-value">
                        {{ $registration->event->venue_name }}@if($registration->event->venue_city), {{ $registration->event->venue_city }}@endif
                    </span>
                </div>
            </div>

            <a href="{{ route('registrations.show', ['registration' => $registration->id, 'token' => $registration->qr_code]) }}" class="btn">
                Otwórz bilet →
            </a>
        </div>
        <div class="footer">
            <hr class="divider" />
            <p>Wiadomość wysłana automatycznie przez Event Platform.</p>
        </div>
    </div>
</body>
</html>
