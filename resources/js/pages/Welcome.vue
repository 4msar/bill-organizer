<script setup lang="ts">
import AppLogoIcon from '@/components/shared/AppLogoIcon.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    BarChart3,
    Bell,
    Calendar,
    Check,
    ChevronRight,
    Github,
    Receipt,
    Shield,
    Sparkles,
    Star,
    TrendingUp,
    Users,
} from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage<SharedData>();

// Check if user is logged in
const isAuthenticated = computed(() => page.props.auth.user);

// Core features
const coreFeatures = [
    {
        icon: Receipt,
        title: 'Bill Tracking',
        description: 'Track all your recurring bills and subscriptions in one central dashboard.',
    },
    {
        icon: Bell,
        title: 'Smart Reminders',
        description: 'Get notified before due dates so you never miss a payment.',
    },
    {
        icon: BarChart3,
        title: 'Spending Analytics',
        description: 'Visualize your spending patterns with detailed charts and reports.',
    },
    {
        icon: Users,
        title: 'Team Sharing',
        description: 'Collaborate with family or team members on shared expenses.',
    },
    {
        icon: Calendar,
        title: 'Calendar View',
        description: 'See all your upcoming payments in an intuitive calendar format.',
    },
    {
        icon: Shield,
        title: 'Secure & Private',
        description: 'Your data is encrypted and never shared with third parties.',
    },
];

// Stats
const stats = [
    { value: '100%', label: 'Free & Open Source' },
    { value: '∞', label: 'Unlimited Bills' },
    { value: '24/7', label: 'Always Available' },
    { value: '0', label: 'Ads or Tracking' },
];

// How it works steps
const steps = [
    {
        step: '01',
        title: 'Add Your Bills',
        description: 'Enter your recurring bills, subscriptions, and expenses with due dates.',
        icon: Receipt,
    },
    {
        step: '02',
        title: 'Get Reminders',
        description: 'Receive timely notifications before payment due dates arrive.',
        icon: Bell,
    },
    {
        step: '03',
        title: 'Track & Analyze',
        description: 'Monitor your spending trends and optimize your budget.',
        icon: TrendingUp,
    },
];
</script>

<template>
    <Head title="Bill Organizer - Manage Your Bills Easily">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="bg-background min-h-screen">
        <!-- Navigation -->
        <header class="fixed top-0 z-50 w-full border-b border-border/40 bg-background/80 backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
                <Link href="/" class="flex items-center gap-2">
                    <div class="bg-foreground flex h-8 w-8 items-center justify-center rounded-lg">
                        <AppLogoIcon class="text-background h-5 w-5" />
                    </div>
                    <span class="text-foreground text-lg font-semibold">{{ $page.props.name }}</span>
                </Link>

                <nav class="flex items-center gap-2 sm:gap-3">
                    <a
                        href="https://github.com/4msar/bill-organizer"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-muted-foreground hover:text-foreground hidden transition-colors sm:flex"
                    >
                        <Github class="h-5 w-5" />
                    </a>
                    <template v-if="isAuthenticated">
                        <Link :href="route('dashboard')">
                            <Button>
                                Dashboard
                                <ChevronRight class="ml-1 h-4 w-4" />
                            </Button>
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="route('login')">
                            <Button variant="ghost" size="sm">Log in</Button>
                        </Link>
                        <Link :href="route('register')">
                            <Button size="sm">Get Started</Button>
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="relative overflow-hidden pt-24 pb-16 sm:pt-32 sm:pb-24">
            <!-- Gradient background -->
            <div
                class="pointer-events-none absolute inset-0 -z-10 overflow-hidden"
                aria-hidden="true"
            >
                <div
                    class="absolute -top-1/2 left-1/2 h-[800px] w-[800px] -translate-x-1/2 rounded-full bg-gradient-to-br from-blue-500/20 via-purple-500/20 to-pink-500/20 blur-3xl dark:from-blue-500/10 dark:via-purple-500/10 dark:to-pink-500/10"
                />
                <div
                    class="absolute top-1/4 right-0 h-[400px] w-[400px] rounded-full bg-gradient-to-br from-emerald-500/20 to-cyan-500/20 blur-3xl dark:from-emerald-500/10 dark:to-cyan-500/10"
                />
            </div>

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-4xl text-center">
                    <!-- Badge -->
                    <div class="mb-6 inline-flex items-center gap-2">
                        <Badge variant="secondary" class="gap-1.5 px-3 py-1">
                            <Sparkles class="h-3.5 w-3.5" />
                            Open Source & Free Forever
                        </Badge>
                    </div>

                    <!-- Main heading -->
                    <h1 class="text-foreground mb-6 text-4xl font-bold tracking-tight sm:text-5xl md:text-6xl lg:text-7xl">
                        Take Control of Your
                        <span class="relative">
                            <span class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent dark:from-blue-400 dark:via-purple-400 dark:to-pink-400">
                                Bills & Expenses
                            </span>
                        </span>
                    </h1>

                    <p class="text-muted-foreground mx-auto mb-8 max-w-2xl text-lg sm:text-xl">
                        The simple, beautiful way to track your bills, set payment reminders, and understand where your money goes.
                        No credit card required.
                    </p>

                    <!-- CTA buttons -->
                    <div class="flex flex-col justify-center gap-3 sm:flex-row sm:gap-4">
                        <Link :href="route('register')">
                            <Button size="lg" class="w-full gap-2 sm:w-auto">
                                Start Free
                                <ArrowRight class="h-4 w-4" />
                            </Button>
                        </Link>
                        <a
                            href="https://github.com/4msar/bill-organizer"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <Button size="lg" variant="outline" class="w-full gap-2 sm:w-auto">
                                <Github class="h-4 w-4" />
                                Star on GitHub
                            </Button>
                        </a>
                    </div>

                    <!-- Trust indicators -->
                    <div class="mt-10 flex flex-wrap items-center justify-center gap-6 text-sm">
                        <div class="text-muted-foreground flex items-center gap-2">
                            <Check class="text-primary h-4 w-4" />
                            Free forever
                        </div>
                        <div class="text-muted-foreground flex items-center gap-2">
                            <Check class="text-primary h-4 w-4" />
                            No credit card needed
                        </div>
                        <div class="text-muted-foreground flex items-center gap-2">
                            <Check class="text-primary h-4 w-4" />
                            Self-hostable
                        </div>
                    </div>
                </div>

                <!-- Dashboard Preview -->
                <div class="relative mx-auto mt-16 max-w-5xl">
                    <div class="bg-card/50 rounded-xl border p-2 shadow-2xl backdrop-blur-sm sm:p-3">
                        <div class="bg-muted/50 aspect-video overflow-hidden rounded-lg border">
                            <div class="flex h-full flex-col">
                                <!-- Mock header -->
                                <div class="border-border/50 flex items-center gap-2 border-b px-4 py-2">
                                    <div class="flex gap-1.5">
                                        <div class="h-3 w-3 rounded-full bg-red-400/60" />
                                        <div class="h-3 w-3 rounded-full bg-yellow-400/60" />
                                        <div class="h-3 w-3 rounded-full bg-green-400/60" />
                                    </div>
                                    <div class="bg-muted ml-4 h-5 flex-1 rounded-md" />
                                </div>
                                <!-- Mock content -->
                                <div class="flex flex-1 p-4">
                                    <div class="hidden w-48 space-y-2 sm:block">
                                        <div class="bg-primary/10 h-8 rounded-md" />
                                        <div class="bg-muted h-6 rounded-md" />
                                        <div class="bg-muted h-6 rounded-md" />
                                        <div class="bg-muted h-6 rounded-md" />
                                    </div>
                                    <div class="flex-1 space-y-3 sm:ml-4">
                                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                            <div class="bg-card h-20 rounded-lg border p-3">
                                                <div class="bg-muted mb-2 h-3 w-16 rounded" />
                                                <div class="bg-primary/20 h-6 w-20 rounded" />
                                            </div>
                                            <div class="bg-card h-20 rounded-lg border p-3">
                                                <div class="bg-muted mb-2 h-3 w-16 rounded" />
                                                <div class="bg-emerald-500/20 h-6 w-16 rounded" />
                                            </div>
                                            <div class="bg-card h-20 rounded-lg border p-3">
                                                <div class="bg-muted mb-2 h-3 w-16 rounded" />
                                                <div class="bg-amber-500/20 h-6 w-14 rounded" />
                                            </div>
                                            <div class="bg-card h-20 rounded-lg border p-3">
                                                <div class="bg-muted mb-2 h-3 w-16 rounded" />
                                                <div class="bg-blue-500/20 h-6 w-20 rounded" />
                                            </div>
                                        </div>
                                        <div class="bg-card h-40 rounded-lg border" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Glow effect -->
                    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-blue-500/20 via-purple-500/20 to-pink-500/20 blur-3xl" />
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="border-border/40 border-y py-12 sm:py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 gap-8 md:grid-cols-4">
                    <div v-for="stat in stats" :key="stat.label" class="text-center">
                        <div class="text-foreground text-3xl font-bold sm:text-4xl">{{ stat.value }}</div>
                        <div class="text-muted-foreground mt-1 text-sm">{{ stat.label }}</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center sm:mb-16">
                    <Badge variant="outline" class="mb-4">Features</Badge>
                    <h2 class="text-foreground mb-4 text-3xl font-bold tracking-tight sm:text-4xl">
                        Everything you need to manage bills
                    </h2>
                    <p class="text-muted-foreground text-lg">
                        A complete toolkit designed to help you stay organized and never miss a payment.
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <Card
                        v-for="feature in coreFeatures"
                        :key="feature.title"
                        class="group bg-card/50 hover:bg-card border-border/50 hover:border-border transition-all duration-200 hover:shadow-md"
                    >
                        <CardContent class="p-6">
                            <div class="bg-primary/10 text-primary mb-4 flex h-11 w-11 items-center justify-center rounded-lg transition-colors group-hover:bg-primary/20">
                                <component :is="feature.icon" class="h-5 w-5" />
                            </div>
                            <h3 class="text-foreground mb-2 text-lg font-semibold">{{ feature.title }}</h3>
                            <p class="text-muted-foreground text-sm leading-relaxed">{{ feature.description }}</p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <!-- How it Works Section -->
        <section class="bg-muted/30 border-border/40 border-y py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto mb-12 max-w-3xl text-center sm:mb-16">
                    <Badge variant="outline" class="mb-4">How It Works</Badge>
                    <h2 class="text-foreground mb-4 text-3xl font-bold tracking-tight sm:text-4xl">
                        Get started in minutes
                    </h2>
                    <p class="text-muted-foreground text-lg">
                        Three simple steps to take control of your finances.
                    </p>
                </div>

                <div class="grid gap-8 md:grid-cols-3">
                    <div v-for="item in steps" :key="item.step" class="relative text-center">
                        <div class="bg-primary/10 text-primary mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl">
                            <component :is="item.icon" class="h-7 w-7" />
                        </div>
                        <div class="text-primary mb-2 text-sm font-semibold">Step {{ item.step }}</div>
                        <h3 class="text-foreground mb-2 text-xl font-semibold">{{ item.title }}</h3>
                        <p class="text-muted-foreground text-sm leading-relaxed">{{ item.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Open Source Section -->
        <section class="py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="bg-gradient-to-br from-slate-900 to-slate-800 dark:from-slate-800 dark:to-slate-900 overflow-hidden rounded-2xl">
                    <div class="relative px-6 py-12 sm:px-12 sm:py-16">
                        <!-- Background pattern -->
                        <div class="absolute inset-0 opacity-10">
                            <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff10_1px,transparent_1px),linear-gradient(to_bottom,#ffffff10_1px,transparent_1px)] bg-[size:24px_24px]" />
                        </div>
                        
                        <div class="relative flex flex-col items-center gap-8 lg:flex-row lg:justify-between">
                            <div class="max-w-xl text-center lg:text-left">
                                <div class="mb-4 flex items-center justify-center gap-2 lg:justify-start">
                                    <Github class="h-6 w-6 text-white" />
                                    <span class="text-sm font-medium text-white/80">Open Source</span>
                                </div>
                                <h2 class="mb-4 text-3xl font-bold text-white sm:text-4xl">
                                    Built by the community, for the community
                                </h2>
                                <p class="text-lg text-white/70">
                                    Bill Organizer is 100% open source. Contribute, customize, or self-host it yourself. 
                                    Your data, your rules.
                                </p>
                            </div>
                            
                            <div class="flex flex-col gap-3 sm:flex-row">
                                <a
                                    href="https://github.com/4msar/bill-organizer"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <Button size="lg" variant="secondary" class="w-full gap-2 sm:w-auto">
                                        <Star class="h-4 w-4" />
                                        Star on GitHub
                                    </Button>
                                </a>
                                <Link :href="route('register')">
                                    <Button size="lg" class="w-full gap-2 bg-white text-slate-900 hover:bg-white/90 sm:w-auto">
                                        Try It Free
                                        <ArrowRight class="h-4 w-4" />
                                    </Button>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Final CTA -->
        <section class="border-border/40 border-t py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-3xl text-center">
                    <h2 class="text-foreground mb-4 text-3xl font-bold tracking-tight sm:text-4xl">
                        Ready to simplify your bill management?
                    </h2>
                    <p class="text-muted-foreground mb-8 text-lg">
                        Join users who have already taken control of their finances. Start for free today.
                    </p>
                    <div class="flex flex-col justify-center gap-3 sm:flex-row">
                        <Link :href="route('register')">
                            <Button size="lg" class="w-full gap-2 sm:w-auto">
                                Create Free Account
                                <ArrowRight class="h-4 w-4" />
                            </Button>
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-border/40 border-t py-8 sm:py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-8 md:flex-row md:items-center md:justify-between">
                    <div class="flex flex-col items-center gap-4 md:flex-row md:gap-6">
                        <Link href="/" class="flex items-center gap-2">
                            <div class="bg-foreground flex h-7 w-7 items-center justify-center rounded-md">
                                <AppLogoIcon class="text-background h-4 w-4" />
                            </div>
                            <span class="text-foreground font-semibold">{{ $page.props.name }}</span>
                        </Link>
                        <p class="text-muted-foreground text-center text-sm md:text-left">
                            © {{ new Date().getFullYear() }} {{ $page.props.name }}. All rights reserved.
                        </p>
                    </div>
                    
                    <nav class="flex flex-wrap items-center justify-center gap-6">
                        <Link :href="route('legal.terms')" class="text-muted-foreground hover:text-foreground text-sm transition-colors">
                            Terms
                        </Link>
                        <Link :href="route('legal.privacy')" class="text-muted-foreground hover:text-foreground text-sm transition-colors">
                            Privacy
                        </Link>
                        <Link :href="route('legal.contact')" class="text-muted-foreground hover:text-foreground text-sm transition-colors">
                            Contact
                        </Link>
                        <a
                            href="https://github.com/4msar/bill-organizer"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-muted-foreground hover:text-foreground flex items-center gap-1.5 text-sm transition-colors"
                        >
                            <Github class="h-4 w-4" />
                            GitHub
                        </a>
                    </nav>
                </div>
            </div>
        </footer>
    </div>
</template>
