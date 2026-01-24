<script lang="ts" setup>
import { Button } from '@/components/ui/button';
import { SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight, Github } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogoIcon from '../shared/AppLogoIcon.vue';
import ThemeSwitcher from '../shared/ThemeSwitcher.vue';

const page = usePage<SharedData>();

// Check if user is logged in
const isAuthenticated = computed(() => page.props.auth.user);
</script>

<template>
    <header class="border-border/40 bg-background/80 sticky top-0 z-50 w-full border-b backdrop-blur-md print:hidden">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
            <Link href="/" class="flex items-center gap-2">
                <div class="bg-foreground flex h-8 w-8 items-center justify-center rounded-lg">
                    <AppLogoIcon class="text-background h-5 w-5" />
                </div>
                <span class="text-foreground text-lg font-semibold">{{ $page.props.name }}</span>
            </Link>

            <nav class="flex items-center gap-2 sm:gap-3">
                <ThemeSwitcher />
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
</template>
