<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

import RaporController from '@/actions/App/Http/Controllers/RaporController';
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';

interface Kelas {
    id: number;
    nama_kelas: string;
    thn_ajaran: string;
}

interface SelectedKelas extends Kelas {
    siswa_count: number;
}

interface Guru {
    id: number;
    nama: string;
    nip: string;
}

interface Tema {
    id: number;
    nama_tema: string;
}

interface Siswa {
    id: number;
    user_id: number;
    nama: string;
    nis: string | null;
}

interface Rapor {
    id: number;
    siswa_id: number;
    guru_id: number;
    tema_id: number;
    thn_ajaran: string;
    keterangan: string | null;
    status: 'draft' | 'diajukan' | 'disetujui' | 'ditolak';
    catatan_validasi: string | null;
    guru: Guru;
}

interface RaporForm {
    kelas_id: string;
    siswa_id: string;
    tema_id: string;
    guru_id: string;
    thn_ajaran: string;
    keterangan: string;
}

const page = usePage();
const kelas = computed(() => page.props.kelas as Kelas[]);
const selectedKelasData = computed(
    () => page.props.selectedKelas as SelectedKelas | null,
);
const currentGuru = computed(() => page.props.currentGuru as Guru | null);
const temas = computed(() => page.props.temas as Tema[]);
const siswas = computed(() => page.props.siswas as Siswa[]);
const rapors = computed(() => page.props.rapors as Rapor[]);
const canManageRapor = computed(() => page.props.canManageRapor as boolean);
const filters = computed(
    () => page.props.filters as { kelas_id: string | null },
);
const selectedKelas = ref(filters.value.kelas_id ?? '');
const isLoading = ref(false);
const selectedSiswa = ref<Siswa | null>(null);
const selectedTema = ref<Tema | null>(null);
const selectedRapor = ref<Rapor | null>(null);
const modalMode = ref<'write' | 'view' | 'edit' | null>(null);

watch(filters, (value) => {
    selectedKelas.value = value.kelas_id ?? '';
});

const raporForm = useForm<RaporForm>({
    kelas_id: '',
    siswa_id: '',
    tema_id: '',
    guru_id: '',
    thn_ajaran: '',
    keterangan: '',
});
const submitForm = useForm({
    kelas_id: '',
    thn_ajaran: '',
});

const loadKelas = () => {
    isLoading.value = true;
    router.get(
        RaporController.index().url,
        { kelas_id: selectedKelas.value },
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

const raporFor = (siswa: Siswa, tema: Tema) =>
    rapors.value.find(
        (rapor) => rapor.siswa_id === siswa.id && rapor.tema_id === tema.id,
    ) ?? null;

const isEditable = (rapor: Rapor) =>
    canManageRapor.value && ['draft', 'ditolak'].includes(rapor.status);

const openWrite = (siswa: Siswa, tema: Tema) => {
    selectedSiswa.value = siswa;
    selectedTema.value = tema;
    selectedRapor.value = null;
    modalMode.value = 'write';
    raporForm.keterangan = '';
};

const openView = (siswa: Siswa, tema: Tema, rapor: Rapor) => {
    selectedSiswa.value = siswa;
    selectedTema.value = tema;
    selectedRapor.value = rapor;
    modalMode.value = 'view';
    raporForm.keterangan = rapor.keterangan ?? '';
};

const closeModal = () => {
    modalMode.value = null;
    selectedSiswa.value = null;
    selectedTema.value = null;
    selectedRapor.value = null;
    raporForm.clearErrors();
};

const submitRapor = () => {
    if (
        !selectedSiswa.value ||
        !selectedTema.value ||
        !selectedKelasData.value ||
        !currentGuru.value
    ) {
        return;
    }

    raporForm.kelas_id = String(selectedKelasData.value.id);
    raporForm.siswa_id = String(selectedSiswa.value.id);
    raporForm.tema_id = String(selectedTema.value.id);
    raporForm.guru_id = String(currentGuru.value.id);
    raporForm.thn_ajaran = String(selectedKelasData.value.thn_ajaran);
    const options = { preserveScroll: true, onSuccess: closeModal };

    if (selectedRapor.value) {
        raporForm.put(
            RaporController.update(selectedRapor.value.id).url,
            options,
        );

        return;
    }

    raporForm.post(RaporController.store().url, options);
};

const submitRaporForValidation = () => {
    if (!selectedKelasData.value) {
        return;
    }

    submitForm.kelas_id = String(selectedKelasData.value.id);
    submitForm.thn_ajaran = String(selectedKelasData.value.thn_ajaran);
    submitForm.post(RaporController.submit().url, { preserveScroll: true });
};

const statusLabel = (status: Rapor['status']) =>
    ({
        draft: 'Draft',
        diajukan: 'Diajukan',
        disetujui: 'Disetujui',
        ditolak: 'Ditolak',
    })[status];

const statusClass = (status: Rapor['status']) =>
    ({
        draft: 'bg-muted text-muted-foreground',
        diajukan: 'bg-primary/15 text-foreground',
        disetujui: 'bg-primary text-primary-foreground',
        ditolak: 'bg-destructive/15 text-destructive',
    })[status];
</script>

<template>
    <Head title="Penilaian Rapor" />
    <div class="space-y-6 bg-background p-4 text-foreground">
        <div>
            <h1 class="text-2xl font-bold">Penilaian Rapor</h1>
            <p class="text-sm text-muted-foreground">
                Kelola catatan perkembangan siswa berdasarkan kelas dan tema.
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
                        @change="loadKelas"
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
                <div v-if="currentGuru" class="space-y-2">
                    <label class="text-sm font-medium">Guru Penilai</label>
                    <div
                        class="h-9 rounded-md border border-border bg-muted px-3 py-2 text-sm text-foreground"
                    >
                        {{ currentGuru.nama }} - {{ currentGuru.nip }}
                    </div>
                </div>
            </div>
            <p v-if="isLoading" class="mt-4 text-sm text-muted-foreground">
                Memuat data...
            </p>
        </div>

        <div
            v-if="selectedKelasData"
            class="flex flex-wrap items-end justify-between gap-4 rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
        >
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <p class="text-sm text-muted-foreground">Nama Kelas</p>
                    <p class="font-medium">
                        {{ selectedKelasData.nama_kelas }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Tahun Ajaran</p>
                    <p class="font-medium">
                        {{ selectedKelasData.thn_ajaran }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Jumlah Siswa</p>
                    <p class="font-medium">
                        {{ selectedKelasData.siswa_count }}
                    </p>
                </div>
            </div>
            <Button
                v-if="canManageRapor"
                :disabled="submitForm.processing"
                @click="submitRaporForValidation"
            >
                {{ submitForm.processing ? 'Mengajukan...' : 'Ajukan Rapor' }}
            </Button>
        </div>

        <div
            v-if="selectedKelasData"
            class="overflow-x-auto rounded-xl border border-border bg-card text-card-foreground"
        >
            <table class="w-full min-w-max">
                <thead class="bg-muted/50">
                    <tr>
                        <th
                            class="sticky left-0 bg-muted/50 px-4 py-3 text-left text-sm font-medium"
                        >
                            Nama Siswa
                        </th>
                        <th
                            v-for="tema in temas"
                            :key="tema.id"
                            class="min-w-48 px-4 py-3 text-left text-sm font-medium"
                        >
                            {{ tema.nama_tema }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="siswa in siswas"
                        :key="siswa.id"
                        class="border-t border-border hover:bg-muted/50"
                    >
                        <td class="sticky left-0 bg-card px-4 py-3">
                            <p class="font-medium">{{ siswa.nama }}</p>
                            <p class="text-sm text-muted-foreground">
                                {{ siswa.nis ?? '-' }}
                            </p>
                        </td>
                        <td
                            v-for="tema in temas"
                            :key="tema.id"
                            class="space-y-2 px-4 py-3"
                        >
                            <Button
                                v-if="!raporFor(siswa, tema) && canManageRapor"
                                size="sm"
                                variant="outline"
                                @click="openWrite(siswa, tema)"
                            >
                                Tulis Catatan
                            </Button>
                            <Button
                                v-else-if="raporFor(siswa, tema)"
                                size="sm"
                                variant="secondary"
                                @click="
                                    openView(
                                        siswa,
                                        tema,
                                        raporFor(siswa, tema)!,
                                    )
                                "
                            >
                                Lihat Catatan
                            </Button>
                            <span v-else class="text-sm text-muted-foreground"
                                >-</span
                            >
                            <span
                                v-if="raporFor(siswa, tema)"
                                class="block w-fit rounded-full px-2 py-1 text-xs font-medium"
                                :class="
                                    statusClass(raporFor(siswa, tema)!.status)
                                "
                            >
                                {{ statusLabel(raporFor(siswa, tema)!.status) }}
                            </span>
                        </td>
                    </tr>
                    <tr v-if="!siswas.length">
                        <td
                            :colspan="temas.length + 1"
                            class="py-8 text-center text-muted-foreground"
                        >
                            Tidak ada siswa pada kelas ini
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-if="modalMode && selectedSiswa && selectedTema"
            class="fixed inset-0 z-50 flex items-center justify-center bg-muted/80 p-4"
        >
            <div
                class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
            >
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold">
                        {{
                            modalMode === 'view'
                                ? 'Detail Catatan Rapor'
                                : modalMode === 'edit'
                                  ? 'Edit Catatan Rapor'
                                  : 'Tulis Catatan Rapor'
                        }}
                    </h2>
                    <p class="text-sm text-muted-foreground">
                        {{ selectedSiswa.nama }} · {{ selectedTema.nama_tema }}
                    </p>
                </div>
                <div v-if="modalMode === 'view'" class="mt-6 space-y-4">
                    <dl class="grid gap-4 md:grid-cols-2">
                        <div>
                            <dt class="text-sm text-muted-foreground">
                                Guru Penilai
                            </dt>
                            <dd class="font-medium">
                                {{ selectedRapor?.guru.nama }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-muted-foreground">
                                Status
                            </dt>
                            <dd class="font-medium">
                                {{
                                    selectedRapor &&
                                    statusLabel(selectedRapor.status)
                                }}
                            </dd>
                        </div>
                    </dl>
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Keterangan Perkembangan Siswa
                        </p>
                        <p class="mt-2 whitespace-pre-wrap">
                            {{ selectedRapor?.keterangan || '-' }}
                        </p>
                    </div>
                    <div v-if="selectedRapor?.catatan_validasi">
                        <p class="text-sm text-muted-foreground">
                            Catatan Validasi Kepsek
                        </p>
                        <p class="mt-2 whitespace-pre-wrap">
                            {{ selectedRapor.catatan_validasi }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <Button
                            v-if="selectedRapor && isEditable(selectedRapor)"
                            @click="modalMode = 'edit'"
                        >
                            Edit Catatan
                        </Button>
                        <Button variant="outline" @click="closeModal"
                            >Tutup</Button
                        >
                    </div>
                </div>
                <form
                    v-else
                    class="mt-6 space-y-4"
                    @submit.prevent="submitRapor"
                >
                    <div class="space-y-2">
                        <label for="keterangan" class="text-sm font-medium">
                            Keterangan Perkembangan Siswa
                        </label>
                        <textarea
                            id="keterangan"
                            v-model="raporForm.keterangan"
                            class="min-h-64 w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground"
                            placeholder="Tulis deskripsi perkembangan siswa"
                        />
                        <InputError :message="raporForm.errors.keterangan" />
                    </div>
                    <div class="flex gap-2">
                        <Button type="submit" :disabled="raporForm.processing">
                            {{
                                raporForm.processing
                                    ? 'Menyimpan...'
                                    : 'Simpan Catatan'
                            }}
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            :disabled="raporForm.processing"
                            @click="closeModal"
                        >
                            Batal
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
