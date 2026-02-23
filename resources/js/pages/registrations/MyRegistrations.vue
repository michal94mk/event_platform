<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/vue3';

interface Event {
    id: number;
    title: string;
    slug: string;
    start_date: string;
    venue_name: string;
}

interface Registration {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    ticket_quantity: number;
    total_amount: string;
    payment_status: string;
    checked_in: boolean;
    event: Event;
}

defineProps<{
    registrations: Registration[];
}>();
</script>

<template>
    <Head title="Moje rejestracje" />

    <div class="min-h-screen bg-[#FDFDFC] dark:bg-[#0a0a0a]">
        <header class="border-b border-[#19140035] px-4 py-3 dark:border-[#3E3E3A]">
            <nav class="mx-auto flex max-w-4xl items-center justify-between">
                <Link :href="route('home')" class="text-sm font-medium text-[#1b1b18] hover:underline dark:text-[#EDEDEC]"> Event Platform </Link>
                <div class="flex gap-4">
                    <Link :href="route('events.index')" class="text-sm hover:underline">Wydarzenia</Link>
                    <Link :href="route('registrations.index')" class="text-sm font-medium hover:underline">Moje rejestracje</Link>
                    <Link :href="route('dashboard')" class="text-sm hover:underline">Dashboard</Link>
                </div>
            </nav>
        </header>

        <main class="mx-auto max-w-2xl px-4 py-8">
            <h1 class="mb-6 text-2xl font-semibold">Moje rejestracje</h1>

            <div v-if="registrations.length === 0" class="rounded-xl border bg-card p-8 text-center text-muted-foreground">
                Nie masz jeszcze żadnych rejestracji.
                <Link :href="route('events.index')" class="mt-2 block text-primary hover:underline">Przejdź do wydarzeń →</Link>
            </div>

            <ul v-else class="space-y-3">
                <li v-for="r in registrations" :key="r.id">
                    <Card>
                        <CardContent class="flex flex-row items-center justify-between gap-4 p-4">
                            <div>
                                <p class="font-medium">{{ r.event.title }}</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ new Date(r.event.start_date).toLocaleDateString('pl-PL') }} · {{ r.event.venue_name }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ r.first_name }} {{ r.last_name }} · {{ r.ticket_quantity }}
                                    {{ r.ticket_quantity === 1 ? 'bilet' : 'bilety' }}
                                    <span v-if="r.checked_in" class="text-green-600 dark:text-green-400"> · Odhaczono</span>
                                </p>
                            </div>
                            <Link :href="route('registrations.show', r.id)">
                                <Button variant="outline" size="sm">Bilet / QR</Button>
                            </Link>
                        </CardContent>
                    </Card>
                </li>
            </ul>
        </main>
    </div>
</template>
