<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { BarChart3 } from 'lucide-vue-next';

interface EventSummary {
    id: number;
    title: string;
    slug: string;
    status: string;
}

interface Stats {
    registrations_count: number;
    tickets_total: number;
    revenue: number;
    currency: string;
    checked_in_count: number;
    not_checked_in_count: number;
    pending_payment_count: number;
    refunded_count: number;
    max_attendees: number | null;
}

const props = defineProps<{
    event: EventSummary;
    stats: Stats;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Strona główna', href: '/' },
    { title: 'Wydarzenia', href: '/events' },
    { title: props.event.title, href: `/events/${props.event.slug}` },
    { title: 'Statystyki', href: `/events/${props.event.slug}/stats` },
];

function formatMoney(amount: number, currency: string): string {
    return new Intl.NumberFormat('pl-PL', { style: 'currency', currency: currency || 'PLN' }).format(amount);
}
</script>

<template>
    <Head :title="'Statystyki: ' + event.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-2xl space-y-6">
            <div class="flex items-start gap-3">
                <div class="rounded-lg bg-primary/10 p-2 dark:bg-primary/20">
                    <BarChart3 class="h-6 w-6 text-primary" />
                </div>
                <div>
                    <h1 class="text-2xl font-semibold">Statystyki wydarzenia</h1>
                    <p class="mt-1 text-muted-foreground">{{ event.title }}</p>
                    <span
                        v-if="event.status !== 'published'"
                        class="mt-2 inline-block rounded bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900 dark:text-amber-200"
                    >
                        {{ event.status }}
                    </span>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <Card>
                    <CardHeader class="pb-2">
                        <CardDescription>Rejestracje</CardDescription>
                        <CardTitle class="text-3xl tabular-nums">{{ stats.registrations_count }}</CardTitle>
                    </CardHeader>
                    <CardContent class="text-sm text-muted-foreground">Łączna liczba zapisów</CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardDescription>Bilety</CardDescription>
                        <CardTitle class="text-3xl tabular-nums">{{ stats.tickets_total }}</CardTitle>
                    </CardHeader>
                    <CardContent class="text-sm text-muted-foreground">Suma liczby biletów</CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardDescription>Przychód (opłacone)</CardDescription>
                        <CardTitle class="text-3xl tabular-nums">{{ formatMoney(stats.revenue, stats.currency) }}</CardTitle>
                    </CardHeader>
                    <CardContent class="text-sm text-muted-foreground">Łączna kwota ze statusem „opłacone”</CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardDescription>Check-in</CardDescription>
                        <CardTitle class="text-3xl tabular-nums">{{ stats.checked_in_count }} / {{ stats.registrations_count }}</CardTitle>
                    </CardHeader>
                    <CardContent class="text-sm text-muted-foreground">Odhaczeni / zapisani</CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardDescription>Oczekujący na wejście</CardDescription>
                        <CardTitle class="text-3xl tabular-nums">{{ stats.not_checked_in_count }}</CardTitle>
                    </CardHeader>
                    <CardContent class="text-sm text-muted-foreground">Zapisani bez check-inu</CardContent>
                </Card>
                <Card v-if="stats.max_attendees !== null">
                    <CardHeader class="pb-2">
                        <CardDescription>Limit miejsc</CardDescription>
                        <CardTitle class="text-3xl tabular-nums">{{ stats.max_attendees }}</CardTitle>
                    </CardHeader>
                    <CardContent class="text-sm text-muted-foreground">Maks. uczestników (wydarzenie)</CardContent>
                </Card>
                <Card v-if="stats.pending_payment_count > 0">
                    <CardHeader class="pb-2">
                        <CardDescription>Oczekujące płatności</CardDescription>
                        <CardTitle class="text-3xl tabular-nums text-amber-600 dark:text-amber-400">{{ stats.pending_payment_count }}</CardTitle>
                    </CardHeader>
                    <CardContent class="text-sm text-muted-foreground">Rejestracje z brakiem potwierdzenia płatności</CardContent>
                </Card>
                <Card v-if="stats.refunded_count > 0">
                    <CardHeader class="pb-2">
                        <CardDescription>Zwroty</CardDescription>
                        <CardTitle class="text-3xl tabular-nums">{{ stats.refunded_count }}</CardTitle>
                    </CardHeader>
                    <CardContent class="text-sm text-muted-foreground">Rejestracje ze zwrotem</CardContent>
                </Card>
            </div>

            <div class="flex flex-wrap gap-2">
                <Link :href="route('events.show', event.slug)">
                    <Button variant="outline">← Szczegóły wydarzenia</Button>
                </Link>
                <Link :href="route('events.check-in.page', event.slug)">
                    <Button variant="secondary">Check-in</Button>
                </Link>
                <Link :href="route('events.registrations.export', event.slug)">
                    <Button variant="outline">Eksport CSV</Button>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
