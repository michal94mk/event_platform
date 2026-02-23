<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/vue3';
import QrcodeVue from 'qrcode.vue';

interface Event {
    id: number;
    title: string;
    slug: string;
    start_date: string;
    end_date: string;
    venue_name: string;
    venue_city: string | null;
}

interface Registration {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    phone: string | null;
    ticket_quantity: number;
    total_amount: string;
    payment_status: string;
    qr_code: string;
    checked_in: boolean;
    event: Event;
}

defineProps<{
    registration: Registration;
}>();
</script>

<template>
    <Head title="Bilet / Potwierdzenie rejestracji" />

    <div class="min-h-screen bg-[#FDFDFC] dark:bg-[#0a0a0a]">
        <header class="border-b border-[#19140035] px-4 py-3 dark:border-[#3E3E3A]">
            <nav class="mx-auto flex max-w-4xl items-center justify-between">
                <Link :href="route('home')" class="text-sm font-medium text-[#1b1b18] hover:underline dark:text-[#EDEDEC]"> Event Platform </Link>
                <div class="flex gap-4">
                    <Link :href="route('events.index')" class="text-sm hover:underline">Wydarzenia</Link>
                    <Link v-if="$page.props.auth?.user" :href="route('registrations.index')" class="text-sm hover:underline"> Moje rejestracje </Link>
                    <Link v-if="$page.props.auth?.user" :href="route('dashboard')" class="text-sm hover:underline">Dashboard</Link>
                </div>
            </nav>
        </header>

        <main class="mx-auto max-w-md px-4 py-8">
            <Card>
                <CardHeader>
                    <CardTitle>Bilet wstępu</CardTitle>
                    <p class="text-sm text-muted-foreground">{{ registration.event.title }}</p>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="flex justify-center rounded-lg border bg-white p-4 dark:bg-muted">
                        <QrcodeVue :value="registration.qr_code" :size="200" level="M" />
                    </div>
                    <p class="text-center text-xs text-muted-foreground">Pokaż ten kod QR przy wejściu na wydarzenie</p>

                    <dl class="grid gap-2 text-sm">
                        <div>
                            <dt class="font-medium text-muted-foreground">Uczestnik</dt>
                            <dd>{{ registration.first_name }} {{ registration.last_name }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-muted-foreground">Liczba biletów</dt>
                            <dd>{{ registration.ticket_quantity }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-muted-foreground">Wydarzenie</dt>
                            <dd>{{ registration.event.title }}</dd>
                            <dd class="text-muted-foreground">
                                {{ new Date(registration.event.start_date).toLocaleString('pl-PL') }} ·
                                {{ registration.event.venue_name }}
                                <template v-if="registration.event.venue_city">, {{ registration.event.venue_city }}</template>
                            </dd>
                        </div>
                    </dl>

                    <div class="pt-4">
                        <Link :href="route('events.show', registration.event.slug)">
                            <Button variant="outline" class="w-full">Szczegóły wydarzenia</Button>
                        </Link>
                        <Link
                            v-if="$page.props.auth?.user"
                            :href="route('registrations.index')"
                            class="mt-2 block text-center text-sm text-muted-foreground hover:underline"
                        >
                            ← Moje rejestracje
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </main>
    </div>
</template>
