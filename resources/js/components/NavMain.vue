<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { NavItem } from '@/types';

defineProps<{
    items: NavItem[];
}>();

const { currentUrl, isCurrentUrl } = useCurrentUrl();

const isActive = (item: NavItem) =>
    isCurrentUrl(item.href) ||
    item.activePrefixes?.some((prefix) =>
        isCurrentUrl(prefix, currentUrl.value, true),
    );
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel
            class="px-2 text-xs font-semibold tracking-wide text-muted-foreground uppercase"
            >Menu Sekolah</SidebarGroupLabel
        >
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="isActive(item)"
                    :tooltip="item.title"
                >
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
