<script setup lang="ts">
import Confirm from '@/components/shared/Confirm.vue';
import HeadingSmall from '@/components/shared/HeadingSmall.vue';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { NotificationData, PaginatedData } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { CheckCircle, Inbox } from 'lucide-vue-next';
import { ref } from 'vue';

const page = usePage<{
    items: PaginatedData<NotificationData>;
}>();

const successMessage = ref<string | null>(null);

/**
 * Mark all notifications as read.
 */
async function markAllAsRead(): Promise<void> {
    try {
        await router.post(route('notifications.markAllAsRead'));
        successMessage.value = 'All notifications marked as read.';
        setTimeout(() => {
            successMessage.value = null;
            router.reload(); // Refresh the page to update notifications
        }, 3000);
    } catch (error) {
        console.error('Failed to mark notifications as read:', error);
    }
}

const extractType = (notification: NotificationData) => {
    return notification.type.split('\\').pop();
};
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            {
                title: 'Notifications',
                href: route('notifications.index'),
            },
        ]"
    >
        <Head title="Notifications" />
        <div class="space-y-6 px-4 py-6">
            <HeadingSmall title="Notifications" description="Latest notifications" class="flex items-center justify-between">
                <Button @click="markAllAsRead" class="flex items-center" v-if="page.props.items.data.length > 0">
                    <Inbox class="mr-2 h-4 w-4" />
                    Mark All as Read
                </Button>
            </HeadingSmall>
            <!-- Success Message -->
            <Alert v-if="successMessage" variant="success" class="mb-4">
                <CheckCircle class="h-4 w-4" />
                <AlertDescription>{{ successMessage }}</AlertDescription>
            </Alert>

            <!-- Notifications List -->
            <div class="space-y-4" v-if="page.props.items.data.length > 0">
                <div
                    v-for="notification in page.props.items.data"
                    :key="notification.id"
                    class="group bg-background rounded-md border p-4"
                    :class="{ 'bg-neutral-100': notification.read_at === null }"
                >
                    <div class="flex w-full items-center justify-between">
                        <Link
                            v-if="notification.url"
                            :href="notification.url"
                            class="text-primary text-sm transition-all hover:underline dark:text-white"
                        >
                            <h3 class="w-full flex-1 text-lg font-semibold">{{ notification.title || 'Notification' }}</h3>
                        </Link>
                        <h3 v-else class="w-full flex-1 text-lg font-semibold">{{ notification.title || 'Notification' }}</h3>

                        <Badge variant="secondary" class="ml-2">
                            {{ extractType(notification) }}
                        </Badge>
                    </div>
                    <p v-if="notification.description" class="text-muted-foreground text-sm">{{ notification.description }}</p>
                    <div class="relative mt-2 flex items-center justify-between">
                        <p class="text-muted-foreground mt-1 text-sm">{{ notification.created_time }}</p>
                        <div class="flex group-hover:flex sm:hidden">
                            <Confirm :url="route('notifications.delete', notification.id)" title="Are you sure?" modal>
                                <span class="cursor-pointer text-sm text-red-500 transition-all duration-150 hover:text-red-700"> Delete </span>
                            </Confirm>
                        </div>
                    </div>
                </div>
            </div>
            <!-- No Notifications Message -->
            <div v-else class="text-muted-foreground text-center">
                <p class="text-lg">No notifications found.</p>
            </div>

            <!-- Pagination -->
            <div v-if="page.props.items?.meta?.last_page > 1" class="mt-6 flex justify-center">
                <div class="flex items-center space-x-2">
                    <Button variant="outline" size="sm" :disabled="!page.props.items.links.prev">
                        <Link v-if="page.props.items.links.prev" :href="page.props.items.links.prev"> Previous </Link>
                        <span v-else> Previous </span>
                    </Button>

                    <span class="text-muted-foreground text-sm">
                        Page {{ page.props.items.meta.current_page }} of {{ page.props.items.meta.last_page }}
                    </span>

                    <Button variant="outline" size="sm" :disabled="!page.props.items.links.next">
                        <Link v-if="page.props.items.links.next" :href="page.props.items.links.next"> Next </Link>
                        <span v-else> Next </span>
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
