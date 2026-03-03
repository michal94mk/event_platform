<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Calendar, QrCode, Ticket } from 'lucide-vue-next';

interface EventCategory {
    id: number;
    name: string;
    slug: string;
}

interface Event {
    id: number;
    title: string;
    slug: string;
    start_date: string;
    end_date: string;
    venue_name: string;
    venue_city: string | null;
    ticket_price: string | null;
    currency?: string;
    status: string;
    cover_image_url?: string | null;
    user?: { id: number; name: string };
    categories?: EventCategory[];
}

defineProps<{
    featuredEvents: Event[];
    canCreate?: boolean;
}>();

function formatDate(dateStr: string) {
    return new Date(dateStr).toLocaleDateString('pl-PL', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}
</script>

<template>
    <Head title="Event Platform – Twórz wydarzenia, zarządzaj uczestnikami" />

    <div class="min-h-screen bg-[#FDFDFC] dark:bg-[#0a0a0a]">
        <header class="border-b border-[#19140035] px-4 py-3 dark:border-[#3E3E3A]">
            <nav class="mx-auto flex max-w-6xl items-center justify-between">
                <Link :href="route('home')" class="text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                    Event Platform
                </Link>
                <div class="flex flex-wrap items-center gap-4">
                    <Link :href="route('events.index')" class="text-sm text-[#1b1b18] hover:underline dark:text-[#EDEDEC]">
                        Wydarzenia
                    </Link>
                    <template v-if="canCreate">
                        <Link :href="route('events.create')" class="text-sm font-medium text-primary hover:underline">
                            Dodaj wydarzenie
                        </Link>
                    </template>
                    <Link v-if="$page.props.auth?.user" :href="route('registrations.index')" class="text-sm hover:underline">
                        Moje rejestracje
                    </Link>
                    <Link v-if="$page.props.auth?.user" :href="route('dashboard')" class="text-sm text-[#1b1b18] hover:underline dark:text-[#EDEDEC]">
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link :href="route('login')" class="text-sm hover:underline">Logowanie</Link>
                        <Link :href="route('register')" class="rounded-sm border border-[#19140035] px-4 py-3 text-sm font-medium text-[#1b1b18] hover:bg-[#1914000a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:bg-[#3E3E3A]">
                            Rejestracja
                        </Link>
                    </template>
                </div>
            </nav>
        </header>

        <main>
            <!-- Hero -->
            <section class="mx-auto max-w-6xl px-4 py-16 md:py-24 lg:py-32">
                <div class="mx-auto max-w-2xl text-center">
                    <h1 class="text-4xl font-bold tracking-tight text-[#1b1b18] dark:text-[#EDEDEC] sm:text-5xl">
                        Twórz wydarzenia, zarządzaj uczestnikami
                    </h1>
                    <p class="mt-4 text-lg text-[#706f6c] dark:text-[#A1A09A]">
                        Platforma do organizacji konferencji, meetupów i warsztatów. Rejestracje, bilety z QR i check-in w jednym miejscu.
                    </p>
                    <div class="mt-8 flex flex-wrap justify-center gap-4">
                        <Link
                            :href="route('events.index')"
                            class="inline-flex items-center rounded-sm bg-[#1b1b18] px-6 py-3 text-sm font-medium text-white hover:bg-[#2d2d2a] dark:bg-[#EDEDEC] dark:text-[#1b1b18] dark:hover:bg-white"
                        >
                            Zobacz wydarzenia
                        </Link>
                        <Link
                            v-if="!$page.props.auth?.user"
                            :href="route('register')"
                            class="inline-flex items-center rounded-sm border border-[#19140035] px-6 py-3 text-sm font-medium text-[#1b1b18] hover:bg-[#1914000a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:bg-[#3E3E3A]"
                        >
                            Zarejestruj się
                        </Link>
                        <Link
                            v-else-if="canCreate"
                            :href="route('events.create')"
                            class="inline-flex items-center rounded-sm border border-[#19140035] px-6 py-3 text-sm font-medium text-[#1b1b18] hover:bg-[#1914000a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:bg-[#3E3E3A]"
                        >
                            Dodaj wydarzenie
                        </Link>
                    </div>
                </div>
            </section>

            <!-- Jak to działa -->
            <section class="border-y border-[#19140035] bg-white dark:border-[#3E3E3A] dark:bg-[#161615]">
                <div class="mx-auto max-w-6xl px-4 py-16 md:py-20">
                    <h2 class="text-center text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                        Jak to działa
                    </h2>
                    <div class="mt-12 grid gap-8 md:grid-cols-3">
                        <div class="flex flex-col items-center text-center">
                            <div class="flex size-12 items-center justify-center rounded-full bg-[#1914000a] dark:bg-[#3E3E3A]">
                                <Calendar class="size-6 text-[#1b1b18] dark:text-[#EDEDEC]" />
                            </div>
                            <h3 class="mt-4 font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Twórz wydarzenia</h3>
                            <p class="mt-2 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                Dodaj tytuł, datę, miejsce i opis. Opublikuj, gdy wydarzenie jest gotowe.
                            </p>
                        </div>
                        <div class="flex flex-col items-center text-center">
                            <div class="flex size-12 items-center justify-center rounded-full bg-[#1914000a] dark:bg-[#3E3E3A]">
                                <Ticket class="size-6 text-[#1b1b18] dark:text-[#EDEDEC]" />
                            </div>
                            <h3 class="mt-4 font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Uczestnicy się rejestrują</h3>
                            <p class="mt-2 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                Każdy dostaje bilet z kodem QR. Można rejestrować się bez logowania.
                            </p>
                        </div>
                        <div class="flex flex-col items-center text-center">
                            <div class="flex size-12 items-center justify-center rounded-full bg-[#1914000a] dark:bg-[#3E3E3A]">
                                <QrCode class="size-6 text-[#1b1b18] dark:text-[#EDEDEC]" />
                            </div>
                            <h3 class="mt-4 font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Check-in na miejscu</h3>
                            <p class="mt-2 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                Skanuj QR z biletu uczestnika i potwierdź przybycie w kilka sekund.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Najbliższe wydarzenia -->
            <section class="mx-auto max-w-6xl px-4 py-16 md:py-20">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                        Najbliższe wydarzenia
                    </h2>
                    <Link :href="route('events.index')" class="text-sm font-medium text-primary hover:underline">
                        Zobacz wszystkie →
                    </Link>
                </div>

                <div
                    v-if="featuredEvents.length === 0"
                    class="mt-8 rounded-xl border border-[#19140035] bg-white p-12 text-center dark:border-[#3E3E3A] dark:bg-[#161615]"
                >
                    <p class="text-[#706f6c] dark:text-[#A1A09A]">
                        Nie ma jeszcze opublikowanych wydarzeń. Bądź pierwszy i dodaj swoje wydarzenie!
                    </p>
                    <Link
                        v-if="canCreate"
                        :href="route('events.create')"
                        class="mt-4 inline-block text-sm font-medium text-primary hover:underline"
                    >
                        Dodaj wydarzenie →
                    </Link>
                    <Link
                        v-else
                        :href="route('register')"
                        class="mt-4 inline-block text-sm font-medium text-primary hover:underline"
                    >
                        Zarejestruj się jako organizator →
                    </Link>
                </div>

                <ul v-else class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <li
                        v-for="event in featuredEvents"
                        :key="event.id"
                        class="overflow-hidden rounded-xl border border-[#19140035] bg-white transition-shadow hover:shadow-md dark:border-[#3E3E3A] dark:bg-[#161615]"
                    >
                        <Link :href="route('events.show', event.slug)" class="block">
                            <div class="relative h-40 bg-[#1914000a] dark:bg-[#3E3E3A]">
                                <img
                                    v-if="event.cover_image_url"
                                    :src="event.cover_image_url"
                                    :alt="event.title"
                                    class="h-full w-full object-cover"
                                />
                                <div
                                    v-else
                                    class="flex h-full w-full items-center justify-center text-[#706f6c] dark:text-[#A1A09A]"
                                >
                                    <Calendar class="size-12 opacity-50" />
                                </div>
                            </div>
                            <div class="p-4">
                                <h3 class="font-medium text-[#1b1b18] hover:underline dark:text-[#EDEDEC]">
                                    {{ event.title }}
                                </h3>
                                <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                    {{ event.venue_name }}<template v-if="event.venue_city">, {{ event.venue_city }}</template>
                                </p>
                                <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                    {{ formatDate(event.start_date) }}
                                </p>
                                <p v-if="event.ticket_price && Number(event.ticket_price) > 0" class="mt-2 text-sm font-medium">
                                    {{ event.ticket_price }} {{ event.currency ?? 'PLN' }}
                                </p>
                                <p v-else class="mt-2 text-sm text-green-600 dark:text-green-400">Wstęp wolny</p>
                            </div>
                        </Link>
                    </li>
                </ul>
            </section>

            <!-- CTA dla organizatorów -->
            <section
                v-if="!$page.props.auth?.user"
                class="border-t border-[#19140035] bg-[#19140035]/5 dark:border-[#3E3E3A] dark:bg-[#3E3E3A]/20"
            >
                <div class="mx-auto max-w-6xl px-4 py-16 md:py-20">
                    <div class="mx-auto max-w-2xl text-center">
                        <h2 class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                            Organizujesz wydarzenia?
                        </h2>
                        <p class="mt-3 text-[#706f6c] dark:text-[#A1A09A]">
                            Dołącz do platformy, twórz wydarzenia i zarządzaj rejestracjami w jednym miejscu.
                        </p>
                        <Link
                            :href="route('register')"
                            class="mt-6 inline-flex items-center rounded-sm bg-[#1b1b18] px-6 py-3 text-sm font-medium text-white hover:bg-[#2d2d2a] dark:bg-[#EDEDEC] dark:text-[#1b1b18] dark:hover:bg-white"
                        >
                            Zarejestruj się za darmo
                        </Link>
                    </div>
                </div>
            </section>
        </main>

        <footer class="mt-16 border-t border-[#19140035] px-4 py-8 dark:border-[#3E3E3A]">
            <div class="mx-auto max-w-6xl text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
                Event Platform – platforma do zarządzania wydarzeniami
            </div>
        </footer>
    </div>
</template>
