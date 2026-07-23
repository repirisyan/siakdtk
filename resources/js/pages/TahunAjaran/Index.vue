<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import type { ColumnDef } from '@tanstack/vue-table';
import { computed, ref } from 'vue';

import TahunAjaranController from '@/actions/App/Http/Controllers/TahunAjaranController';
import SortableTableHeader from '@/components/SortableTableHeader.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';

interface TahunAjaran {
    id: number;
    tahun_ajaran: string;
    kelas_count: number;
    created_at: string;
}
interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

const page = usePage();
const tahunAjarans = computed(
    () =>
        page.props.tahunAjarans as {
            data: TahunAjaran[];
            links: PaginationLink[];
        },
);
const filters = computed(
    () =>
        page.props.filters as {
            search: string;
            sort: string;
            direction: string;
        },
);
const search = ref(filters.value.search ?? '');

const applySearch = () => {
    router.get(
        TahunAjaranController.index().url,
        {
            search: search.value,
            sort: filters.value.sort,
            direction: filters.value.direction,
        },
        { preserveState: true, replace: true },
    );
};
const changeSort = (column: string) => {
    const direction =
        filters.value.sort === column && filters.value.direction === 'asc'
            ? 'desc'
            : 'asc';

    router.get(
        TahunAjaranController.index().url,
        { search: search.value, sort: column, direction },
        { preserveState: true, replace: true },
    );
};
const visit = (url: string | null) => {
    if (url) {
        router.visit(url, { preserveState: true, preserveScroll: true });
    }
};
const create = () => router.visit(TahunAjaranController.create().url);
const show = (id: number) => router.visit(TahunAjaranController.show(id).url);
const edit = (id: number) => router.visit(TahunAjaranController.edit(id).url);
const destroy = (item: TahunAjaran) => {
    if (confirm(`Hapus tahun ajaran ${item.tahun_ajaran}?`)) {
        router.delete(TahunAjaranController.destroy(item.id).url);
    }
};

const columns: ColumnDef<TahunAjaran>[] = [
    { accessorKey: 'id', header: 'ID' },
    { accessorKey: 'tahun_ajaran', header: 'Tahun Ajaran' },
    { accessorKey: 'kelas_count', header: 'Jumlah Kelas' },
    { accessorKey: 'created_at', header: 'Dibuat' },
];
const table = useVueTable({
    get data() {
        return tahunAjarans.value.data;
    },
    columns,
    getCoreRowModel: getCoreRowModel(),
});
</script>

<template>
    <Head title="Tahun Ajaran" />

    <div class="space-y-4 bg-background p-4 text-foreground">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold">Tahun Ajaran</h1>
                <p class="text-sm text-muted-foreground">
                    Kelola tahun ajaran yang digunakan oleh data kelas.
                </p>
            </div>
            <Button @click="create">Tambah Tahun Ajaran</Button>
        </div>

        <div class="flex flex-wrap gap-2">
            <Input
                v-model="search"
                placeholder="Cari tahun ajaran..."
                @keyup.enter="applySearch"
            />
            <Button @click="applySearch">Cari</Button>
        </div>

        <div
            class="overflow-x-auto rounded-xl border border-border bg-card text-card-foreground"
        >
            <table class="w-full">
                <thead class="bg-muted/50">
                    <tr>
                        <SortableTableHeader
                            label="ID"
                            column="id"
                            :sort="filters.sort"
                            :direction="filters.direction"
                            @sort="changeSort"
                        />
                        <SortableTableHeader
                            label="Tahun Ajaran"
                            column="tahun_ajaran"
                            :sort="filters.sort"
                            :direction="filters.direction"
                            @sort="changeSort"
                        />
                        <th class="px-4 py-3 text-left">Jumlah Kelas</th>
                        <SortableTableHeader
                            label="Dibuat"
                            column="created_at"
                            :sort="filters.sort"
                            :direction="filters.direction"
                            @sort="changeSort"
                        />
                        <th class="px-4 py-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="row in table.getRowModel().rows"
                        :key="row.id"
                        class="border-t border-border hover:bg-muted/50"
                    >
                        <td class="px-4 py-3">{{ row.original.id }}</td>
                        <td class="px-4 py-3 font-medium">
                            {{ row.original.tahun_ajaran }}
                        </td>
                        <td class="px-4 py-3">
                            {{ row.original.kelas_count }}
                        </td>
                        <td class="px-4 py-3">
                            {{
                                new Date(
                                    row.original.created_at,
                                ).toLocaleString('id-ID')
                            }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                <Button
                                    variant="outline"
                                    @click="show(row.original.id)"
                                    >Detail</Button
                                >
                                <Button
                                    variant="secondary"
                                    @click="edit(row.original.id)"
                                    >Edit</Button
                                >
                                <Button
                                    variant="destructive"
                                    :disabled="row.original.kelas_count > 0"
                                    :title="
                                        row.original.kelas_count > 0
                                            ? 'Tahun ajaran masih digunakan oleh kelas dan tidak dapat dihapus'
                                            : 'Hapus tahun ajaran'
                                    "
                                    @click="destroy(row.original)"
                                    >Hapus</Button
                                >
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!table.getRowModel().rows.length">
                        <td
                            colspan="5"
                            class="py-8 text-center text-muted-foreground"
                        >
                            Tidak ada data tahun ajaran.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap gap-2">
            <button
                v-for="link in tahunAjarans.links"
                :key="link.label"
                :disabled="!link.url"
                class="inline-flex h-9 items-center justify-center rounded-md border border-border px-3 text-sm"
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
