<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Github } from 'lucide-vue-next';
import AppLogo from '../shared/AppLogo.vue';
</script>

<template>
    <!-- Footer -->
    <footer class="border-border/40 border-t py-8 sm:py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-8 md:flex-row md:items-center md:justify-between">
                <div class="flex flex-col items-center gap-4 md:flex-row md:gap-6">
                    <p class="text-muted-foreground text-center text-sm md:text-left">
                        © {{ new Date().getFullYear() }} {{ $page.props.name }}. All rights reserved.
                    </p>
                </div>

                <div class="flex w-auto justify-center">
                    <Link href="/" class="flex items-center gap-2">
                        <AppLogo class="text-foreground h-8 w-8" />
                    </Link>
                </div>

                <nav class="flex flex-wrap items-center justify-center gap-6">
                    <Link :href="route('legal.terms')" class="text-muted-foreground hover:text-foreground text-sm transition-colors"> Terms </Link>
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
</template>
