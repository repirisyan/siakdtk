<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import TagihanSayaController from '@/actions/App/Http/Controllers/TagihanSayaController';
import Button from '@/components/ui/button/Button.vue';

interface Spp {
    id: number;
    jenis_pembayaran: string;
    nominal: string;
    total_dibayar: string | null;
    tanggal_tagihan: string;
    jatuh_tempo: string;
    siswa: { nama: string; nis: string | null };
}
const page = usePage();
const spps = computed(
    () =>
        page.props.spps as {
            data: Spp[];
            links: { url: string | null; label: string; active: boolean }[];
        },
);
const siswas = computed(
    () =>
        page.props.siswas as { id: number; nama: string; nis: string | null }[],
);
const filters = computed(
    () => page.props.filters as { siswa_id: string | null },
);
const siswaId = computed({
    get: () => filters.value.siswa_id ?? '',
    set: (value: string) =>
        router.get(
            TagihanSayaController.index().url,
            { siswa_id: value },
            { preserveState: true, replace: true },
        ),
});
const money = (value: string | number) =>
    `Rp${Math.round(Number(value))
        .toString()
        .replace(/\B(?=(\d{3})+(?!\d))/g, '.')}`;
</script>
<template>
    <Head title="Tagihan Saya" />
    <div class="space-y-6 bg-background p-4 text-foreground">
        <div>
            <h1 class="text-2xl font-bold">Tagihan Saya</h1>
            <p class="text-sm text-muted-foreground">
                Tagihan dan riwayat pembayaran anak Anda.
            </p>
        </div>
        <div
            class="overflow-x-auto rounded-xl border border-border bg-card text-card-foreground"
        >
            <div class="border-b border-border p-4">
                <label for="siswa_id" class="mb-2 block text-sm font-medium"
                    >Filter Anak</label
                >
                <select
                    id="siswa_id"
                    v-model="siswaId"
                    class="h-9 w-full max-w-sm rounded-md border border-border bg-background px-3 text-sm text-foreground"
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
            <table class="w-full min-w-[900px]">
                <thead class="bg-muted/50">
                    <tr>
                        <th class="px-4 py-3 text-left">Jenis</th>
                        <th class="px-4 py-3 text-left">Siswa</th>
                        <th class="px-4 py-3 text-left">Nominal</th>
                        <th class="px-4 py-3 text-left">Dibayar</th>
                        <th class="px-4 py-3 text-left">Sisa</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Jatuh Tempo</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="spp in spps.data"
                        :key="spp.id"
                        class="border-t border-border"
                    >
                        <td class="px-4 py-3">{{ spp.jenis_pembayaran }}</td>
                        <td class="px-4 py-3">{{ spp.siswa.nama }}</td>
                        <td class="px-4 py-3">{{ money(spp.nominal) }}</td>
                        <td class="px-4 py-3">
                            {{ money(spp.total_dibayar ?? 0) }}
                        </td>
                        <td class="px-4 py-3">
                            {{
                                money(
                                    Number(spp.nominal) -
                                        Number(spp.total_dibayar ?? 0),
                                )
                            }}
                        </td>
                        <td class="px-4 py-3">{{ spp.tanggal_tagihan }}</td>
                        <td class="px-4 py-3">{{ spp.jatuh_tempo }}</td>
                        <td class="px-4 py-3 text-right">
                            <Button
                                variant="outline"
                                @click="
                                    router.visit(
                                        TagihanSayaController.show(spp.id).url,
                                    )
                                "
                                >Detail</Button
                            >
                        </td>
                    </tr>
                    <tr v-if="!spps.data.length">
                        <td
                            colspan="8"
                            class="py-8 text-center text-muted-foreground"
                        >
                            Tidak ada tagihan.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
