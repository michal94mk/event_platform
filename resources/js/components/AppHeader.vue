<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Breadcrumb, BreadcrumbItem, BreadcrumbLink, BreadcrumbList, BreadcrumbPage, BreadcrumbSeparator } from '@/components/ui/breadcrumb';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
    navigationMenuTriggerStyle,
} from '@/components/ui/navigation-menu';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import type { BreadcrumbItem as BreadcrumbItemType, NavItem, NotificationRecent } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Bell, Calendar, LayoutGrid, Menu, Shield, Ticket } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const auth = computed(() => page.props.auth as { user?: { name: string; email: string; avatar?: string; role?: string } } | null);
const isLoggedIn = computed(() => !!auth.value?.user);

const notifications = computed(() => page.props.notifications as { unreadCount: number; recent: NotificationRecent[] } | undefined);
const unreadCount = computed(() => notifications.value?.unreadCount ?? 0);
const recent = computed(() => notifications.value?.recent ?? []);

const mainNavItems = computed<NavItem[]>(() => {
    if (!isLoggedIn.value) {
        return [{ title: 'Wydarzenia', href: '/events', icon: Calendar }];
    }
    const items: NavItem[] = [
        { title: 'Dashboard', href: '/dashboard', icon: LayoutGrid },
        { title: 'Wydarzenia', href: '/events', icon: Calendar },
        { title: 'Moje rejestracje', href: '/registrations', icon: Ticket },
        { title: 'Powiadomienia', href: '/notifications', icon: Bell },
    ];
    if (auth.value?.user?.role === 'admin') {
        items.push({ title: 'Panel admina', href: '/admin', icon: Shield });
    }
    return items;
});

const isCurrentRoute = (url: string) => page.url.startsWith(url) || (url === '/dashboard' && page.url === '/');

const activeItemStyles = computed(() => (url: string) => (isCurrentRoute(url) ? 'bg-accent text-accent-foreground' : ''));

function notificationUrl(n: NotificationRecent): string {
    if (n.data?.event_slug) {
        return `/events/${n.data.event_slug}/check-in`;
    }
    return '/dashboard';
}
</script>

<template>
    <div>
        <div class="border-b border-sidebar-border/80">
            <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
                <!-- Mobile Menu -->
                <div class="lg:hidden">
                    <Sheet>
                        <SheetTrigger :as-child="true">
                            <Button variant="ghost" size="icon" class="mr-2 h-9 w-9">
                                <Menu class="h-5 w-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="left" class="w-[300px] p-6">
                            <SheetTitle class="sr-only">Menu</SheetTitle>
                            <SheetHeader class="flex justify-start text-left">
                                <AppLogoIcon class="size-6 fill-current text-black dark:text-white" />
                            </SheetHeader>
                            <div class="flex h-full flex-1 flex-col justify-between space-y-4 py-6">
                                <nav class="-mx-3 space-y-1">
                                    <Link
                                        v-for="item in mainNavItems"
                                        :key="item.title"
                                        :href="item.href"
                                        class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                                        :class="activeItemStyles(item.href)"
                                    >
                                        <component v-if="item.icon" :is="item.icon" class="h-5 w-5" />
                                        {{ item.title }}
                                    </Link>
                                    <template v-if="!isLoggedIn">
                                        <Link
                                            :href="route('login')"
                                            class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                                        >
                                            Logowanie
                                        </Link>
                                        <Link
                                            :href="route('register')"
                                            class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                                        >
                                            Rejestracja
                                        </Link>
                                    </template>
                                </nav>
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>

                <Link :href="route('home')" class="flex items-center gap-x-2">
                    <AppLogo class="hidden h-6 xl:block" />
                </Link>

                <!-- Desktop Menu -->
                <div class="hidden h-full lg:flex lg:flex-1">
                    <NavigationMenu class="ml-10 flex h-full items-stretch">
                        <NavigationMenuList class="flex h-full items-stretch space-x-1">
                            <NavigationMenuItem v-for="(item, index) in mainNavItems" :key="index" class="relative flex h-full items-center">
                                <Link :href="item.href">
                                    <NavigationMenuLink
                                        :class="[navigationMenuTriggerStyle(), activeItemStyles(item.href), 'h-9 cursor-pointer px-3']"
                                    >
                                        <component v-if="item.icon" :is="item.icon" class="mr-2 h-4 w-4" />
                                        {{ item.title }}
                                    </NavigationMenuLink>
                                </Link>
                            </NavigationMenuItem>
                            <template v-if="!isLoggedIn">
                                <NavigationMenuItem class="flex h-full items-center">
                                    <Link :href="route('login')">
                                        <NavigationMenuLink :class="[navigationMenuTriggerStyle(), 'h-9 cursor-pointer px-3']">
                                            Logowanie
                                        </NavigationMenuLink>
                                    </Link>
                                </NavigationMenuItem>
                                <NavigationMenuItem class="flex h-full items-center">
                                    <Link :href="route('register')">
                                        <NavigationMenuLink :class="[navigationMenuTriggerStyle(), 'h-9 cursor-pointer px-3']">
                                            Rejestracja
                                        </NavigationMenuLink>
                                    </Link>
                                </NavigationMenuItem>
                            </template>
                        </NavigationMenuList>
                    </NavigationMenu>
                </div>

                <div class="ml-auto flex items-center gap-2">
                    <!-- Notifications (logged in only) -->
                    <template v-if="isLoggedIn">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" size="icon" class="relative h-9 w-9" aria-label="Powiadomienia">
                                    <Bell class="size-5" />
                                    <span
                                        v-if="unreadCount > 0"
                                        class="absolute -right-0.5 -top-0.5 flex size-4 items-center justify-center rounded-full bg-primary text-[10px] font-semibold text-primary-foreground"
                                    >
                                        {{ unreadCount > 9 ? '9+' : unreadCount }}
                                    </span>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-80">
                                <div class="border-b px-3 py-2 font-medium">Powiadomienia</div>
                                <div v-if="recent.length === 0" class="px-3 py-4 text-center text-sm text-muted-foreground">
                                    Brak nowych powiadomień.
                                </div>
                                <template v-else>
                                    <Link
                                        v-for="n in recent"
                                        :key="n.id"
                                        :href="notificationUrl(n)"
                                        class="block border-b px-3 py-2 text-sm last:border-0 hover:bg-accent"
                                    >
                                        <p class="font-medium">{{ n.title }}</p>
                                        <p class="mt-0.5 line-clamp-2 text-muted-foreground">{{ n.message }}</p>
                                    </Link>
                                </template>
                                <div class="border-t p-2">
                                    <Link
                                        :href="route('notifications.index')"
                                        class="block rounded-md px-2 py-1.5 text-center text-sm font-medium hover:bg-accent"
                                    >
                                        Zobacz wszystkie
                                    </Link>
                                </div>
                            </DropdownMenuContent>
                        </DropdownMenu>

                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="relative size-9 w-auto rounded-full p-1 focus-within:ring-2 focus-within:ring-primary"
                                >
                                    <Avatar class="size-8 overflow-hidden rounded-full">
                                        <AvatarImage :src="auth?.user?.avatar" :alt="auth?.user?.name" />
                                        <AvatarFallback
                                            class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white"
                                        >
                                            {{ getInitials(auth?.user?.name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-56">
                                <UserMenuContent :user="auth!.user!" />
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </template>
                </div>
            </div>
        </div>

        <div v-if="props.breadcrumbs.length > 1" class="flex w-full border-b border-sidebar-border/70">
            <div class="mx-auto flex h-12 w-full items-center justify-start px-4 md:max-w-7xl">
                <Breadcrumb>
                    <BreadcrumbList>
                        <template v-for="(item, index) in props.breadcrumbs" :key="index">
                            <BreadcrumbItem>
                                <template v-if="index === props.breadcrumbs.length - 1">
                                    <BreadcrumbPage>{{ item.title }}</BreadcrumbPage>
                                </template>
                                <template v-else>
                                    <BreadcrumbLink :href="item.href">{{ item.title }}</BreadcrumbLink>
                                </template>
                            </BreadcrumbItem>
                            <BreadcrumbSeparator v-if="index !== props.breadcrumbs.length - 1" />
                        </template>
                    </BreadcrumbList>
                </Breadcrumb>
            </div>
        </div>
    </div>
</template>
