<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import type { ColumnDef } from '@tanstack/vue-table';
import { computed, ref } from 'vue';

import GuruController from '@/actions/App/Http/Controllers/GuruController';

import SortableTableHeader from '@/components/SortableTableHeader.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';

interface Guru {
    id: number;
    nip: string;
    nama: string;
    jenis_ptk: string | null;
    pendidikan: string | null;
    stts_kepegawaian: string | null;
    created_at: string;
    user: { name: string; email: string; status: boolean } | null;
}
interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

const page = usePage();
const gurus = computed(
    () =>
        page.props.gurus as {
            data: Guru[];
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
            status: 'aktif' | 'nonaktif' | null;
        },
);
const search = ref(filters.value?.search ?? '');
const status = ref(filters.value?.status ?? '');

const visit = (url?: string | null) => {
    if (!url) {
        return;
    }

    router.visit(url, { preserveState: true, preserveScroll: true });
};
const applySearch = () =>
    router.get(
        GuruController.index().url,
        {
            search: search.value,
            sort: filters.value.sort,
            direction: filters.value.direction,
            status: status.value,
        },
        { preserveState: true, replace: true },
    );
const changeSort = (column: string) => {
    const direction =
        filters.value.sort === column && filters.value.direction === 'asc'
            ? 'desc'
            : 'asc';

    router.get(
        GuruController.index().url,
        { search: search.value, sort: column, direction, status: status.value },
        { preserveState: true, replace: true },
    );
};
const createGuru = () => router.visit(GuruController.create().url);
const editGuru = (id: number) => router.visit(GuruController.edit(id).url);
const deleteGuru = (id: number) => {
    if (!confirm('Hapus guru ini?')) {
        return;
    }

    router.delete(GuruController.destroy(id).url);
};
const toggleStatus = (guru: Guru) => {
    if (!guru.user) {
        return;
    }

    const action = guru.user.status ? 'nonaktifkan' : 'aktifkan';

    if (confirm(`Yakin ingin ${action} guru ini?`)) {
        router.put(GuruController.toggleStatus(guru.id).url);
    }
};

const columns: ColumnDef<Guru>[] = [
    { accessorKey: 'id', header: 'ID' },
    { accessorKey: 'nip', header: 'NIP' },
    { accessorKey: 'nama', header: 'Nama Guru' },
    {
        accessorFn: (row) => row.user?.name ?? '-',
        id: 'user_name',
        header: 'Username',
    },
    {
        accessorFn: (row) => row.user?.email ?? '-',
        id: 'user_email',
        header: 'Email',
    },
    {
        accessorFn: (row) => row.user?.status ?? false,
        id: 'user_status',
        header: 'Status',
    },
    { accessorKey: 'jenis_ptk', header: 'Jenis PTK' },
    { accessorKey: 'pendidikan', header: 'Pendidikan' },
    { accessorKey: 'stts_kepegawaian', header: 'Status Kepegawaian' },
    { accessorKey: 'created_at', header: 'Dibuat' },
];
const tableColumns = [
    { key: 'id', label: 'ID' },
    { key: 'nip', label: 'NIP' },
    { key: 'nama', label: 'Nama Guru' },
    { key: 'user_name', label: 'Username' },
    { key: 'user_email', label: 'Email' },
    { key: 'user_status', label: 'Status' },
    { key: 'jenis_ptk', label: 'Jenis PTK' },
    { key: 'pendidikan', label: 'Pendidikan' },
    { key: 'stts_kepegawaian', label: 'Status Kepegawaian' },
    { key: 'created_at', label: 'Dibuat' },
];
const table = useVueTable({
    get data() {
        return gurus.value.data;
    },
    columns,
    getCoreRowModel: getCoreRowModel(),
});
</script>

<template>
    <Head title="Guru" />
    <div class="space-y-4 bg-background p-4 text-foreground">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Daftar Guru</h1>
            <Button @click="createGuru">Tambah Guru</Button>
        </div>
        <div class="flex flex-wrap gap-2">
            <Input
                v-model="search"
                placeholder="Cari guru..."
                @keyup.enter="applySearch"
            /><select
                v-model="status"
                class="h-10 rounded-lg border border-border bg-background px-3 text-sm text-foreground"
                @change="applySearch"
            >
                <option value="">Semua Status</option>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option></select
            ><Button @click="applySearch">Cari</Button>
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
                        <td class="px-4 py-3">{{ row.original.nip }}</td>
                        <td class="px-4 py-3">{{ row.original.nama }}</td>
                        <td class="px-4 py-3">
                            {{ row.original.user?.name ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            {{ row.original.user?.email ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <span
                                v-if="row.original.user"
                                class="rounded-full px-2.5 py-1 text-xs font-semibold"
                                :class="
                                    row.original.user.status
                                        ? 'bg-secondary text-secondary-foreground'
                                        : 'bg-muted text-muted-foreground'
                                "
                                >{{
                                    row.original.user.status
                                        ? 'Aktif'
                                        : 'Nonaktif'
                                }}</span
                            >
                            <span v-else class="text-sm text-muted-foreground"
                                >-</span
                            >
                        </td>
                        <td class="px-4 py-3">
                            {{ row.original.jenis_ptk ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            {{ row.original.pendidikan ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            {{ row.original.stts_kepegawaian ?? '-' }}
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
                                    @click="editGuru(row.original.id)"
                                    >Edit</Button
                                ><Button
                                    v-if="row.original.user"
                                    variant="outline"
                                    @click="toggleStatus(row.original)"
                                    >{{
                                        row.original.user.status
                                            ? 'Nonaktifkan'
                                            : 'Aktifkan'
                                    }}</Button
                                ><Button
                                    variant="destructive"
                                    @click="deleteGuru(row.original.id)"
                                    >Hapus</Button
                                >
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!table.getRowModel().rows.length">
                        <td
                            colspan="11"
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
                v-for="link in gurus.links"
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
