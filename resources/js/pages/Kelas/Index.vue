<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import type { ColumnDef } from '@tanstack/vue-table';
import { computed, ref } from 'vue';

import KelasController from '@/actions/App/Http/Controllers/KelasController';

import InputError from '@/components/InputError.vue';
import SortableTableHeader from '@/components/SortableTableHeader.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';

interface Kelas {
    id: number;
    nama_kelas: string;
    thn_ajaran: string;
    semester: number;
    created_at: string;
    siswa_count: number;
    status: boolean;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

const page = usePage();

const kelas = computed(
    () =>
        page.props.kelas as {
            data: Kelas[];
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
            semester: string | null;
        },
);
const kelasOptions = computed(() => page.props.kelasOptions as Kelas[]);
const canMoveStudents = computed(() => page.props.canMoveStudents as boolean);

const search = ref(filters.value?.search ?? '');
const status = ref(filters.value?.status ?? '');
const semester = ref(filters.value?.semester ?? '');
const selectedKelas = ref<Kelas | null>(null);
const moveForm = useForm({
    kelas_tujuan_id: '',
});
const targetKelas = computed(() =>
    kelasOptions.value.filter((item) => item.id !== selectedKelas.value?.id),
);

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
        KelasController.index().url,
        {
            search: search.value,
            sort: filters.value.sort,
            direction: filters.value.direction,
            status: status.value,
            semester: semester.value,
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
        KelasController.index().url,
        {
            search: search.value,
            sort: column,
            direction,
            status: status.value,
            semester: semester.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const createKelas = () => {
    router.visit(KelasController.create().url);
};

const editKelas = (id: number) => {
    router.visit(KelasController.edit(id).url);
};

const deleteKelas = (id: number) => {
    if (!confirm('Hapus kelas ini?')) {
        return;
    }

    router.delete(KelasController.destroy(id).url);
};

const toggleStatus = (item: Kelas) => {
    const action = item.status ? 'nonaktifkan' : 'aktifkan';

    if (confirm(`Yakin ingin ${action} kelas ${item.nama_kelas}?`)) {
        router.patch(KelasController.toggleStatus(item.id).url);
    }
};

const openMoveStudents = (item: Kelas) => {
    selectedKelas.value = item;
    moveForm.reset();
    moveForm.clearErrors();
};

const closeMoveStudents = () => {
    selectedKelas.value = null;
    moveForm.reset();
    moveForm.clearErrors();
};

const submitMoveStudents = () => {
    if (!selectedKelas.value) {
        return;
    }

    moveForm.post(KelasController.moveStudents(selectedKelas.value.id).url, {
        preserveScroll: true,
        onSuccess: closeMoveStudents,
    });
};

const columns: ColumnDef<Kelas>[] = [
    { accessorKey: 'id', header: 'ID' },
    { accessorKey: 'nama_kelas', header: 'Nama Kelas' },
    { accessorKey: 'thn_ajaran', header: 'Tahun Ajaran' },
    { accessorKey: 'semester', header: 'Semester' },
    { accessorKey: 'status', header: 'Status' },
    {
        accessorKey: 'created_at',
        header: 'Dibuat',
        cell: ({ row }) =>
            new Date(row.original.created_at).toLocaleString('id-ID'),
    },
];

const table = useVueTable({
    get data() {
        return kelas.value.data;
    },
    columns,
    getCoreRowModel: getCoreRowModel(),
});
</script>

<template>
    <Head title="Kelas" />

    <div class="space-y-4 bg-background p-4 text-foreground">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Daftar Kelas</h1>

            <Button @click="createKelas">Tambah Kelas</Button>
        </div>

        <div class="flex flex-wrap gap-2">
            <Input
                v-model="search"
                placeholder="Cari kelas..."
                @keyup.enter="applySearch"
            />

            <Button @click="applySearch">Cari</Button>
            <select
                v-model="status"
                class="h-10 rounded-lg border border-border bg-background px-3 text-sm text-foreground"
                @change="applySearch"
            >
                <option value="">Semua Status</option>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
            </select>
            <select
                v-model="semester"
                class="h-10 rounded-lg border border-border bg-background px-3 text-sm text-foreground"
                @change="applySearch"
            >
                <option value="">Semua Semester</option>
                <option value="1">Semester 1</option>
                <option value="2">Semester 2</option>
            </select>
        </div>

        <div
            class="overflow-hidden rounded-xl border border-border bg-card text-card-foreground"
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
                            label="Nama Kelas"
                            column="nama_kelas"
                            :sort="filters.sort"
                            :direction="filters.direction"
                            @sort="changeSort"
                        />
                        <SortableTableHeader
                            label="Tahun Ajaran"
                            column="thn_ajaran"
                            :sort="filters.sort"
                            :direction="filters.direction"
                            @sort="changeSort"
                        />
                        <SortableTableHeader
                            label="Semester"
                            column="semester"
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
                        <td class="px-4 py-3">{{ row.original.nama_kelas }}</td>
                        <td class="px-4 py-3">{{ row.original.thn_ajaran }}</td>
                        <td class="px-4 py-3">
                            Semester {{ row.original.semester }}
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
                                    @click="editKelas(row.original.id)"
                                    >Edit</Button
                                >
                                <Button
                                    variant="destructive"
                                    @click="deleteKelas(row.original.id)"
                                    >Hapus</Button
                                >
                                <Button
                                    variant="outline"
                                    @click="toggleStatus(row.original)"
                                    >{{
                                        row.original.status
                                            ? 'Nonaktifkan'
                                            : 'Aktifkan'
                                    }}</Button
                                >
                                <Button
                                    v-if="canMoveStudents"
                                    variant="outline"
                                    @click="openMoveStudents(row.original)"
                                    >Pindahkan Siswa</Button
                                >
                            </div>
                        </td>
                    </tr>

                    <tr v-if="!table.getRowModel().rows.length">
                        <td
                            colspan="7"
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
                v-for="link in kelas.links"
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

        <div
            v-if="selectedKelas"
            class="fixed inset-0 z-50 flex items-center justify-center bg-muted/80 p-4"
        >
            <div
                class="w-full max-w-lg rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
            >
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold">Pindahkan Siswa</h2>
                    <p class="text-sm text-muted-foreground">
                        Pindahkan seluruh siswa ke kelas tujuan.
                    </p>
                </div>

                <div class="mt-6 rounded-md bg-muted p-4">
                    <p class="text-sm text-muted-foreground">Kelas Asal</p>
                    <p class="font-medium">{{ selectedKelas.nama_kelas }}</p>
                    <p class="mt-3 text-sm text-muted-foreground">
                        Jumlah Siswa
                    </p>
                    <p class="font-medium">
                        {{ selectedKelas.siswa_count }} siswa
                    </p>
                </div>

                <p
                    v-if="selectedKelas.siswa_count === 0"
                    class="mt-4 text-sm text-muted-foreground"
                >
                    Tidak ada siswa yang dapat dipindahkan.
                </p>

                <form
                    v-else
                    class="mt-6 space-y-4"
                    @submit.prevent="submitMoveStudents"
                >
                    <div class="space-y-2">
                        <label for="kelas_tujuan_id" class="text-sm font-medium"
                            >Kelas Tujuan</label
                        >
                        <select
                            id="kelas_tujuan_id"
                            v-model="moveForm.kelas_tujuan_id"
                            class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground"
                        >
                            <option value="">Pilih kelas tujuan</option>
                            <option
                                v-for="item in targetKelas"
                                :key="item.id"
                                :value="String(item.id)"
                            >
                                {{ item.nama_kelas }} - {{ item.thn_ajaran }} -
                                Semester {{ item.semester }}
                            </option>
                        </select>
                        <InputError
                            :message="moveForm.errors.kelas_tujuan_id"
                        />
                    </div>

                    <div class="flex gap-2">
                        <Button type="submit" :disabled="moveForm.processing">{{
                            moveForm.processing ? 'Memindahkan...' : 'Pindahkan'
                        }}</Button>
                        <Button
                            type="button"
                            variant="outline"
                            :disabled="moveForm.processing"
                            @click="closeMoveStudents"
                            >Batal</Button
                        >
                    </div>
                </form>

                <div v-if="selectedKelas.siswa_count === 0" class="mt-6">
                    <Button variant="outline" @click="closeMoveStudents"
                        >Tutup</Button
                    >
                </div>
            </div>
        </div>
    </div>
</template>
