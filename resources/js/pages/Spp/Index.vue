<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import type { ColumnDef } from '@tanstack/vue-table';
import { computed, ref } from 'vue';

import SppController from '@/actions/App/Http/Controllers/SppController';
import InputError from '@/components/InputError.vue';
import SortableTableHeader from '@/components/SortableTableHeader.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';

interface Spp {
    id: number;
    thn_ajaran: string;
    jenis_pembayaran: string;
    nominal: string;
    total_dibayar: string | null;
    last_notification_at: string | null;
    created_at: string;
    siswa: {
        nama: string;
        nis: string | null;
        kelas: { nama_kelas: string } | null;
    };
    payments_count: number;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

const page = usePage();
const canManageSpp = computed(() => page.props.canManageSpp as boolean);
const spps = computed(
    () =>
        page.props.spps as {
            data: Spp[];
            links: PaginationLink[];
            total: number;
        },
);
const filters = computed(
    () =>
        page.props.filters as {
            search: string | null;
            sort: string;
            direction: string;
            kelas_id: string | null;
            thn_ajaran: string | null;
            status: string | null;
            jenis_pembayaran: string | null;
        },
);
const kelasOptions = computed(
    () =>
        page.props.kelasOptions as {
            id: number;
            nama_kelas: string;
            thn_ajaran: string;
        }[],
);
const tahunAjaranOptions = computed(
    () => page.props.tahunAjaranOptions as string[],
);
const jenisPembayaranOptions = computed(
    () =>
        page.props.jenisPembayaranOptions as {
            id: number;
            nama_jenis: string;
        }[],
);
const summary = computed(
    () =>
        page.props.summary as {
            total_tagihan: number;
            total_dibayar: number;
            total_sisa_tagihan: number;
        },
);
const search = ref(filters.value.search ?? '');
const kelasId = ref(filters.value.kelas_id ?? '');
const thnAjaran = ref(filters.value.thn_ajaran ?? '');
const status = ref(filters.value.status ?? '');
const jenisPembayaran = ref(filters.value.jenis_pembayaran ?? '');
const isGenerateModalOpen = ref(false);
const selectedSppIds = ref<number[]>([]);
const generateForm = useForm({
    target: 'kelas',
    kelas_id: '',
    thn_ajaran: '',
    jenis_pembayaran_id: '',
    nominal: '',
    tanggal_tagihan: '',
    jatuh_tempo: '',
    keterangan: '',
});
const formatCurrency = (value: string | number) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(Number(value));
const paid = (spp: Spp) => Number(spp.total_dibayar ?? 0);
const isLunas = (spp: Spp) => paid(spp) >= Number(spp.nominal);
const allCurrentSelected = computed(
    () =>
        spps.value.data.length > 0 &&
        spps.value.data.every((spp) => selectedSppIds.value.includes(spp.id)),
);
const toggleAll = (event: Event) => {
    selectedSppIds.value = (event.target as HTMLInputElement).checked
        ? spps.value.data.map((spp) => spp.id)
        : [];
};

const visit = (url?: string | null) =>
    url && router.visit(url, { preserveState: true, preserveScroll: true });
const applySearch = () =>
    router.get(
        SppController.index().url,
        {
            search: search.value,
            sort: filters.value.sort,
            direction: filters.value.direction,
            kelas_id: kelasId.value,
            thn_ajaran: thnAjaran.value,
            status: status.value,
            jenis_pembayaran: jenisPembayaran.value,
        },
        { preserveState: true, replace: true },
    );
const changeSort = (column: string) => {
    const direction =
        filters.value.sort === column && filters.value.direction === 'asc'
            ? 'desc'
            : 'asc';
    router.get(
        SppController.index().url,
        {
            search: search.value,
            sort: column,
            direction,
            kelas_id: kelasId.value,
            thn_ajaran: thnAjaran.value,
            status: status.value,
            jenis_pembayaran: jenisPembayaran.value,
        },
        { preserveState: true, replace: true },
    );
};
const destroySpp = (spp: Spp) => {
    if (confirm(`Hapus tagihan SPP ${spp.siswa.nama}?`)) {
        router.delete(SppController.destroy(spp.id).url);
    }
};
const sendNotification = (spp: Spp) => {
    if (confirm(`Kirim notifikasi tagihan untuk ${spp.siswa.nama}?`)) {
        router.post(SppController.sendNotification(spp.id).url);
    }
};
const sendSelectedNotifications = () => {
    if (
        !selectedSppIds.value.length ||
        !confirm(
            `Anda akan mengirim notifikasi kepada ${selectedSppIds.value.length} Orang Tua. Lanjutkan?`,
        )
    ) {
        return;
    }

    router.post(
        SppController.sendNotifications().url,
        { spp_ids: selectedSppIds.value },
        {
            onSuccess: () => {
                selectedSppIds.value = [];
            },
        },
    );
};
const sendNotificationsByFilter = () => {
    if (
        !spps.value.total ||
        !confirm(
            `Anda akan mengirim notifikasi kepada ${spps.value.total} Orang Tua berdasarkan filter aktif. Lanjutkan?`,
        )
    ) {
        return;
    }

    router.post(SppController.sendNotificationsByFilter().url, {
        search: search.value,
        kelas_id: kelasId.value,
        thn_ajaran: thnAjaran.value,
        status: status.value,
        jenis_pembayaran: jenisPembayaran.value,
    });
};
const openGenerate = () => {
    generateForm.reset();
    generateForm.target = 'kelas';
    generateForm.jenis_pembayaran_id = '';
    generateForm.clearErrors();
    isGenerateModalOpen.value = true;
};
const submitGenerate = () =>
    generateForm.post(SppController.generate().url, {
        preserveScroll: true,
        onSuccess: () => {
            isGenerateModalOpen.value = false;
        },
    });
const columns: ColumnDef<Spp>[] = [
    { accessorKey: 'id', header: 'ID' },
    { accessorKey: 'thn_ajaran', header: 'Tahun Ajaran' },
    { accessorKey: 'nominal', header: 'Nominal' },
    { accessorKey: 'total_dibayar', header: 'Dibayar' },
    { accessorKey: 'created_at', header: 'Dibuat' },
];
const tableColumns = [
    { key: 'id', label: 'ID' },
    { key: 'siswa', label: 'Siswa' },
    { key: 'thn_ajaran', label: 'Tahun Ajaran' },
    { key: 'jenis_pembayaran', label: 'Jenis Pembayaran' },
    { key: 'nominal', label: 'Nominal' },
    { key: 'total_dibayar', label: 'Total Dibayar' },
    { key: 'status', label: 'Status' },
    { key: 'created_at', label: 'Dibuat' },
    { key: 'last_notification_at', label: 'Notifikasi Terakhir' },
];
const table = useVueTable({
    get data() {
        return spps.value.data;
    },
    columns,
    getCoreRowModel: getCoreRowModel(),
});
</script>

<template>
    <Head title="Kelola Pembayaran" />
    <div class="space-y-4 bg-background p-4 text-foreground">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Kelola Pembayaran</h1>
                <p class="text-sm text-muted-foreground">
                    Kelola tagihan dan riwayat pembayaran cicilan siswa.
                </p>
            </div>
            <div v-if="canManageSpp" class="flex gap-2">
                <Button variant="outline" @click="openGenerate"
                    >Generate Pembayaran</Button
                ><Button @click="router.visit(SppController.create().url)"
                    >Tambah Pembayaran</Button
                >
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <Input
                v-model="search"
                placeholder="Cari nama atau NIS siswa..."
                @keyup.enter="applySearch"
            /><Button @click="applySearch">Cari</Button>
            <select
                v-model="kelasId"
                class="h-9 rounded-md border border-border bg-background px-3 text-sm text-foreground"
                @change="applySearch"
            >
                <option value="">Semua Kelas</option>
                <option
                    v-for="kelas in kelasOptions"
                    :key="kelas.id"
                    :value="String(kelas.id)"
                >
                    {{ kelas.nama_kelas }} - {{ kelas.thn_ajaran }}
                </option>
            </select>
            <select
                v-model="thnAjaran"
                class="h-9 rounded-md border border-border bg-background px-3 text-sm text-foreground"
                @change="applySearch"
            >
                <option value="">Semua Tahun</option>
                <option
                    v-for="tahun in tahunAjaranOptions"
                    :key="tahun"
                    :value="String(tahun)"
                >
                    {{ tahun }}
                </option>
            </select>
            <select
                v-model="status"
                class="h-9 rounded-md border border-border bg-background px-3 text-sm text-foreground"
                @change="applySearch"
            >
                <option value="">Semua Status</option>
                <option value="lunas">Lunas</option>
                <option value="belum_lunas">Belum Lunas</option>
            </select>
            <select
                v-model="jenisPembayaran"
                class="h-9 rounded-md border border-border bg-background px-3 text-sm text-foreground"
                @change="applySearch"
            >
                <option value="">Semua Jenis</option>
                <option
                    v-for="jenis in jenisPembayaranOptions"
                    :key="jenis.id"
                    :value="jenis.nama_jenis"
                >
                    {{ jenis.nama_jenis }}
                </option>
            </select>
        </div>
        <div
            v-if="canManageSpp"
            class="flex flex-wrap items-center gap-2 rounded-xl border border-border bg-card p-3"
        >
            <p class="text-sm text-muted-foreground">
                {{ selectedSppIds.length }} tagihan dipilih.
            </p>
            <Button
                variant="outline"
                :disabled="!selectedSppIds.length"
                @click="sendSelectedNotifications"
                >Kirim Notifikasi Pembayaran</Button
            >
            <Button
                variant="outline"
                :disabled="!spps.total"
                @click="sendNotificationsByFilter"
                >Kirim Email Berdasarkan Filter</Button
            >
        </div>
        <div class="grid gap-4 md:grid-cols-3">
            <div
                class="rounded-xl border border-border bg-card p-4 text-card-foreground"
            >
                <p class="text-sm text-muted-foreground">Total Tagihan</p>
                <p class="mt-1 text-lg font-semibold">
                    {{ formatCurrency(summary.total_tagihan) }}
                </p>
            </div>
            <div
                class="rounded-xl border border-border bg-card p-4 text-card-foreground"
            >
                <p class="text-sm text-muted-foreground">Total Sudah Dibayar</p>
                <p class="mt-1 text-lg font-semibold">
                    {{ formatCurrency(summary.total_dibayar) }}
                </p>
            </div>
            <div
                class="rounded-xl border border-border bg-card p-4 text-card-foreground"
            >
                <p class="text-sm text-muted-foreground">Total Sisa Tagihan</p>
                <p class="mt-1 text-lg font-semibold">
                    {{ formatCurrency(summary.total_sisa_tagihan) }}
                </p>
            </div>
        </div>
        <div
            class="overflow-x-auto rounded-xl border border-border bg-card text-card-foreground"
        >
            <table class="w-full min-w-max">
                <thead class="bg-muted/50">
                    <tr>
                        <th v-if="canManageSpp" class="px-4 py-3">
                            <input
                                type="checkbox"
                                :checked="allCurrentSelected"
                                aria-label="Pilih seluruh tagihan pada halaman ini"
                                @change="toggleAll"
                            />
                        </th>
                        <SortableTableHeader
                            v-for="column in tableColumns"
                            :key="column.key"
                            :label="column.label"
                            :column="column.key"
                            :sort="filters.sort"
                            :direction="filters.direction"
                            @sort="changeSort"
                        />
                        <th class="px-4 py-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="row in table.getRowModel().rows"
                        :key="row.id"
                        class="border-t border-border hover:bg-muted/50"
                    >
                        <td v-if="canManageSpp" class="px-4 py-3">
                            <input
                                v-model="selectedSppIds"
                                type="checkbox"
                                :value="row.original.id"
                                :aria-label="`Pilih tagihan ${row.original.siswa.nama}`"
                            />
                        </td>
                        <td class="px-4 py-3">{{ row.original.id }}</td>
                        <td class="px-4 py-3">
                            <p class="font-medium">
                                {{ row.original.siswa.nama }}
                            </p>
                            <p class="text-sm text-muted-foreground">
                                {{ row.original.siswa.nis ?? '-' }} ·
                                {{
                                    row.original.siswa.kelas?.nama_kelas ?? '-'
                                }}
                            </p>
                        </td>
                        <td class="px-4 py-3">{{ row.original.thn_ajaran }}</td>
                        <td class="px-4 py-3">
                            {{ row.original.jenis_pembayaran }}
                        </td>
                        <td class="px-4 py-3">
                            {{ formatCurrency(row.original.nominal) }}
                        </td>
                        <td class="px-4 py-3">
                            {{ formatCurrency(paid(row.original)) }}
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="rounded-full px-2 py-1 text-xs font-medium"
                                :class="
                                    isLunas(row.original)
                                        ? 'bg-primary text-primary-foreground'
                                        : 'bg-muted text-muted-foreground'
                                "
                                >{{
                                    isLunas(row.original)
                                        ? 'Lunas'
                                        : 'Belum Lunas'
                                }}</span
                            >
                        </td>
                        <td class="px-4 py-3">
                            {{
                                new Date(
                                    row.original.created_at,
                                ).toLocaleDateString('id-ID')
                            }}
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="rounded-full px-2 py-1 text-xs font-medium"
                                :class="
                                    row.original.last_notification_at
                                        ? 'bg-primary text-primary-foreground'
                                        : 'bg-muted text-muted-foreground'
                                "
                                >{{
                                    row.original.last_notification_at
                                        ? new Date(
                                              row.original.last_notification_at,
                                          ).toLocaleString('id-ID')
                                        : 'Belum Pernah Dikirim'
                                }}</span
                            >
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <Button
                                    variant="outline"
                                    @click="
                                        router.visit(
                                            SppController.show(row.original.id)
                                                .url,
                                        )
                                    "
                                    >Detail</Button
                                ><Button
                                    v-if="canManageSpp"
                                    variant="secondary"
                                    @click="
                                        router.visit(
                                            SppController.edit(row.original.id)
                                                .url,
                                        )
                                    "
                                    >Edit</Button
                                ><Button
                                    v-if="canManageSpp"
                                    variant="destructive"
                                    :disabled="row.original.payments_count > 0"
                                    :title="
                                        row.original.payments_count > 0
                                            ? 'Tagihan sudah memiliki pembayaran dan tidak dapat dihapus'
                                            : 'Hapus tagihan'
                                    "
                                    @click="destroySpp(row.original)"
                                    >Hapus</Button
                                >
                                <Button
                                    v-if="canManageSpp"
                                    variant="outline"
                                    @click="sendNotification(row.original)"
                                    >Kirim Email</Button
                                >
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!table.getRowModel().rows.length">
                        <td
                            :colspan="canManageSpp ? 11 : 10"
                            class="py-8 text-center text-muted-foreground"
                        >
                            Tidak ada data pembayaran
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="flex flex-wrap gap-2">
            <button
                v-for="link in spps.links"
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
            v-if="isGenerateModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-muted/80 p-4"
        >
            <form
                class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
                @submit.prevent="submitGenerate"
            >
                <h2 class="text-lg font-semibold">Generate SPP</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Buat tagihan massal untuk siswa aktif.
                </p>
                <div class="mt-6 space-y-2">
                    <label class="text-sm font-medium">Target Generate</label
                    ><select
                        v-model="generateForm.target"
                        class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground"
                    >
                        <option value="kelas">Per Kelas</option>
                        <option value="tahun_ajaran">
                            Per Tahun Ajaran
                        </option></select
                    ><InputError :message="generateForm.errors.target" />
                </div>
                <div
                    v-if="generateForm.target === 'kelas'"
                    class="mt-4 space-y-2"
                >
                    <label class="text-sm font-medium">Kelas</label
                    ><select
                        v-model="generateForm.kelas_id"
                        class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground"
                    >
                        <option value="">Pilih kelas</option>
                        <option
                            v-for="kelas in kelasOptions"
                            :key="kelas.id"
                            :value="String(kelas.id)"
                        >
                            {{ kelas.nama_kelas }} - {{ kelas.thn_ajaran }}
                        </option></select
                    ><InputError :message="generateForm.errors.kelas_id" />
                </div>
                <div v-else class="mt-4 space-y-2">
                    <label class="text-sm font-medium">Tahun Ajaran</label
                    ><select
                        v-model="generateForm.thn_ajaran"
                        class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground"
                    >
                        <option value="">Pilih tahun ajaran</option>
                        <option
                            v-for="tahun in tahunAjaranOptions"
                            :key="tahun"
                            :value="String(tahun)"
                        >
                            {{ tahun }}
                        </option></select
                    ><InputError :message="generateForm.errors.thn_ajaran" />
                </div>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-sm font-medium"
                            >Jenis Pembayaran</label
                        ><select
                            v-model="generateForm.jenis_pembayaran_id"
                            class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground"
                        >
                            <option value="">Pilih jenis pembayaran</option>
                            <option
                                v-for="jenis in jenisPembayaranOptions"
                                :key="jenis.id"
                                :value="String(jenis.id)"
                            >
                                {{ jenis.nama_jenis }}
                            </option></select
                        ><InputError
                            :message="generateForm.errors.jenis_pembayaran_id"
                        />
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium"
                            >Nominal Tagihan</label
                        ><Input
                            v-model="generateForm.nominal"
                            type="number"
                            min="0"
                            step="0.01"
                        /><InputError :message="generateForm.errors.nominal" />
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium"
                            >Tanggal Tagihan</label
                        ><Input
                            v-model="generateForm.tanggal_tagihan"
                            type="date"
                        /><InputError
                            :message="generateForm.errors.tanggal_tagihan"
                        />
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Jatuh Tempo</label
                        ><Input
                            v-model="generateForm.jatuh_tempo"
                            type="date"
                        /><InputError
                            :message="generateForm.errors.jatuh_tempo"
                        />
                    </div>
                </div>
                <div class="mt-4 space-y-2">
                    <label class="text-sm font-medium">Keterangan</label
                    ><textarea
                        v-model="generateForm.keterangan"
                        class="min-h-24 w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground"
                    /><InputError :message="generateForm.errors.keterangan" />
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        :disabled="generateForm.processing"
                        @click="isGenerateModalOpen = false"
                        >Batal</Button
                    ><Button
                        type="submit"
                        :disabled="generateForm.processing"
                        >{{
                            generateForm.processing
                                ? 'Memproses...'
                                : 'Generate SPP'
                        }}</Button
                    >
                </div>
            </form>
        </div>
    </div>
</template>
