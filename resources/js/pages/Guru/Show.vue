<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import GuruController from '@/actions/App/Http/Controllers/GuruController';
import Button from '@/components/ui/button/Button.vue';

interface Guru {
    id: number;
    nama: string;
    nip: string;
    tmp_lhr: string;
    tgl_lahir: string;
    alamat: string;
    agama: string | null;
    nohp_guru: string | null;
    email: string | null;
    jk: string | null;
    jenis_ptk: string | null;
    nuptk: string | null;
    pendidikan: string | null;
    stts_kepegawaian: string | null;
    sk_cpns: string | null;
    tgl_cpns: string | null;
    sk_pengangkatan: string | null;
    tmt_pengangkatan: string | null;
    pangkat_golongan: string | null;
    tmt_pns: string | null;
    npwp: string | null;
    foto: string | null;
    user: { name: string; email: string };
}
type Detail = [string, string | number | null];
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Guru', href: GuruController.index().url },
            { title: 'Detail', href: '#' },
        ],
    },
});
const page = usePage();
const guru = computed(() => page.props.guru as Guru);
const details = computed<Detail[]>(() => [
    ['Username', guru.value.user.name],
    ['Email Akun', guru.value.user.email],
    ['Nama Guru', guru.value.nama],
    ['NIP', guru.value.nip],
    ['Tempat Lahir', guru.value.tmp_lhr],
    ['Tanggal Lahir', guru.value.tgl_lahir],
    ['Alamat', guru.value.alamat],
    ['Agama', guru.value.agama],
    ['Nomor HP', guru.value.nohp_guru],
    ['Email Guru', guru.value.email],
    ['Jenis Kelamin', guru.value.jk],
    ['Jenis PTK', guru.value.jenis_ptk],
    ['NUPTK', guru.value.nuptk],
    ['Pendidikan', guru.value.pendidikan],
    ['Status Kepegawaian', guru.value.stts_kepegawaian],
    ['SK CPNS', guru.value.sk_cpns],
    ['Tanggal CPNS', guru.value.tgl_cpns],
    ['SK Pengangkatan', guru.value.sk_pengangkatan],
    ['TMT Pengangkatan', guru.value.tmt_pengangkatan],
    ['Pangkat/Golongan', guru.value.pangkat_golongan],
    ['TMT PNS', guru.value.tmt_pns],
    ['NPWP', guru.value.npwp],
    ['Foto', guru.value.foto],
]);
</script>

<template>
    <Head :title="`Detail Guru - ${guru.nama}`" />
    <div class="max-w-4xl space-y-6 bg-background p-4 text-foreground">
        <div>
            <h1 class="text-2xl font-bold">Detail Guru</h1>
            <p class="text-sm text-muted-foreground">Informasi lengkap guru.</p>
        </div>
        <div
            class="rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
        >
            <dl class="grid gap-4 md:grid-cols-2">
                <div
                    v-for="[label, value] in details"
                    :key="label"
                    class="space-y-1"
                >
                    <dt class="text-sm text-muted-foreground">{{ label }}</dt>
                    <dd class="font-medium">{{ value || '-' }}</dd>
                </div>
            </dl>
            <div class="mt-6 flex gap-2">
                <Button as-child variant="secondary"
                    ><Link :href="GuruController.edit(guru.id).url"
                        >Edit</Link
                    ></Button
                ><Button as-child variant="outline"
                    ><Link :href="GuruController.index().url"
                        >Kembali</Link
                    ></Button
                >
            </div>
        </div>
    </div>
</template>
