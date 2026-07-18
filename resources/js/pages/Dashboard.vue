<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    BookOpenCheck,
    CalendarDays,
    ClipboardCheck,
    ClipboardList,
    GraduationCap,
    Receipt,
    School,
    Users,
    Wallet,
} from '@lucide/vue';
import type { ApexOptions } from 'apexcharts';
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

import { index as raporAnakIndex } from '@/actions/App/Http/Controllers/RaporAnakController';
import { index as tagihanSayaIndex } from '@/actions/App/Http/Controllers/TagihanSayaController';
import { useChartTheme } from '@/composables/useChartTheme';
import { dashboard } from '@/routes';

type StatKey =
    | 'total_siswa_aktif'
    | 'total_kelas_aktif'
    | 'total_guru_aktif'
    | 'total_jadwal_hari_ini'
    | 'total_tema_tahun_ajaran_berjalan'
    | 'total_jadwal_minggu_ini'
    | 'total_absensi_sudah_diisi'
    | 'total_absensi_belum_diisi'
    | 'total_penilaian_sudah_diisi'
    | 'total_tagihan_aktif'
    | 'total_tunggakan'
    | 'total_pembayaran_tahun_ini'
    | 'total_anak'
    | 'total_rapor_tersedia';

interface DashboardData {
    type: 'school' | 'guru' | 'orangtua';
    stats: Partial<Record<StatKey, number>>;
    financeStats?: {
        total_tagihan_periode: number;
        total_pembayaran_periode: number;
    };
    studentChart?: { tahun_ajaran: string; total: number }[];
    child?: { nama: string; nis: string | null; kelas: string | null };
}

const page = usePage();
const { chartTheme } = useChartTheme();
const data = computed(() => page.props.dashboard as DashboardData);
const money = (value: number) =>
    `Rp${Math.round(value)
        .toString()
        .replace(/\B(?=(\d{3})+(?!\d))/g, '.')}`;
const number = (value: number | undefined) =>
    new Intl.NumberFormat('id-ID').format(value ?? 0);
const schoolStats = [
    { key: 'total_siswa_aktif', label: 'Total Siswa Aktif', icon: Users },
    { key: 'total_kelas_aktif', label: 'Total Kelas Aktif', icon: School },
    { key: 'total_guru_aktif', label: 'Total Guru Aktif', icon: GraduationCap },
    {
        key: 'total_jadwal_hari_ini',
        label: 'Jadwal Hari Ini',
        icon: CalendarDays,
    },
    {
        key: 'total_tema_tahun_ajaran_berjalan',
        label: 'Tema Tahun Ajaran Berjalan',
        icon: BookOpenCheck,
    },
] as const;
const guruStats = [
    {
        key: 'total_jadwal_hari_ini',
        label: 'Jadwal Hari Ini',
        icon: CalendarDays,
    },
    {
        key: 'total_jadwal_minggu_ini',
        label: 'Jadwal Minggu Ini',
        icon: CalendarDays,
    },
    {
        key: 'total_absensi_sudah_diisi',
        label: 'Absensi Sudah Diisi',
        icon: ClipboardCheck,
    },
    {
        key: 'total_absensi_belum_diisi',
        label: 'Absensi Belum Diisi',
        icon: ClipboardCheck,
    },
    {
        key: 'total_penilaian_sudah_diisi',
        label: 'Penilaian Sudah Diisi',
        icon: ClipboardList,
    },
] as const;
const studentChart = computed(() => data.value.studentChart ?? []);
const chartSeries = computed(() => [
    {
        name: 'Jumlah Siswa',
        data: studentChart.value.map((item) => item.total),
    },
]);
const chartOptions = computed<ApexOptions>(() => {
    const theme = chartTheme.value;

    return {
        chart: {
            type: 'bar',
            background: 'transparent',
            toolbar: { show: false },
            fontFamily: 'inherit',
            foreColor: theme?.mutedForeground,
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 450,
            },
        },
        colors: [theme?.chart1 ?? theme?.primary ?? 'transparent'],
        plotOptions: {
            bar: {
                borderRadius: 8,
                columnWidth: '52%',
            },
        },
        dataLabels: { enabled: false },
        stroke: { width: 0 },
        grid: {
            borderColor: theme?.border,
            strokeDashArray: 4,
            padding: { left: 4, right: 12 },
        },
        xaxis: {
            categories: studentChart.value.map((item) => item.tahun_ajaran),
            axisBorder: { color: theme?.border },
            axisTicks: { color: theme?.border },
            labels: {
                style: { colors: theme?.mutedForeground },
                rotate: studentChart.value.length > 6 ? -35 : 0,
                trim: true,
                hideOverlappingLabels: true,
            },
        },
        yaxis: {
            min: 0,
            forceNiceScale: true,
            labels: {
                style: { colors: theme?.mutedForeground },
                formatter: (value) => Math.round(value).toString(),
            },
        },
        tooltip: {
            theme: theme?.isDark ? 'dark' : 'light',
            x: { formatter: (value) => `Tahun Ajaran: ${value}` },
            y: { formatter: (value) => `Jumlah Siswa: ${value}` },
            marker: { show: false },
        },
        legend: { show: false },
    };
});
const financeCompletion = computed(() => {
    const financeStats = data.value.financeStats;

    if (!financeStats || financeStats.total_tagihan_periode === 0) {
        return 0;
    }

    return Math.min(
        Math.round(
            (financeStats.total_pembayaran_periode /
                financeStats.total_tagihan_periode) *
                100,
        ),
        100,
    );
});

defineOptions({
    layout: { breadcrumbs: [{ title: 'Dashboard', href: dashboard() }] },
});
</script>

<template>
    <Head title="Dashboard" />
    <div class="space-y-6 bg-background p-4 text-foreground">
        <div>
            <h1 class="text-2xl font-bold">Dashboard</h1>
            <p class="text-sm text-muted-foreground">
                Ringkasan data sesuai akses akun Anda.
            </p>
        </div>

        <template v-if="data.type === 'school'">
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <article
                    v-for="stat in schoolStats"
                    :key="stat.key"
                    class="rounded-2xl border border-border bg-card p-5 text-card-foreground shadow-sm shadow-primary/5"
                >
                    <div
                        class="mb-5 flex size-11 items-center justify-center rounded-xl bg-secondary text-secondary-foreground"
                    >
                        <component :is="stat.icon" class="size-5" />
                    </div>
                    <p class="text-sm font-medium text-muted-foreground">
                        {{ stat.label }}
                    </p>
                    <p class="mt-1 text-3xl font-bold tracking-tight">
                        {{ number(data.stats[stat.key]) }}
                    </p>
                </article>
            </section>
            <section v-if="data.financeStats" class="grid gap-4 md:grid-cols-3">
                <article
                    class="rounded-2xl border border-border bg-card p-5 text-card-foreground shadow-sm shadow-primary/5"
                >
                    <div
                        class="mb-5 flex size-11 items-center justify-center rounded-xl bg-accent text-accent-foreground"
                    >
                        <Wallet class="size-5" />
                    </div>
                    <p class="text-sm font-medium text-muted-foreground">
                        Total Tagihan SPP Periode Berjalan
                    </p>
                    <p class="mt-1 text-3xl font-bold tracking-tight">
                        {{ money(data.financeStats.total_tagihan_periode) }}
                    </p>
                </article>
                <article
                    class="rounded-2xl border border-border bg-card p-5 text-card-foreground shadow-sm shadow-primary/5"
                >
                    <div
                        class="mb-5 flex size-11 items-center justify-center rounded-xl bg-secondary text-secondary-foreground"
                    >
                        <Receipt class="size-5" />
                    </div>
                    <p class="text-sm font-medium text-muted-foreground">
                        Total Pembayaran SPP Periode Berjalan
                    </p>
                    <p class="mt-1 text-3xl font-bold tracking-tight">
                        {{ money(data.financeStats.total_pembayaran_periode) }}
                    </p>
                </article>
                <article
                    class="rounded-2xl border border-border bg-card p-5 text-card-foreground shadow-sm shadow-primary/5"
                >
                    <div
                        class="mb-5 flex size-11 items-center justify-center rounded-xl bg-primary text-primary-foreground"
                    >
                        <Receipt class="size-5" />
                    </div>
                    <p class="text-sm font-medium text-muted-foreground">
                        Persentase Pelunasan
                    </p>
                    <p class="mt-1 text-3xl font-bold tracking-tight">
                        {{ financeCompletion }}%
                    </p>
                </article>
            </section>
            <section
                class="rounded-2xl border border-border bg-card p-6 text-card-foreground shadow-sm shadow-primary/5"
            >
                <h2 class="text-lg font-semibold">
                    Jumlah Siswa per Tahun Ajaran
                </h2>
                <p class="mb-6 text-sm text-muted-foreground">
                    Jumlah siswa aktif berdasarkan tahun ajaran kelas.
                </p>
                <div v-if="chartTheme && studentChart.length" class="h-80">
                    <VueApexCharts
                        type="bar"
                        height="320"
                        :options="chartOptions"
                        :series="chartSeries"
                    />
                </div>
                <div
                    v-else-if="chartTheme"
                    class="flex h-80 items-center justify-center rounded-xl border border-dashed border-border bg-muted/50 px-6 text-center text-sm text-muted-foreground"
                >
                    Belum ada data siswa yang dapat ditampilkan.
                </div>
                <div v-else class="h-80 animate-pulse rounded-xl bg-muted" />
            </section>
        </template>

        <section
            v-else-if="data.type === 'guru'"
            class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5"
        >
            <article
                v-for="stat in guruStats"
                :key="stat.key"
                class="rounded-2xl border border-border bg-card p-5 text-card-foreground shadow-sm shadow-primary/5"
            >
                <div
                    class="mb-5 flex size-11 items-center justify-center rounded-xl bg-secondary text-secondary-foreground"
                >
                    <component :is="stat.icon" class="size-5" />
                </div>
                <p class="text-sm font-medium text-muted-foreground">
                    {{ stat.label }}
                </p>
                <p class="mt-1 text-3xl font-bold tracking-tight">
                    {{ number(data.stats[stat.key]) }}
                </p>
            </article>
        </section>

        <template v-else
            ><section
                class="rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
            >
                <p class="text-sm text-muted-foreground">Data Anak</p>
                <h2 class="mt-1 text-2xl font-semibold">Ringkasan Keluarga</h2>
                <p class="mt-2 text-sm text-muted-foreground">
                    Ringkasan tagihan, pembayaran, dan rapor seluruh anak Anda.
                </p>
            </section>
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <article
                    class="rounded-xl border border-border bg-card p-5 text-card-foreground shadow-sm"
                >
                    <p class="text-sm text-muted-foreground">Total Anak</p>
                    <p class="mt-1 text-2xl font-semibold">
                        {{ number(data.stats.total_anak) }}
                    </p>
                </article>
                <article
                    class="rounded-xl border border-border bg-card p-5 text-card-foreground shadow-sm"
                >
                    <p class="text-sm text-muted-foreground">
                        Total Tagihan Aktif
                    </p>
                    <p class="mt-1 text-2xl font-semibold">
                        {{ number(data.stats.total_tagihan_aktif) }}
                    </p>
                </article>
                <article
                    class="rounded-xl border border-border bg-card p-5 text-card-foreground shadow-sm"
                >
                    <p class="text-sm text-muted-foreground">Rapor Tersedia</p>
                    <p class="mt-1 text-2xl font-semibold">
                        {{ number(data.stats.total_rapor_tersedia) }}
                    </p>
                </article>
                <article
                    class="rounded-xl border border-border bg-card p-5 text-card-foreground shadow-sm"
                >
                    <p class="text-sm text-muted-foreground">Total Tunggakan</p>
                    <p class="mt-1 text-2xl font-semibold">
                        {{ money(data.stats.total_tunggakan ?? 0) }}
                    </p>
                </article>
                <article
                    class="rounded-xl border border-border bg-card p-5 text-card-foreground shadow-sm"
                >
                    <p class="text-sm text-muted-foreground">
                        Total Pembayaran Tahun Ini
                    </p>
                    <p class="mt-1 text-2xl font-semibold">
                        {{ money(data.stats.total_pembayaran_tahun_ini ?? 0) }}
                    </p>
                </article>
            </section>
            <section class="flex flex-wrap gap-3">
                <Link
                    :href="raporAnakIndex().url"
                    class="inline-flex h-9 items-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground"
                    >Lihat Rapor Anak</Link
                ><Link
                    :href="tagihanSayaIndex().url"
                    class="inline-flex h-9 items-center rounded-md border border-border bg-card px-4 text-sm font-medium text-card-foreground"
                    >Lihat Tagihan Saya</Link
                >
            </section></template
        >
    </div>
</template>

<style scoped>
:deep(.apexcharts-tooltip) {
    border-color: var(--border) !important;
    background: var(--card) !important;
    color: var(--card-foreground) !important;
    box-shadow: 0 10px 24px
        color-mix(in hsl, var(--foreground) 12%, transparent) !important;
}

:deep(.apexcharts-tooltip-title) {
    border-bottom-color: var(--border) !important;
    background: var(--muted) !important;
    color: var(--card-foreground) !important;
}

:deep(.apexcharts-tooltip-text-y-label),
:deep(.apexcharts-tooltip-text-y-value) {
    color: var(--card-foreground) !important;
}
</style>
