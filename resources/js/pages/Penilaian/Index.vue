<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import type { ColumnDef } from '@tanstack/vue-table';
import { computed, ref, watch } from 'vue';

import PenilaianController from '@/actions/App/Http/Controllers/PenilaianController';

import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';

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
    subTema: { nama_sub_tema: string } | null;
}
type StatusAbsen = 'hadir' | 'izin' | 'sakit' | 'alfa';
interface Nilai {
    id: number;
    nilai: string;
    keterangan: string | null;
}
interface Absen {
    id: number;
    status: StatusAbsen;
    siswa: { id: number; nama: string; nis: string | null };
    nilai: Nilai | null;
}
interface PenilaianForm {
    kelas_id: string;
    jadwal_id: string;
    absen_id: string;
    nilai: string;
    keterangan: string;
}

const page = usePage();
const kelas = computed(() => page.props.kelas as Kelas[]);
const jadwals = computed(() => page.props.jadwals as Jadwal[]);
const absens = computed(() => page.props.absens as Absen[]);
const filters = computed(
    () =>
        page.props.filters as {
            kelas_id: string | null;
            jadwal_id: string | null;
        },
);
const selectedKelas = ref(filters.value.kelas_id ?? '');
const selectedJadwal = ref(filters.value.jadwal_id ?? '');
const selectedAbsen = ref<Absen | null>(null);
const isLoading = ref(false);

watch(filters, (value) => {
    selectedKelas.value = value.kelas_id ?? '';
    selectedJadwal.value = value.jadwal_id ?? '';
});

const nilaiForm = useForm<PenilaianForm>({
    kelas_id: selectedKelas.value,
    jadwal_id: selectedJadwal.value,
    absen_id: '',
    nilai: '',
    keterangan: '',
});

const loadData = (kelasId: string, jadwalId = '') => {
    isLoading.value = true;

    router.get(
        PenilaianController.index().url,
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
const canAssess = (absen: Absen) => absen.status === 'hadir' && !absen.nilai;
const openModal = (absen: Absen) => {
    selectedAbsen.value = absen;
    nilaiForm.nilai = '';
    nilaiForm.keterangan = '';
};
const submitNilai = () => {
    if (!selectedAbsen.value) {
        return;
    }

    nilaiForm.kelas_id = selectedKelas.value;
    nilaiForm.jadwal_id = selectedJadwal.value;
    nilaiForm.absen_id = String(selectedAbsen.value.id);
    nilaiForm.post(PenilaianController.store().url, {
        preserveScroll: true,
        onSuccess: () => {
            selectedAbsen.value = null;
            nilaiForm.reset('absen_id', 'nilai', 'keterangan');
        },
    });
};

const columns: ColumnDef<Absen>[] = [
    { accessorFn: (row) => row.siswa.nama, id: 'nama', header: 'Nama Siswa' },
    { accessorFn: (row) => row.siswa.nis, id: 'nis', header: 'NIS' },
    { accessorKey: 'status', header: 'Status Absensi' },
    { accessorFn: (row) => row.nilai?.nilai, id: 'nilai', header: 'Nilai' },
    {
        accessorFn: (row) => row.nilai?.keterangan,
        id: 'keterangan',
        header: 'Keterangan',
    },
];
const table = useVueTable({
    get data() {
        return absens.value;
    },
    columns,
    getCoreRowModel: getCoreRowModel(),
});
</script>

<template>
    <Head title="Penilaian Siswa" />
    <div class="space-y-6 bg-background p-4 text-foreground">
        <div>
            <h1 class="text-2xl font-bold">Penilaian Siswa</h1>
            <p class="text-sm text-muted-foreground">
                Pilih kelas dan jadwal untuk memberikan nilai siswa yang hadir.
            </p>
        </div>
        <div
            class="rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
        >
            <p v-if="selectedJadwal" class="mb-4 text-sm text-muted-foreground">
                Tema:
                {{
                    jadwals.find((item) => item.id === Number(selectedJadwal))
                        ?.tema.nama_tema ?? '-'
                }}
                · Sub Tema:
                {{
                    jadwals.find((item) => item.id === Number(selectedJadwal))
                        ?.subTema?.nama_sub_tema ?? '-'
                }}
            </p>
            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-2">
                    <label for="kelas_id" class="text-sm font-medium"
                        >Kelas</label
                    ><select
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
                    ><select
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
                            Nilai
                        </th>
                        <th class="px-4 py-3 text-left text-sm font-medium">
                            Keterangan
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
                        <td class="px-4 py-3">{{ row.original.siswa.nama }}</td>
                        <td class="px-4 py-3">
                            {{ row.original.siswa.nis ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="rounded-md bg-muted px-2 py-1 text-sm"
                                >{{ row.original.status }}</span
                            >
                        </td>
                        <td class="px-4 py-3">
                            {{ row.original.nilai?.nilai ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            {{ row.original.nilai?.keterangan ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <Button
                                v-if="canAssess(row.original)"
                                :disabled="nilaiForm.processing"
                                @click="openModal(row.original)"
                                >Berikan Nilai</Button
                            ><span
                                v-else-if="row.original.nilai"
                                class="text-sm text-muted-foreground"
                                >Sudah dinilai</span
                            ><span v-else class="text-sm text-muted-foreground"
                                >Tidak dapat dinilai</span
                            >
                        </td>
                    </tr>
                    <tr v-if="!table.getRowModel().rows.length">
                        <td
                            colspan="7"
                            class="py-8 text-center text-muted-foreground"
                        >
                            Belum ada data absensi pada jadwal ini
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-if="selectedAbsen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-muted/80 p-4"
        >
            <div
                class="w-full max-w-md rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
            >
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold">Berikan Nilai</h2>
                    <p class="text-sm text-muted-foreground">
                        {{ selectedAbsen.siswa.nama }}
                    </p>
                </div>
                <form class="mt-6 space-y-4" @submit.prevent="submitNilai">
                    <div class="space-y-2">
                        <label for="nilai" class="text-sm font-medium"
                            >Nilai</label
                        ><Input
                            id="nilai"
                            v-model="nilaiForm.nilai"
                            maxlength="5"
                            placeholder="Contoh: A"
                        /><InputError :message="nilaiForm.errors.nilai" />
                    </div>
                    <div class="space-y-2">
                        <label for="keterangan" class="text-sm font-medium"
                            >Keterangan</label
                        ><textarea
                            id="keterangan"
                            v-model="nilaiForm.keterangan"
                            class="min-h-24 w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground"
                            placeholder="Masukkan keterangan"
                        /><InputError :message="nilaiForm.errors.keterangan" />
                    </div>
                    <div class="flex gap-2">
                        <Button
                            type="submit"
                            :disabled="nilaiForm.processing"
                            >{{
                                nilaiForm.processing
                                    ? 'Menyimpan...'
                                    : 'Simpan Nilai'
                            }}</Button
                        ><Button
                            type="button"
                            variant="outline"
                            :disabled="nilaiForm.processing"
                            @click="selectedAbsen = null"
                            >Batal</Button
                        >
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
