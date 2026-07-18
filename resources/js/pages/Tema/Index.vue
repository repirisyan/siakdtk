<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    getCoreRowModel,
    useVueTable
    
} from '@tanstack/vue-table';
import type {ColumnDef} from '@tanstack/vue-table';
import { computed, ref } from 'vue';

import TemaController from '@/actions/App/Http/Controllers/TemaController';

import SortableTableHeader from '@/components/SortableTableHeader.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';

interface Tema {
    id: number;
    nama_tema: string;
    created_at: string;
    status: boolean;
    sub_temas_count: number;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

const page = usePage();

const temas = computed(
    () =>
        page.props.temas as {
            data: Tema[];
            links: PaginationLink[];
            current_page: number;
            last_page: number;
            total: number;
        },
);

const filters = computed(
    () =>
        page.props.filters as {
            search: string;
            sort: string;
            direction: string;
            status: string | null;
        },
);

const search = ref(filters.value?.search ?? '');
const status = ref(filters.value?.status ?? '');

const visit = (url?: string | null) => {
    if (!url) {
return;
}

    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
    });
};

const applySearch = () => {
    router.get(
        TemaController.index().url,
        {
            search: search.value,
            sort: filters.value.sort,
            direction: filters.value.direction,
            status: status.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const changeSort = (column: string) => {
    const direction =
        filters.value.sort === column && filters.value.direction === 'asc'
            ? 'desc'
            : 'asc';

    router.get(
        TemaController.index().url,
        {
            search: search.value,
            sort: column,
            direction,
            status: status.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const createTema = () => {
    router.visit(TemaController.create().url);
};

const editTema = (id: number) => {
    router.visit(TemaController.edit(id).url);
};

const manageSubTema = (id: number) => {
    router.visit(`/sub-tema?tema_id=${id}`);
};

const deleteTema = (id: number) => {
    if (!confirm('Hapus tema ini?')) {
return;
}

    router.delete(TemaController.destroy(id).url);
};

const toggleStatus = (item: Tema) => {
    const action = item.status ? 'nonaktifkan' : 'aktifkan';

    if (confirm(`Yakin ingin ${action} tema ${item.nama_tema}?`)) {
        router.patch(TemaController.toggleStatus(item.id).url);
    }
};

const columns: ColumnDef<Tema>[] = [
    {
        accessorKey: 'id',
        header: 'ID',
    },
    {
        accessorKey: 'nama_tema',
        header: 'Nama Tema',
    },
    {
        accessorKey: 'created_at',
        header: 'Dibuat',
        cell: ({ row }) =>
            new Date(row.original.created_at).toLocaleString('id-ID'),
    },
    { accessorKey: 'status', header: 'Status' },
];

const table = useVueTable({
    get data() {
        return temas.value.data;
    },
    columns,
    getCoreRowModel: getCoreRowModel(),
});
</script>

<template>
    <Head title="Tema" />

    <div class="space-y-4 p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Daftar Tema</h1>

            <Button @click="createTema"> Tambah Tema </Button>
        </div>

        <div class="flex flex-wrap gap-2">
            <Input
                v-model="search"
                placeholder="Cari tema..."
                @keyup.enter="applySearch"
            />

            <Button @click="applySearch"> Cari </Button>
            <select
                v-model="status"
                class="h-10 rounded-lg border border-border bg-background px-3 text-sm text-foreground"
                @change="applySearch"
            >
                <option value="">Semua Status</option>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
            </select>
        </div>

        <div class="overflow-hidden rounded-xl border border-border bg-card">
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
                            label="Nama Tema"
                            column="nama_tema"
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
                        <SortableTableHeader
                            label="Status"
                            column="status"
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
                        <td class="px-4 py-3">
                            {{ row.original.id }}
                        </td>

                        <td class="px-4 py-3">
                            {{ row.original.nama_tema }}
                        </td>

                        <td class="px-4 py-3">
                            {{
                                new Date(
                                    row.original.created_at,
                                ).toLocaleString('id-ID')
                            }}
                        </td>

                        <td class="px-4 py-3">
                            <span
                                class="rounded-full px-2 py-1 text-xs font-medium"
                                :class="
                                    row.original.status
                                        ? 'bg-primary text-primary-foreground'
                                        : 'bg-muted text-muted-foreground'
                                "
                                >{{
                                    row.original.status ? 'Aktif' : 'Nonaktif'
                                }}</span
                            >
                        </td>

                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <Button
                                    variant="secondary"
                                    @click="editTema(row.original.id)"
                                >
                                    Edit
                                </Button>

                                <Button
                                    variant="outline"
                                    @click="manageSubTema(row.original.id)"
                                >
                                    Kelola Sub Tema
                                </Button>

                                <Button
                                    variant="destructive"
                                    @click="deleteTema(row.original.id)"
                                >
                                    Hapus
                                </Button>
                                <Button
                                    variant="outline"
                                    @click="toggleStatus(row.original)"
                                    >{{
                                        row.original.status
                                            ? 'Nonaktifkan'
                                            : 'Aktifkan'
                                    }}</Button
                                >
                            </div>
                        </td>
                    </tr>

                    <tr v-if="!table.getRowModel().rows.length">
                        <td
                            colspan="5"
                            class="py-8 text-center text-muted-foreground"
                        >
                            Tidak ada data
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap gap-2">
            <button
                v-for="link in temas.links"
                :key="link.label"
                :disabled="!link.url"
                @click="visit(link.url)"
                v-html="link.label"
                class="inline-flex h-9 items-center justify-center rounded-md border border-border px-3 text-sm"
                :class="{
                    'bg-primary text-primary-foreground': link.active,
                    'opacity-50': !link.url,
                }"
            />
        </div>
    </div>
</template>
