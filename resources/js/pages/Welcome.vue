<script setup lang="ts">
import AppLogo from '@/components/shared/AppLogo.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage<SharedData>();

// Check if user is logged in
const isAuthenticated = computed(() => page.props.auth.user);

// Features list
const features = [
    {
        title: 'Easy Bill Management',
        description: 'Organize and track all your bills in one place with a simple and intuitive interface.',
        icon: '📝',
    },
    {
        title: 'Payment Reminders',
        description: 'Never miss a payment with automated reminders and notifications.',
        icon: '🔔',
    },
    {
        title: 'Expense Analytics',
        description: 'Visualize your spending patterns with comprehensive analytics and reports.',
        icon: '📊',
    },
    {
        title: 'Secure Storage',
        description: 'All your financial data is encrypted and stored securely.',
        icon: '🔒',
    },
];
</script>

<template>
    <Head title="Bill Organizer - Manage Your Bills Easily">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="bg-background flex min-h-screen flex-col">
        <!-- Navigation -->
        <header class="border-border/40 border-b">
            <div class="container mx-auto flex items-center justify-between px-4 py-4">
                <!-- Logo -->
                <Link href="/" class="flex items-center space-x-2">
                    <AppLogo />
                </Link>

                <!-- Auth Links -->
                <nav class="flex items-center gap-4">
                    <template v-if="isAuthenticated">
                        <Link :href="route('dashboard')">
                            <Button variant="default">Dashboard</Button>
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="route('login')">
                            <Button variant="ghost">Log in</Button>
                        </Link>
                        <Link :href="route('register')">
                            <Button variant="default">Register</Button>
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="py-20 md:py-32">
            <div class="container mx-auto px-4 text-center">
                <h1 class="text-foreground mb-6 text-4xl font-bold tracking-tight md:text-6xl">
                    Simplify Your <span class="text-primary">Bill Management</span>
                </h1>
                <p class="text-muted-foreground mx-auto mb-12 max-w-3xl text-xl md:text-2xl">
                    Keep track of all your bills, set reminders, and organize your finances in one place.
                </p>
                <div class="flex flex-col justify-center gap-4 sm:flex-row">
                    <Link :href="route('register')">
                        <Button size="lg" class="w-full sm:w-auto">Get Started</Button>
                    </Link>
                    <Button size="lg" variant="outline" class="w-full sm:w-auto">Learn More</Button>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="bg-muted/50 py-20">
            <div class="container mx-auto px-4">
                <h2 class="text-foreground mb-16 text-center text-3xl font-bold md:text-4xl">Everything you need to manage your bills</h2>

                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                    <Card v-for="(feature, index) in features" :key="index" class="border-border/50">
                        <CardHeader>
                            <div class="mb-4 text-4xl">{{ feature.icon }}</div>
                            <CardTitle>{{ feature.title }}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-muted-foreground">{{ feature.description }}</p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20">
            <div class="container mx-auto px-4">
                <div class="bg-primary/5 border-primary/20 rounded-xl border p-8 text-center md:p-12">
                    <h2 class="text-foreground mb-6 text-3xl font-bold md:text-4xl">Ready to get organized?</h2>
                    <p class="text-muted-foreground mx-auto mb-8 max-w-2xl text-xl">
                        Join thousands of users who have simplified their bill management process.
                    </p>
                    <Link :href="route('register')">
                        <Button size="lg">Create Free Account</Button>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-border/40 border-t py-12">
            <div class="container mx-auto px-4">
                <div class="flex flex-col items-center justify-between md:flex-row">
                    <div class="mb-6 md:mb-0">
                        <Link href="/" class="flex items-center space-x-2">
                            <AppLogo />
                        </Link>
                        <p class="text-muted-foreground mt-2 text-sm">
                            © {{ new Date().getFullYear() }} {{ $page.props.name }}. All rights reserved.
                        </p>
                    </div>
                    <div class="flex space-x-6">
                        <a href="#" class="text-muted-foreground hover:text-foreground transition-colors"> Terms </a>
                        <a href="#" class="text-muted-foreground hover:text-foreground transition-colors"> Privacy </a>
                        <a href="#" class="text-muted-foreground hover:text-foreground transition-colors"> Contact </a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
