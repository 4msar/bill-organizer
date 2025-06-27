<script lang="ts" setup>
import { cn } from '@/lib/utils';
import EasyMDE, { Options } from 'easymde';
import { defineEmits, defineExpose, defineProps, onMounted, onUnmounted, ref, watch, withDefaults } from 'vue';
import { defaultOptions } from './default-values';
import './style.css';

export interface EditorProps {
    modelValue?: string;
    options?: Options;
    class?: string;
}
export interface EditorEvents {
    (type: 'update:modelValue', value: string): void;
    (type: 'change', value: string): void;
    (type: 'blur'): void;
}
export interface EditorInstance {
    clear: () => void;
    getMDEInstance: () => EasyMDE | null;
}

const textArea = ref<null | HTMLTextAreaElement>();
let easyMDEInstance: InstanceType<typeof EasyMDE> | null = null;
const props = withDefaults(defineProps<EditorProps>(), {
    modelValue: '',
});
const emit = defineEmits<EditorEvents>();
const innerValue = ref(props.modelValue);
onMounted(() => {
    if (textArea.value) {
        const config: Options = {
            ...defaultOptions,
            ...(props.options || {}),
            autofocus: true,
            element: textArea.value,
        };
        easyMDEInstance = new EasyMDE(config);

        // Add shadcn/ui styling class to the container
        setTimeout(() => {
            const container = textArea.value?.nextElementSibling as HTMLElement;
            if (container && container.classList.contains('EasyMDEContainer')) {
                // Class already exists
            } else if (container) {
                container.classList.add('EasyMDEContainer');
            }
        }, 0);

        // call value method instead of initialValue prop
        // https://github.com/vikingmute/vue3-easymde/issues/1
        easyMDEInstance.value(props.modelValue);
        easyMDEInstance.codemirror.on('change', () => {
            if (easyMDEInstance) {
                const updatedValue = easyMDEInstance.value();
                innerValue.value = updatedValue;
                emit('update:modelValue', updatedValue);
                emit('change', updatedValue);
            }
        });
        easyMDEInstance.codemirror.on('blur', () => {
            if (easyMDEInstance) {
                emit('blur');
                // Remove focus styling
                const container = textArea.value?.nextElementSibling as HTMLElement;
                if (container) {
                    container.classList.remove('CodeMirror-focused');
                }
            }
        });
        easyMDEInstance.codemirror.on('focus', () => {
            // Add focus styling
            const container = textArea.value?.nextElementSibling as HTMLElement;
            if (container) {
                container.classList.add('CodeMirror-focused');
            }
        });
    }
});
// watch for async change to modelValue
watch(
    () => props.modelValue,
    (newValue) => {
        if (easyMDEInstance) {
            if (newValue !== innerValue.value) {
                easyMDEInstance.value(newValue || '');
            }
        }
    },
);
const clear = () => {
    if (easyMDEInstance) {
        easyMDEInstance.value('');
    }
};
const getMDEInstance = () => {
    return easyMDEInstance;
};
defineExpose({
    clear,
    getMDEInstance,
});

onUnmounted(() => {
    if (easyMDEInstance) {
        easyMDEInstance.cleanup();
    }
    easyMDEInstance = null;
});
</script>

<template>
    <div :class="cn('markdown-editor relative', props.class)">
        <textarea
            ref="textArea"
            class="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:ring-ring flex min-h-[200px] w-full rounded-md border px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
        />
    </div>
</template>
