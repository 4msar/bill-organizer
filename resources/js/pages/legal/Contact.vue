<script setup lang="ts">
import AppLogo from '@/components/shared/AppLogo.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Contact - Bill Organizer" />

    <div class="bg-background flex min-h-screen flex-col">
        <!-- Navigation -->
        <header class="border-border/40 border-b">
            <div class="container mx-auto flex items-center justify-between px-4 py-4">
                <!-- Logo -->
                <Link href="/" class="flex items-center space-x-2">
                    <AppLogo class="text-black dark:text-white" />
                </Link>

                <!-- Navigation -->
                <nav class="flex items-center gap-4">
                    <Link :href="route('home')">
                        <Button variant="ghost">Home</Button>
                    </Link>
                </nav>
            </div>
        </header>

        <!-- Content -->
        <main class="flex-1 py-12">
            <div class="container mx-auto max-w-4xl px-4">
                <h1 class="text-foreground mb-4 text-4xl font-bold">Contact Us</h1>
                <p class="text-muted-foreground mb-8 text-lg">
                    We'd love to hear from you! Whether you have questions, feedback, or need support, there are
                    several ways to get in touch.
                </p>

                <div class="grid gap-6 md:grid-cols-2">
                    <!-- GitHub Repository Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <span class="text-2xl">🔗</span>
                                GitHub Repository
                            </CardTitle>
                            <CardDescription>View source code and documentation</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <p class="text-muted-foreground mb-4 text-sm">
                                Bill Organizer is an open-source project. Explore the codebase, read documentation, and
                                stay updated with the latest changes.
                            </p>
                            <a
                                href="https://github.com/4msar/bill-organizer"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-block"
                            >
                                <Button variant="outline" class="w-full">
                                    Visit Repository
                                    <span class="ml-2">→</span>
                                </Button>
                            </a>
                        </CardContent>
                    </Card>

                    <!-- Issue Tracker Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <span class="text-2xl">🐛</span>
                                Report Bugs & Issues
                            </CardTitle>
                            <CardDescription>Found a bug or have a feature request?</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <p class="text-muted-foreground mb-4 text-sm">
                                Use our GitHub issue tracker to report bugs, request features, or ask technical
                                questions. Please check existing issues before creating a new one.
                            </p>
                            <a
                                href="https://github.com/4msar/bill-organizer/issues"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-block"
                            >
                                <Button variant="outline" class="w-full">
                                    Open Issue Tracker
                                    <span class="ml-2">→</span>
                                </Button>
                            </a>
                        </CardContent>
                    </Card>

                    <!-- Project Maintainer Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <span class="text-2xl">👤</span>
                                Project Maintainer
                            </CardTitle>
                            <CardDescription>Connect with the project owner</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <p class="text-muted-foreground mb-4 text-sm">
                                Bill Organizer is maintained by
                                <strong>4msar</strong>. You can reach out directly through GitHub for project-related
                                inquiries.
                            </p>
                            <a
                                href="https://github.com/4msar"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-block"
                            >
                                <Button variant="outline" class="w-full">
                                    View GitHub Profile
                                    <span class="ml-2">→</span>
                                </Button>
                            </a>
                        </CardContent>
                    </Card>

                    <!-- Contributions Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <span class="text-2xl">🤝</span>
                                Contribute
                            </CardTitle>
                            <CardDescription>Help improve Bill Organizer</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <p class="text-muted-foreground mb-4 text-sm">
                                We welcome contributions! Check out our repository for contribution guidelines, open
                                issues labeled "good first issue", and ways to help.
                            </p>
                            <a
                                href="https://github.com/4msar/bill-organizer/blob/main/CONTRIBUTING.md"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-block"
                            >
                                <Button variant="outline" class="w-full">
                                    Contributing Guide
                                    <span class="ml-2">→</span>
                                </Button>
                            </a>
                        </CardContent>
                    </Card>
                </div>

                <!-- How to Report Bugs Section -->
                <div class="mt-12">
                    <h2 class="text-foreground mb-4 text-2xl font-semibold">How to Report a Bug</h2>
                    <Card>
                        <CardContent class="pt-6">
                            <div class="text-muted-foreground space-y-4 text-sm">
                                <p>When reporting a bug, please include:</p>
                                <ol class="ml-6 list-decimal space-y-2">
                                    <li>
                                        <strong>Clear description:</strong> Explain what you expected to happen and what
                                        actually happened
                                    </li>
                                    <li>
                                        <strong>Steps to reproduce:</strong> Provide detailed steps so we can recreate
                                        the issue
                                    </li>
                                    <li>
                                        <strong>Environment details:</strong> Browser version, operating system, and any
                                        relevant configuration
                                    </li>
                                    <li>
                                        <strong>Screenshots:</strong> If applicable, include screenshots or error
                                        messages
                                    </li>
                                    <li>
                                        <strong>Expected behavior:</strong> Describe what you think should happen
                                        instead
                                    </li>
                                </ol>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Feature Requests Section -->
                <div class="mt-8">
                    <h2 class="text-foreground mb-4 text-2xl font-semibold">Requesting a Feature</h2>
                    <Card>
                        <CardContent class="pt-6">
                            <div class="text-muted-foreground space-y-4 text-sm">
                                <p>When requesting a feature, please include:</p>
                                <ol class="ml-6 list-decimal space-y-2">
                                    <li>
                                        <strong>Use case:</strong> Explain the problem you're trying to solve
                                    </li>
                                    <li>
                                        <strong>Proposed solution:</strong> Describe how you envision the feature
                                        working
                                    </li>
                                    <li>
                                        <strong>Alternatives:</strong> Mention any alternative solutions you've
                                        considered
                                    </li>
                                    <li>
                                        <strong>Benefits:</strong> Explain how this feature would benefit users
                                    </li>
                                    <li>
                                        <strong>Examples:</strong> If possible, provide examples from other applications
                                    </li>
                                </ol>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Community Guidelines -->
                <div class="mt-8">
                    <h2 class="text-foreground mb-4 text-2xl font-semibold">Community Guidelines</h2>
                    <Card>
                        <CardContent class="pt-6">
                            <div class="text-muted-foreground space-y-3 text-sm">
                                <p>
                                    Bill Organizer is an open-source community project. We ask all contributors and
                                    users to:
                                </p>
                                <ul class="ml-6 list-disc space-y-1">
                                    <li>Be respectful and considerate in all interactions</li>
                                    <li>Search existing issues before creating new ones</li>
                                    <li>Provide clear and constructive feedback</li>
                                    <li>Follow the project's code of conduct</li>
                                    <li>Help others when possible</li>
                                </ul>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Quick Links -->
                <div class="bg-muted/50 mt-12 rounded-lg p-6">
                    <h2 class="text-foreground mb-4 text-xl font-semibold">Quick Links</h2>
                    <div class="grid gap-3 md:grid-cols-2">
                        <a
                            href="https://github.com/4msar/bill-organizer"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-primary hover:underline flex items-center gap-2 text-sm"
                        >
                            <span>→</span>
                            Project Repository
                        </a>
                        <a
                            href="https://github.com/4msar/bill-organizer/issues"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-primary hover:underline flex items-center gap-2 text-sm"
                        >
                            <span>→</span>
                            Issue Tracker
                        </a>
                        <a
                            href="https://github.com/4msar/bill-organizer/pulls"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-primary hover:underline flex items-center gap-2 text-sm"
                        >
                            <span>→</span>
                            Pull Requests
                        </a>
                        <a
                            href="https://github.com/4msar/bill-organizer/discussions"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-primary hover:underline flex items-center gap-2 text-sm"
                        >
                            <span>→</span>
                            Discussions
                        </a>
                        <Link :href="route('legal.terms')" class="text-primary hover:underline flex items-center gap-2 text-sm">
                            <span>→</span>
                            Terms of Service
                        </Link>
                        <Link :href="route('legal.privacy')" class="text-primary hover:underline flex items-center gap-2 text-sm">
                            <span>→</span>
                            Privacy Policy
                        </Link>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-border/40 border-t py-8">
            <div class="container mx-auto px-4">
                <div class="flex flex-col items-center justify-between gap-4 md:flex-row">
                    <div class="text-muted-foreground text-sm">
                        © {{ new Date().getFullYear() }} {{ $page.props.name }}. All rights reserved.
                    </div>
                    <div class="flex space-x-6">
                        <Link :href="route('legal.terms')" class="text-muted-foreground hover:text-foreground text-sm transition-colors">
                            Terms
                        </Link>
                        <Link :href="route('legal.privacy')" class="text-muted-foreground hover:text-foreground text-sm transition-colors">
                            Privacy
                        </Link>
                        <Link :href="route('legal.contact')" class="text-muted-foreground hover:text-foreground text-sm transition-colors">
                            Contact
                        </Link>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
