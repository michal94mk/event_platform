<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

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
    user?: { id: number; name: string };
    categories?: EventCategory[];
}

defineProps<{
    events: Event[];
}>();
</script>

<template>
    <Head title="Wydarzenia" />

    <div class="min-h-screen bg-[#FDFDFC] dark:bg-[#0a0a0a]">
        <header class="border-b border-[#19140035] dark:border-[#3E3E3A] px-4 py-3">
            <nav class="mx-auto flex max-w-4xl items-center justify-between">
                <Link :href="route('home')" class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] hover:underline">
                    Event Platform
                </Link>
                <div class="flex gap-4">
                    <Link :href="route('events.index')" class="text-sm text-[#1b1b18] dark:text-[#EDEDEC] hover:underline">
                        Wydarzenia
                    </Link>
                    <Link
                        v-if="$page.props.auth?.user"
                        :href="route('dashboard')"
                        class="text-sm text-[#1b1b18] dark:text-[#EDEDEC] hover:underline"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link :href="route('login')" class="text-sm hover:underline">Logowanie</Link>
                        <Link :href="route('register')" class="text-sm hover:underline">Rejestracja</Link>
                    </template>
                </div>
            </nav>
        </header>

        <main class="mx-auto max-w-4xl px-4 py-8">
            <h1 class="mb-6 text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                Wydarzenia
            </h1>

            <div v-if="events.length === 0" class="rounded-xl border border-sidebar-border/70 bg-white p-8 text-center dark:border-[#3E3E3A] dark:bg-[#161615]">
                <p class="text-[#706f6c] dark:text-[#A1A09A]">
                    Nie ma jeszcze opublikowanych wydarzeń.
                </p>
            </div>

            <ul v-else class="grid gap-4 sm:grid-cols-2">
                <li
                    v-for="event in events"
                    :key="event.id"
                    class="rounded-xl border border-[#19140035] bg-white p-4 dark:border-[#3E3E3A] dark:bg-[#161615]"
                >
                    <h2 class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                        {{ event.title }}
                    </h2>
                    <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        {{ event.venue_name }}<template v-if="event.venue_city">, {{ event.venue_city }}</template>
                    </p>
                    <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        {{ new Date(event.start_date).toLocaleDateString('pl-PL') }}
                    </p>
                    <p v-if="event.ticket_price && Number(event.ticket_price) > 0" class="mt-1 text-sm font-medium">
                        {{ event.ticket_price }} {{ event.currency ?? 'PLN' }}
                    </p>
                    <p v-else class="mt-1 text-sm text-green-600 dark:text-green-400">
                        Wstęp wolny
                    </p>
                </li>
            </ul>
        </main>
    </div>
</template>
