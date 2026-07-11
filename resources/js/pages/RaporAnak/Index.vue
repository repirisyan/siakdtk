<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

import RaporAnakController from '@/actions/App/Http/Controllers/RaporAnakController';
import Button from '@/components/ui/button/Button.vue';

interface Siswa {
    id: number;
    nama: string;
    nis: string | null;
    kelas: { nama_kelas: string; thn_ajaran: string } | null;
}
interface Rapor {
    id: number;
    thn_ajaran: string;
    approved_at: string | null;
    details: {
        tema: { nama_tema: string };
        guru: { nama: string; nip: string | null };
    }[];
    siswa: { nama: string; nis: string | null };
}

const page = usePage();
const siswas = computed(() => page.props.siswas as Siswa[]);
const rapors = computed(() => page.props.rapors as Rapor[]);
const tahunAjaranOptions = computed(
    () => page.props.tahunAjaranOptions as string[],
);
const filters = computed(
    () =>
        page.props.filters as {
            thn_ajaran: string | null;
            siswa_id: string | null;
        },
);
const tahunAjaran = ref(filters.value.thn_ajaran ?? '');
const siswaId = ref(filters.value.siswa_id ?? '');
const isLoading = ref(false);

watch(filters, (value) => {
    tahunAjaran.value = value.thn_ajaran ?? '';
    siswaId.value = value.siswa_id ?? '';
});
const loadRapor = () => {
    isLoading.value = true;
    router.get(
        RaporAnakController.index().url,
        { thn_ajaran: tahunAjaran.value, siswa_id: siswaId.value },
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
</script>

<template>
    <Head title="Rapor Anak" />
    <div class="space-y-6 bg-background p-4 text-foreground">
        <div>
            <h1 class="text-2xl font-bold">Rapor Anak</h1>
            <p class="text-sm text-muted-foreground">
                Lihat catatan perkembangan seluruh anak yang telah disetujui.
            </p>
        </div>
        <div
            class="rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
        >
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2">
                    <label for="siswa_id" class="text-sm font-medium"
                        >Anak</label
                    ><select
                        id="siswa_id"
                        v-model="siswaId"
                        :disabled="isLoading"
                        class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground"
                        @change="loadRapor"
                    >
                        <option value="">Semua Anak</option>
                        <option
                            v-for="siswa in siswas"
                            :key="siswa.id"
                            :value="String(siswa.id)"
                        >
                            {{ siswa.nama }}
                        </option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label for="thn_ajaran" class="text-sm font-medium"
                        >Tahun Ajaran</label
                    ><select
                        id="thn_ajaran"
                        v-model="tahunAjaran"
                        :disabled="isLoading"
                        class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground"
                        @change="loadRapor"
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
            </div>
            <p v-if="isLoading" class="mt-4 text-sm text-muted-foreground">
                Memuat rapor...
            </p>
        </div>
        <div
            class="overflow-x-auto rounded-xl border border-border bg-card text-card-foreground"
        >
            <table class="w-full min-w-[860px]">
                <thead class="bg-muted/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-medium">
                            Nama Siswa
                        </th>
                        <th
                            class="px-4 py-3 text-left text-sm font-medium"
                        ></th>
                        <th class="px-4 py-3 text-left text-sm font-medium">
                            Tanggal Validasi
                        </th>
                        <th class="px-4 py-3 text-right text-sm font-medium">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="rapor in rapors"
                        :key="rapor.id"
                        class="border-t border-border"
                    >
                        <td class="px-4 py-3 font-medium">
                            {{ rapor.siswa.nama }}
                        </td>
                        <td class="px-4 py-3">
                            {{
                                rapor.approved_at
                                    ? new Date(
                                          rapor.approved_at,
                                      ).toLocaleString('id-ID')
                                    : '-'
                            }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <Button
                                variant="outline"
                                @click="
                                    router.visit(
                                        RaporAnakController.show(rapor.id).url,
                                    )
                                "
                                >Detail</Button
                            >
                        </td>
                    </tr>
                    <tr v-if="!rapors.length">
                        <td
                            colspan="3"
                            class="py-8 text-center text-muted-foreground"
                        >
                            Belum ada rapor yang disetujui.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
