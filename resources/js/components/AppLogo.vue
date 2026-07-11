<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="flex aspect-square size-9 items-center justify-center rounded-xl bg-sidebar-primary text-sidebar-primary-foreground shadow-sm"
    >
        <AppLogoIcon class="size-5 fill-current" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="truncate leading-tight font-semibold">SIAKDTK</span>
        <span class="truncate text-xs text-muted-foreground"
            >Sistem Informasi Sekolah</span
        >
    </div>
</template>
