<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { formatDate } from '@/lib/utils';
import { Note } from '@/types/model';
import { Pin } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    note: Note;
    isActive?: boolean;
    showTeamBadge?: boolean;
}>();

const emit = defineEmits<{
    select: [note: Note];
}>();

const summaryText = computed(() => {
    if (!props.note.content) return 'No content';

    // Remove markdown syntax and HTML tags for a clean summary
    const cleanContent = props.note.content
        .replace(/[#*`_~\[\]]/g, '') // Remove markdown symbols
        .replace(/<[^>]*>/g, '') // Remove HTML tags
        .replace(/\n+/g, ' ') // Replace newlines with spaces
        .trim();

    return cleanContent.length > 80 ? cleanContent.substring(0, 80) + '...' : cleanContent;
});

const handleClick = () => {
    emit('select', props.note);
};
</script>

<template>
    <div @click="handleClick" :class="['group hover:bg-muted/50 cursor-pointer border-b p-4 transition-colors', isActive ? 'bg-muted/100' : '']">
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0 flex-1">
                <div class="mb-1 flex items-center gap-2">
                    <h3 class="truncate text-sm font-semibold">{{ note.title || 'Untitled Note' }}</h3>
                    <Pin v-if="note.is_pinned" class="text-muted-foreground h-3 w-3 flex-shrink-0" />
                    <Badge v-if="showTeamBadge && note.team" variant="secondary" class="flex-shrink-0 text-xs">
                        {{ note.team.name }}
                    </Badge>
                </div>
                <p class="text-muted-foreground truncate text-xs">{{ summaryText }}</p>
                <p class="text-muted-foreground mt-1 text-xs">
                    {{ formatDate(note.updated_at) }}
                </p>
            </div>
        </div>
    </div>
</template>
