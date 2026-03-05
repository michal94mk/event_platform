<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Bell } from 'lucide-vue-next';

interface NotificationItem {
    id: number;
    type: string;
    title: string;
    message: string;
    read_at: string | null;
    data: { event_slug?: string } | null;
    created_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface NotificationsPaginated {
    data: NotificationItem[];
    current_page: number;
    last_page: number;
    links: PaginationLink[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Powiadomienia', href: '/notifications' },
];

defineProps<{
    notifications: NotificationsPaginated;
}>();

function notificationUrl(n: NotificationItem): string {
    if (n.data?.event_slug) {
        return route('events.check-in.page', n.data.event_slug);
    }
    return route('dashboard');
}

function markAsRead(n: NotificationItem) {
    if (n.read_at) return;
    router.patch(route('notifications.read', n.id), {}, { preserveScroll: true });
}

function formatDate(iso: string) {
    return new Date(iso).toLocaleDateString('pl-PL', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <Head title="Powiadomienia" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <div>
                <h1 class="text-2xl font-semibold">Powiadomienia</h1>
                <p class="mt-1 text-muted-foreground">
                    Tutaj znajdziesz informacje o nowych rejestracjach i anulowaniach na Twoje wydarzenia.
                </p>
            </div>

            <Card v-if="notifications.data.length === 0">
                <CardContent class="flex flex-col items-center justify-center gap-2 py-12">
                    <Bell class="size-10 text-muted-foreground" />
                    <p class="text-muted-foreground">Brak powiadomień.</p>
                </CardContent>
            </Card>

            <ul v-else class="space-y-3">
                <li v-for="n in notifications.data" :key="n.id">
                    <Card :class="!n.read_at ? 'border-primary/30' : ''">
                        <CardContent class="p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <p class="font-medium">{{ n.title }}</p>
                                    <p class="mt-1 text-sm text-muted-foreground">{{ n.message }}</p>
                                    <p class="mt-2 text-xs text-muted-foreground">{{ formatDate(n.created_at) }}</p>
                                </div>
                                <div class="flex shrink-0 gap-2">
                                    <Link v-if="n.data?.event_slug" :href="notificationUrl(n)">
                                        <Button variant="outline" size="sm" @click="markAsRead(n)">
                                            Zobacz wydarzenie
                                        </Button>
                                    </Link>
                                    <Button
                                        v-if="!n.read_at"
                                        variant="ghost"
                                        size="sm"
                                        @click="markAsRead(n)"
                                    >
                                        Oznacz jako przeczytane
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </li>
            </ul>

            <nav v-if="notifications.last_page > 1" class="flex justify-center gap-2">
                <template v-for="(link, index) in notifications.links" :key="index">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="inline-flex min-w-[2.5rem] items-center justify-center rounded-lg border px-3 py-2 text-sm"
                        :class="link.active ? 'border-primary bg-primary text-white' : ''"
                    >
                        <span v-html="link.label" />
                    </Link>
                    <span v-else class="inline-flex items-center px-3 py-2 text-sm text-muted-foreground" v-html="link.label" />
                </template>
            </nav>
        </div>
    </AppLayout>
</template>
