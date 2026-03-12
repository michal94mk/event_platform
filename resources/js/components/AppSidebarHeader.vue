<script setup lang="ts">
import { Breadcrumb, BreadcrumbItem, BreadcrumbLink, BreadcrumbList, BreadcrumbPage, BreadcrumbSeparator } from '@/components/ui/breadcrumb';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType, NotificationRecent } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Bell } from 'lucide-vue-next';

defineProps<{
    breadcrumbs?: BreadcrumbItemType[];
}>();

const page = usePage();
const notifications = page.props.notifications as { unreadCount: number; recent: NotificationRecent[] } | undefined;
const unreadCount = notifications?.unreadCount ?? 0;
const recent = notifications?.recent ?? [];

function notificationUrl(n: NotificationRecent): string {
    if (n.data?.event_slug) {
        return `/events/${n.data.event_slug}/check-in`;
    }
    return '/dashboard';
}
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-[[data-collapsible=icon]]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex flex-1 items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumb>
                    <BreadcrumbList>
                        <template v-for="(item, index) in breadcrumbs" :key="index">
                            <BreadcrumbItem>
                                <template v-if="index === breadcrumbs.length - 1">
                                    <BreadcrumbPage>{{ item.title }}</BreadcrumbPage>
                                </template>
                                <template v-else>
                                    <BreadcrumbLink :href="item.href">
                                        {{ item.title }}
                                    </BreadcrumbLink>
                                </template>
                            </BreadcrumbItem>
                            <BreadcrumbSeparator v-if="index !== breadcrumbs.length - 1" />
                        </template>
                    </BreadcrumbList>
                </Breadcrumb>
            </template>
        </div>

        <div v-if="notifications" class="flex items-center">
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <button
                        type="button"
                        class="relative rounded-lg p-2 text-muted-foreground transition hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
                        aria-label="Powiadomienia"
                    >
                        <Bell class="size-5" />
                        <span
                            v-if="unreadCount > 0"
                            class="absolute -right-0.5 -top-0.5 flex size-4 items-center justify-center rounded-full bg-primary text-[10px] font-semibold text-primary-foreground"
                        >
                            {{ unreadCount > 9 ? '9+' : unreadCount }}
                        </span>
                    </button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-80 rounded-lg">
                    <div class="border-b px-3 py-2 font-medium">Powiadomienia</div>
                    <div v-if="recent.length === 0" class="px-3 py-4 text-center text-sm text-muted-foreground">Brak nowych powiadomień.</div>
                    <template v-else>
                        <Link
                            v-for="n in recent"
                            :key="n.id"
                            :href="notificationUrl(n)"
                            class="block border-b px-3 py-2 text-sm last:border-0 hover:bg-sidebar-accent/50"
                        >
                            <p class="font-medium">{{ n.title }}</p>
                            <p class="mt-0.5 line-clamp-2 text-muted-foreground">{{ n.message }}</p>
                        </Link>
                    </template>
                    <div class="border-t p-2">
                        <Link
                            :href="route('notifications.index')"
                            class="block rounded-md px-2 py-1.5 text-center text-sm font-medium hover:bg-sidebar-accent"
                        >
                            Zobacz wszystkie
                        </Link>
                    </div>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    </header>
</template>
