<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import type { ColumnDef } from '@tanstack/vue-table';
import { computed, ref } from 'vue';

import KontenController from '@/actions/App/Http/Controllers/KontenController';
import SortableTableHeader from '@/components/SortableTableHeader.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';

interface Konten {
    id: number;
    thumbnail: string | null;
    judul: string;
    jenis_konten: string;
    status: string;
    tanggal_publish: string | null;
    created_at: string;
    user: { name: string };
}
interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}
const page = usePage();
const kontens = computed(
    () => page.props.kontens as { data: Konten[]; links: PaginationLink[] },
);
const filters = computed(
    () =>
        page.props.filters as {
            search: string | null;
            sort: string;
            direction: string;
            jenisKonten: string | null;
            status: string | null;
        },
);
const search = ref(filters.value.search ?? '');
const jenisKonten = ref(filters.value.jenisKonten ?? '');
const status = ref(filters.value.status ?? '');
const applyFilters = (
    sort = filters.value.sort,
    direction = filters.value.direction,
) =>
    router.get(
        KontenController.index().url,
        {
            search: search.value,
            jenis_konten: jenisKonten.value,
            status: status.value,
            sort,
            direction,
        },
        { preserveState: true, replace: true },
    );
const changeSort = (column: string) =>
    applyFilters(
        column,
        filters.value.sort === column && filters.value.direction === 'asc'
            ? 'desc'
            : 'asc',
    );
const visit = (url: string | null) => {
    if (url) router.visit(url, { preserveState: true, preserveScroll: true });
};
const remove = (id: number) => {
    if (confirm('Hapus konten ini?'))
        router.delete(KontenController.destroy(id).url);
};
const date = (value: string | null) =>
    value ? new Date(value).toLocaleString('id-ID') : '-';
const columns: ColumnDef<Konten>[] = [
    { accessorKey: 'id', header: 'ID' },
    { accessorKey: 'judul', header: 'Judul' },
    { accessorKey: 'jenis_konten', header: 'Jenis Konten' },
    { accessorKey: 'status', header: 'Status' },
    { accessorKey: 'tanggal_publish', header: 'Tanggal Publish' },
    { accessorKey: 'created_at', header: 'Dibuat' },
];
const table = useVueTable({
    get data() {
        return kontens.value.data;
    },
    columns,
    getCoreRowModel: getCoreRowModel(),
});
</script>

<template>
    <Head title="Kelola Konten" />
    <div class="space-y-5 p-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-foreground">
                    Kelola Konten
                </h1>
                <p class="text-sm text-muted-foreground">
                    Berita, event, pengumuman, dan galeri sekolah.
                </p>
            </div>
            <Button @click="router.visit(KontenController.create().url)"
                >Tambah Konten</Button
            >
        </div>
        <div
            class="grid gap-3 rounded-xl border border-border bg-card p-4 md:grid-cols-4"
        >
            <Input
                v-model="search"
                placeholder="Cari judul atau penulis..."
                @keyup.enter="applyFilters()"
            /><select
                v-model="jenisKonten"
                class="h-9 rounded-md border border-border bg-background px-3 text-sm text-foreground"
            >
                <option value="">Semua Jenis</option>
                <option value="berita">Berita</option>
                <option value="event">Event</option>
                <option value="pengumuman">Pengumuman</option>
                <option value="galeri">Galeri</option></select
            ><select
                v-model="status"
                class="h-9 rounded-md border border-border bg-background px-3 text-sm text-foreground"
            >
                <option value="">Semua Status</option>
                <option value="draft">Draft</option>
                <option value="published">Published</option></select
            ><Button @click="applyFilters()">Terapkan Filter</Button>
        </div>
        <div class="overflow-x-auto rounded-xl border border-border bg-card">
            <table class="w-full min-w-225">
                <thead class="bg-muted/50">
                    <tr>
                        <SortableTableHeader
                            label="ID"
                            column="id"
                            :sort="filters.sort"
                            :direction="filters.direction"
                            @sort="changeSort"
                        />
                        <th class="px-4 py-3 text-left text-sm font-medium">
                            Thumbnail
                        </th>
                        <SortableTableHeader
                            label="Judul"
                            column="judul"
                            :sort="filters.sort"
                            :direction="filters.direction"
                            @sort="changeSort"
                        />
                        <SortableTableHeader
                            label="Jenis"
                            column="jenis_konten"
                            :sort="filters.sort"
                            :direction="filters.direction"
                            @sort="changeSort"
                        />
                        <th class="px-4 py-3 text-left text-sm font-medium">
                            Penulis
                        </th>
                        <SortableTableHeader
                            label="Status"
                            column="status"
                            :sort="filters.sort"
                            :direction="filters.direction"
                            @sort="changeSort"
                        />
                        <SortableTableHeader
                            label="Publish"
                            column="tanggal_publish"
                            :sort="filters.sort"
                            :direction="filters.direction"
                            @sort="changeSort"
                        />
                        <SortableTableHeader
                            label="Dibuat"
                            column="created_at"
                            :sort="filters.sort"
                            :direction="filters.direction"
                            @sort="changeSort"
                        />
                        <th class="px-4 py-3 text-left text-sm font-medium">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="row in table.getRowModel().rows"
                        :key="row.id"
                        class="border-t border-border hover:bg-muted/50"
                    >
                        <td class="px-4 py-3">{{ row.original.id }}</td>
                        <td class="px-4 py-3">
                            <img
                                v-if="row.original.thumbnail"
                                :src="`/storage/${row.original.thumbnail}`"
                                :alt="row.original.judul"
                                class="h-12 w-16 rounded object-cover"
                            /><span v-else class="text-sm text-muted-foreground"
                                >-</span
                            >
                        </td>
                        <td class="px-4 py-3 font-medium">
                            {{ row.original.judul }}
                        </td>
                        <td class="px-4 py-3 capitalize">
                            {{ row.original.jenis_konten }}
                        </td>
                        <td class="px-4 py-3">{{ row.original.user.name }}</td>
                        <td class="px-4 py-3">
                            <span
                                class="rounded-full bg-muted px-2 py-1 text-xs text-foreground capitalize"
                                >{{ row.original.status }}</span
                            >
                        </td>
                        <td class="px-4 py-3 text-sm">
                            {{ date(row.original.tanggal_publish) }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            {{ date(row.original.created_at) }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <Button
                                    size="sm"
                                    variant="outline"
                                    @click="
                                        router.visit(
                                            KontenController.show(
                                                row.original.id,
                                            ).url,
                                        )
                                    "
                                    >Detail</Button
                                ><Button
                                    size="sm"
                                    variant="secondary"
                                    @click="
                                        router.visit(
                                            KontenController.edit(
                                                row.original.id,
                                            ).url,
                                        )
                                    "
                                    >Edit</Button
                                ><Button
                                    size="sm"
                                    variant="destructive"
                                    @click="remove(row.original.id)"
                                    >Hapus</Button
                                >
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!table.getRowModel().rows.length">
                        <td
                            colspan="9"
                            class="py-8 text-center text-muted-foreground"
                        >
                            Tidak ada konten.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="flex flex-wrap gap-2">
            <button
                v-for="link in kontens.links"
                :key="link.label"
                :disabled="!link.url"
                class="inline-flex h-9 items-center justify-center rounded-md border border-border px-3 text-sm text-foreground"
                :class="{
                    'bg-primary text-primary-foreground': link.active,
                    'opacity-50': !link.url,
                }"
                @click="visit(link.url)"
                v-html="link.label"
            />
        </div>
    </div>
</template>
