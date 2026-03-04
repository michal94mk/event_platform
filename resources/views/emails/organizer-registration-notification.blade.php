<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nowa rejestracja na wydarzenie</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background-color: #f4f4f5; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 15px; color: #18181b; }
        .wrapper { max-width: 600px; margin: 32px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .header { background-color: #18181b; padding: 24px 32px; }
        .header h1 { color: #ffffff; font-size: 18px; font-weight: 600; letter-spacing: -0.3px; }
        .body { padding: 28px 32px; }
        .intro { color: #52525b; line-height: 1.6; margin-bottom: 20px; }
        .event-title { font-size: 16px; font-weight: 700; margin-bottom: 8px; }
        .detail { display: flex; gap: 10px; margin-bottom: 6px; align-items: flex-start; }
        .detail-label { color: #71717a; font-size: 13px; min-width: 110px; padding-top: 1px; }
        .detail-value { font-size: 14px; color: #18181b; font-weight: 500; }
        .footer { padding: 18px 32px 24px; }
        .divider { border: none; border-top: 1px solid #f1f1f1; margin-bottom: 16px; }
        .footer p { color: #a1a1aa; font-size: 12px; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Nowa rejestracja na wydarzenie</h1>
        </div>
        <div class="body">
            <p class="intro">
                Na Twoje wydarzenie pojawiła się nowa rejestracja. Poniżej znajdziesz najważniejsze szczegóły.
            </p>

            <p class="event-title">{{ $registration->event->title }}</p>

            <div class="detail">
                <span class="detail-label">Data</span>
                <span class="detail-value">
                    {{ \Carbon\Carbon::parse($registration->event->start_date)->format('d.m.Y, H:i') }}
                </span>
            </div>

            <div class="detail">
                <span class="detail-label">Uczestnik</span>
                <span class="detail-value">
                    {{ $registration->first_name }} {{ $registration->last_name }} ({{ $registration->email }})
                </span>
            </div>

            <div class="detail">
                <span class="detail-label">Liczba biletów</span>
                <span class="detail-value">
                    {{ $registration->ticket_quantity }}
                </span>
            </div>

            <div class="detail">
                <span class="detail-label">Kwota</span>
                <span class="detail-value">
                    @if(floatval($registration->total_amount) > 0)
                        {{ number_format($registration->total_amount, 2) }} {{ $registration->event->currency ?? 'PLN' }}
                    @else
                        Wstęp wolny
                    @endif
                </span>
            </div>

            <p class="intro" style="margin-top: 16px;">
                Pełne szczegóły rejestracji znajdziesz w panelu Event Platform w sekcji „Rejestracje” lub na stronie check-in danego wydarzenia.
            </p>
        </div>
        <div class="footer">
            <hr class="divider" />
            <p>
                Wiadomość wysłana automatycznie przez Event Platform.<br />
                Prosimy nie odpowiadać na tego maila.
            </p>
        </div>
    </div>
</body>
</html>

