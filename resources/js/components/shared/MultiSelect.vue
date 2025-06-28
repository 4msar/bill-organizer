<script setup lang="ts">
import { CommandEmpty, CommandGroup, CommandItem, CommandList } from '@/components/ui/command';
import { TagsInput, TagsInputInput, TagsInputItem, TagsInputItemDelete, TagsInputItemText } from '@/components/ui/tags-input';
import { ComboboxAnchor, ComboboxInput, ComboboxPortal, ComboboxRoot } from 'reka-ui';
import { computed, ref } from 'vue';

const frameworks = [
    { value: 'next.js', label: 'Next.js' },
    { value: 'sveltekit', label: 'SvelteKit' },
    { value: 'nuxt.js', label: 'Nuxt.js' },
    { value: 'remix', label: 'Remix' },
    { value: 'astro', label: 'Astro' },
];

const modelValue = ref<string[]>([]);
const open = ref(false);
const searchTerm = ref('');

const filteredFrameworks = computed(() => frameworks.filter((i) => !modelValue.value.includes(i.label)));
</script>
<template>
    <TagsInput class="gap-0 px-0" :model-value="modelValue">
        <div class="flex flex-wrap items-center gap-2 px-3">
            <TagsInputItem v-for="item in modelValue" :key="item" :value="item">
                <TagsInputItemText />
                <TagsInputItemDelete />
            </TagsInputItem>
        </div>

        <ComboboxRoot v-model="modelValue" v-model:open="open" v-model:searchTerm="searchTerm" class="w-full">
            <ComboboxAnchor as-child>
                <ComboboxInput placeholder="Framework..." as-child>
                    <TagsInputInput class="w-full px-3" :class="modelValue.length > 0 ? 'mt-2' : ''" @keydown.enter.prevent />
                </ComboboxInput>
            </ComboboxAnchor>

            <ComboboxPortal>
                <CommandList
                    position="popper"
                    class="bg-popover text-popover-foreground data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2 mt-2 w-[--radix-popper-anchor-width] rounded-md border shadow-md outline-none"
                >
                    <CommandEmpty />
                    <CommandGroup>
                        <CommandItem
                            v-for="framework in filteredFrameworks"
                            :key="framework.value"
                            :value="framework.label"
                            @select.prevent="
                                (ev) => {
                                    if (typeof ev.detail.value === 'string') {
                                        searchTerm = '';
                                        modelValue.push(ev.detail.value);
                                    }

                                    if (filteredFrameworks.length === 0) {
                                        open = false;
                                    }
                                }
                            "
                        >
                            {{ framework.label }}
                        </CommandItem>
                    </CommandGroup>
                </CommandList>
            </ComboboxPortal>
        </ComboboxRoot>
    </TagsInput>
</template>
