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

interface Siswa {
    id: number;
    user_id: number;
    nama: string;
    nis: string | null;
}

interface Rapor {
    id: number;
    siswa_id: number;
    tema_id: number;
    keterangan: string | null;
    status: 'draft' | 'diajukan' | 'disetujui' | 'ditolak';
}

type StatusRapor = Rapor['status'];

const page = usePage();
const kelas = computed(() => page.props.kelas as Kelas[]);
const tahunAjaranOptions = computed(
    () => page.props.tahunAjaranOptions as string[],
);
const selectedKelasData = computed(
    () => page.props.selectedKelas as Kelas | null,
);
const siswas = computed(() => page.props.siswas as Siswa[]);
const rapors = computed(() => page.props.rapors as Rapor[]);
const temaCount = computed(() => page.props.temaCount as number);
const filters = computed(
    () =>
        page.props.filters as {
            kelas_id: string | null;
            thn_ajaran: string | null;
        },
);
const selectedKelas = ref(filters.value.kelas_id ?? '');
const selectedTahunAjaran = ref(filters.value.thn_ajaran ?? '');
const isLoading = ref(false);
const approveTarget = ref<Siswa | null>(null);
const rejectTarget = ref<Siswa | null>(null);
const isApproveAllModalOpen = ref(false);

watch(filters, (value) => {
    selectedKelas.value = value.kelas_id ?? '';
    selectedTahunAjaran.value = value.thn_ajaran ?? '';
});

const approvalForm = useForm({
    kelas_id: '',
    thn_ajaran: '',
});
const rejectForm = useForm({
    kelas_id: '',
    thn_ajaran: '',
    catatan_validasi: '',
});

const loadData = () => {
    isLoading.value = true;
    router.get(
        RaporController.validationIndex().url,
        {
            kelas_id: selectedKelas.value,
            thn_ajaran: selectedTahunAjaran.value,
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

const raporsFor = (siswa: Siswa) =>
    rapors.value.filter((rapor) => rapor.siswa_id === siswa.id);

const assessedTemaCount = (siswa: Siswa) =>
    new Set(
        raporsFor(siswa)
            .filter((rapor) => Boolean(rapor.keterangan))
            .map((rapor) => rapor.tema_id),
    ).size;

const statusFor = (siswa: Siswa): StatusRapor => {
    const studentRapors = raporsFor(siswa);

    if (!studentRapors.length) {
        return 'draft';
    }

    if (studentRapors.every((rapor) => rapor.status === 'disetujui')) {
        return 'disetujui';
    }

    if (studentRapors.some((rapor) => rapor.status === 'ditolak')) {
        return 'ditolak';
    }

    if (studentRapors.every((rapor) => rapor.status === 'diajukan')) {
        return 'diajukan';
    }

    return 'draft';
};

const statusLabel = (status: StatusRapor) =>
    ({
        draft: 'Draft',
        diajukan: 'Diajukan',
        disetujui: 'Disetujui',
        ditolak: 'Ditolak',
    })[status];

const statusClass = (status: StatusRapor) =>
    ({
        draft: 'bg-muted text-muted-foreground',
        diajukan: 'bg-primary/15 text-foreground',
        disetujui: 'bg-primary text-primary-foreground',
        ditolak: 'bg-destructive/15 text-destructive',
    })[status];

const setApprovalContext = (form: typeof approvalForm | typeof rejectForm) => {
    if (!selectedKelasData.value) {
        return false;
    }

    form.kelas_id = String(selectedKelasData.value.id);
    form.thn_ajaran = String(selectedKelasData.value.thn_ajaran);

    return true;
};

const approve = () => {
    if (!approveTarget.value || !setApprovalContext(approvalForm)) {
        return;
    }

    approvalForm.post(RaporController.approve(approveTarget.value.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            approveTarget.value = null;
        },
    });
};

const approveAll = () => {
    if (!setApprovalContext(approvalForm)) {
        return;
    }

    approvalForm.post(RaporController.approveAll().url, {
        preserveScroll: true,
        onSuccess: () => {
            isApproveAllModalOpen.value = false;
        },
    });
};

const reject = () => {
    if (!rejectTarget.value || !setApprovalContext(rejectForm)) {
        return;
    }

    rejectForm.post(RaporController.reject(rejectTarget.value.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            rejectTarget.value = null;
            rejectForm.reset('catatan_validasi');
        },
    });
};

const closeRejectModal = () => {
    rejectTarget.value = null;
    rejectForm.reset('catatan_validasi');
    rejectForm.clearErrors();
};
</script>

<template>
    <Head title="Validasi Rapor" />
    <div class="space-y-6 bg-background p-4 text-foreground">
        <div>
            <h1 class="text-2xl font-bold">Validasi Rapor</h1>
            <p class="text-sm text-muted-foreground">
                Validasi catatan rapor siswa yang telah diajukan guru.
            </p>
        </div>

        <div
            class="rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
        >
            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-2">
                    <label for="thn_ajaran" class="text-sm font-medium">
                        Tahun Ajaran
                    </label>
                    <select
                        id="thn_ajaran"
                        v-model="selectedTahunAjaran"
                        :disabled="isLoading"
                        class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground disabled:cursor-not-allowed disabled:opacity-50"
                        @change="
                            selectedKelas = '';
                            loadData();
                        "
                    >
                        <option value="">Semua tahun ajaran</option>
                        <option
                            v-for="tahun in tahunAjaranOptions"
                            :key="tahun"
                            :value="String(tahun)"
                        >
                            {{ tahun }}
                        </option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label for="kelas_id" class="text-sm font-medium"
                        >Kelas</label
                    >
                    <select
                        id="kelas_id"
                        v-model="selectedKelas"
                        :disabled="isLoading"
                        class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground disabled:cursor-not-allowed disabled:opacity-50"
                        @change="loadData"
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
            </div>
            <p v-if="isLoading" class="mt-4 text-sm text-muted-foreground">
                Memuat data...
            </p>
        </div>

        <div
            v-if="selectedKelasData"
            class="space-y-4 rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
        >
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="font-semibold">
                        {{ selectedKelasData.nama_kelas }}
                    </h2>
                    <p class="text-sm text-muted-foreground">
                        Tahun ajaran {{ selectedKelasData.thn_ajaran }}
                    </p>
                </div>
                <Button @click="isApproveAllModalOpen = true"
                    >Setujui Semua</Button
                >
            </div>

            <div class="overflow-x-auto rounded-lg border border-border">
                <table class="w-full min-w-[760px]">
                    <thead class="bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-medium">
                                Nama Siswa
                            </th>
                            <th class="px-4 py-3 text-left text-sm font-medium">
                                Jumlah Tema
                            </th>
                            <th class="px-4 py-3 text-left text-sm font-medium">
                                Tema Sudah Dinilai
                            </th>
                            <th class="px-4 py-3 text-left text-sm font-medium">
                                Status Rapor
                            </th>
                            <th
                                class="px-4 py-3 text-right text-sm font-medium"
                            >
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="siswa in siswas"
                            :key="siswa.id"
                            class="border-t border-border"
                        >
                            <td class="px-4 py-3">
                                <p class="font-medium">{{ siswa.nama }}</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ siswa.nis ?? '-' }}
                                </p>
                            </td>
                            <td class="px-4 py-3">{{ temaCount }}</td>
                            <td class="px-4 py-3">
                                {{ assessedTemaCount(siswa) }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="rounded-full px-2 py-1 text-xs font-medium"
                                    :class="statusClass(statusFor(siswa))"
                                >
                                    {{ statusLabel(statusFor(siswa)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div
                                    v-if="statusFor(siswa) === 'diajukan'"
                                    class="flex justify-end gap-2"
                                >
                                    <Button
                                        size="sm"
                                        @click="approveTarget = siswa"
                                        >Validasi</Button
                                    >
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        @click="rejectTarget = siswa"
                                    >
                                        Tolak
                                    </Button>
                                </div>
                                <span
                                    v-else
                                    class="text-sm text-muted-foreground"
                                    >-</span
                                >
                            </td>
                        </tr>
                        <tr v-if="!siswas.length">
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
        </div>

        <div
            v-if="approveTarget"
            class="fixed inset-0 z-50 flex items-center justify-center bg-muted/80 p-4"
        >
            <div
                class="w-full max-w-lg rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
            >
                <h2 class="text-lg font-semibold">Setujui Rapor</h2>
                <p class="mt-2 text-sm text-muted-foreground">
                    Setujui seluruh catatan rapor {{ approveTarget.nama }}?
                </p>
                <div class="mt-6 flex justify-end gap-2">
                    <Button
                        variant="outline"
                        :disabled="approvalForm.processing"
                        @click="approveTarget = null"
                    >
                        Batal
                    </Button>
                    <Button
                        :disabled="approvalForm.processing"
                        @click="approve"
                    >
                        {{
                            approvalForm.processing
                                ? 'Memvalidasi...'
                                : 'Setujui'
                        }}
                    </Button>
                </div>
            </div>
        </div>

        <div
            v-if="isApproveAllModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-muted/80 p-4"
        >
            <div
                class="w-full max-w-lg rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
            >
                <h2 class="text-lg font-semibold">Setujui Semua Rapor</h2>
                <p class="mt-2 text-sm text-muted-foreground">
                    Setujui seluruh rapor berstatus diajukan pada kelas ini?
                </p>
                <div class="mt-6 flex justify-end gap-2">
                    <Button
                        variant="outline"
                        :disabled="approvalForm.processing"
                        @click="isApproveAllModalOpen = false"
                    >
                        Batal
                    </Button>
                    <Button
                        :disabled="approvalForm.processing"
                        @click="approveAll"
                    >
                        {{
                            approvalForm.processing
                                ? 'Memvalidasi...'
                                : 'Setujui Semua'
                        }}
                    </Button>
                </div>
            </div>
        </div>

        <div
            v-if="rejectTarget"
            class="fixed inset-0 z-50 flex items-center justify-center bg-muted/80 p-4"
        >
            <form
                class="w-full max-w-2xl rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
                @submit.prevent="reject"
            >
                <h2 class="text-lg font-semibold">Tolak Rapor</h2>
                <p class="mt-2 text-sm text-muted-foreground">
                    Berikan alasan penolakan rapor {{ rejectTarget.nama }}.
                </p>
                <div class="mt-6 space-y-2">
                    <label for="catatan_validasi" class="text-sm font-medium">
                        Catatan Validasi
                    </label>
                    <textarea
                        id="catatan_validasi"
                        v-model="rejectForm.catatan_validasi"
                        class="min-h-40 w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground"
                        placeholder="Tulis alasan penolakan rapor"
                    />
                    <InputError :message="rejectForm.errors.catatan_validasi" />
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        :disabled="rejectForm.processing"
                        @click="closeRejectModal"
                    >
                        Batal
                    </Button>
                    <Button type="submit" :disabled="rejectForm.processing">
                        {{
                            rejectForm.processing
                                ? 'Menyimpan...'
                                : 'Tolak Rapor'
                        }}
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>
