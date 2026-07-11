<script setup lang="ts">
import { ArrowDown, ArrowUp, ArrowUpDown } from '@lucide/vue';
import { computed } from 'vue';

const props = defineProps<{
    label: string;
    column: string;
    sort: string;
    direction: string;
}>();

const emit = defineEmits<{
    sort: [column: string];
}>();

const isActive = computed(() => props.sort === props.column);
const ariaSort = computed(() => {
    if (!isActive.value) {
        return 'none';
    }

    return props.direction === 'asc' ? 'ascending' : 'descending';
});
const nextDirection = computed(() =>
    isActive.value && props.direction === 'asc' ? 'menurun' : 'menaik',
);
</script>

<template>
    <th
        scope="col"
        :aria-sort="ariaSort"
        class="px-4 py-3 text-left text-sm font-medium"
    >
        <button
            type="button"
            class="group -m-2 inline-flex items-center gap-1.5 rounded-md p-2 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground focus-visible:ring-3 focus-visible:ring-ring/50 focus-visible:outline-none"
            :class="isActive && 'font-semibold text-primary'"
            :aria-label="`Urutkan berdasarkan ${label} secara ${nextDirection}`"
            :title="`Urutkan berdasarkan ${label}`"
            @click="emit('sort', column)"
        >
            <span>{{ label }}</span>
            <ArrowUp
                v-if="isActive && direction === 'asc'"
                class="size-4 shrink-0 text-primary"
                aria-hidden="true"
            />
            <ArrowDown
                v-else-if="isActive && direction === 'desc'"
                class="size-4 shrink-0 text-primary"
                aria-hidden="true"
            />
            <ArrowUpDown
                v-else
                class="size-4 shrink-0 opacity-50 transition-opacity group-hover:opacity-100"
                aria-hidden="true"
            />
        </button>
    </th>
</template>
