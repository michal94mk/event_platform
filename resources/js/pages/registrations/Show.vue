<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types';
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

const props = defineProps<{
    registration: Registration;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Strona główna', href: '/' },
    { title: 'Moje rejestracje', href: '/registrations' },
    { title: 'Bilet', href: '#' },
];
</script>

<template>
    <Head title="Bilet / Potwierdzenie rejestracji" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-md">
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
        </div>
    </AppLayout>
</template>
