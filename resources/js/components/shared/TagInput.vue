<script setup lang="ts">
import { Combobox, ComboboxAnchor, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxList } from '@/components/ui/combobox';
import { TagsInput, TagsInputInput, TagsInputItem, TagsInputItemDelete, TagsInputItemText } from '@/components/ui/tags-input';
import { useFilter } from 'reka-ui';
import { computed, ref } from 'vue';

const { options: items, placeholder } = defineProps<{
    placeholder?: string;
    options: { value: string; label: string }[];
}>();

const modelValue = defineModel<string[]>({
    type: Array,
    default: () => [],
});

const open = ref(false);
const searchTerm = ref('');

const { contains } = useFilter({ sensitivity: 'base' });
const filteredItems = computed(() => {
    const options = items.filter((i) => !modelValue.value.includes(i.label));
    return searchTerm.value ? options.filter((option) => contains(option.label, searchTerm.value)) : options;
});
</script>

<template>
    <Combobox v-model="modelValue" v-model:open="open" :ignore-filter="true">
        <ComboboxAnchor as-child class="min-h-9">
            <TagsInput v-model="modelValue" class="w-auto gap-2">
                <div class="flex flex-wrap items-center gap-2">
                    <TagsInputItem v-for="item in modelValue" :key="item" :value="item">
                        <TagsInputItemText />
                        <TagsInputItemDelete />
                    </TagsInputItem>
                </div>

                <ComboboxInput v-model="searchTerm" as-child root-class="border-none p-0 h-auto" hide-icon>
                    <TagsInputInput
                        :placeholder="placeholder"
                        class="h-auto w-full min-w-[200px] border-none p-0 focus-visible:ring-0"
                        @keydown.enter.prevent
                    />
                </ComboboxInput>
            </TagsInput>

            <ComboboxList class="w-[var(--reka-popper-anchor-width)]">
                <ComboboxEmpty v-if="options.length" />
                <ComboboxGroup v-if="options.length">
                    <ComboboxItem
                        v-for="item in filteredItems"
                        :key="item.value"
                        :value="item.label"
                        @select.prevent="
                            (ev) => {
                                if (typeof ev.detail.value === 'string') {
                                    searchTerm = '';
                                    modelValue.push(ev.detail.value);
                                }

                                if (filteredItems.length === 0) {
                                    open = false;
                                }
                            }
                        "
                    >
                        {{ item.label }}
                    </ComboboxItem>
                </ComboboxGroup>
            </ComboboxList>
        </ComboboxAnchor>
    </Combobox>
</template>
