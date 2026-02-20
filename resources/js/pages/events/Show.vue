<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link, router } from '@inertiajs/vue3';

interface Category {
    id: number;
    name: string;
    slug: string;
}

interface Event {
    id: number;
    title: string;
    slug: string;
    description: string;
    start_date: string;
    end_date: string;
    venue_name: string;
    venue_address: string | null;
    venue_city: string | null;
    venue_country: string | null;
    ticket_price: string | null;
    currency: string;
    status: string;
    max_attendees: number | null;
    user?: { id: number; name: string };
    categories?: Category[];
}

defineProps<{
    event: Event;
    canUpdate: boolean;
    canDelete: boolean;
}>();
</script>

<template>
    <Head :title="event.title" />

    <div class="min-h-screen bg-[#FDFDFC] dark:bg-[#0a0a0a]">
        <header class="border-b border-[#19140035] px-4 py-3 dark:border-[#3E3E3A]">
            <nav class="mx-auto flex max-w-4xl items-center justify-between">
                <Link :href="route('home')" class="text-sm font-medium text-[#1b1b18] hover:underline dark:text-[#EDEDEC]"> Event Platform </Link>
                <div class="flex gap-4">
                    <Link :href="route('events.index')" class="text-sm hover:underline">Wydarzenia</Link>
                    <Link v-if="$page.props.auth?.user" :href="route('dashboard')" class="text-sm hover:underline">Dashboard</Link>
                    <template v-else>
                        <Link :href="route('login')" class="text-sm hover:underline">Logowanie</Link>
                        <Link :href="route('register')" class="text-sm hover:underline">Rejestracja</Link>
                    </template>
                </div>
            </nav>
        </header>

        <main class="mx-auto max-w-2xl px-4 py-8">
            <Card>
                <CardHeader class="flex flex-row items-start justify-between gap-4">
                    <div>
                        <span
                            v-if="event.status !== 'published'"
                            class="mb-2 inline-block rounded bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900 dark:text-amber-200"
                        >
                            {{ event.status }}
                        </span>
                        <CardTitle class="text-2xl">{{ event.title }}</CardTitle>
                        <p v-if="event.user" class="mt-1 text-sm text-muted-foreground">Organizator: {{ event.user.name }}</p>
                    </div>
                    <div v-if="canUpdate || canDelete" class="flex gap-2">
                        <Link v-if="canUpdate" :href="route('events.edit', event.slug)">
                            <Button variant="outline" size="sm">Edytuj</Button>
                        </Link>
                        <button
                            v-if="canDelete"
                            type="button"
                            class="inline-flex h-9 items-center justify-center rounded-md bg-destructive px-4 text-sm font-medium text-destructive-foreground hover:bg-destructive/90"
                            @click="
                                if (confirm('Czy na pewno usunąć to wydarzenie?')) {
                                    router.delete(route('events.destroy', event.slug));
                                }
                            "
                        >
                            Usuń
                        </button>
                    </div>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div v-if="event.categories?.length" class="flex flex-wrap gap-2">
                        <span v-for="c in event.categories" :key="c.id" class="rounded-md bg-muted px-2 py-1 text-xs">
                            {{ c.name }}
                        </span>
                    </div>

                    <p class="whitespace-pre-wrap text-sm">{{ event.description }}</p>

                    <dl class="grid gap-2 text-sm">
                        <div>
                            <dt class="font-medium text-muted-foreground">Start</dt>
                            <dd>{{ new Date(event.start_date).toLocaleString('pl-PL') }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-muted-foreground">Koniec</dt>
                            <dd>{{ new Date(event.end_date).toLocaleString('pl-PL') }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-muted-foreground">Miejsce</dt>
                            <dd>
                                {{ event.venue_name }}
                                <template v-if="event.venue_address">, {{ event.venue_address }}</template>
                                <template v-if="event.venue_city">, {{ event.venue_city }}</template>
                                <template v-if="event.venue_country">, {{ event.venue_country }}</template>
                            </dd>
                        </div>
                        <div>
                            <dt class="font-medium text-muted-foreground">Cena</dt>
                            <dd>
                                <template v-if="event.ticket_price && Number(event.ticket_price) > 0">
                                    {{ event.ticket_price }} {{ event.currency }}
                                </template>
                                <template v-else>Wstęp wolny</template>
                            </dd>
                        </div>
                        <div v-if="event.max_attendees">
                            <dt class="font-medium text-muted-foreground">Maks. uczestników</dt>
                            <dd>{{ event.max_attendees }}</dd>
                        </div>
                    </dl>

                    <div class="pt-4">
                        <Link :href="route('events.index')">
                            <Button variant="outline">← Powrót do listy</Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </main>
    </div>
</template>
