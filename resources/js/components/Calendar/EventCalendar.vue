<script setup lang="ts">
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import plLocale from '@fullcalendar/core/locales/pl';
import { computed } from 'vue';

interface Filters {
    search?: string;
    city?: string;
    category?: string;
    price?: string;
}

const props = withDefaults(
    defineProps<{
        feedUrl: string;
        filters?: Filters;
        mine?: boolean;
    }>(),
    {
        filters: () => ({}),
        mine: false,
    }
);

const eventSources = computed(() => [
    {
        url: props.feedUrl,
        extraParams: () => ({
            ...(props.filters?.search && { search: props.filters.search }),
            ...(props.filters?.city && { city: props.filters.city }),
            ...(props.filters?.category && { category: props.filters.category }),
            ...(props.filters?.price && { price: props.filters.price }),
            ...(props.mine && { mine: 1 }),
        }),
    },
]);

const calendarOptions = computed(() => ({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek',
    },
    locale: plLocale,
    buttonText: {
        today: 'Dziś',
        month: 'Miesiąc',
        week: 'Tydzień',
    },
    eventSources: eventSources.value,
    eventClick: (info: { event: { url?: string } }) => {
        if (info.event.url) {
            window.location.href = info.event.url;
        }
    },
    height: 'auto',
}));
</script>

<template>
    <div class="rounded-2xl border border-[#19140035] bg-white p-4 dark:border-[#3E3E3A] dark:bg-[#161615]">
        <FullCalendar :options="calendarOptions" />
    </div>
</template>
