# Event Management Platform

Platforma do zarządzania wydarzeniami z możliwością rejestracji uczestników, sprzedaży biletów i integracji z zewnętrznymi serwisami.

## 🚀 Stack Technologiczny

- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Vue 3 (Composition API) + Inertia 2 + TypeScript
- **Styling**: Tailwind CSS 4
- **Komponenty UI**: shadcn-vue
- **Autentykacja**: Laravel Starter Kit (Vue) – Fortify pod spodem
- **Baza danych**: MySQL/PostgreSQL
- **Kolejki**: Laravel Queue

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

Aplikacja zakładana jest przez oficjalny Laravel installer z starter kit **Vue** (Inertia 2, TypeScript, shadcn-vue):

```bash
# W katalogu nadrzędnym – tworzenie projektu z wyborem Vue
laravel new event_platform

# W katalogu projektu
cd event_platform
npm install && npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate
composer run dev
```

## 🌿 Branching Strategy

- `main` - produkcja
- `develop` - główna gałąź deweloperska
- `feature/*` - funkcje

## 📝 Dokumentacja

Szczegółowy plan projektu znajduje się w `PROJECT_PLAN.md` w folderze nadrzędnym.

