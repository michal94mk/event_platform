<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Calendar, CreditCard, Mail, MessageSquare } from 'lucide-vue-next';

interface Integration {
    id: number;
    provider: string;
    is_active: boolean;
}

const props = defineProps<{
    integrations: Integration[];
    stripeConfigured: boolean;
    googleCalendarConfigured: boolean;
    sendGridConfigured: boolean;
    twilioConfigured: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Strona główna', href: '/' },
    { title: 'Integracje', href: '/integrations' },
];

const googleCalendarIntegration = () =>
    props.integrations.find((i) => i.provider === 'google_calendar' && i.is_active);

const disconnect = (id: number) => {
    if (confirm('Czy na pewno rozłączyć tę integrację?')) {
        router.delete(route('integrations.destroy', id));
    }
};

const testIntegration = (id: number) => {
    router.post(route('integrations.test', id));
};
</script>

<template>
    <Head title="Integracje" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-2xl space-y-6">
            <div>
                <h1 class="text-2xl font-semibold">Integracje</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Połącz zewnętrzne serwisy, aby rozszerzyć funkcjonalność platformy.
                </p>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Calendar class="h-5 w-5" />
                        Google Calendar
                    </CardTitle>
                    <CardDescription>
                        Synchronizuj wydarzenia z kalendarzem Google. Po publikacji wydarzenie pojawi się w Twoim kalendarzu.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-3">
                    <p v-if="!googleCalendarConfigured" class="text-sm text-muted-foreground">
                        Administrator musi skonfigurować Google OAuth w pliku .env.
                    </p>
                    <template v-else>
                        <div v-if="googleCalendarIntegration()" class="flex items-center gap-2">
                            <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200">
                                Połączono
                            </span>
                            <Button
                                variant="outline"
                                size="sm"
                                @click="testIntegration(googleCalendarIntegration()!.id)"
                            >
                                Test
                            </Button>
                            <Button
                                variant="destructive"
                                size="sm"
                                @click="disconnect(googleCalendarIntegration()!.id)"
                            >
                                Rozłącz
                            </Button>
                        </div>
                        <Link v-else :href="route('integrations.google-calendar.connect')">
                            <Button>Połącz z Google Calendar</Button>
                        </Link>
                    </template>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <CreditCard class="h-5 w-5" />
                        Stripe
                    </CardTitle>
                    <CardDescription>
                        Płatności za bilety. Konfiguracja globalna w .env (STRIPE_KEY, STRIPE_SECRET).
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <span
                        v-if="stripeConfigured"
                        class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200"
                    >
                        Skonfigurowano
                    </span>
                    <p v-else class="text-sm text-muted-foreground">
                        Ustaw STRIPE_KEY i STRIPE_SECRET w .env.
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Mail class="h-5 w-5" />
                        SendGrid
                    </CardTitle>
                    <CardDescription>
                        Wysyłka emaili przez SendGrid. Obecnie używany jest Laravel Mail (log/smtp).
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <span
                        v-if="sendGridConfigured"
                        class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200"
                    >
                        Skonfigurowano
                    </span>
                    <p v-else class="text-sm text-muted-foreground">
                        Ustaw SENDGRID_API_KEY w .env, aby używać SendGrid.
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <MessageSquare class="h-5 w-5" />
                        Twilio
                    </CardTitle>
                    <CardDescription>
                        Powiadomienia SMS. Wymaga konfiguracji w .env.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <span
                        v-if="twilioConfigured"
                        class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200"
                    >
                        Skonfigurowano
                    </span>
                    <p v-else class="text-sm text-muted-foreground">
                        Ustaw TWILIO_SID, TWILIO_AUTH_TOKEN i TWILIO_PHONE_NUMBER w .env.
                    </p>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
