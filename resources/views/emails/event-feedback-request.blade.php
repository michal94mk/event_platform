<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Feedback</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background-color: #f4f4f5; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 15px; color: #18181b; }
        .wrapper { max-width: 600px; margin: 32px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .header { background-color: #18181b; padding: 28px 36px; }
        .header h1 { color: #ffffff; font-size: 20px; font-weight: 600; }
        .body { padding: 32px 36px; }
        .greeting { font-size: 17px; font-weight: 600; margin-bottom: 12px; }
        .intro { color: #52525b; line-height: 1.6; margin-bottom: 24px; }
        .event-title { font-size: 17px; font-weight: 700; margin-bottom: 8px; }
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
                Wydarzenie „{{ $registration->event->title }}” już się zakończyło. Dziękujemy za udział!
                Jeśli masz chwilę, napisz do organizatora – Twoja opinia jest dla nas ważna.
            </p>
            <p class="event-title">{{ $registration->event->title }}</p>
            <p style="color: #52525b; font-size: 14px; font-style: italic;">
                (W przyszłości tutaj może pojawić się link do ankiety.)
            </p>
        </div>
        <div class="footer">
            <hr class="divider" />
            <p>Wiadomość wysłana automatycznie przez Event Platform.</p>
        </div>
    </div>
</body>
</html>
