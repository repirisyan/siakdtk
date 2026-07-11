<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import type { ColumnDef } from '@tanstack/vue-table';
import { computed, ref } from 'vue';

import JadwalController from '@/actions/App/Http/Controllers/JadwalController';
import SortableTableHeader from '@/components/SortableTableHeader.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';

interface Jadwal {
    id: number;
    tanggal: string;
    jam_mulai: string;
    jam_selesai: string;
    created_at: string;
    kelas: { nama_kelas: string };
    guru: { nama: string; nip: string };
    tema: { nama_tema: string };
    subTema: { nama_sub_tema: string } | null;
}
interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

const page = usePage();
const jadwals = computed(
    () =>
        page.props.jadwals as {
            data: Jadwal[];
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
        },
);
const search = ref(filters.value?.search ?? '');
const visit = (url?: string | null) => {
    if (!url) {
        return;
    }

    router.visit(url, { preserveState: true, preserveScroll: true });
};
const applySearch = () =>
    router.get(
        JadwalController.index().url,
        {
            search: search.value,
            sort: filters.value.sort,
            direction: filters.value.direction,
        },
        { preserveState: true, replace: true },
    );
const changeSort = (column: string) => {
    const direction =
        filters.value.sort === column && filters.value.direction === 'asc'
            ? 'desc'
            : 'asc';

    router.get(
        JadwalController.index().url,
        { search: search.value, sort: column, direction },
        { preserveState: true, replace: true },
    );
};
const createJadwal = () => router.visit(JadwalController.create().url);
const editJadwal = (id: number) => router.visit(JadwalController.edit(id).url);
const deleteJadwal = (id: number) => {
    if (!confirm('Hapus jadwal ini?')) {
        return;
    }

    router.delete(JadwalController.destroy(id).url);
};
const columns: ColumnDef<Jadwal>[] = [
    { accessorKey: 'id', header: 'ID' },
    { accessorKey: 'tanggal', header: 'Tanggal' },
    { accessorKey: 'jam_mulai', header: 'Jam Mulai' },
    { accessorKey: 'jam_selesai', header: 'Jam Selesai' },
    {
        accessorFn: (row) => row.kelas.nama_kelas,
        id: 'nama_kelas',
        header: 'Kelas',
    },
    { accessorFn: (row) => row.guru.nama, id: 'nama_guru', header: 'Guru' },
    {
        accessorFn: (row) =>
            `${row.tema.nama_tema} — ${row.subTema?.nama_sub_tema ?? '-'}`,
        id: 'nama_tema',
        header: 'Tema',
    },
    { accessorKey: 'created_at', header: 'Dibuat' },
];
const tableColumns = [
    { key: 'id', label: 'ID' },
    { key: 'tanggal', label: 'Tanggal' },
    { key: 'jam_mulai', label: 'Jam Mulai' },
    { key: 'jam_selesai', label: 'Jam Selesai' },
    { key: 'nama_kelas', label: 'Kelas' },
    { key: 'nama_guru', label: 'Guru' },
    { key: 'nama_tema', label: 'Tema / Sub Tema' },
    { key: 'created_at', label: 'Dibuat' },
];
const table = useVueTable({
    get data() {
        return jadwals.value.data;
    },
    columns,
    getCoreRowModel: getCoreRowModel(),
});
</script>

<template>
    <Head title="Jadwal" />
    <div class="space-y-4 bg-background p-4 text-foreground">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Daftar Jadwal</h1>
            <Button @click="createJadwal">Tambah Jadwal</Button>
        </div>
        <div class="flex gap-2">
            <Input
                v-model="search"
                placeholder="Cari jadwal..."
                @keyup.enter="applySearch"
            /><Button @click="applySearch">Cari</Button>
        </div>
        <div
            class="overflow-x-auto rounded-xl border border-border bg-card text-card-foreground"
        >
            <table class="w-full min-w-max">
                <thead class="bg-muted/50">
                    <tr>
                        <SortableTableHeader
                            v-for="column in tableColumns"
                            :key="column.key"
                            :label="column.label"
                            :column="column.key"
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
                        <td class="px-4 py-3">{{ row.original.tanggal }}</td>
                        <td class="px-4 py-3">{{ row.original.jam_mulai }}</td>
                        <td class="px-4 py-3">
                            {{ row.original.jam_selesai }}
                        </td>
                        <td class="px-4 py-3">
                            {{ row.original.kelas.nama_kelas }}
                        </td>
                        <td class="px-4 py-3">{{ row.original.guru.nama }}</td>
                        <td class="px-4 py-3">
                            {{ row.original.tema.nama_tema }} —
                            {{ row.original.subTema?.nama_sub_tema ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            {{
                                new Date(
                                    row.original.created_at,
                                ).toLocaleString('id-ID')
                            }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <Button
                                    variant="secondary"
                                    @click="editJadwal(row.original.id)"
                                    >Edit</Button
                                ><Button
                                    variant="destructive"
                                    @click="deleteJadwal(row.original.id)"
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
                            Tidak ada data
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="flex flex-wrap gap-2">
            <button
                v-for="link in jadwals.links"
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
