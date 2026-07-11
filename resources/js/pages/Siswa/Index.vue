<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import type { ColumnDef } from '@tanstack/vue-table';
import { computed, ref } from 'vue';

import SiswaController from '@/actions/App/Http/Controllers/SiswaController';

import InputError from '@/components/InputError.vue';
import SortableTableHeader from '@/components/SortableTableHeader.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';

interface Kelas {
    id: number;
    nama_kelas: string;
    thn_ajaran: string;
}

interface Siswa {
    id: number;
    kelas_id: number | null;
    nis: string | null;
    nama: string;
    user: {
        name: string;
        email: string;
    };
    kelas: Kelas | null;
    status: 'pending' | 'aktif' | 'ditolak';
    tanggal_registrasi: string;
    approved_at: string | null;
    approver: { name: string } | null;
    created_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

const page = usePage();

const siswas = computed(
    () =>
        page.props.siswas as {
            data: Siswa[];
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
const kelasOptions = computed(() => page.props.kelasOptions as Kelas[]);
const canMoveClass = computed(() => page.props.canMoveClass as boolean);
const canApproveSiswa = computed(() => page.props.canApproveSiswa as boolean);

const search = ref(filters.value?.search ?? '');
const status = ref(filters.value?.status ?? '');
const selectedSiswa = ref<Siswa | null>(null);
const moveForm = useForm({
    kelas_id: '',
});
const approvalForm = useForm({
    thn_ajaran: '',
    kelas_id: '',
});
const approvalAction = ref<'approve' | 'reject' | null>(null);

/** True when the selected student has no class yet (first assignment, not a "move"). */
const isAssigningClass = computed(() => !selectedSiswa.value?.kelas);

const targetKelas = computed(() => {
    if (!selectedSiswa.value) {
        return [];
    }

    // No current class yet: any class is a valid target, nothing to exclude/match against.
    if (!selectedSiswa.value.kelas) {
        return kelasOptions.value;
    }

    return kelasOptions.value.filter(
        (item) =>
            item.id !== selectedSiswa.value?.kelas_id &&
            item.thn_ajaran === selectedSiswa.value?.kelas?.thn_ajaran,
    );
});

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
        SiswaController.index().url,
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
        SiswaController.index().url,
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

const createSiswa = () => {
    router.visit(SiswaController.create().url);
};

const editSiswa = (id: number) => {
    router.visit(SiswaController.edit(id).url);
};

const showSiswa = (id: number) => {
    router.visit(SiswaController.show(id).url);
};

const deleteSiswa = (id: number) => {
    if (!confirm('Hapus siswa ini?')) {
        return;
    }

    router.delete(SiswaController.destroy(id).url);
};

const openMoveClass = (siswa: Siswa) => {
    selectedSiswa.value = siswa;
    moveForm.reset();
    moveForm.clearErrors();
};

const closeMoveClass = () => {
    selectedSiswa.value = null;
    moveForm.reset();
    moveForm.clearErrors();
};

const submitMoveClass = () => {
    if (!selectedSiswa.value) {
        return;
    }

    moveForm.post(SiswaController.moveClass(selectedSiswa.value.id).url, {
        preserveScroll: true,
        onSuccess: closeMoveClass,
    });
};

const openApproval = (siswa: Siswa, action: 'approve' | 'reject') => {
    selectedSiswa.value = siswa;
    approvalAction.value = action;
    approvalForm.reset();
    approvalForm.thn_ajaran = siswa.kelas?.thn_ajaran ?? '';
    approvalForm.kelas_id = siswa.kelas_id ? String(siswa.kelas_id) : '';
    approvalForm.clearErrors();
};

const closeApproval = () => {
    selectedSiswa.value = null;
    approvalAction.value = null;
    approvalForm.clearErrors();
};

const submitApproval = () => {
    if (!selectedSiswa.value || !approvalAction.value) {
        return;
    }

    const url =
        approvalAction.value === 'approve'
            ? SiswaController.approve(selectedSiswa.value.id).url
            : SiswaController.reject(selectedSiswa.value.id).url;

    approvalForm.post(url, {
        preserveScroll: true,
        onSuccess: closeApproval,
    });
};

const columns: ColumnDef<Siswa>[] = [
    { accessorKey: 'nis', header: 'NIS' },
    { accessorKey: 'nama', header: 'Nama' },
    {
        accessorFn: (row) => row.user.name,
        id: 'user_name',
        header: 'Nama Akun Orangtua',
    },
    { accessorFn: (row) => row.user.email, id: 'email', header: 'Email' },
    {
        accessorFn: (row) => row.kelas?.nama_kelas ?? null,
        id: 'nama_kelas',
        header: 'Kelas',
    },
    {
        accessorFn: (row) => row.kelas?.thn_ajaran ?? null,
        id: 'thn_ajaran',
        header: 'Tahun Ajaran',
    },
    { accessorKey: 'status', header: 'Status' },
    { accessorKey: 'tanggal_registrasi', header: 'Tanggal Registrasi' },
    {
        accessorFn: (row) => row.approver?.name,
        id: 'approved_by',
        header: 'Approved By',
    },
    { accessorKey: 'approved_at', header: 'Approved At' },
];

const tableColumns = [
    { key: 'nis', label: 'NIS' },
    { key: 'nama', label: 'Nama Siswa' },
    { key: 'user_name', label: 'Nama Akun Orangtua' },
    { key: 'email', label: 'Email Orangtua' },
    { key: 'nama_kelas', label: 'Kelas' },
    { key: 'thn_ajaran', label: 'Tahun Ajaran' },
    { key: 'status', label: 'Status' },
    { key: 'tanggal_registrasi', label: 'Tanggal Registrasi' },
    { key: 'approved_by', label: 'Approved By' },
    { key: 'approved_at', label: 'Approved At' },
];

const table = useVueTable({
    get data() {
        return siswas.value.data;
    },
    columns,
    getCoreRowModel: getCoreRowModel(),
});
</script>

<template>
    <Head title="Siswa" />

    <div class="space-y-4 bg-background p-4 text-foreground">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Daftar Siswa</h1>

            <Button @click="createSiswa">Tambah Siswa</Button>
        </div>

        <div class="flex flex-wrap gap-2">
            <Input
                v-model="search"
                placeholder="Cari siswa, user, atau kelas..."
                @keyup.enter="applySearch"
            />

            <Button @click="applySearch">Cari</Button>
            <select
                v-model="status"
                class="h-9 rounded-md border border-border bg-background px-3 text-sm text-foreground"
                @change="applySearch"
            >
                <option value="">Semua Status</option>
                <option value="pending">Pending</option>
                <option value="aktif">Aktif</option>
                <option value="ditolak">Ditolak</option>
            </select>
        </div>

        <div
            class="overflow-x-auto rounded-xl border border-border bg-card text-card-foreground"
        >
            <table class="w-full">
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
                        <td class="px-4 py-3">{{ row.original.nis ?? '-' }}</td>
                        <td class="px-4 py-3">{{ row.original.nama }}</td>
                        <td class="px-4 py-3">{{ row.original.user.name }}</td>
                        <td class="px-4 py-3">{{ row.original.user.email }}</td>
                        <td class="px-4 py-3">
                            {{ row.original.kelas?.nama_kelas ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            {{ row.original.kelas?.thn_ajaran ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="rounded-full px-2 py-1 text-xs font-medium"
                                :class="{
                                    'bg-muted text-muted-foreground':
                                        row.original.status === 'pending',
                                    'bg-primary text-primary-foreground':
                                        row.original.status === 'aktif',
                                    'bg-destructive/15 text-destructive':
                                        row.original.status === 'ditolak',
                                }"
                            >
                                {{ row.original.status }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            {{
                                new Date(
                                    row.original.tanggal_registrasi,
                                ).toLocaleDateString('id-ID')
                            }}
                        </td>
                        <td class="px-4 py-3">
                            {{ row.original.approver?.name ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            {{
                                row.original.approved_at
                                    ? new Date(
                                          row.original.approved_at,
                                      ).toLocaleString('id-ID')
                                    : '-'
                            }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <Button
                                    variant="outline"
                                    @click="showSiswa(row.original.id)"
                                >
                                    Detail
                                </Button>
                                <Button
                                    variant="secondary"
                                    @click="editSiswa(row.original.id)"
                                >
                                    Edit
                                </Button>
                                <Button
                                    v-if="canMoveClass"
                                    variant="outline"
                                    @click="openMoveClass(row.original)"
                                >
                                    {{
                                        row.original.kelas
                                            ? 'Pindahkan Kelas'
                                            : 'Tetapkan Kelas'
                                    }}
                                </Button>
                                <Button
                                    variant="destructive"
                                    @click="deleteSiswa(row.original.id)"
                                >
                                    Hapus
                                </Button>
                                <Button
                                    v-if="
                                        canApproveSiswa &&
                                        ['pending', 'ditolak'].includes(
                                            row.original.status,
                                        )
                                    "
                                    @click="
                                        openApproval(row.original, 'approve')
                                    "
                                >
                                    Approve
                                </Button>
                                <Button
                                    v-if="
                                        canApproveSiswa &&
                                        ['pending', 'aktif'].includes(
                                            row.original.status,
                                        )
                                    "
                                    variant="outline"
                                    @click="
                                        openApproval(row.original, 'reject')
                                    "
                                >
                                    Reject
                                </Button>
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
                v-for="link in siswas.links"
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
            v-if="selectedSiswa && !approvalAction"
            class="fixed inset-0 z-50 flex items-center justify-center bg-muted/80 p-4"
        >
            <div
                class="w-full max-w-lg rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
            >
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold">
                        {{
                            isAssigningClass
                                ? 'Tetapkan Kelas Siswa'
                                : 'Pindahkan Kelas Siswa'
                        }}
                    </h2>
                    <p class="text-sm text-muted-foreground">
                        {{
                            isAssigningClass
                                ? 'Siswa ini belum memiliki kelas. Pilih kelas untuk ditetapkan.'
                                : 'Perbarui kelas siswa pada tahun ajaran yang sama.'
                        }}
                    </p>
                </div>

                <div class="mt-6 rounded-md bg-muted p-4">
                    <p class="text-sm text-muted-foreground">Nama Siswa</p>
                    <p class="font-medium">{{ selectedSiswa.nama }}</p>
                    <p class="mt-3 text-sm text-muted-foreground">NIS</p>
                    <p class="font-medium">{{ selectedSiswa.nis ?? '-' }}</p>
                    <p class="mt-3 text-sm text-muted-foreground">
                        Kelas Saat Ini
                    </p>
                    <p class="font-medium">
                        <template v-if="selectedSiswa.kelas">
                            {{ selectedSiswa.kelas.nama_kelas }} -
                            {{ selectedSiswa.kelas.thn_ajaran }}
                        </template>
                        <template v-else>Belum ditetapkan</template>
                    </p>
                </div>

                <form class="mt-6 space-y-4" @submit.prevent="submitMoveClass">
                    <div class="space-y-2">
                        <label for="kelas_id" class="text-sm font-medium">{{
                            isAssigningClass ? 'Kelas' : 'Kelas Tujuan'
                        }}</label>
                        <select
                            id="kelas_id"
                            v-model="moveForm.kelas_id"
                            class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground"
                        >
                            <option value="">Pilih kelas tujuan</option>
                            <option
                                v-for="item in targetKelas"
                                :key="item.id"
                                :value="String(item.id)"
                            >
                                {{ item.nama_kelas }} - {{ item.thn_ajaran }}
                            </option>
                        </select>
                        <InputError :message="moveForm.errors.kelas_id" />
                    </div>

                    <div class="flex gap-2">
                        <Button type="submit" :disabled="moveForm.processing">
                            {{
                                moveForm.processing
                                    ? isAssigningClass
                                        ? 'Menetapkan...'
                                        : 'Memindahkan...'
                                    : isAssigningClass
                                      ? 'Tetapkan'
                                      : 'Pindahkan'
                            }}
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            :disabled="moveForm.processing"
                            @click="closeMoveClass"
                        >
                            Batal
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <div
            v-if="selectedSiswa && approvalAction"
            class="fixed inset-0 z-[60] flex items-center justify-center bg-muted/80 p-4"
        >
            <div
                class="w-full max-w-lg rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
            >
                <h2 class="text-lg font-semibold">
                    {{
                        approvalAction === 'approve'
                            ? 'Setujui Pendaftaran Siswa'
                            : 'Tolak Pendaftaran Siswa'
                    }}
                </h2>
                <p class="mt-2 text-sm text-muted-foreground">
                    {{
                        approvalAction === 'approve'
                            ? 'Apakah Anda yakin ingin menyetujui pendaftaran siswa ini?'
                            : 'Apakah Anda yakin ingin menolak pendaftaran siswa ini?'
                    }}
                </p>
                <p class="mt-4 font-medium">{{ selectedSiswa.nama }}</p>
                <div
                    v-if="approvalAction === 'approve'"
                    class="mt-5 grid gap-4 sm:grid-cols-2"
                >
                    <div class="space-y-2">
                        <label
                            for="approval_thn_ajaran"
                            class="text-sm font-medium"
                            >Tahun Ajaran</label
                        >
                        <select
                            id="approval_thn_ajaran"
                            v-model="approvalForm.thn_ajaran"
                            class="h-10 w-full rounded-lg border border-border bg-background px-3 text-sm text-foreground"
                        >
                            <option value="">Pilih tahun ajaran</option>
                            <option
                                v-for="tahun in [
                                    ...new Set(
                                        kelasOptions.map(
                                            (item) => item.thn_ajaran,
                                        ),
                                    ),
                                ]"
                                :key="tahun"
                                :value="tahun"
                            >
                                {{ tahun }}
                            </option>
                        </select>
                        <InputError :message="approvalForm.errors.thn_ajaran" />
                    </div>
                    <div class="space-y-2">
                        <label
                            for="approval_kelas_id"
                            class="text-sm font-medium"
                            >Kelas</label
                        >
                        <select
                            id="approval_kelas_id"
                            v-model="approvalForm.kelas_id"
                            class="h-10 w-full rounded-lg border border-border bg-background px-3 text-sm text-foreground"
                        >
                            <option value="">Pilih kelas</option>
                            <option
                                v-for="kelasItem in kelasOptions.filter(
                                    (item) =>
                                        item.thn_ajaran ===
                                        approvalForm.thn_ajaran,
                                )"
                                :key="kelasItem.id"
                                :value="String(kelasItem.id)"
                            >
                                {{ kelasItem.nama_kelas }}
                            </option>
                        </select>
                        <InputError :message="approvalForm.errors.kelas_id" />
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <Button
                        variant="outline"
                        :disabled="approvalForm.processing"
                        @click="closeApproval"
                        >Batal</Button
                    >
                    <Button
                        :disabled="approvalForm.processing"
                        @click="submitApproval"
                    >
                        {{
                            approvalForm.processing
                                ? 'Memproses...'
                                : approvalAction === 'approve'
                                  ? 'Approve'
                                  : 'Reject'
                        }}
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
