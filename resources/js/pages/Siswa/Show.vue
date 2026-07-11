<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import SiswaController from '@/actions/App/Http/Controllers/SiswaController';

import Button from '@/components/ui/button/Button.vue';

interface Siswa {
    id: number;
    nis: string | null;
    nisn: string | null;
    nik: string;
    nomor_kk: string;
    nama: string;
    tmp_lahir: string;
    tgl_lahir: string;
    jk: string;
    agama: string;
    tinggi_bdn: string | null;
    berat_bdn: string | null;
    anak_ke: string | null;
    jml_sdr: string | null;
    nama_pgl: string | null;
    alamat: string;
    nama_ayah: string | null;
    nohp_ayah: string | null;
    ttl_ayah: string | null;
    agama_ayah: string | null;
    pekerjaan: string | null;
    penghasilan: string | null;
    nama_ibu: string | null;
    nohp_ibu: string | null;
    ttl_ibu: string | null;
    agama_ibu: string | null;
    pekerjaan_ibu: string | null;
    penghasilan_ibu: string | null;
    nama_wali: string | null;
    kelas_id: number;
    user_id: number;
    user: {
        name: string;
        email: string;
        status: boolean;
        avatar: string | null;
    };
    kelas: {
        nama_kelas: string;
        thn_ajaran: string;
    };
    status: 'pending' | 'aktif' | 'ditolak';
    tanggal_registrasi: string;
    approved_at: string | null;
    approver: { name: string; email: string } | null;
}

type Detail = [string, string | number | null];

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Siswa', href: SiswaController.index().url },
            { title: 'Detail', href: '#' },
        ],
    },
});

const page = usePage();
const siswa = computed(() => page.props.siswa as Siswa);
const details = computed<Detail[]>(() => [
    ['Nama Akun Orangtua', siswa.value.user.name],
    ['Email Orangtua', siswa.value.user.email],
    ['Status Akun Orangtua', siswa.value.user.status ? 'Aktif' : 'Nonaktif'],
    ['Status Registrasi', siswa.value.status],
    ['Tanggal Registrasi', siswa.value.tanggal_registrasi],
    ['Approved By', siswa.value.approver?.name ?? null],
    ['Approved At', siswa.value.approved_at],
    ['Nama Kelas', siswa.value.kelas.nama_kelas],
    ['Tahun Ajaran', siswa.value.kelas.thn_ajaran],
    ['NIS', siswa.value.nis],
    ['NISN', siswa.value.nisn],
    ['NIK', siswa.value.nik],
    ['Nomor KK', siswa.value.nomor_kk],
    ['Nama Lengkap', siswa.value.nama],
    ['Nama Panggilan', siswa.value.nama_pgl],
    ['Tempat Lahir', siswa.value.tmp_lahir],
    ['Tanggal Lahir', siswa.value.tgl_lahir],
    ['Jenis Kelamin', siswa.value.jk],
    ['Agama', siswa.value.agama],
    ['Tinggi Badan', siswa.value.tinggi_bdn],
    ['Berat Badan', siswa.value.berat_bdn],
    ['Anak Ke', siswa.value.anak_ke],
    ['Jumlah Saudara', siswa.value.jml_sdr],
    ['Alamat', siswa.value.alamat],
    ['Nama Ayah', siswa.value.nama_ayah],
    ['Nomor HP Ayah', siswa.value.nohp_ayah],
    ['Tanggal Lahir Ayah', siswa.value.ttl_ayah],
    ['Agama Ayah', siswa.value.agama_ayah],
    ['Pekerjaan Ayah', siswa.value.pekerjaan],
    ['Penghasilan Ayah', siswa.value.penghasilan],
    ['Nama Ibu', siswa.value.nama_ibu],
    ['Nomor HP Ibu', siswa.value.nohp_ibu],
    ['Tanggal Lahir Ibu', siswa.value.ttl_ibu],
    ['Agama Ibu', siswa.value.agama_ibu],
    ['Pekerjaan Ibu', siswa.value.pekerjaan_ibu],
    ['Penghasilan Ibu', siswa.value.penghasilan_ibu],
    ['Nama Wali', siswa.value.nama_wali],
]);
</script>

<template>
    <Head :title="`Detail Siswa - ${siswa.nama}`" />

    <div class="max-w-4xl space-y-6 bg-background p-4 text-foreground">
        <div>
            <h1 class="text-2xl font-bold">Detail Siswa</h1>
            <p class="text-sm text-muted-foreground">
                Informasi lengkap siswa.
            </p>
        </div>

        <div
            class="rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
        >
            <div class="mb-6 flex items-center gap-4">
                <img
                    v-if="siswa.user.avatar"
                    :src="siswa.user.avatar"
                    :alt="siswa.user.name"
                    class="h-20 w-20 rounded-full object-cover"
                />
                <div
                    v-else
                    class="flex h-20 w-20 items-center justify-center rounded-full bg-muted text-muted-foreground"
                >
                    {{ siswa.user.name.slice(0, 1) }}
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">
                        Foto Profil Akun Orangtua
                    </p>
                    <p class="font-medium">{{ siswa.user.name }}</p>
                </div>
            </div>
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
                <Button as-child variant="secondary">
                    <Link :href="SiswaController.edit(siswa.id).url">Edit</Link>
                </Button>
                <Button as-child variant="outline">
                    <Link :href="SiswaController.index().url">Kembali</Link>
                </Button>
            </div>
        </div>
    </div>
</template>
