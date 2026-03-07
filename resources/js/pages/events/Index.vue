<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Calendar } from 'lucide-vue-next';
import { reactive } from 'vue';

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
    cover_image_url?: string | null;
    user?: { id: number; name: string };
    categories?: EventCategory[];
}

interface Filters {
    search?: string;
    city?: string;
    category?: string;
    price?: string;
    sort?: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface EventsPaginated {
    data: Event[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
}

const props = defineProps<{
    events: EventsPaginated;
    canCreate?: boolean;
    showingMine?: boolean;
    filters?: Filters;
    categories?: EventCategory[];
}>();

const form = reactive<Required<Filters>>({
    search: props.filters?.search ?? '',
    city: props.filters?.city ?? '',
    category: props.filters?.category ?? '',
    price: props.filters?.price ?? '',
    sort: props.filters?.sort ?? '',
});

function applyFilters() {
    const params: Record<string, string | number | undefined> = {
        ...form,
    };

    if (props.showingMine) {
        params.mine = 1;
    }

    router.get(route('events.index'), params, {
        preserveScroll: true,
        replace: true,
    });
}

function resetFilters() {
    form.search = '';
    form.city = '';
    form.category = '';
    form.price = '';

    const params: Record<string, string | number | undefined> = {};
    if (props.showingMine) {
        params.mine = 1;
    }

    router.get(route('events.index'), params, {
        preserveScroll: true,
        replace: true,
    });
}

function formatDate(dateStr: string) {
    return new Date(dateStr).toLocaleDateString('pl-PL', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Strona główna', href: '/' },
    { title: 'Wydarzenia', href: '/events' },
];
</script>

<template>
    <Head title="Wydarzenia" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-6xl">
            <h1 class="mb-8 text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC] sm:text-3xl">
                {{ showingMine ? 'Moje wydarzenia' : 'Wydarzenia' }}
            </h1>

            <form
                class="mb-8 grid gap-4 rounded-2xl border border-[#19140035] bg-white p-4 dark:border-[#3E3E3A] dark:bg-[#161615] sm:grid-cols-5"
                @submit.prevent="applyFilters"
            >
                <div class="sm:col-span-2">
                    <label for="search" class="mb-1 block text-xs font-medium uppercase tracking-wide text-[#706f6c] dark:text-[#A1A09A]">
                        Szukaj
                    </label>
                    <input
                        id="search"
                        v-model="form.search"
                        type="text"
                        placeholder="Nazwa wydarzenia, miejsce, miasto"
                        class="block w-full rounded-lg border border-[#19140035] bg-white px-3 py-2 text-sm text-[#1b1b18] outline-none ring-0 transition focus:border-[#19140080] focus:ring-2 focus:ring-[#19140010] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#EDEDEC] dark:focus:border-[#e4e4e7] dark:focus:ring-[#27272a]"
                    />
                </div>

                <div>
                    <label for="city" class="mb-1 block text-xs font-medium uppercase tracking-wide text-[#706f6c] dark:text-[#A1A09A]">
                        Miasto
                    </label>
                    <input
                        id="city"
                        v-model="form.city"
                        type="text"
                        placeholder="np. Warszawa"
                        class="block w-full rounded-lg border border-[#19140035] bg-white px-3 py-2 text-sm text-[#1b1b18] outline-none ring-0 transition focus:border-[#19140080] focus:ring-2 focus:ring-[#19140010] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#EDEDEC] dark:focus:border-[#e4e4e7] dark:focus:ring-[#27272a]"
                    />
                </div>

                <div>
                    <label for="category" class="mb-1 block text-xs font-medium uppercase tracking-wide text-[#706f6c] dark:text-[#A1A09A]">
                        Kategoria
                    </label>
                    <select
                        id="category"
                        v-model="form.category"
                        class="block w-full rounded-lg border border-[#19140035] bg-white px-3 py-2 text-sm text-[#1b1b18] outline-none ring-0 transition focus:border-[#19140080] focus:ring-2 focus:ring-[#19140010] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#EDEDEC] dark:focus:border-[#e4e4e7] dark:focus:ring-[#27272a]"
                    >
                        <option value="">Wszystkie</option>
                        <option
                            v-for="category in categories"
                            :key="category.id"
                            :value="category.slug"
                        >
                            {{ category.name }}
                        </option>
                    </select>
                </div>

                <div>
                    <label for="price" class="mb-1 block text-xs font-medium uppercase tracking-wide text-[#706f6c] dark:text-[#A1A09A]">
                        Cena
                    </label>
                    <select
                        id="price"
                        v-model="form.price"
                        class="block w-full rounded-lg border border-[#19140035] bg-white px-3 py-2 text-sm text-[#1b1b18] outline-none ring-0 transition focus:border-[#19140080] focus:ring-2 focus:ring-[#19140010] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#EDEDEC] dark:focus:border-[#e4e4e7] dark:focus:ring-[#27272a]"
                    >
                        <option value="">Wszystkie</option>
                        <option value="free">Wstęp wolny</option>
                        <option value="paid">Płatne</option>
                    </select>
                </div>

                <div>
                    <label for="sort" class="mb-1 block text-xs font-medium uppercase tracking-wide text-[#706f6c] dark:text-[#A1A09A]">
                        Sortuj
                    </label>
                    <select
                        id="sort"
                        v-model="form.sort"
                        class="block w-full rounded-lg border border-[#19140035] bg-white px-3 py-2 text-sm text-[#1b1b18] outline-none ring-0 transition focus:border-[#19140080] focus:ring-2 focus:ring-[#19140010] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#EDEDEC] dark:focus:border-[#e4e4e7] dark:focus:ring-[#27272a]"
                    >
                        <option value="">Najbliższe (domyślnie)</option>
                        <option value="date_desc">Najpóźniejsze</option>
                        <option value="title_asc">Nazwa A–Z</option>
                        <option value="title_desc">Nazwa Z–A</option>
                    </select>
                </div>

                <div class="flex items-end gap-2 sm:col-span-5">
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-primary/90"
                    >
                        Zastosuj filtry
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium text-[#706f6c] transition hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                        @click="resetFilters"
                    >
                        Wyczyść
                    </button>
                </div>
            </form>

            <div
                v-if="events.data.length === 0"
                class="rounded-2xl border border-[#19140035] bg-white p-12 text-center dark:border-[#3E3E3A] dark:bg-[#161615]"
            >
                <p class="text-[#706f6c] dark:text-[#A1A09A]">
                    {{ showingMine ? 'Nie masz jeszcze żadnych wydarzeń.' : 'Nie ma jeszcze opublikowanych wydarzeń.' }}
                </p>
                <Link
                    v-if="canCreate && showingMine"
                    :href="route('events.create')"
                    class="mt-4 inline-block text-sm font-medium text-primary hover:underline"
                >
                    Dodaj pierwsze wydarzenie →
                </Link>
            </div>

            <ul v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <li
                    v-for="event in events.data"
                    :key="event.id"
                    class="flex"
                >
                    <Link
                        :href="route('events.show', event.slug)"
                        class="flex w-full flex-col overflow-hidden rounded-2xl border border-[#19140035] bg-white shadow-sm transition-all hover:shadow-lg hover:border-[#19140050] dark:border-[#3E3E3A] dark:bg-[#161615] dark:hover:border-[#52524e]"
                    >
                        <div class="relative aspect-[16/9] min-h-[140px] shrink-0 overflow-hidden bg-gradient-to-br from-[#19140008] via-[#19140012] to-[#19140008] dark:from-[#27272a] dark:via-[#3f3f46] dark:to-[#27272a]">
                            <img
                                v-if="event.cover_image_url"
                                :src="event.cover_image_url"
                                :alt="event.title"
                                class="h-full w-full object-cover"
                            />
                            <div
                                v-else
                                class="flex h-full w-full flex-col items-center justify-center gap-2 text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                <div class="rounded-full bg-white/60 p-4 dark:bg-black/20">
                                    <Calendar class="size-8" stroke-width="1.5" />
                                </div>
                                <span class="text-xs font-medium">Brak okładki</span>
                            </div>
                        </div>
                        <div class="flex flex-1 flex-col p-5">
                            <h2 class="line-clamp-2 font-semibold text-[#1b1b18] leading-snug hover:underline dark:text-[#EDEDEC]">
                                {{ event.title }}
                            </h2>
                            <p class="mt-2 line-clamp-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                {{ event.venue_name }}<template v-if="event.venue_city">, {{ event.venue_city }}</template>
                            </p>
                            <p class="mt-1 text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                                {{ formatDate(event.start_date) }}
                            </p>
                            <div class="mt-auto pt-4">
                                <p v-if="event.ticket_price && Number(event.ticket_price) > 0" class="text-sm font-semibold">
                                    {{ event.ticket_price }} {{ event.currency ?? 'PLN' }}
                                </p>
                                <p v-else class="text-sm font-medium text-green-600 dark:text-green-400">Wstęp wolny</p>
                            </div>
                        </div>
                    </Link>
                </li>
            </ul>

            <nav
                v-if="events.last_page > 1"
                class="mt-10 flex flex-wrap items-center justify-center gap-2"
                aria-label="Paginacja"
            >
                <template v-for="(link, index) in events.links" :key="index">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="inline-flex min-w-[2.5rem] items-center justify-center rounded-lg border px-3 py-2 text-sm font-medium transition"
                        :class="link.active
                            ? 'border-primary bg-primary text-white'
                            : 'border-[#19140035] bg-white text-[#1b1b18] hover:bg-[#19140008] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#EDEDEC] dark:hover:bg-[#27272a]'"
                    >
                        <span v-html="link.label" />
                    </Link>
                    <span
                        v-else
                        class="inline-flex min-w-[2.5rem] items-center justify-center px-3 py-2 text-sm text-[#706f6c] dark:text-[#A1A09A]"
                        v-html="link.label"
                    />
                </template>
            </nav>

            <p
                v-if="events.data.length > 0"
                class="mt-6 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]"
            >
                Wyświetlono {{ events.from }}–{{ events.to }} z {{ events.total }}
            </p>
        </div>
    </AppLayout>
</template>
