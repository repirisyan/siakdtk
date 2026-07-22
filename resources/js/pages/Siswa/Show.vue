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
    nohp_wali: string | null;
    ttl_wali: string | null;
    agama_wali: string | null;
    pekerjaan_wali: string | null;
    penghasilan_wali: string | null;
    alamat_wali: string | null;
    desa: string | null;
    kecamatan: string | null;
    kabupaten: string | null;
    provinsi: string | null;
    akta_kelahiran_file: string | null;
    kartu_keluarga_file: string | null;
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
const accountDetails = computed<Detail[]>(() => [
    ['Nama Akun Orangtua', siswa.value.user.name],
    ['Email Orangtua', siswa.value.user.email],
    ['Status Akun Orangtua', siswa.value.user.status ? 'Aktif' : 'Nonaktif'],
    ['Status Registrasi', siswa.value.status],
    ['Tanggal Registrasi', siswa.value.tanggal_registrasi],
    ['Approved By', siswa.value.approver?.name ?? null],
    ['Approved At', siswa.value.approved_at],
    ['Nama Kelas', siswa.value.kelas.nama_kelas],
    ['Tahun Ajaran', siswa.value.kelas.thn_ajaran],
]);
const studentDetails = computed<Detail[]>(() => [
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
    ['Desa / Kelurahan', siswa.value.desa],
    ['Kecamatan', siswa.value.kecamatan],
    ['Kabupaten / Kota', siswa.value.kabupaten],
    ['Provinsi', siswa.value.provinsi],
    ['Alamat Siswa', siswa.value.alamat],
]);
const fatherDetails = computed<Detail[]>(() => [
    ['Nama Ayah', siswa.value.nama_ayah],
    ['Nomor HP Ayah', siswa.value.nohp_ayah],
    ['Tanggal Lahir Ayah', siswa.value.ttl_ayah],
    ['Agama Ayah', siswa.value.agama_ayah],
    ['Pekerjaan Ayah', siswa.value.pekerjaan],
    ['Penghasilan Ayah', siswa.value.penghasilan],
]);
const motherDetails = computed<Detail[]>(() => [
    ['Nama Ibu', siswa.value.nama_ibu],
    ['Nomor HP Ibu', siswa.value.nohp_ibu],
    ['Tanggal Lahir Ibu', siswa.value.ttl_ibu],
    ['Agama Ibu', siswa.value.agama_ibu],
    ['Pekerjaan Ibu', siswa.value.pekerjaan_ibu],
    ['Penghasilan Ibu', siswa.value.penghasilan_ibu],
]);
const guardianDetails = computed<Detail[]>(() => [
    ['Nama Wali', siswa.value.nama_wali],
    ['Nomor HP Wali', siswa.value.nohp_wali],
    ['Tanggal Lahir Wali', siswa.value.ttl_wali],
    ['Agama Wali', siswa.value.agama_wali],
    ['Pekerjaan Wali', siswa.value.pekerjaan_wali],
    ['Penghasilan Wali', siswa.value.penghasilan_wali],
    ['Alamat Wali', siswa.value.alamat_wali],
]);
const documentUrl = (path: string | null) => (path ? `/storage/${path}` : null);
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
            <h2 class="mb-4 text-lg font-semibold">Akun Orangtua dan Status</h2>
            <dl class="grid gap-4 md:grid-cols-2">
                <div
                    v-for="[label, value] in accountDetails"
                    :key="label"
                    class="space-y-1"
                >
                    <dt class="text-sm text-muted-foreground">{{ label }}</dt>
                    <dd class="font-medium">{{ value || '-' }}</dd>
                </div>
            </dl>

            <h2 class="mt-8 mb-4 text-lg font-semibold">Data Siswa</h2>
            <dl class="grid gap-4 md:grid-cols-2">
                <div
                    v-for="[label, value] in studentDetails"
                    :key="label"
                    class="space-y-1"
                >
                    <dt class="text-sm text-muted-foreground">{{ label }}</dt>
                    <dd class="font-medium">{{ value || '-' }}</dd>
                </div>
            </dl>

            <div class="mt-8 grid gap-6 lg:grid-cols-2">
                <section class="rounded-xl border border-border p-5">
                    <h2 class="mb-4 text-lg font-semibold">Data Ayah</h2>
                    <dl class="grid gap-4">
                        <div
                            v-for="[label, value] in fatherDetails"
                            :key="label"
                            class="space-y-1"
                        >
                            <dt class="text-sm text-muted-foreground">
                                {{ label }}
                            </dt>
                            <dd class="font-medium">{{ value || '-' }}</dd>
                        </div>
                    </dl>
                </section>
                <section class="rounded-xl border border-border p-5">
                    <h2 class="mb-4 text-lg font-semibold">Data Ibu</h2>
                    <dl class="grid gap-4">
                        <div
                            v-for="[label, value] in motherDetails"
                            :key="label"
                            class="space-y-1"
                        >
                            <dt class="text-sm text-muted-foreground">
                                {{ label }}
                            </dt>
                            <dd class="font-medium">{{ value || '-' }}</dd>
                        </div>
                    </dl>
                </section>
            </div>

            <section class="mt-6 rounded-xl border border-border p-5">
                <h2 class="mb-4 text-lg font-semibold">Data Wali</h2>
                <dl class="grid gap-4 md:grid-cols-2">
                    <div
                        v-for="[label, value] in guardianDetails"
                        :key="label"
                        class="space-y-1"
                    >
                        <dt class="text-sm text-muted-foreground">
                            {{ label }}
                        </dt>
                        <dd class="font-medium">{{ value || '-' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="mt-6 rounded-xl border border-border p-5">
                <h2 class="mb-4 text-lg font-semibold">Dokumen Siswa</h2>
                <div class="flex flex-wrap gap-3">
                    <Button
                        v-if="documentUrl(siswa.akta_kelahiran_file)"
                        as-child
                        variant="outline"
                    >
                        <a
                            :href="documentUrl(siswa.akta_kelahiran_file)!"
                            target="_blank"
                            rel="noopener"
                            >Lihat Akta Kelahiran</a
                        >
                    </Button>
                    <Button
                        v-if="documentUrl(siswa.kartu_keluarga_file)"
                        as-child
                        variant="outline"
                    >
                        <a
                            :href="documentUrl(siswa.kartu_keluarga_file)!"
                            target="_blank"
                            rel="noopener"
                            >Lihat Kartu Keluarga</a
                        >
                    </Button>
                    <p
                        v-if="
                            !siswa.akta_kelahiran_file &&
                            !siswa.kartu_keluarga_file
                        "
                        class="text-sm text-muted-foreground"
                    >
                        Dokumen belum diunggah.
                    </p>
                </div>
            </section>

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
