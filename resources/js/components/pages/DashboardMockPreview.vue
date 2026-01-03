<template>
    <div class="relative mx-auto mt-16 max-w-6xl">
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
                        <div class="bg-muted h-5 w-16 rounded-md" />
                    </div>
                    <!-- Mock content -->
                    <div class="flex flex-1 p-4">
                        <!-- Sidebar -->
                        <div class="hidden w-56 space-y-2 sm:block">
                            <div class="bg-primary/15 h-8 rounded-md" />
                            <div class="space-y-1">
                                <div class="bg-muted h-6 rounded-md" />
                                <div class="bg-muted h-6 rounded-md" />
                                <div class="bg-muted h-6 rounded-md" />
                                <div class="bg-muted h-6 rounded-md" />
                            </div>
                            <div class="pt-2">
                                <div class="bg-muted/60 mb-1 h-4 w-20 rounded" />
                                <div class="bg-muted h-5 rounded-md" />
                                <div class="bg-muted h-5 rounded-md" />
                            </div>
                        </div>
                        <!-- Main content -->
                        <div class="flex-1 space-y-4 sm:ml-6">
                            <!-- Stats cards -->
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                <div class="bg-card h-20 rounded-lg border p-3 shadow-sm">
                                    <div class="bg-muted mb-2 h-3 w-16 rounded" />
                                    <div class="bg-primary/20 flex h-6 w-20 items-center justify-center rounded">
                                        <div class="bg-primary/40 h-3 w-12 rounded" />
                                    </div>
                                </div>
                                <div class="bg-card h-20 rounded-lg border p-3 shadow-sm">
                                    <div class="bg-muted mb-2 h-3 w-16 rounded" />
                                    <div class="flex h-6 w-16 items-center justify-center rounded bg-emerald-500/20">
                                        <div class="h-3 w-10 rounded bg-emerald-500/40" />
                                    </div>
                                </div>
                                <div class="bg-card h-20 rounded-lg border p-3 shadow-sm">
                                    <div class="bg-muted mb-2 h-3 w-16 rounded" />
                                    <div class="flex h-6 w-14 items-center justify-center rounded bg-amber-500/20">
                                        <div class="h-3 w-8 rounded bg-amber-500/40" />
                                    </div>
                                </div>
                                <div class="bg-card h-20 rounded-lg border p-3 shadow-sm">
                                    <div class="bg-muted mb-2 h-3 w-16 rounded" />
                                    <div class="flex h-6 w-20 items-center justify-center rounded bg-blue-500/20">
                                        <div class="h-3 w-14 rounded bg-blue-500/40" />
                                    </div>
                                </div>
                            </div>

                            <!-- Chart and bills section -->
                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                                <!-- Chart -->
                                <div class="bg-card rounded-lg border p-4 shadow-sm lg:col-span-2">
                                    <div class="mb-3 flex items-center justify-between">
                                        <div class="bg-muted h-4 w-24 rounded" />
                                        <div class="bg-muted h-6 w-16 rounded" />
                                    </div>
                                    <div class="relative h-32">
                                        <div class="absolute bottom-0 left-0 h-24 w-full rounded bg-gradient-to-t from-blue-500/10 to-purple-500/5" />
                                        <div class="absolute bottom-0 left-0 flex h-24 w-full items-end justify-between px-2">
                                            <div class="w-4 rounded-t bg-blue-500/30" style="height: 60%" />
                                            <div class="w-4 rounded-t bg-purple-500/30" style="height: 80%" />
                                            <div class="w-4 rounded-t bg-pink-500/30" style="height: 45%" />
                                            <div class="w-4 rounded-t bg-emerald-500/30" style="height: 90%" />
                                            <div class="w-4 rounded-t bg-amber-500/30" style="height: 70%" />
                                            <div class="w-4 rounded-t bg-cyan-500/30" style="height: 55%" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Upcoming bills -->
                                <div class="bg-card rounded-lg border p-4 shadow-sm">
                                    <div class="bg-muted mb-3 h-4 w-20 rounded" />
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 rounded-full bg-red-500/20" />
                                            <div class="flex-1">
                                                <div class="bg-muted mb-1 h-3 w-16 rounded" />
                                                <div class="bg-muted h-2 w-12 rounded" />
                                            </div>
                                            <div class="h-4 w-10 rounded bg-red-500/30" />
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 rounded-full bg-amber-500/20" />
                                            <div class="flex-1">
                                                <div class="bg-muted mb-1 h-3 w-20 rounded" />
                                                <div class="bg-muted h-2 w-14 rounded" />
                                            </div>
                                            <div class="h-4 w-12 rounded bg-amber-500/30" />
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 rounded-full bg-green-500/20" />
                                            <div class="flex-1">
                                                <div class="bg-muted mb-1 h-3 w-18 rounded" />
                                                <div class="bg-muted h-2 w-16 rounded" />
                                            </div>
                                            <div class="h-4 w-14 rounded bg-green-500/30" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent activity -->
                            <div class="bg-card rounded-lg border p-4 shadow-sm">
                                <div class="mb-3 flex items-center justify-between">
                                    <div class="bg-muted h-4 w-28 rounded" />
                                    <div class="bg-muted h-6 w-20 rounded" />
                                </div>
                                <div class="space-y-2">
                                    <div class="hover:bg-muted/30 flex items-center gap-3 rounded p-2">
                                        <div class="h-6 w-6 rounded bg-blue-500/20" />
                                        <div class="flex-1">
                                            <div class="bg-muted mb-1 h-3 w-32 rounded" />
                                            <div class="bg-muted h-2 w-24 rounded" />
                                        </div>
                                        <div class="bg-muted h-3 w-12 rounded" />
                                    </div>
                                    <div class="hover:bg-muted/30 flex items-center gap-3 rounded p-2">
                                        <div class="h-6 w-6 rounded bg-green-500/20" />
                                        <div class="flex-1">
                                            <div class="bg-muted mb-1 h-3 w-40 rounded" />
                                            <div class="bg-muted h-2 w-20 rounded" />
                                        </div>
                                        <div class="bg-muted h-3 w-16 rounded" />
                                    </div>
                                    <div class="hover:bg-muted/30 flex items-center gap-3 rounded p-2">
                                        <div class="h-6 w-6 rounded bg-purple-500/20" />
                                        <div class="flex-1">
                                            <div class="bg-muted mb-1 h-3 w-28 rounded" />
                                            <div class="bg-muted h-2 w-32 rounded" />
                                        </div>
                                        <div class="bg-muted h-3 w-14 rounded" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Enhanced glow effect -->
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-blue-500/20 via-purple-500/20 to-pink-500/20 blur-3xl" />
        <div class="absolute -inset-2 -z-20 rounded-2xl bg-gradient-to-r from-blue-500/10 via-purple-500/10 to-pink-500/10 blur-2xl" />
    </div>
</template>
