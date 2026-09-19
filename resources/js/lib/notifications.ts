import type { SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

// PushManager needs the VAPID public key as a raw byte array, not base64url.
function urlBase64ToUint8Array(base64String: string): Uint8Array<ArrayBuffer> {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; i++) {
        outputArray[i] = rawData.charCodeAt(i);
    }

    return outputArray;
}

export function isPushSupported(): boolean {
    return typeof window !== 'undefined' && 'serviceWorker' in navigator && 'PushManager' in window;
}

/**
 * Registers the service worker and exposes the user's push subscription state for this device.
 */
export function useBrowserNotifications() {
    const page = usePage<SharedData>();
    const isSupported = isPushSupported();
    const permission = ref<NotificationPermission>(isSupported ? Notification.permission : 'denied');
    const subscribed = ref(false);

    async function refreshSubscribedState() {
        const registration = await navigator.serviceWorker.ready;
        const subscription = await registration.pushManager.getSubscription();

        subscribed.value = subscription !== null;
    }

    if (isSupported) {
        navigator.serviceWorker.register('/sw.js').then(refreshSubscribedState);
    }

    async function enable(): Promise<boolean> {
        if (!isSupported || !page.props.webPushPublicKey) {
            return false;
        }

        permission.value = await Notification.requestPermission();

        if (permission.value !== 'granted') {
            return false;
        }

        try {
            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(page.props.webPushPublicKey),
            });

            await axios.post(route('push-subscriptions.store'), subscription.toJSON());
            subscribed.value = true;

            return true;
        } catch {
            toast.error('Could not enable browser notifications', {
                description: 'Something went wrong while subscribing this device.',
            });

            return false;
        }
    }

    async function disable(): Promise<void> {
        if (!isSupported) {
            return;
        }

        const registration = await navigator.serviceWorker.ready;
        const subscription = await registration.pushManager.getSubscription();

        if (subscription) {
            await axios.delete(route('push-subscriptions.destroy'), { data: { endpoint: subscription.endpoint } });
            await subscription.unsubscribe();
        }

        subscribed.value = false;
    }

    return { isSupported, permission, subscribed, enable, disable };
}
