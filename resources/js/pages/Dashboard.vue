<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Calendar, Plus, Ticket } from 'lucide-vue-next';

interface Event {
    id: number;
    title: string;
    slug: string;
    start_date: string;
    venue_name: string;
    venue_city: string | null;
}

interface Registration {
    id: number;
    first_name: string;
    last_name: string;
    ticket_quantity: number;
    event: Event;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

defineProps<{
    upcomingRegistrations: Registration[];
    organizerStats: { eventsCount: number; registrationsCount: number } | null;
    canCreate: boolean;
}>();

function formatDate(dateStr: string) {
    return new Date(dateStr).toLocaleDateString('pl-PL', {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <div>
                <h1 class="text-2xl font-semibold">
                    Cześć{{ $page.props.auth?.user?.name ? `, ${$page.props.auth.user.name}` : '' }}!
                </h1>
                <p class="mt-1 text-muted-foreground">
                    Oto podsumowanie Twojej aktywności na platformie.
                </p>
            </div>

            <!-- Quick links -->
            <div class="flex flex-wrap gap-3">
                <Link :href="route('registrations.index')">
                    <Button variant="outline" size="sm" class="gap-2">
                        <Ticket class="size-4" />
                        Moje rejestracje
                    </Button>
                </Link>
                <Link :href="route('events.index')">
                    <Button variant="outline" size="sm" class="gap-2">
                        <Calendar class="size-4" />
                        Wydarzenia
                    </Button>
                </Link>
                <Link v-if="canCreate" :href="route('events.create')">
                    <Button size="sm" class="gap-2">
                        <Plus class="size-4" />
                        Dodaj wydarzenie
                    </Button>
                </Link>
            </div>

            <!-- Organizer stats -->
            <div v-if="organizerStats" class="grid gap-4 sm:grid-cols-2">
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-base font-medium text-muted-foreground">
                            Twoje wydarzenia
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-3xl font-bold">{{ organizerStats.eventsCount }}</p>
                        <Link
                            v-if="canCreate"
                            :href="route('events.index', { mine: 1 })"
                            class="mt-2 inline-flex items-center gap-1 text-sm text-primary hover:underline"
                        >
                            Zobacz wszystkie
                            <ArrowRight class="size-4" />
                        </Link>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-base font-medium text-muted-foreground">
                            Łącznie uczestników
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-3xl font-bold">{{ organizerStats.registrationsCount }}</p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Na wszystkich Twoich wydarzeniach
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Upcoming registrations -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle>Nadchodzące wydarzenia</CardTitle>
                    <Link :href="route('registrations.index')">
                        <Button variant="ghost" size="sm">Zobacz wszystkie</Button>
                    </Link>
                </CardHeader>
                <CardContent>
                    <div
                        v-if="upcomingRegistrations.length === 0"
                        class="rounded-lg border border-dashed p-8 text-center text-muted-foreground"
                    >
                        Nie masz jeszcze rejestracji na nadchodzące wydarzenia.
                        <Link :href="route('events.index')" class="mt-2 block text-primary hover:underline">
                            Przeglądaj wydarzenia →
                        </Link>
                    </div>
                    <ul v-else class="space-y-3">
                        <li
                            v-for="r in upcomingRegistrations"
                            :key="r.id"
                            class="flex items-center justify-between gap-4 rounded-lg border p-4 transition-colors hover:bg-muted/50"
                        >
                            <div class="min-w-0 flex-1">
                                <p class="font-medium">{{ r.event.title }}</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ formatDate(r.event.start_date) }}
                                    <span v-if="r.event.venue_city"> · {{ r.event.venue_name }}, {{ r.event.venue_city }}</span>
                                    <span v-else> · {{ r.event.venue_name }}</span>
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ r.first_name }} {{ r.last_name }} · {{ r.ticket_quantity }}
                                    {{ r.ticket_quantity === 1 ? 'bilet' : 'bilety' }}
                                </p>
                            </div>
                            <Link :href="route('registrations.show', r.id)">
                                <Button variant="outline" size="sm">Bilet / QR</Button>
                            </Link>
                        </li>
                    </ul>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
