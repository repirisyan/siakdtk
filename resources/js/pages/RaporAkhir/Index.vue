<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import RaporAkhirController from '@/actions/App/Http/Controllers/RaporAkhirController';
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';

interface Kelas {
    id: number;
    nama_kelas: string;
    thn_ajaran: string;
}
interface Siswa {
    id: number;
    nama: string;
    nis: string | null;
}
interface Tema {
    id: number;
    nama_tema: string;
}
interface Detail {
    tema_id: number;
    keterangan: string;
    guru: { nama: string };
}
interface RaporAkhir {
    id: number;
    siswa_id: number;
    status: 'draft' | 'menunggu_validasi' | 'disetujui' | 'ditolak';
    catatan_penolakan: string | null;
    details: Detail[];
}
interface Reference {
    id: number;
    nilai: string;
    keterangan: string | null;
    komponen_penilaian: { nama_komponen: string } | null;
    absen: {
        siswa: { id: number };
        jadwal: { tema_id: number; sub_tema: { nama_sub_tema: string } | null };
    };
}

const page = usePage();
const kelas = computed(() => page.props.kelas as Kelas[]);
const selectedKelasData = computed(
    () => page.props.selectedKelas as Kelas | null,
);
const siswas = computed(() => page.props.siswas as Siswa[]);
const temas = computed(() => page.props.temas as Tema[]);
const raporAkhirs = computed(() => page.props.raporAkhirs as RaporAkhir[]);
const references = computed(
    () => page.props.assessmentReferences as Reference[],
);
const canManage = computed(() => page.props.canManage as boolean);
const canApprove = computed(() => page.props.canApprove as boolean);
const filters = computed(
    () =>
        page.props.filters as {
            kelasId: number | null;
            tahunAjaran: string | null;
            siswaId: number | null;
        },
);
const selectedKelas = ref(
    filters.value.kelasId ? String(filters.value.kelasId) : '',
);
const selectedSiswa = ref(
    filters.value.siswaId ? String(filters.value.siswaId) : '',
);
const modal = ref<{
    siswa: Siswa;
    tema: Tema;
    rapor: RaporAkhir | null;
} | null>(null);
const form = useForm({
    kelas_id: '',
    siswa_id: '',
    tema_id: '',
    thn_ajaran: '',
    keterangan: '',
});

watch(filters, (value) => {
    selectedKelas.value = value.kelasId ? String(value.kelasId) : '';
    selectedSiswa.value = value.siswaId ? String(value.siswaId) : '';
});
const load = () =>
    router.get(
        RaporAkhirController.index().url,
        { kelas_id: selectedKelas.value, siswa_id: selectedSiswa.value },
        { preserveState: true, preserveScroll: true, replace: true },
    );
const raporFor = (siswa: Siswa) =>
    raporAkhirs.value.find((item) => item.siswa_id === siswa.id) ?? null;
const detailFor = (siswa: Siswa, tema: Tema) =>
    raporFor(siswa)?.details.find((detail) => detail.tema_id === tema.id) ??
    null;
const open = (siswa: Siswa, tema: Tema) => {
    const rapor = raporFor(siswa);
    modal.value = { siswa, tema, rapor };
    form.keterangan = detailFor(siswa, tema)?.keterangan ?? '';
    form.clearErrors();
};
const close = () => {
    modal.value = null;
    form.reset();
    form.clearErrors();
};
const submit = () => {
    if (!modal.value || !selectedKelasData.value) {
return;
}

    form.kelas_id = String(selectedKelasData.value.id);
    form.siswa_id = String(modal.value.siswa.id);
    form.tema_id = String(modal.value.tema.id);
    form.thn_ajaran = String(selectedKelasData.value.thn_ajaran);
    form.post(RaporAkhirController.store().url, {
        preserveScroll: true,
        onSuccess: close,
    });
};
const submitValidation = (rapor: RaporAkhir) =>
    router.post(
        RaporAkhirController.submit(rapor.id).url,
        {},
        { preserveScroll: true },
    );
const approve = (rapor: RaporAkhir) =>
    router.post(
        RaporAkhirController.approve(rapor.id).url,
        {},
        { preserveScroll: true },
    );
const reject = (rapor: RaporAkhir) => {
    const catatan = window.prompt('Catatan penolakan:');

    if (catatan) {
router.post(
            RaporAkhirController.reject(rapor.id).url,
            { catatan_penolakan: catatan },
            { preserveScroll: true },
        );
}
};
const approveAll = () => {
    if (
        !selectedKelasData.value ||
        !window.confirm('Setujui semua Rapor Akhir yang menunggu validasi?')
    ) {
return;
}

    router.post(
        RaporAkhirController.approveAll().url,
        {
            kelas_id: selectedKelasData.value.id,
            thn_ajaran: selectedKelasData.value.thn_ajaran,
        },
        { preserveScroll: true },
    );
};
const openPrint = (url: string) => {
    const printWindow = window.open(url, '_blank');

    if (!printWindow) {
        window.location.assign(url);
    }
};
const printClass = () => {
    if (selectedKelasData.value) {
        openPrint(
            RaporAkhirController.printClass(selectedKelasData.value.id).url,
        );
    }
};
const printStudent = (rapor: RaporAkhir) => {
    openPrint(RaporAkhirController.printStudent(rapor.id).url);
};
const referenceFor = () =>
    !modal.value
        ? []
        : references.value.filter(
              (item) =>
                  item.absen.siswa.id === modal.value?.siswa.id &&
                  item.absen.jadwal.tema_id === modal.value?.tema.id,
          );
const generateDescription = () => {
    if (!modal.value) {
return;
}

    const referencesBySubTema = referenceFor().reduce<Map<string, Reference[]>>(
        (groups, reference) => {
            const subTema =
                reference.absen.jadwal.sub_tema?.nama_sub_tema ?? 'Sub Tema';
            groups.set(subTema, [...(groups.get(subTema) ?? []), reference]);

            return groups;
        },
        new Map(),
    );

    form.keterangan = Array.from(referencesBySubTema.entries())
        .map(([subTema, subTemaReferences]) => {
            const components = subTemaReferences
                .map((reference) => {
                    const komponen =
                        reference.komponen_penilaian?.nama_komponen ??
                        'komponen penilaian';
                    const catatan = reference.keterangan
                        ? ` ${reference.keterangan}`
                        : '';

                    return `${komponen} memperoleh nilai ${reference.nilai}.${catatan}`;
                })
                .join(' ');

            return `${modal.value?.siswa.nama} menunjukkan perkembangan yang baik pada Sub Tema ${subTema}. ${components}`;
        })
        .join('\n\n');
};
</script>

<template>
    <Head title="Hasil Akhir Rapor" />
    <div class="space-y-6 bg-background p-4 text-foreground">
        <div>
            <h1 class="text-2xl font-bold">Hasil Akhir Rapor</h1>
            <p class="text-sm text-muted-foreground">
                Catatan resmi per tema dengan referensi penilaian Sub Tema.
            </p>
        </div>
        <div v-if="selectedKelasData" class="flex flex-wrap justify-end gap-2">
            <Button variant="outline" @click="printClass"
                >Cetak Rapor Kelas</Button
            >
            <Button v-if="canApprove" @click="approveAll">Setujui Semua</Button>
        </div>
        <div
            class="grid gap-4 rounded-xl border border-border bg-card p-6 text-card-foreground md:grid-cols-2"
        >
            <div class="space-y-2">
                <label class="text-sm font-medium">Kelas</label
                ><select
                    v-model="selectedKelas"
                    class="h-10 w-full rounded-md border border-border bg-background px-3"
                    @change="load"
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
                <label class="text-sm font-medium">Siswa</label
                ><select
                    v-model="selectedSiswa"
                    :disabled="!selectedKelas"
                    class="h-10 w-full rounded-md border border-border bg-background px-3"
                    @change="load"
                >
                    <option value="">Semua siswa</option>
                    <option
                        v-for="siswa in siswas"
                        :key="siswa.id"
                        :value="String(siswa.id)"
                    >
                        {{ siswa.nama }}
                    </option>
                </select>
            </div>
        </div>
        <div
            v-if="selectedKelasData"
            class="overflow-x-auto rounded-xl border border-border bg-card text-card-foreground"
        >
            <table class="w-full min-w-max">
                <thead class="bg-muted/60">
                    <tr>
                        <th class="px-4 py-3 text-left">Siswa</th>
                        <th
                            v-for="tema in temas"
                            :key="tema.id"
                            class="px-4 py-3 text-left"
                        >
                            {{ tema.nama_tema }}
                        </th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Aksi</th>
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
                        <td
                            v-for="tema in temas"
                            :key="tema.id"
                            class="px-4 py-3"
                        >
                            <Button
                                variant="outline"
                                size="sm"
                                @click="open(siswa, tema)"
                                >{{
                                    detailFor(siswa, tema)
                                        ? 'Lihat Penilaian'
                                        : 'Isi Penilaian'
                                }}</Button
                            >
                        </td>
                        <td class="px-4 py-3">
                            {{
                                raporFor(siswa)?.status?.replace('_', ' ') ??
                                'Belum diisi'
                            }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <Button
                                    v-if="
                                        canManage &&
                                        raporFor(siswa)?.status !==
                                            'menunggu_validasi' &&
                                        raporFor(siswa)?.status !== 'disetujui'
                                    "
                                    size="sm"
                                    @click="submitValidation(raporFor(siswa)!)"
                                    >Ajukan Validasi</Button
                                ><Button
                                    v-if="
                                        canApprove &&
                                        raporFor(siswa)?.status ===
                                            'menunggu_validasi'
                                    "
                                    size="sm"
                                    @click="approve(raporFor(siswa)!)"
                                    >Setujui</Button
                                ><Button
                                    v-if="
                                        canApprove &&
                                        raporFor(siswa)?.status ===
                                            'menunggu_validasi'
                                    "
                                    size="sm"
                                    variant="outline"
                                    @click="reject(raporFor(siswa)!)"
                                    >Tolak</Button
                                >
                                <Button
                                    v-if="
                                        raporFor(siswa)?.status === 'disetujui'
                                    "
                                    size="sm"
                                    variant="outline"
                                    @click="printStudent(raporFor(siswa)!)"
                                    >Cetak Rapor</Button
                                >
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div
            v-if="modal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-muted/80 p-4"
        >
            <div
                class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-xl border border-border bg-card p-6 text-card-foreground"
            >
                <div>
                    <h2 class="text-xl font-semibold">
                        Catatan Perkembangan Tema
                    </h2>
                    <p class="text-sm text-muted-foreground">
                        {{ modal.siswa.nama }} · {{ modal.tema.nama_tema }} ·
                        {{ selectedKelasData?.thn_ajaran }}
                    </p>
                </div>
                <div
                    class="mt-6 rounded-lg border border-border bg-muted/50 p-4"
                >
                    <h3 class="font-medium">Referensi Penilaian Sub Tema</h3>
                    <div v-if="referenceFor().length" class="mt-3 space-y-3">
                        <div
                            v-for="item in referenceFor()"
                            :key="item.id"
                            class="text-sm"
                        >
                            <p class="font-medium">
                                {{
                                    item.absen.jadwal.sub_tema?.nama_sub_tema ??
                                    'Sub Tema'
                                }}
                                · Nilai {{ item.nilai }}
                            </p>
                            <p class="text-muted-foreground">
                                Komponen:
                                {{
                                    item.komponen_penilaian?.nama_komponen ??
                                    '-'
                                }}
                            </p>
                            <p class="text-muted-foreground">
                                {{ item.keterangan ?? 'Tidak ada catatan.' }}
                            </p>
                        </div>
                    </div>
                    <p v-else class="mt-2 text-sm text-muted-foreground">
                        Belum ada penilaian Sub Tema sebagai referensi.
                    </p>
                </div>
                <form class="mt-5 space-y-3" @submit.prevent="submit">
                    <div
                        class="flex flex-wrap items-center justify-between gap-2"
                    >
                        <label class="text-sm font-medium"
                            >Catatan Perkembangan Tema</label
                        >
                        <Button
                            v-if="canManage"
                            type="button"
                            variant="outline"
                            size="sm"
                            :disabled="!referenceFor().length"
                            @click="generateDescription"
                            >Generate dari Penilaian</Button
                        >
                    </div>
                    <textarea
                        v-model="form.keterangan"
                        :readonly="!canManage"
                        required
                        class="min-h-48 w-full rounded-md border border-border bg-background p-3 text-foreground"
                    /><InputError :message="form.errors.keterangan" />
                    <div class="flex justify-end gap-2">
                        <Button type="button" variant="outline" @click="close"
                            >Tutup</Button
                        ><Button
                            v-if="canManage"
                            :disabled="form.processing"
                            type="submit"
                            >{{
                                form.processing
                                    ? 'Menyimpan...'
                                    : 'Simpan Catatan'
                            }}</Button
                        >
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
