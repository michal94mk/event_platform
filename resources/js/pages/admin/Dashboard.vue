<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Calendar, Ticket, Users } from 'lucide-vue-next';

interface Event {
    id: number;
    title: string;
    slug: string;
    status: string;
    start_date: string;
    user: { id: number; name: string };
    created_at: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Panel admina', href: '/admin' },
];

defineProps<{
    stats: {
        usersCount: number;
        eventsCount: number;
        registrationsCount: number;
        organizersCount: number;
        publishedEventsCount: number;
    };
    recentEvents: Event[];
}>();

function formatDate(dateStr: string) {
    return new Date(dateStr).toLocaleDateString('pl-PL', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}

function statusLabel(status: string): string {
    const labels: Record<string, string> = {
        draft: 'Szkic',
        published: 'Opublikowane',
        cancelled: 'Anulowane',
        completed: 'Zakończone',
    };
    return labels[status] ?? status;
}
</script>

<template>
    <Head title="Panel admina" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <div>
                <h1 class="text-2xl font-semibold">Panel admina</h1>
                <p class="mt-1 text-muted-foreground">
                    Statystyki platformy i zarządzanie treścią.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="flex items-center gap-2 text-base font-medium text-muted-foreground">
                            <Users class="size-4" />
                            Użytkownicy
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-3xl font-bold">{{ stats.usersCount }}</p>
                        <Link :href="route('admin.users.index')">
                            <Button variant="link" class="h-auto p-0 text-primary">
                                Zobacz listę →
                            </Button>
                        </Link>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="flex items-center gap-2 text-base font-medium text-muted-foreground">
                            <Calendar class="size-4" />
                            Wydarzenia
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-3xl font-bold">{{ stats.eventsCount }}</p>
                        <p class="text-sm text-muted-foreground">
                            {{ stats.publishedEventsCount }} opublikowanych
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="flex items-center gap-2 text-base font-medium text-muted-foreground">
                            <Ticket class="size-4" />
                            Rejestracje
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-3xl font-bold">{{ stats.registrationsCount }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-base font-medium text-muted-foreground">
                            Organizatorzy
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-3xl font-bold">{{ stats.organizersCount }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-base font-medium text-muted-foreground">
                            Kategorie
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Link :href="route('admin.categories.index')">
                            <Button variant="link" class="h-auto p-0 text-primary">
                                Zarządzaj kategoriami →
                            </Button>
                        </Link>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle>Ostatnie wydarzenia</CardTitle>
                    <Link :href="route('events.index')">
                        <Button variant="ghost" size="sm">Wszystkie wydarzenia</Button>
                    </Link>
                </CardHeader>
                <CardContent>
                    <div
                        v-if="recentEvents.length === 0"
                        class="rounded-lg border border-dashed p-8 text-center text-muted-foreground"
                    >
                        Brak wydarzeń na platformie.
                    </div>
                    <ul v-else class="space-y-3">
                        <li
                            v-for="e in recentEvents"
                            :key="e.id"
                            class="flex items-center justify-between gap-4 rounded-lg border p-4 transition-colors hover:bg-muted/50"
                        >
                            <div class="min-w-0 flex-1">
                                <p class="font-medium">{{ e.title }}</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ e.user?.name }} · {{ formatDate(e.start_date) }}
                                </p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <span
                                    class="rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="{
                                        'bg-muted': e.status === 'draft',
                                        'bg-green-100 text-green-800': e.status === 'published',
                                        'bg-red-100 text-red-800': e.status === 'cancelled',
                                        'bg-blue-100 text-blue-800': e.status === 'completed',
                                    }"
                                >
                                    {{ statusLabel(e.status) }}
                                </span>
                                <Link :href="route('events.show', e.slug)">
                                    <Button variant="outline" size="sm">Zobacz</Button>
                                </Link>
                            </div>
                        </li>
                    </ul>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
