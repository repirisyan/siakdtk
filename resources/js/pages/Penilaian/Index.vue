<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import type { ColumnDef } from '@tanstack/vue-table';
import { ChevronDown } from '@lucide/vue';
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
}
type StatusAbsen = 'hadir' | 'izin' | 'sakit' | 'alfa';
interface Nilai {
    id: number;
    komponen_penilaian_id: number;
    nilai: string;
    keterangan: string | null;
    komponen_penilaian: {
        nama_komponen: string;
        sub_tema: {
            nama_sub_tema: string;
            tema: { nama_tema: string } | null;
        } | null;
    } | null;
    foto_kegiatans: { id: number; url: string }[];
}
interface KomponenPenilaian {
    id: number;
    nama_komponen: string;
    deskripsi: string | null;
}
interface Tema {
    id: number;
    nama_tema: string;
}
interface SubTema {
    id: number;
    tema_id: number;
    nama_sub_tema: string;
}
interface SummaryStudent {
    id: number;
    nama: string;
    nis: string | null;
}
interface Absen {
    id: number;
    status: StatusAbsen;
    siswa: { id: number; nama: string; nis: string | null };
    nilais: Nilai[];
}
interface PenilaianGroup {
    tema: string;
    subTema: string;
    nilais: Nilai[];
}
interface PenilaianForm {
    kelas_id: string;
    jadwal_id: string;
    absen_id: string;
    sub_tema_id: string;
    komponen_penilaian_id: string;
    nilai: string;
    keterangan: string;
    foto_kegiatan: File[];
}

const page = usePage();
const kelas = computed(() => page.props.kelas as Kelas[]);
const jadwals = computed(() => page.props.jadwals as Jadwal[]);
const absens = computed(() => page.props.absens as Absen[]);
const komponenPenilaians = computed(
    () => page.props.komponenPenilaians as KomponenPenilaian[],
);
const lockedSiswaIds = computed(() => page.props.lockedSiswaIds as number[]);
const temas = computed(() => page.props.temas as Tema[]);
const subTemas = computed(() => page.props.subTemas as SubTema[]);
const summaryStudents = computed(
    () => page.props.summaryStudents as SummaryStudent[],
);
const filters = computed(
    () =>
        page.props.filters as {
            kelas_id: string | null;
            jadwal_id: string | null;
            summary: boolean;
            tema_id: string | null;
            sub_tema_id: string | null;
        },
);
const selectedKelas = ref(filters.value.kelas_id ?? '');
const selectedJadwal = ref(filters.value.jadwal_id ?? '');
const isSummary = ref(filters.value.summary ?? false);
const selectedTema = ref(filters.value.tema_id ?? '');
const selectedSubTema = ref(filters.value.sub_tema_id ?? '');
const selectedAbsen = ref<Absen | null>(null);
const isLoading = ref(false);
const photoInput = ref<HTMLInputElement | null>(null);
const photoPreviews = ref<string[]>([]);
const expandedPenilaianGroups = ref<string[]>([]);

watch(filters, (value) => {
    selectedKelas.value = value.kelas_id ?? '';
    selectedJadwal.value = value.jadwal_id ?? '';
    isSummary.value = value.summary ?? false;
    selectedTema.value = value.tema_id ?? '';
    selectedSubTema.value = value.sub_tema_id ?? '';
});

const nilaiForm = useForm<PenilaianForm>({
    kelas_id: selectedKelas.value,
    jadwal_id: selectedJadwal.value,
    absen_id: '',
    sub_tema_id: '',
    komponen_penilaian_id: '',
    nilai: '',
    keterangan: '',
    foto_kegiatan: [],
});

const loadData = () => {
    isLoading.value = true;

    router.get(
        PenilaianController.index().url,
        {
            kelas_id: selectedKelas.value,
            jadwal_id: isSummary.value ? '' : selectedJadwal.value,
            summary: isSummary.value ? 1 : 0,
            tema_id: isSummary.value ? selectedTema.value : '',
            sub_tema_id: selectedSubTema.value,
        },
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
    selectedTema.value = '';
    selectedSubTema.value = '';
    loadData();
};
const changeJadwal = () => {
    selectedSubTema.value = '';
    loadData();
};
const changeSummary = () => {
    selectedJadwal.value = '';
    selectedTema.value = '';
    selectedSubTema.value = '';
    loadData();
};
const changeTema = () => {
    selectedSubTema.value = '';
    loadData();
};
const changeSubTema = () => loadData();
const summaryDetailUrl = (siswaId: number) =>
    `/penilaian/summary/${siswaId}?${new URLSearchParams({
        kelas_id: selectedKelas.value,
        tema_id: selectedTema.value,
        sub_tema_id: selectedSubTema.value,
    }).toString()}`;
const isLocked = (absen: Absen) =>
    lockedSiswaIds.value.includes(absen.siswa.id);
const canAssess = (absen: Absen) =>
    !isLocked(absen) &&
    absen.status === 'hadir' &&
    komponenPenilaians.value.length > 0;
const selectedNilai = computed(() =>
    selectedAbsen.value?.nilais.find(
        (nilai) =>
            nilai.komponen_penilaian_id ===
            Number(nilaiForm.komponen_penilaian_id),
    ),
);
const openModal = (absen: Absen) => {
    selectedAbsen.value = absen;
    nilaiForm.nilai = '';
    nilaiForm.keterangan = '';
    nilaiForm.komponen_penilaian_id = '';
    clearPhotos();
};
watch(
    () => nilaiForm.komponen_penilaian_id,
    () => {
        nilaiForm.nilai = selectedNilai.value?.nilai ?? '';
        nilaiForm.keterangan = selectedNilai.value?.keterangan ?? '';
    },
);
const clearPhotos = () => {
    photoPreviews.value.forEach((preview) => URL.revokeObjectURL(preview));
    photoPreviews.value = [];
    nilaiForm.foto_kegiatan = [];

    if (photoInput.value) {
        photoInput.value.value = '';
    }
};
const setPhotos = (event: Event) => {
    const input = event.target as HTMLInputElement;

    photoPreviews.value.forEach((preview) => URL.revokeObjectURL(preview));
    nilaiForm.foto_kegiatan = Array.from(input.files ?? []);
    photoPreviews.value = nilaiForm.foto_kegiatan.map((file) =>
        URL.createObjectURL(file),
    );
};
const closeModal = () => {
    clearPhotos();
    selectedAbsen.value = null;
};
const submitNilai = () => {
    if (!selectedAbsen.value) {
        return;
    }

    nilaiForm.kelas_id = selectedKelas.value;
    nilaiForm.jadwal_id = selectedJadwal.value;
    nilaiForm.absen_id = String(selectedAbsen.value.id);
    nilaiForm.sub_tema_id = selectedSubTema.value;
    nilaiForm.post(PenilaianController.store().url, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            closeModal();
            nilaiForm.reset(
                'absen_id',
                'sub_tema_id',
                'komponen_penilaian_id',
                'nilai',
                'keterangan',
                'foto_kegiatan',
            );
        },
    });
};

const penilaianGroups = (nilais: Nilai[]) => {
    const groups = new Map<string, PenilaianGroup>();

    nilais.forEach((nilai) => {
        const subTema = nilai.komponen_penilaian?.sub_tema;
        const tema = subTema?.tema?.nama_tema ?? 'Tema tidak tersedia';
        const namaSubTema = subTema?.nama_sub_tema ?? 'Sub Tema tidak tersedia';
        const key = `${tema}-${namaSubTema}`;
        const group = groups.get(key) ?? {
            tema,
            subTema: namaSubTema,
            nilais: [],
        };

        group.nilais.push(nilai);
        groups.set(key, group);
    });

    return Array.from(groups.values());
};
const penilaianGroupKey = (absenId: number, group: PenilaianGroup) =>
    `${absenId}-${group.tema}-${group.subTema}`;
const isPenilaianGroupExpanded = (absenId: number, group: PenilaianGroup) =>
    expandedPenilaianGroups.value.includes(penilaianGroupKey(absenId, group));
const togglePenilaianGroup = (absenId: number, group: PenilaianGroup) => {
    const key = penilaianGroupKey(absenId, group);

    expandedPenilaianGroups.value = isPenilaianGroupExpanded(absenId, group)
        ? expandedPenilaianGroups.value.filter((item) => item !== key)
        : [...expandedPenilaianGroups.value, key];
};

const columns: ColumnDef<Absen>[] = [
    { accessorFn: (row) => row.siswa.nama, id: 'nama', header: 'Nama Siswa' },
    { accessorFn: (row) => row.siswa.nis, id: 'nis', header: 'NIS' },
    { accessorKey: 'status', header: 'Status Absensi' },
    {
        accessorFn: (row) =>
            row.nilais
                .map((nilai) =>
                    `${nilai.nilai} ${nilai.keterangan ?? ''}`.trim(),
                )
                .join(', '),
        id: 'penilaian',
        header: 'Hasil Penilaian',
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
            <p
                v-if="selectedJadwal && !isSummary"
                class="mb-4 text-sm text-muted-foreground"
            >
                Tema:
                {{
                    jadwals.find((item) => item.id === Number(selectedJadwal))
                        ?.tema.nama_tema ?? '-'
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
                <div v-if="!isSummary" class="space-y-2">
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
                <div v-if="!isSummary && selectedJadwal" class="space-y-2">
                    <label for="jadwal_sub_tema_id" class="text-sm font-medium"
                        >Sub Tema</label
                    >
                    <select
                        id="jadwal_sub_tema_id"
                        v-model="selectedSubTema"
                        :disabled="isLoading"
                        class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground disabled:cursor-not-allowed disabled:opacity-50"
                        @change="loadData"
                    >
                        <option value="">Pilih sub tema untuk penilaian</option>
                        <option
                            v-for="item in subTemas"
                            :key="item.id"
                            :value="String(item.id)"
                        >
                            {{ item.nama_sub_tema }}
                        </option>
                    </select>
                </div>
                <div v-else class="space-y-2">
                    <label for="tema_id" class="text-sm font-medium"
                        >Tema</label
                    >
                    <select
                        id="tema_id"
                        v-model="selectedTema"
                        :disabled="!selectedKelas || isLoading"
                        class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground disabled:cursor-not-allowed disabled:opacity-50"
                        @change="changeTema"
                    >
                        <option value="">Pilih tema</option>
                        <option
                            v-for="item in temas"
                            :key="item.id"
                            :value="String(item.id)"
                        >
                            {{ item.nama_tema }}
                        </option>
                    </select>
                </div>
                <div v-if="isSummary" class="space-y-2">
                    <label for="sub_tema_id" class="text-sm font-medium"
                        >Sub Tema</label
                    >
                    <select
                        id="sub_tema_id"
                        v-model="selectedSubTema"
                        :disabled="!selectedTema || isLoading"
                        class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground disabled:cursor-not-allowed disabled:opacity-50"
                        @change="changeSubTema"
                    >
                        <option value="">Pilih sub tema</option>
                        <option
                            v-for="item in subTemas"
                            :key="item.id"
                            :value="String(item.id)"
                        >
                            {{ item.nama_sub_tema }}
                        </option>
                    </select>
                </div>
            </div>
            <label
                class="mt-4 flex w-fit cursor-pointer items-center gap-2 text-sm font-medium"
            >
                <input
                    v-model="isSummary"
                    type="checkbox"
                    :disabled="isLoading"
                    class="size-4 rounded border-border"
                    @change="changeSummary"
                />
                Summary
            </label>
            <p v-if="isLoading" class="mt-4 text-sm text-muted-foreground">
                Memuat data...
            </p>
        </div>

        <div
            v-if="!isSummary && selectedJadwal"
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
                        <th
                            class="min-w-96 px-4 py-3 text-left text-sm font-medium"
                        >
                            Hasil Penilaian
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
                        <td class="px-4 py-3 align-top">{{ index + 1 }}</td>
                        <td class="px-4 py-3 align-top">
                            {{ row.original.siswa.nama }}
                        </td>
                        <td class="px-4 py-3 align-top">
                            {{ row.original.siswa.nis ?? '-' }}
                        </td>
                        <td class="px-4 py-3 align-top">
                            <span
                                class="rounded-md bg-muted px-2 py-1 text-sm"
                                >{{ row.original.status }}</span
                            >
                        </td>
                        <td class="px-4 py-3 align-top">
                            <div
                                v-if="row.original.nilais.length"
                                class="space-y-3"
                            >
                                <section
                                    v-for="group in penilaianGroups(
                                        row.original.nilais,
                                    )"
                                    :key="`${group.tema}-${group.subTema}`"
                                    class="rounded-lg border border-border bg-muted/40 p-3"
                                >
                                    <button
                                        type="button"
                                        class="flex w-full items-center justify-between gap-3 text-left"
                                        :aria-expanded="
                                            isPenilaianGroupExpanded(
                                                row.original.id,
                                                group,
                                            )
                                        "
                                        :aria-label="`Tampilkan detail penilaian ${group.tema}, ${group.subTema}`"
                                        @click="
                                            togglePenilaianGroup(
                                                row.original.id,
                                                group,
                                            )
                                        "
                                    >
                                        <span
                                            class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs"
                                        >
                                            <span
                                                class="font-medium text-foreground"
                                                >Tema: {{ group.tema }}</span
                                            >
                                            <span class="text-muted-foreground"
                                                >•</span
                                            >
                                            <span class="text-muted-foreground"
                                                >Sub Tema:
                                                {{ group.subTema }}</span
                                            >
                                        </span>
                                        <ChevronDown
                                            class="size-4 shrink-0 text-muted-foreground transition-transform"
                                            :class="{
                                                'rotate-180':
                                                    isPenilaianGroupExpanded(
                                                        row.original.id,
                                                        group,
                                                    ),
                                            }"
                                        />
                                    </button>
                                    <div
                                        v-if="
                                            isPenilaianGroupExpanded(
                                                row.original.id,
                                                group,
                                            )
                                        "
                                        class="space-y-3"
                                    >
                                        <div
                                            v-for="nilai in group.nilais"
                                            :key="nilai.id"
                                            class="border-t border-border pt-3 first:border-t-0 first:pt-0"
                                        >
                                            <div
                                                class="flex flex-wrap items-center justify-between gap-2"
                                            >
                                                <p class="text-sm font-medium">
                                                    {{
                                                        nilai.komponen_penilaian
                                                            ?.nama_komponen ??
                                                        'Komponen Penilaian'
                                                    }}
                                                </p>
                                                <span
                                                    class="rounded-md bg-primary px-2 py-1 text-xs font-semibold text-primary-foreground"
                                                >
                                                    Nilai {{ nilai.nilai }}
                                                </span>
                                            </div>
                                            <p
                                                class="mt-1 text-sm text-muted-foreground"
                                            >
                                                {{
                                                    nilai.keterangan ??
                                                    'Belum ada keterangan.'
                                                }}
                                            </p>
                                            <p
                                                v-if="
                                                    nilai.foto_kegiatans.length
                                                "
                                                class="mt-2 text-xs text-muted-foreground"
                                            >
                                                {{
                                                    nilai.foto_kegiatans.length
                                                }}
                                                foto kegiatan tersedia
                                            </p>
                                        </div>
                                    </div>
                                </section>
                            </div>
                            <span v-else class="text-sm text-muted-foreground"
                                >Belum ada penilaian.</span
                            >
                        </td>
                        <td class="px-4 py-3 align-top">
                            <Button
                                v-if="canAssess(row.original)"
                                :disabled="nilaiForm.processing"
                                @click="openModal(row.original)"
                                >Kelola Nilai</Button
                            ><span
                                v-else
                                class="text-sm text-muted-foreground"
                                >{{
                                    row.original.status === 'hadir'
                                        ? isLocked(row.original)
                                            ? 'Terkunci setelah Rapor Akhir disetujui'
                                            : 'Komponen belum tersedia'
                                        : 'Tidak dapat dinilai'
                                }}</span
                            >
                        </td>
                    </tr>
                    <tr v-if="!table.getRowModel().rows.length">
                        <td
                            colspan="6"
                            class="py-8 text-center text-muted-foreground"
                        >
                            Belum ada data absensi pada jadwal ini
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-if="isSummary && selectedSubTema"
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
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(siswa, index) in summaryStudents"
                        :key="siswa.id"
                        class="border-t border-border hover:bg-muted/50"
                    >
                        <td class="px-4 py-3">{{ index + 1 }}</td>
                        <td class="px-4 py-3">{{ siswa.nama }}</td>
                        <td class="px-4 py-3">{{ siswa.nis ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <Button as-child>
                                <Link :href="summaryDetailUrl(siswa.id)"
                                    >Detail</Link
                                >
                            </Button>
                        </td>
                    </tr>
                    <tr v-if="!summaryStudents.length">
                        <td
                            colspan="4"
                            class="py-8 text-center text-muted-foreground"
                        >
                            Belum ada data siswa pada tema dan sub tema ini
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
                    <h2 class="text-lg font-semibold">
                        {{ selectedNilai ? 'Edit Nilai' : 'Berikan Nilai' }}
                    </h2>
                    <p class="text-sm text-muted-foreground">
                        {{ selectedAbsen.siswa.nama }}
                    </p>
                </div>
                <form class="mt-6 space-y-4" @submit.prevent="submitNilai">
                    <div class="space-y-2">
                        <label
                            for="komponen_penilaian_id"
                            class="text-sm font-medium"
                            >Komponen Penilaian</label
                        >
                        <select
                            id="komponen_penilaian_id"
                            v-model="nilaiForm.komponen_penilaian_id"
                            required
                            :disabled="!selectedSubTema"
                            class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground"
                        >
                            <option value="">Pilih komponen</option>
                            <option
                                v-for="komponen in komponenPenilaians"
                                :key="komponen.id"
                                :value="String(komponen.id)"
                            >
                                {{ komponen.nama_komponen }}
                            </option>
                        </select>
                        <InputError
                            :message="nilaiForm.errors.komponen_penilaian_id"
                        />
                        <p
                            v-if="selectedNilai"
                            class="text-xs text-muted-foreground"
                        >
                            Nilai sebelumnya dimuat dan dapat diperbarui.
                        </p>
                    </div>
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
                    <div class="space-y-2">
                        <label for="foto_kegiatan" class="text-sm font-medium">
                            Foto Kegiatan
                            <span class="text-muted-foreground"
                                >(opsional)</span
                            >
                        </label>
                        <Input
                            id="foto_kegiatan"
                            ref="photoInput"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            multiple
                            @change="setPhotos"
                        />
                        <p class="text-xs text-muted-foreground">
                            Maksimal 10 foto, format JPG, JPEG, PNG, atau WEBP.
                            Maksimal 5 MB per foto.
                        </p>
                        <InputError :message="nilaiForm.errors.foto_kegiatan" />
                        <InputError
                            :message="nilaiForm.errors['foto_kegiatan.0']"
                        />
                        <div
                            v-if="photoPreviews.length"
                            class="grid grid-cols-3 gap-2 sm:grid-cols-4"
                        >
                            <img
                                v-for="(preview, index) in photoPreviews"
                                :key="preview"
                                :src="preview"
                                :alt="`Pratinjau foto kegiatan ${index + 1}`"
                                class="aspect-square w-full rounded-md border border-border object-cover"
                            />
                        </div>
                    </div>
                    <div
                        v-if="selectedNilai?.foto_kegiatans.length"
                        class="space-y-2"
                    >
                        <p class="text-sm font-medium">
                            Foto Kegiatan Tersimpan
                        </p>
                        <div class="grid grid-cols-3 gap-2 sm:grid-cols-4">
                            <a
                                v-for="foto in selectedNilai.foto_kegiatans"
                                :key="foto.id"
                                :href="foto.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="overflow-hidden rounded-md border border-border"
                            >
                                <img
                                    :src="foto.url"
                                    alt="Foto kegiatan"
                                    class="aspect-square w-full object-cover"
                                />
                            </a>
                        </div>
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
                            @click="closeModal"
                            >Batal</Button
                        >
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
