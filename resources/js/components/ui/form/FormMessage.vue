<script lang="ts" setup>
import { cn } from '@/lib/utils'
import { ErrorMessage } from 'vee-validate'
import { type HTMLAttributes, toValue } from 'vue'
import { useFormField } from './useFormField'

const props = defineProps<{
  class?: HTMLAttributes['class']
  message?:string
}>()

const { name, formMessageId } = useFormField()
</script>

<template>
  <ErrorMessage
    v-if="!props.message"
    :id="formMessageId"
    data-slot="form-message"
    as="p"
    :name="toValue(name)"
    :class="cn('text-destructive-foreground text-sm', props.class)"
  />
  <span v-else class="text-red-500 italic text-xs">{{props.message}}</span>
</template>
