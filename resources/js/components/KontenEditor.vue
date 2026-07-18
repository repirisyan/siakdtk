<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';

const props = defineProps<{ modelValue: string }>();
const emit = defineEmits<{ 'update:modelValue': [value: string] }>();
const editor = ref<HTMLElement | null>(null);

const format = (command: string) => {
    editor.value?.focus();
    document.execCommand(command);
    emit('update:modelValue', editor.value?.innerHTML ?? '');
};

const sync = () => emit('update:modelValue', editor.value?.innerHTML ?? '');

onMounted(() => {
    if (editor.value) {
editor.value.innerHTML = props.modelValue ?? '';
}
});

watch(
    () => props.modelValue,
    (value) => {
        if (editor.value && editor.value.innerHTML !== value) {
editor.value.innerHTML = value ?? '';
}
    },
);
</script>

<template>
    <div class="overflow-hidden rounded-md border border-border bg-background">
        <div class="flex gap-1 border-b border-border bg-muted p-2">
            <button
                type="button"
                class="rounded px-2 py-1 text-sm text-foreground hover:bg-background"
                @click="format('bold')"
            >
                <strong>B</strong>
            </button>
            <button
                type="button"
                class="rounded px-2 py-1 text-sm text-foreground italic hover:bg-background"
                @click="format('italic')"
            >
                I
            </button>
            <button
                type="button"
                class="rounded px-2 py-1 text-sm text-foreground underline hover:bg-background"
                @click="format('underline')"
            >
                U
            </button>
            <button
                type="button"
                class="rounded px-2 py-1 text-sm text-foreground hover:bg-background"
                @click="format('insertUnorderedList')"
            >
                Daftar
            </button>
        </div>
        <div
            ref="editor"
            contenteditable
            class="min-h-52 p-3 text-sm text-foreground outline-none"
            @input="sync"
        />
    </div>
</template>
