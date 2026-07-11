<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import type { ColumnDef } from '@tanstack/vue-table';
import { computed, ref, watch } from 'vue';

import AbsenController from '@/actions/App/Http/Controllers/AbsenController';

import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';

interface Kelas {
    id: number;
    nama_kelas: string;
    thn_ajaran: string;
}
interface Jadwal {
    id: number;
    tanggal: string;
    jam_mulai: string;
    jam_selesai: string;
    guru: { nama: string };
    tema: { nama_tema: string };
}
type StatusAbsen = 'hadir' | 'izin' | 'sakit' | 'alfa';

interface Absen {
    id: number;
    status: StatusAbsen;
    keterangan: string | null;
}
interface Siswa {
    id: number;
    nama: string;
    nis: string | null;
    absens: Absen[];
}

interface AbsenForm {
    kelas_id: string;
    jadwal_id: string;
    siswa_id: string;
    status: StatusAbsen;
    keterangan: string;
}

const page = usePage();
const kelas = computed(() => page.props.kelas as Kelas[]);
const jadwals = computed(() => page.props.jadwals as Jadwal[]);
const siswas = computed(() => page.props.siswas as Siswa[]);
const filters = computed(
    () =>
        page.props.filters as {
            kelas_id: string | null;
            jadwal_id: string | null;
        },
);
const selectedKelas = ref(filters.value.kelas_id ?? '');
const selectedJadwal = ref(filters.value.jadwal_id ?? '');
const isLoading = ref(false);
const selectedSiswa = ref<Siswa | null>(null);

watch(filters, (value) => {
    selectedKelas.value = value.kelas_id ?? '';
    selectedJadwal.value = value.jadwal_id ?? '';
});

const absenForm = useForm<AbsenForm>({
    kelas_id: selectedKelas.value,
    jadwal_id: selectedJadwal.value,
    siswa_id: '',
    status: 'hadir',
    keterangan: '',
});

const loadData = (kelasId: string, jadwalId = '') => {
    isLoading.value = true;

    router.get(
        AbsenController.index().url,
        { kelas_id: kelasId, jadwal_id: jadwalId },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onFinish: () => {
                isLoading.value = false;
            },
        },
    );
};

const changeKelas = () => {
    selectedJadwal.value = '';
    loadData(selectedKelas.value);
};

const changeJadwal = () => loadData(selectedKelas.value, selectedJadwal.value);

const statusFor = (siswa: Siswa) => siswa.absens[0] ?? null;

const submitAbsen = (siswa: Siswa, status: StatusAbsen, keterangan = '') => {
    absenForm.kelas_id = selectedKelas.value;
    absenForm.jadwal_id = selectedJadwal.value;
    absenForm.siswa_id = String(siswa.id);
    absenForm.status = status;
    absenForm.keterangan = keterangan;

    absenForm.post(AbsenController.store().url, {
        preserveScroll: true,
        onSuccess: () => {
            selectedSiswa.value = null;
            absenForm.reset('siswa_id', 'status', 'keterangan');
        },
    });
};

const openTidakHadir = (siswa: Siswa) => {
    selectedSiswa.value = siswa;
    absenForm.status = 'izin';
    absenForm.keterangan = '';
};

const submitTidakHadir = () => {
    if (!selectedSiswa.value) {
        return;
    }

    submitAbsen(selectedSiswa.value, absenForm.status, absenForm.keterangan);
};

const undoAbsen = (absen: Absen, siswa: Siswa) => {
    if (!window.confirm(`Batalkan absensi ${siswa.nama}?`)) {
        return;
    }

    router.delete(AbsenController.destroy(absen.id).url, {
        preserveScroll: true,
    });
};

const columns: ColumnDef<Siswa>[] = [
    { accessorKey: 'nama', header: 'Nama Siswa' },
    { accessorKey: 'nis', header: 'NIS' },
    { id: 'status', header: 'Status Absensi' },
];
const table = useVueTable({
    get data() {
        return siswas.value;
    },
    columns,
    getCoreRowModel: getCoreRowModel(),
});
</script>

<template>
    <Head title="Absensi Siswa" />

    <div class="space-y-6 bg-background p-4 text-foreground">
        <div>
            <h1 class="text-2xl font-bold">Absensi Siswa</h1>
            <p class="text-sm text-muted-foreground">
                Pilih kelas dan jadwal untuk mencatat kehadiran siswa.
            </p>
        </div>

        <div
            class="rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
        >
            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-2">
                    <label for="kelas_id" class="text-sm font-medium"
                        >Kelas</label
                    >
                    <select
                        id="kelas_id"
                        v-model="selectedKelas"
                        :disabled="isLoading"
                        class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground disabled:cursor-not-allowed disabled:opacity-50"
                        @change="changeKelas"
                    >
                        <option value="">Pilih kelas</option>
                        <option
                            v-for="item in kelas"
                            :key="item.id"
                            :value="String(item.id)"
                        >
                            {{ item.nama_kelas }} - {{ item.thn_ajaran }}
                        </option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label for="jadwal_id" class="text-sm font-medium"
                        >Jadwal</label
                    >
                    <select
                        id="jadwal_id"
                        v-model="selectedJadwal"
                        :disabled="!selectedKelas || isLoading"
                        class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground disabled:cursor-not-allowed disabled:opacity-50"
                        @change="changeJadwal"
                    >
                        <option value="">Pilih jadwal</option>
                        <option
                            v-for="item in jadwals"
                            :key="item.id"
                            :value="String(item.id)"
                        >
                            {{ item.tanggal }} {{ item.jam_mulai }} -
                            {{ item.jam_selesai }} | {{ item.guru.nama }} |
                            {{ item.tema.nama_tema }}
                        </option>
                    </select>
                </div>
            </div>
            <p v-if="isLoading" class="mt-4 text-sm text-muted-foreground">
                Memuat data...
            </p>
        </div>

        <div
            v-if="selectedJadwal"
            class="overflow-x-auto rounded-xl border border-border bg-card text-card-foreground"
        >
            <table class="w-full min-w-max">
                <thead class="bg-muted/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium">
                            No
                        </th>
                        <th class="px-4 py-3 text-left text-sm font-medium">
                            Nama Siswa
                        </th>
                        <th class="px-4 py-3 text-left text-sm font-medium">
                            NIS
                        </th>
                        <th class="px-4 py-3 text-left text-sm font-medium">
                            Status Absensi
                        </th>
                        <th class="px-4 py-3 text-left text-sm font-medium">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(row, index) in table.getRowModel().rows"
                        :key="row.id"
                        class="border-t border-border hover:bg-muted/50"
                    >
                        <td class="px-4 py-3">{{ index + 1 }}</td>
                        <td class="px-4 py-3">{{ row.original.nama }}</td>
                        <td class="px-4 py-3">{{ row.original.nis ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span
                                v-if="statusFor(row.original)"
                                class="rounded-md bg-muted px-2 py-1 text-sm"
                                >{{ statusFor(row.original)?.status }}</span
                            ><span v-else class="text-muted-foreground"
                                >Belum diabsen</span
                            >
                        </td>
                        <td class="px-4 py-3">
                            <div
                                v-if="!statusFor(row.original)"
                                class="flex gap-2"
                            >
                                <Button
                                    :disabled="absenForm.processing"
                                    @click="submitAbsen(row.original, 'hadir')"
                                    >Hadir</Button
                                ><Button
                                    variant="outline"
                                    :disabled="absenForm.processing"
                                    @click="openTidakHadir(row.original)"
                                    >Tidak Hadir</Button
                                >
                            </div>
                            <Button
                                v-else
                                type="button"
                                variant="outline"
                                :disabled="absenForm.processing"
                                @click="
                                    undoAbsen(
                                        statusFor(row.original)!,
                                        row.original,
                                    )
                                "
                                >Batalkan Absensi</Button
                            >
                        </td>
                    </tr>
                    <tr v-if="!table.getRowModel().rows.length">
                        <td
                            colspan="5"
                            class="py-8 text-center text-muted-foreground"
                        >
                            Tidak ada siswa pada kelas ini
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-if="selectedSiswa"
            class="fixed inset-0 z-50 flex items-center justify-center bg-muted/80 p-4"
        >
            <div
                class="w-full max-w-md rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
            >
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold">Tidak Hadir</h2>
                    <p class="text-sm text-muted-foreground">
                        {{ selectedSiswa.nama }}
                    </p>
                </div>
                <form class="mt-6 space-y-4" @submit.prevent="submitTidakHadir">
                    <div class="space-y-2">
                        <label for="status" class="text-sm font-medium"
                            >Status</label
                        ><select
                            id="status"
                            v-model="absenForm.status"
                            class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground"
                        >
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                            <option value="alfa">Alfa</option></select
                        ><InputError :message="absenForm.errors.status" />
                    </div>
                    <div class="space-y-2">
                        <label for="keterangan" class="text-sm font-medium"
                            >Keterangan</label
                        ><textarea
                            id="keterangan"
                            v-model="absenForm.keterangan"
                            class="min-h-24 w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground"
                            placeholder="Masukkan keterangan"
                        /><InputError :message="absenForm.errors.keterangan" />
                    </div>
                    <div class="flex gap-2">
                        <Button
                            type="submit"
                            :disabled="absenForm.processing"
                            >{{
                                absenForm.processing ? 'Menyimpan...' : 'Simpan'
                            }}</Button
                        ><Button
                            type="button"
                            variant="outline"
                            :disabled="absenForm.processing"
                            @click="selectedSiswa = null"
                            >Batal</Button
                        >
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
