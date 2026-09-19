self.addEventListener('push', (event) => {
    console.log('Push event received:', event);
    let payload = {};
    try {
        payload = event.data ? event.data.json() : {};
    } catch (error) {
        try {
            payload = {
                title: event.data.text(),
                body: 'You have a new notification.',
            };
        } catch (error) {
            console.error('Error creating default payload:', error);
            return;
        }
    }

    const { title, ...options } = payload;
    event.waitUntil(self.registration.showNotification(title || 'Bill Organizer', options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const url = event.notification.data?.url;

    if (!url) {
        return;
    }

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
            const existing = clients.find((client) => client.url === url);

            if (existing) {
                return existing.focus();
            }

            return self.clients.openWindow(url);
        }),
    );
});
