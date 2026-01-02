<script setup lang="ts">
import AppLogo from '@/components/shared/AppLogo.vue';
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Terms of Service - Bill Organizer" />

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
                <h1 class="text-foreground mb-8 text-4xl font-bold">Terms of Service</h1>
                <div class="text-muted-foreground space-y-6 text-base leading-relaxed">
                    <section>
                        <p class="mb-4"><strong>Last Updated:</strong> {{ new Date().getFullYear() }}</p>
                        <p>
                            Welcome to Bill Organizer. By accessing or using our service, you agree to be bound by these Terms of Service. Please read
                            them carefully.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-foreground mb-3 text-2xl font-semibold">1. Service Description</h2>
                        <p>
                            Bill Organizer is a web-based application that helps you manage and track your bills, subscriptions, and financial
                            obligations. Our service includes:
                        </p>
                        <ul class="mt-2 ml-6 list-disc space-y-1">
                            <li>Bill and subscription management</li>
                            <li>Payment reminders and notifications via email</li>
                            <li>Team collaboration features for shared bill management</li>
                            <li>Categories and transaction tracking</li>
                            <li>Recurring bills (weekly, monthly, yearly) management</li>
                            <li>Analytics and reporting</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-foreground mb-3 text-2xl font-semibold">2. User Accounts</h2>
                        <p>To use Bill Organizer, you must create an account. You are responsible for:</p>
                        <ul class="mt-2 ml-6 list-disc space-y-1">
                            <li>Maintaining the confidentiality of your account credentials</li>
                            <li>All activities that occur under your account</li>
                            <li>Ensuring the accuracy of information you provide</li>
                            <li>Notifying us immediately of any unauthorized use of your account</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-foreground mb-3 text-2xl font-semibold">3. Data Collection and Usage</h2>
                        <p>When you use Bill Organizer, we collect and process information necessary to provide our service, including:</p>
                        <ul class="mt-2 ml-6 list-disc space-y-1">
                            <li>Bill information (names, amounts, due dates, payment status)</li>
                            <li>Payment details and transaction history</li>
                            <li>Category and tag information</li>
                            <li>Email notifications and reminder preferences</li>
                            <li>Team membership and collaboration data</li>
                        </ul>
                        <p class="mt-2">
                            For detailed information about data collection and privacy, please refer to our
                            <Link :href="route('legal.privacy')" class="text-primary hover:underline">Privacy Policy</Link>.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-foreground mb-3 text-2xl font-semibold">4. Team Features</h2>
                        <p>Bill Organizer allows you to create and join teams for collaborative bill management. When using team features:</p>
                        <ul class="mt-2 ml-6 list-disc space-y-1">
                            <li>You can invite other users to join your team via email</li>
                            <li>Team members can view and manage shared bills and transactions</li>
                            <li>Team owners are responsible for managing team membership</li>
                            <li>You are responsible for the accuracy of information shared within your team</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-foreground mb-3 text-2xl font-semibold">5. Acceptable Use Policy</h2>
                        <p>You agree not to:</p>
                        <ul class="mt-2 ml-6 list-disc space-y-1">
                            <li>Use the service for any illegal or unauthorized purpose</li>
                            <li>Attempt to gain unauthorized access to any part of the service</li>
                            <li>Interfere with or disrupt the service or servers</li>
                            <li>Use automated means to access the service without permission</li>
                            <li>Share your account credentials with others</li>
                            <li>Upload malicious code or viruses</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-foreground mb-3 text-2xl font-semibold">6. Notifications and Communications</h2>
                        <p>By using Bill Organizer, you consent to receive email notifications about:</p>
                        <ul class="mt-2 ml-6 list-disc space-y-1">
                            <li>Upcoming bill due dates and payment reminders</li>
                            <li>Team invitations and collaboration updates</li>
                            <li>Service announcements and important updates</li>
                            <li>Security alerts and account notifications</li>
                        </ul>
                        <p class="mt-2">You can manage your notification preferences in your account settings.</p>
                    </section>

                    <section>
                        <h2 class="text-foreground mb-3 text-2xl font-semibold">7. Disclaimer of Warranties</h2>
                        <p>
                            Bill Organizer is provided "as is" and "as available" without warranties of any kind, either express or implied. We do not
                            guarantee that:
                        </p>
                        <ul class="mt-2 ml-6 list-disc space-y-1">
                            <li>The service will be uninterrupted, timely, secure, or error-free</li>
                            <li>The results obtained from using the service will be accurate or reliable</li>
                            <li>Any errors in the service will be corrected</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-foreground mb-3 text-2xl font-semibold">8. Limitation of Liability</h2>
                        <p>
                            To the maximum extent permitted by law, Bill Organizer and its contributors shall not be liable for any indirect,
                            incidental, special, consequential, or punitive damages, including:
                        </p>
                        <ul class="mt-2 ml-6 list-disc space-y-1">
                            <li>Loss of profits, data, or other intangible losses</li>
                            <li>Missed payments or late fees resulting from service errors</li>
                            <li>Damage caused by your reliance on the service</li>
                            <li>Unauthorized access to or alteration of your data</li>
                        </ul>
                        <p class="mt-2">
                            You are responsible for ensuring timely payment of your bills. Bill Organizer is a tracking tool and does not make
                            payments on your behalf.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-foreground mb-3 text-2xl font-semibold">9. Open Source License</h2>
                        <p>
                            Bill Organizer is open-source software licensed under the
                            <a
                                href="https://www.gnu.org/licenses/gpl-3.0.en.html"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-primary hover:underline"
                            >
                                GNU General Public License v3.0 (GPL-3.0) </a
                            >.
                        </p>
                        <p class="mt-2">
                            The source code is available on
                            <a
                                href="https://github.com/4msar/bill-organizer"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-primary hover:underline"
                            >
                                GitHub </a
                            >. You are free to view, modify, and distribute the software in accordance with the GPL-3.0 license terms.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-foreground mb-3 text-2xl font-semibold">10. Data Retention and Account Deletion</h2>
                        <p>You may request deletion of your account and associated data at any time. Upon deletion:</p>
                        <ul class="mt-2 ml-6 list-disc space-y-1">
                            <li>Your personal information will be permanently removed</li>
                            <li>Bills and transactions you created will be deleted</li>
                            <li>Team data you own will be removed</li>
                            <li>Some anonymized data may be retained for analytics purposes</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-foreground mb-3 text-2xl font-semibold">11. Changes to Terms</h2>
                        <p>
                            We reserve the right to modify these Terms of Service at any time. We will notify users of any material changes via email
                            or through the service. Continued use of the service after changes constitutes acceptance of the modified terms.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-foreground mb-3 text-2xl font-semibold">12. Governing Law</h2>
                        <p>
                            These Terms shall be governed by and construed in accordance with applicable laws, without regard to conflict of law
                            principles.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-foreground mb-3 text-2xl font-semibold">13. Contact Information</h2>
                        <p>If you have any questions about these Terms of Service, please contact us:</p>
                        <ul class="mt-2 ml-6 list-disc space-y-1">
                            <li>
                                GitHub Repository:
                                <a
                                    href="https://github.com/4msar/bill-organizer"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-primary hover:underline"
                                >
                                    https://github.com/4msar/bill-organizer
                                </a>
                            </li>
                            <li>
                                Issue Tracker:
                                <a
                                    href="https://github.com/4msar/bill-organizer/issues"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-primary hover:underline"
                                >
                                    https://github.com/4msar/bill-organizer/issues
                                </a>
                            </li>
                            <li>
                                Owner:
                                <a href="https://github.com/4msar" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline">
                                    4msar
                                </a>
                            </li>
                        </ul>
                        <p class="mt-2">
                            For more contact options, visit our
                            <Link :href="route('legal.contact')" class="text-primary hover:underline">Contact page</Link>.
                        </p>
                    </section>

                    <section class="border-border/40 border-t pt-6">
                        <p class="text-sm">
                            By using Bill Organizer, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service.
                        </p>
                    </section>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-border/40 border-t py-8">
            <div class="container mx-auto px-4">
                <div class="flex flex-col items-center justify-between gap-4 md:flex-row">
                    <div class="text-muted-foreground text-sm">© {{ new Date().getFullYear() }} {{ $page.props.name }}. All rights reserved.</div>
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
