<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import { ref } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
    role: string | null;
    created_at: string;
    events_count: number;
    registrations_count: number;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface UsersPaginated {
    data: User[];
    current_page: number;
    last_page: number;
    links: PaginationLink[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Panel admina', href: '/admin' },
    { title: 'Użytkownicy', href: '/admin/users' },
];

const props = defineProps<{
    users: UsersPaginated;
    filters?: { search?: string; role?: string };
}>();

const search = ref(props.filters?.search ?? '');
const roleFilter = ref(props.filters?.role ?? '');

function applyFilters() {
    router.get(
        route('admin.users.index'),
        {
            search: search.value || undefined,
            role: roleFilter.value || undefined,
        },
        { preserveState: true },
    );
}

function roleLabel(role: string | null): string {
    if (!role) return 'Uczestnik';
    const labels: Record<string, string> = {
        organizer: 'Organizator',
        admin: 'Admin',
    };
    return labels[role] ?? role;
}

function formatDate(iso: string) {
    return new Date(iso).toLocaleDateString('pl-PL', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}
</script>

<template>
    <Head title="Użytkownicy – Panel admina" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <div>
                <h1 class="text-2xl font-semibold">Użytkownicy</h1>
                <p class="mt-1 text-muted-foreground">Lista wszystkich użytkowników platformy.</p>
            </div>

            <div class="flex flex-wrap gap-4">
                <div class="relative min-w-[200px] flex-1">
                    <Search class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="search" placeholder="Szukaj po nazwie lub e-mailu..." class="pl-9" @keyup.enter="applyFilters" />
                </div>
                <select v-model="roleFilter" class="h-10 rounded-md border border-input bg-background px-3 py-2 text-sm" @change="applyFilters">
                    <option value="">Wszystkie role</option>
                    <option value="none">Uczestnik</option>
                    <option value="organizer">Organizator</option>
                    <option value="admin">Admin</option>
                </select>
                <Button @click="applyFilters">Filtruj</Button>
            </div>

            <Card>
                <CardContent class="p-0">
                    <div v-if="users.data.length === 0" class="p-8 text-center text-muted-foreground">Brak użytkowników spełniających kryteria.</div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th class="px-4 py-3 text-left font-medium">Użytkownik</th>
                                    <th class="px-4 py-3 text-left font-medium">Rola</th>
                                    <th class="px-4 py-3 text-left font-medium">Wydarzenia</th>
                                    <th class="px-4 py-3 text-left font-medium">Rejestracje</th>
                                    <th class="px-4 py-3 text-left font-medium">Dołączył</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="u in users.data" :key="u.id" class="border-b transition-colors hover:bg-muted/30">
                                    <td class="px-4 py-3">
                                        <p class="font-medium">{{ u.name }}</p>
                                        <p class="text-muted-foreground">{{ u.email }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="rounded-full px-2 py-0.5 text-xs font-medium"
                                            :class="{
                                                'bg-muted': !u.role,
                                                'bg-blue-100 text-blue-800': u.role === 'organizer',
                                                'bg-amber-100 text-amber-800': u.role === 'admin',
                                            }"
                                        >
                                            {{ roleLabel(u.role) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">{{ u.events_count }}</td>
                                    <td class="px-4 py-3">{{ u.registrations_count }}</td>
                                    <td class="px-4 py-3 text-muted-foreground">{{ formatDate(u.created_at) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <nav v-if="users.last_page > 1" class="flex justify-center gap-2">
                <template v-for="(link, index) in users.links" :key="index">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="inline-flex min-w-[2.5rem] items-center justify-center rounded-lg border px-3 py-2 text-sm"
                        :class="link.active ? 'border-primary bg-primary text-primary-foreground' : ''"
                    >
                        <span v-html="link.label" />
                    </Link>
                    <span v-else class="inline-flex items-center px-3 py-2 text-sm text-muted-foreground" v-html="link.label" />
                </template>
            </nav>
        </div>
    </AppLayout>
</template>
