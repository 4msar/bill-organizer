<!--
MultiSelect Component

A flexible multi-select component built with shadcn/ui components.

Features:
- Multiple selection with badges
- Search functionality
- Customizable placeholder text
- Maximum selection limit
- Clearable selections
- Keyboard navigation
- Accessible design

Usage:
<MultiSelect
  :options="[
    { label: 'Option 1', value: '1' },
    { label: 'Option 2', value: '2', disabled: true },
    { label: 'Option 3', value: '3' }
  ]"
  v-model="selectedValues"
  placeholder="Choose items..."
  :max-selected="5"
  searchable
  clearable
/>

Props:
- options: Array of { label: string, value: string|number, disabled?: boolean }
- modelValue: Array of selected values
- placeholder: Placeholder text for trigger button
- searchPlaceholder: Placeholder text for search input
- emptyText: Text shown when no options match search
- maxSelected: Maximum number of items that can be selected
- disabled: Disable the entire component
- searchable: Enable/disable search functionality
- clearable: Show clear buttons and clear all option
- class: Additional CSS classes

Events:
- update:modelValue: Emitted when selection changes
- change: Emitted when selection changes (same as update:modelValue)
-->

<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';
import { Check, ChevronsUpDown, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

export interface MultiSelectOption {
    label: string;
    value: string | number;
    disabled?: boolean;
}

interface Props {
    options: MultiSelectOption[];
    modelValue?: (string | number)[];
    placeholder?: string;
    searchPlaceholder?: string;
    emptyText?: string;
    maxSelected?: number;
    disabled?: boolean;
    searchable?: boolean;
    clearable?: boolean;
    class?: string;
}

interface Emits {
    (e: 'update:modelValue', value: (string | number)[]): void;
    (e: 'change', value: (string | number)[]): void;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: () => [],
    placeholder: 'Select items...',
    searchPlaceholder: 'Search...',
    emptyText: 'No items found',
    searchable: true,
    clearable: true,
    disabled: false,
});

const emits = defineEmits<Emits>();

const open = ref(false);
const searchQuery = ref('');

const selectedValues = computed({
    get: () => props.modelValue || [],
    set: (value) => {
        emits('update:modelValue', value);
        emits('change', value);
    },
});

const selectedOptions = computed(() => props.options.filter((option) => selectedValues.value.includes(option.value)));

const filteredOptions = computed(() => {
    if (!props.searchable || !searchQuery.value) {
        return props.options;
    }

    return props.options.filter((option) => option.label.toLowerCase().includes(searchQuery.value.toLowerCase()));
});

const isSelected = (value: string | number) => {
    return selectedValues.value.includes(value);
};

const toggleOption = (option: MultiSelectOption) => {
    if (option.disabled) return;

    const currentValues = [...selectedValues.value];
    const index = currentValues.indexOf(option.value);

    if (index > -1) {
        currentValues.splice(index, 1);
    } else {
        if (props.maxSelected && currentValues.length >= props.maxSelected) {
            return;
        }
        currentValues.push(option.value);
    }

    selectedValues.value = currentValues;
};

const removeOption = (value: string | number) => {
    selectedValues.value = selectedValues.value.filter((v) => v !== value);
};

const clearAll = () => {
    selectedValues.value = [];
    open.value = false;
};

const canSelectMore = computed(() => {
    if (!props.maxSelected) return true;
    return selectedValues.value.length < props.maxSelected;
});

// Reset search when popover closes
watch(open, (newValue) => {
    if (!newValue) {
        searchQuery.value = '';
    }
});
</script>

<template>
    <div :class="cn('w-full', props.class)">
        <Popover v-model:open="open">
            <PopoverTrigger as-child>
                <Button
                    variant="outline"
                    role="combobox"
                    :aria-expanded="open"
                    :disabled="disabled"
                    class="h-auto min-h-10 w-full justify-between px-3 py-2"
                >
                    <div class="flex flex-1 flex-wrap gap-1">
                        <template v-if="selectedOptions.length === 0">
                            <span class="text-muted-foreground">{{ placeholder }}</span>
                        </template>
                        <template v-else-if="selectedOptions.length <= 3">
                            <Badge v-for="option in selectedOptions" :key="option.value" variant="secondary" class="h-6 text-xs">
                                {{ option.label }}
                                <button v-if="!disabled" @click.stop="removeOption(option.value)" class="hover:bg-muted ml-1 rounded-sm p-0.5">
                                    <X class="h-3 w-3" />
                                </button>
                            </Badge>
                        </template>
                        <template v-else>
                            <Badge v-for="option in selectedOptions.slice(0, 2)" :key="option.value" variant="secondary" class="h-6 text-xs">
                                {{ option.label }}
                                <button v-if="!disabled" @click.stop="removeOption(option.value)" class="hover:bg-muted ml-1 rounded-sm p-0.5">
                                    <X class="h-3 w-3" />
                                </button>
                            </Badge>
                            <Badge variant="secondary" class="h-6 text-xs"> +{{ selectedOptions.length - 2 }} more </Badge>
                        </template>
                    </div>
                    <div class="ml-2 flex items-center gap-2">
                        <button
                            v-if="clearable && selectedOptions.length > 0 && !disabled"
                            @click.stop="clearAll"
                            class="hover:bg-muted rounded-sm p-1"
                        >
                            <X class="h-4 w-4" />
                        </button>
                        <Separator orientation="vertical" class="h-4" />
                        <ChevronsUpDown class="h-4 w-4 shrink-0 opacity-50" />
                    </div>
                </Button>
            </PopoverTrigger>

            <PopoverContent class="w-[var(--reka-popper-anchor-width)] p-0" align="start">
                <div class="flex flex-col">
                    <div v-if="searchable" class="p-3 pb-2">
                        <Input v-model="searchQuery" :placeholder="searchPlaceholder" class="h-9" />
                    </div>

                    <div class="max-h-60 overflow-y-auto">
                        <div class="p-1">
                            <template v-if="filteredOptions.length === 0">
                                <div class="text-muted-foreground px-2 py-6 text-center text-sm">
                                    {{ emptyText }}
                                </div>
                            </template>

                            <template v-else>
                                <div
                                    v-for="option in filteredOptions"
                                    :key="option.value"
                                    @click="toggleOption(option)"
                                    :class="
                                        cn(
                                            'relative flex cursor-default items-center rounded-sm px-2 py-1.5 text-sm transition-colors outline-none select-none',
                                            'hover:bg-accent hover:text-accent-foreground',
                                            option.disabled && 'pointer-events-none opacity-50',
                                            !canSelectMore && !isSelected(option.value) && 'pointer-events-none opacity-50',
                                        )
                                    "
                                >
                                    <Check :class="cn('mr-2 h-4 w-4', isSelected(option.value) ? 'opacity-100' : 'opacity-0')" />
                                    <span>{{ option.label }}</span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <template v-if="selectedOptions.length > 0">
                        <Separator />
                        <div class="flex items-center justify-between p-3 pt-2">
                            <span class="text-muted-foreground text-sm">
                                {{ selectedOptions.length }} selected
                                <template v-if="maxSelected"> / {{ maxSelected }} </template>
                            </span>
                            <Button v-if="clearable && !disabled" variant="ghost" size="sm" @click="clearAll" class="h-auto p-1 text-xs">
                                Clear all
                            </Button>
                        </div>
                    </template>
                </div>
            </PopoverContent>
        </Popover>
    </div>
</template>
