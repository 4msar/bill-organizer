<template>
  <div class="mt-6">
    <div class="flex rounded-md border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20">
      <AlertCircle class="mt-0.5 mr-3 h-5 w-5 text-yellow-600 dark:text-yellow-500" />
      <div>
        <h3 class="font-medium text-yellow-800 dark:text-yellow-500">No categories found</h3>
        <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-400">
          Categories help you organize your bills and track expenses by type. Click the "Add Category" button to create your
          first category.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { AlertCircle } from 'lucide-vue-next';
</script>