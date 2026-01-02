<script setup lang="ts">
import AppLogo from '@/components/shared/AppLogo.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    BarChart3,
    Bell,
    Calendar,
    CheckCircle,
    CreditCard,
    Receipt,
    Shield,
    Sparkles,
    Users,
    Wallet,
    Zap,
} from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage<SharedData>();

// Check if user is logged in
const isAuthenticated = computed(() => page.props.auth.user);

// Features list with lucide icons
const features = [
    {
        title: 'Easy Bill Management',
        description: 'Organize and track all your bills in one place with a simple and intuitive interface.',
        icon: Receipt,
    },
    {
        title: 'Payment Reminders',
        description: 'Never miss a payment with automated reminders and notifications.',
        icon: Bell,
    },
    {
        title: 'Expense Analytics',
        description: 'Visualize your spending patterns with comprehensive analytics and reports.',
        icon: BarChart3,
    },
    {
        title: 'Secure Storage',
        description: 'All your financial data is encrypted and stored securely.',
        icon: Shield,
    },
];

// Benefits/stats section
const benefits = [
    {
        icon: Calendar,
        title: 'Due Date Tracking',
        description: 'Track upcoming due dates and never miss a payment again.',
    },
    {
        icon: CreditCard,
        title: 'Multiple Categories',
        description: 'Organize bills by categories for better financial overview.',
    },
    {
        icon: Users,
        title: 'Team Collaboration',
        description: 'Share and manage bills with your family or team members.',
    },
    {
        icon: Wallet,
        title: 'Budget Insights',
        description: 'Get insights into your spending patterns and optimize your budget.',
    },
];

// Why choose us
const highlights = [
    { text: 'Free to use', icon: Sparkles },
    { text: 'Open source', icon: CheckCircle },
    { text: 'Fast & reliable', icon: Zap },
];
</script>

<template>
    <Head title="Bill Organizer - Manage Your Bills Easily">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="bg-background flex min-h-screen flex-col">
        <!-- Navigation -->
        <header class="border-border/40 sticky top-0 z-50 border-b backdrop-blur-sm">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <!-- Logo -->
                <Link href="/" class="flex items-center space-x-2">
                    <AppLogo class="text-black dark:text-white" />
                </Link>

                <!-- Auth Links -->
                <nav class="flex items-center gap-2 sm:gap-4">
                    <template v-if="isAuthenticated">
                        <Link :href="route('dashboard')">
                            <Button variant="default">
                                Dashboard
                                <ArrowRight class="ml-1 h-4 w-4" />
                            </Button>
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="route('login')">
                            <Button variant="ghost">Log in</Button>
                        </Link>
                        <Link :href="route('register')">
                            <Button variant="default">Get Started</Button>
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="relative overflow-hidden py-16 sm:py-24 lg:py-32">
            <!-- Background gradient decoration -->
            <div
                class="absolute inset-0 -z-10 bg-[radial-gradient(45%_40%_at_50%_60%,hsl(var(--primary)/0.12),transparent)] dark:bg-[radial-gradient(45%_40%_at_50%_60%,hsl(var(--primary)/0.08),transparent)]"
            />

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-3xl text-center">
                    <!-- Highlights badges -->
                    <div class="mb-8 flex flex-wrap justify-center gap-3">
                        <span
                            v-for="highlight in highlights"
                            :key="highlight.text"
                            class="bg-muted text-muted-foreground inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-sm font-medium"
                        >
                            <component :is="highlight.icon" class="h-3.5 w-3.5" />
                            {{ highlight.text }}
                        </span>
                    </div>

                    <h1 class="text-foreground mb-6 text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl">
                        Simplify Your
                        <span class="bg-gradient-to-r from-primary to-primary/60 bg-clip-text text-transparent"> Bill Management </span>
                    </h1>

                    <p class="text-muted-foreground mx-auto mb-10 max-w-2xl text-lg sm:text-xl">
                        Keep track of all your bills, set reminders, and organize your finances in one place. Never miss a payment again.
                    </p>

                    <div class="flex flex-col justify-center gap-4 sm:flex-row">
                        <Link :href="route('register')">
                            <Button size="lg" class="w-full sm:w-auto">
                                Start for Free
                                <ArrowRight class="ml-2 h-4 w-4" />
                            </Button>
                        </Link>
                        <a href="https://github.com/4msar/bill-organizer" target="_blank" rel="noopener noreferrer">
                            <Button size="lg" variant="outline" class="w-full sm:w-auto"> View on GitHub </Button>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="bg-muted/30 border-border/40 border-y py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-2xl text-center sm:mb-16">
                    <h2 class="text-foreground mb-4 text-3xl font-bold tracking-tight sm:text-4xl">Everything you need to manage your bills</h2>
                    <p class="text-muted-foreground text-lg">
                        A complete solution for tracking, organizing, and managing all your recurring expenses.
                    </p>
                </div>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <Card
                        v-for="(feature, index) in features"
                        :key="index"
                        class="bg-card/50 border-border/50 transition-shadow duration-200 hover:shadow-md"
                    >
                        <CardHeader class="pb-2">
                            <div class="bg-primary/10 text-primary mb-4 flex h-12 w-12 items-center justify-center rounded-lg">
                                <component :is="feature.icon" class="h-6 w-6" />
                            </div>
                            <CardTitle class="text-lg">{{ feature.title }}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-muted-foreground text-sm leading-relaxed">{{ feature.description }}</p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <!-- Benefits Section -->
        <section class="py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-2xl text-center sm:mb-16">
                    <h2 class="text-foreground mb-4 text-3xl font-bold tracking-tight sm:text-4xl">Why choose Bill Organizer?</h2>
                    <p class="text-muted-foreground text-lg">Powerful features designed to help you stay on top of your finances.</p>
                </div>

                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="(benefit, index) in benefits" :key="index" class="group text-center">
                        <div
                            class="bg-muted group-hover:bg-primary/10 mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full transition-colors duration-200"
                        >
                            <component :is="benefit.icon" class="text-primary h-7 w-7" />
                        </div>
                        <h3 class="text-foreground mb-2 text-lg font-semibold">{{ benefit.title }}</h3>
                        <p class="text-muted-foreground text-sm leading-relaxed">{{ benefit.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div
                    class="bg-gradient-to-br from-primary/5 via-primary/10 to-primary/5 border-primary/20 rounded-2xl border p-8 text-center sm:p-12 lg:p-16"
                >
                    <h2 class="text-foreground mb-4 text-3xl font-bold tracking-tight sm:text-4xl">Ready to get organized?</h2>
                    <p class="text-muted-foreground mx-auto mb-8 max-w-2xl text-lg">
                        Join users who have simplified their bill management process. Start tracking your expenses today.
                    </p>
                    <div class="flex flex-col justify-center gap-4 sm:flex-row">
                        <Link :href="route('register')">
                            <Button size="lg" class="w-full sm:w-auto">
                                Create Free Account
                                <ArrowRight class="ml-2 h-4 w-4" />
                            </Button>
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-border/40 mt-auto border-t py-8 sm:py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-6 md:flex-row">
                    <div class="flex flex-col items-center md:items-start">
                        <Link href="/" class="flex items-center space-x-2">
                            <AppLogo />
                        </Link>
                        <p class="text-muted-foreground mt-2 text-sm">
                            © {{ new Date().getFullYear() }} {{ $page.props.name }}. All rights reserved.
                        </p>
                    </div>

                    <Separator class="md:hidden" />

                    <nav class="flex flex-wrap justify-center gap-6">
                        <Link :href="route('legal.terms')" class="text-muted-foreground hover:text-foreground text-sm transition-colors">
                            Terms of Service
                        </Link>
                        <Link :href="route('legal.privacy')" class="text-muted-foreground hover:text-foreground text-sm transition-colors">
                            Privacy Policy
                        </Link>
                        <Link :href="route('legal.contact')" class="text-muted-foreground hover:text-foreground text-sm transition-colors">
                            Contact
                        </Link>
                        <a
                            href="https://github.com/4msar/bill-organizer"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-muted-foreground hover:text-foreground text-sm transition-colors"
                        >
                            GitHub
                        </a>
                    </nav>
                </div>
            </div>
        </footer>
    </div>
</template>
