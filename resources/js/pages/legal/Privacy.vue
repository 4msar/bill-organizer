<script setup lang="ts">
import Footer from '@/components/pages/Footer.vue';
import Header from '@/components/pages/Header.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Privacy Policy - Bill Organizer" />

    <div class="bg-background flex min-h-screen flex-col">
        <!-- Navigation -->
        <Header />

        <!-- Content -->
        <main class="flex-1 py-12">
            <div class="container mx-auto max-w-4xl px-4">
                <h1 class="text-foreground mb-8 text-4xl font-bold">Privacy Policy</h1>
                <div class="text-muted-foreground space-y-6 text-base leading-relaxed">
                    <section>
                        <p class="mb-4"><strong>Last Updated:</strong> {{ new Date().getFullYear() }}</p>
                        <p>
                            At Bill Organizer, we take your privacy seriously. This Privacy Policy explains how we collect, use, disclose, and
                            safeguard your information when you use our service.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-foreground mb-3 text-2xl font-semibold">1. Information We Collect</h2>
                        <p>We collect information that you provide directly to us when using Bill Organizer:</p>

                        <h3 class="text-foreground mt-4 mb-2 text-xl font-semibold">Personal Information</h3>
                        <ul class="ml-6 list-disc space-y-1">
                            <li>Name and email address</li>
                            <li>Account credentials (encrypted password)</li>
                            <li>Profile preferences and settings</li>
                            <li>Team information and membership details</li>
                        </ul>

                        <h3 class="text-foreground mt-4 mb-2 text-xl font-semibold">Financial Information</h3>
                        <ul class="ml-6 list-disc space-y-1">
                            <li>Bill details (names, amounts, due dates, payment status)</li>
                            <li>Payment history and transaction records</li>
                            <li>Category and tag information</li>
                            <li>Recurring bill configurations (frequency, amounts)</li>
                            <li>Notes and attachments related to bills</li>
                        </ul>

                        <h3 class="text-foreground mt-4 mb-2 text-xl font-semibold">Usage Information</h3>
                        <ul class="ml-6 list-disc space-y-1">
                            <li>Log data (IP address, browser type, device information)</li>
                            <li>Pages visited and features used</li>
                            <li>Date and time of access</li>
                            <li>Notification preferences and interaction history</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-foreground mb-3 text-2xl font-semibold">2. How We Use Your Information</h2>
                        <p>We use the information we collect for the following purposes:</p>
                        <ul class="mt-2 ml-6 list-disc space-y-1">
                            <li>
                                <strong>Bill Management:</strong> To provide core functionality for tracking and organizing your bills and
                                subscriptions
                            </li>
                            <li>
                                <strong>Reminders and Notifications:</strong> To send email notifications about upcoming due dates, payment reminders,
                                and important updates
                            </li>
                            <li>
                                <strong>Team Collaboration:</strong> To enable team features, including sharing bills and managing team membership
                            </li>
                            <li><strong>Analytics and Reports:</strong> To generate insights about your spending patterns and financial trends</li>
                            <li><strong>Service Improvement:</strong> To analyze usage patterns and improve our features and user experience</li>
                            <li><strong>Security:</strong> To protect against unauthorized access and ensure account security</li>
                            <li>
                                <strong>Communication:</strong> To respond to your inquiries, provide support, and send service-related announcements
                            </li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-foreground mb-3 text-2xl font-semibold">3. Team Data and Sharing</h2>
                        <p>When you use team features in Bill Organizer:</p>
                        <ul class="mt-2 ml-6 list-disc space-y-1">
                            <li>Bills and transactions created within a team are visible to all team members</li>
                            <li>Team members can view and edit shared financial data</li>
                            <li>Your email address may be visible to team members when you join or create a team</li>
                            <li>Team owners have administrative control over team data and membership</li>
                            <li>We do not share your personal team data with users outside your teams</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-foreground mb-3 text-2xl font-semibold">4. Email Notifications</h2>
                        <p>We send email notifications for:</p>
                        <ul class="mt-2 ml-6 list-disc space-y-1">
                            <li>Upcoming bill due dates and payment reminders</li>
                            <li>Team invitations and membership changes</li>
                            <li>Important account updates and security alerts</li>
                            <li>Service announcements and new features</li>
                        </ul>
                        <p class="mt-2">
                            You can manage your notification preferences in your account settings. However, we may still send you important
                            service-related or security notifications even if you opt out of other communications.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-foreground mb-3 text-2xl font-semibold">5. Data Security</h2>
                        <p>We implement appropriate technical and organizational measures to protect your information:</p>
                        <ul class="mt-2 ml-6 list-disc space-y-1">
                            <li>Passwords are encrypted using industry-standard hashing algorithms</li>
                            <li>Data is transmitted over secure HTTPS connections</li>
                            <li>Access to personal data is restricted to authorized personnel only</li>
                            <li>Regular security audits and updates</li>
                            <li>Monitoring for suspicious activity and unauthorized access attempts</li>
                        </ul>
                        <p class="mt-2">
                            However, no method of transmission over the Internet or electronic storage is 100% secure. We cannot guarantee absolute
                            security of your data.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-foreground mb-3 text-2xl font-semibold">6. Data Retention</h2>
                        <p>We retain your information for as long as your account is active or as needed to provide our services:</p>
                        <ul class="mt-2 ml-6 list-disc space-y-1">
                            <li>Account information is retained until you delete your account</li>
                            <li>Bills and transaction history are retained to provide historical tracking</li>
                            <li>Log data may be retained for security and troubleshooting purposes</li>
                            <li>Some anonymized data may be retained for analytics after account deletion</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-foreground mb-3 text-2xl font-semibold">7. Your Rights</h2>
                        <p>You have the following rights regarding your personal data:</p>
                        <ul class="mt-2 ml-6 list-disc space-y-1">
                            <li><strong>Access:</strong> You can access your personal information through your account settings</li>
                            <li><strong>Correction:</strong> You can update or correct your information at any time</li>
                            <li><strong>Deletion:</strong> You can request deletion of your account and associated data</li>
                            <li><strong>Export:</strong> You can export your bill and transaction data</li>
                            <li><strong>Opt-out:</strong> You can manage notification preferences and opt out of non-essential communications</li>
                        </ul>
                        <p class="mt-2">
                            To exercise these rights, please use the settings in your account or contact us through the
                            <Link :href="route('legal.contact')" class="text-primary hover:underline">Contact page</Link>.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-foreground mb-3 text-2xl font-semibold">8. Cookies and Tracking</h2>
                        <p>Bill Organizer uses cookies and similar technologies to:</p>
                        <ul class="mt-2 ml-6 list-disc space-y-1">
                            <li>Maintain your login session and remember your preferences</li>
                            <li>Analyze site usage and improve functionality</li>
                            <li>Provide security features and prevent fraud</li>
                            <li>Remember your theme and display preferences</li>
                        </ul>
                        <p class="mt-2">
                            You can control cookie preferences through your browser settings. However, disabling cookies may limit some functionality
                            of the service.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-foreground mb-3 text-2xl font-semibold">9. Third-Party Services</h2>
                        <p>Bill Organizer may use third-party services for:</p>
                        <ul class="mt-2 ml-6 list-disc space-y-1">
                            <li>Email delivery and notifications</li>
                            <li>Hosting and infrastructure</li>
                            <li>Analytics and monitoring</li>
                        </ul>
                        <p class="mt-2">
                            These third-party service providers have access only to information necessary to perform their functions and are obligated
                            not to disclose or use it for other purposes.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-foreground mb-3 text-2xl font-semibold">10. International Data Transfers</h2>
                        <p>
                            Your information may be transferred to and processed in countries other than your country of residence. These countries
                            may have different data protection laws. By using Bill Organizer, you consent to such transfers.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-foreground mb-3 text-2xl font-semibold">11. Children's Privacy</h2>
                        <p>
                            Bill Organizer is not intended for use by children under the age of 13. We do not knowingly collect personal information
                            from children under 13. If you become aware that a child has provided us with personal information, please contact us.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-foreground mb-3 text-2xl font-semibold">12. Open Source Transparency</h2>
                        <p>
                            Bill Organizer is open-source software. You can review our data handling practices by examining the source code on
                            <a
                                href="https://github.com/4msar/bill-organizer"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-primary hover:underline"
                            >
                                GitHub </a
                            >. This transparency allows you to verify our privacy practices.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-foreground mb-3 text-2xl font-semibold">13. Changes to This Privacy Policy</h2>
                        <p>We may update this Privacy Policy from time to time. We will notify you of any material changes by:</p>
                        <ul class="mt-2 ml-6 list-disc space-y-1">
                            <li>Posting the new Privacy Policy on this page</li>
                            <li>Updating the "Last Updated" date</li>
                            <li>Sending an email notification for significant changes</li>
                        </ul>
                        <p class="mt-2">
                            We encourage you to review this Privacy Policy periodically to stay informed about how we protect your information.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-foreground mb-3 text-2xl font-semibold">14. Contact Us</h2>
                        <p>
                            If you have any questions, concerns, or requests regarding this Privacy Policy or our data practices, please contact us:
                        </p>
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
                            By using Bill Organizer, you acknowledge that you have read and understood this Privacy Policy and agree to the collection
                            and use of information as described herein.
                        </p>
                    </section>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <Footer />
    </div>
</template>
