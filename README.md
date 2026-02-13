# Event Management Platform

Platforma do zarządzania wydarzeniami z możliwością rejestracji uczestników, sprzedaży biletów i integracji z zewnętrznymi serwisami.

## 🚀 Stack Technologiczny

- **Backend**: Laravel 11
- **Frontend**: Vue 3 (Composition API) + Inertia.js
- **Styling**: TailwindCSS
- **Autentykacja**: Laravel Breeze (Inertia + Vue)

## 📋 Funkcjonalności

### Dla Organizatora:
- Tworzenie i zarządzanie wydarzeniami
- System rejestracji uczestników
- Check-in przez QR code
- Integracje: Google Calendar, Stripe, SendGrid, Twilio

### Dla Uczestnika:
- Przeglądanie wydarzeń
- Rejestracja na wydarzenia
- Płatności przez Stripe
- Powiadomienia email/SMS

## 🔌 Integracje API

- **Google Calendar** - synchronizacja wydarzeń
- **Stripe** - płatności za bilety
- **SendGrid** - powiadomienia email
- **Twilio** - powiadomienia SMS

## 📦 Instalacja

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run dev
```

## 🌿 Branching Strategy

- `main` - produkcja
- `develop` - główna gałąź deweloperska
- `feature/*` - funkcje

## 📝 Dokumentacja

Szczegółowy plan projektu znajduje się w `PROJECT_PLAN.md` w folderze nadrzędnym.

