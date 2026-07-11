<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import SiswaController from '@/actions/App/Http/Controllers/SiswaController';

import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';

interface SiswaForm {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    foto_profil: File | null;
    thn_ajaran: string;
    nis: string;
    nisn: string;
    nik: string;
    nomor_kk: string;
    nama: string;
    tmp_lahir: string;
    tgl_lahir: string;
    jk: string;
    agama: string;
    tinggi_bdn: string;
    berat_bdn: string;
    anak_ke: string;
    jml_sdr: string;
    nama_pgl: string;
    alamat: string;
    nama_ayah: string;
    nohp_ayah: string;
    ttl_ayah: string;
    agama_ayah: string;
    pekerjaan: string;
    penghasilan: string;
    nama_ibu: string;
    nohp_ibu: string;
    ttl_ibu: string;
    agama_ibu: string;
    pekerjaan_ibu: string;
    penghasilan_ibu: string;
    nama_wali: string;
    kelas_id: string;
}

type SiswaField = Exclude<keyof SiswaForm, 'foto_profil'>;

interface Field {
    name: SiswaField;
    label: string;
    type?: string;
    required?: boolean;
    options?: readonly string[];
}

const AGAMA_OPTIONS = [
    'Islam',
    'Kristen',
    'Katolik',
    'Hindu',
    'Buddha',
    'Konghucu',
] as const;

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Siswa', href: SiswaController.index().url },
            { title: 'Create', href: SiswaController.create().url },
        ],
    },
});

const fields: Field[] = [
    { name: 'nis', label: 'NIS' },
    { name: 'nisn', label: 'NISN' },
    { name: 'nik', label: 'NIK', required: true },
    { name: 'nomor_kk', label: 'Nomor KK', required: true },
    { name: 'nama', label: 'Nama Lengkap', required: true },
    { name: 'tmp_lahir', label: 'Tempat Lahir', required: true },
    { name: 'tgl_lahir', label: 'Tanggal Lahir', type: 'date', required: true },
    { name: 'jk', label: 'Jenis Kelamin', required: true },
    { name: 'agama', label: 'Agama', required: true, options: AGAMA_OPTIONS },
    { name: 'tinggi_bdn', label: 'Tinggi Badan', type: 'number' },
    { name: 'berat_bdn', label: 'Berat Badan', type: 'number' },
    { name: 'anak_ke', label: 'Anak Ke' },
    { name: 'jml_sdr', label: 'Jumlah Saudara' },
    { name: 'nama_pgl', label: 'Nama Panggilan' },
    { name: 'alamat', label: 'Alamat', required: true },
    { name: 'nama_ayah', label: 'Nama Ayah' },
    { name: 'nohp_ayah', label: 'Nomor HP Ayah', type: 'tel' },
    { name: 'ttl_ayah', label: 'Tanggal Lahir Ayah', type: 'date' },
    { name: 'agama_ayah', label: 'Agama Ayah', options: AGAMA_OPTIONS },
    { name: 'pekerjaan', label: 'Pekerjaan Ayah' },
    { name: 'penghasilan', label: 'Penghasilan Ayah' },
    { name: 'nama_ibu', label: 'Nama Ibu' },
    { name: 'nohp_ibu', label: 'Nomor HP Ibu', type: 'tel' },
    { name: 'ttl_ibu', label: 'Tanggal Lahir Ibu', type: 'date' },
    { name: 'agama_ibu', label: 'Agama Ibu', options: AGAMA_OPTIONS },
    { name: 'pekerjaan_ibu', label: 'Pekerjaan Ibu' },
    { name: 'penghasilan_ibu', label: 'Penghasilan Ibu' },
    { name: 'nama_wali', label: 'Nama Wali' },
];

interface TahunAjaran {
    thn_ajaran: string;
}

interface Kelas {
    id: number;
    nama_kelas: string;
    thn_ajaran: string;
}

const page = usePage();
const tahunAjarans = computed(() => page.props.tahunAjarans as TahunAjaran[]);
const kelas = computed(() => page.props.kelas as Kelas[]);

const form = useForm<SiswaForm>({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    foto_profil: null,
    thn_ajaran: '',
    nis: '',
    nisn: '',
    nik: '',
    nomor_kk: '',
    nama: '',
    tmp_lahir: '',
    tgl_lahir: '',
    jk: '',
    agama: '',
    tinggi_bdn: '',
    berat_bdn: '',
    anak_ke: '',
    jml_sdr: '',
    nama_pgl: '',
    alamat: '',
    nama_ayah: '',
    nohp_ayah: '',
    ttl_ayah: '',
    agama_ayah: '',
    pekerjaan: '',
    penghasilan: '',
    nama_ibu: '',
    nohp_ibu: '',
    ttl_ibu: '',
    agama_ibu: '',
    pekerjaan_ibu: '',
    penghasilan_ibu: '',
    nama_wali: '',
    kelas_id: '',
});

const changeTahunAjaran = () => {
    form.kelas_id = '';

    router.get(
        SiswaController.create().url,
        { thn_ajaran: form.thn_ajaran },
        {
            only: ['kelas'],
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const submit = () => {
    form.post(SiswaController.store().url, { forceFormData: true });
};

const onPhotoChange = (event: Event) => {
    form.foto_profil = (event.target as HTMLInputElement).files?.[0] ?? null;
};
</script>

<template>
    <Head title="Tambah Siswa" />

    <div class="max-w-4xl space-y-6 bg-background p-4 text-foreground">
        <div>
            <h1 class="text-2xl font-bold">Tambah Siswa</h1>
            <p class="text-sm text-muted-foreground">
                Tambahkan data siswa baru.
            </p>
        </div>

        <div
            class="rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
        >
            <form class="space-y-6" @submit.prevent="submit">
                <section class="space-y-4">
                    <div>
                        <h2 class="text-lg font-semibold">Akun Orangtua</h2>
                        <p class="text-sm text-muted-foreground">
                            Data akun yang digunakan orangtua siswa untuk login.
                        </p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <label for="foto_profil" class="text-sm font-medium"
                                >Foto Profil</label
                            ><input
                                id="foto_profil"
                                type="file"
                                accept=".jpg,.jpeg,.png,.webp"
                                class="block w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground"
                                @change="onPhotoChange"
                            /><InputError :message="form.errors.foto_profil" />
                        </div>
                        <div class="space-y-2">
                            <label for="name" class="text-sm font-medium"
                                >Nama Akun</label
                            >
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="Masukkan nama akun orangtua"
                            />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div class="space-y-2">
                            <label for="email" class="text-sm font-medium"
                                >Email</label
                            >
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="Masukkan email"
                            />
                            <InputError :message="form.errors.email" />
                        </div>
                        <div class="space-y-2">
                            <label for="password" class="text-sm font-medium"
                                >Password</label
                            >
                            <Input
                                id="password"
                                v-model="form.password"
                                type="password"
                                placeholder="Masukkan password"
                            />
                            <InputError :message="form.errors.password" />
                        </div>
                        <div class="space-y-2">
                            <label
                                for="password_confirmation"
                                class="text-sm font-medium"
                                >Konfirmasi Password</label
                            >
                            <Input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                placeholder="Konfirmasi password"
                            />
                        </div>
                    </div>
                </section>

                <section class="space-y-4 border-t border-border pt-6">
                    <div>
                        <h2 class="text-lg font-semibold">Kelas</h2>
                        <p class="text-sm text-muted-foreground">
                            Pilih tahun ajaran sebelum memilih kelas.
                        </p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <label for="thn_ajaran" class="text-sm font-medium"
                                >Tahun Ajaran</label
                            >
                            <select
                                id="thn_ajaran"
                                v-model="form.thn_ajaran"
                                class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground"
                                @change="changeTahunAjaran"
                            >
                                <option value="">Pilih tahun ajaran</option>
                                <option
                                    v-for="tahun in tahunAjarans"
                                    :key="tahun.thn_ajaran"
                                    :value="tahun.thn_ajaran"
                                >
                                    {{ tahun.thn_ajaran }}
                                </option>
                            </select>
                            <InputError :message="form.errors.thn_ajaran" />
                        </div>
                        <div class="space-y-2">
                            <label for="kelas_id" class="text-sm font-medium"
                                >Kelas</label
                            >
                            <select
                                id="kelas_id"
                                v-model="form.kelas_id"
                                :disabled="!form.thn_ajaran"
                                class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="">Pilih kelas</option>
                                <option
                                    v-for="item in kelas"
                                    :key="item.id"
                                    :value="String(item.id)"
                                >
                                    {{ item.nama_kelas }}
                                </option>
                            </select>
                            <InputError :message="form.errors.kelas_id" />
                        </div>
                    </div>
                </section>

                <section class="space-y-4 border-t border-border pt-6">
                    <div>
                        <h2 class="text-lg font-semibold">Data Siswa</h2>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div
                            v-for="field in fields"
                            :key="field.name"
                            class="space-y-2"
                        >
                            <label
                                :for="field.name"
                                class="text-sm font-medium"
                            >
                                {{ field.label
                                }}<span v-if="field.required"> *</span>
                            </label>
                            <select
                                v-if="field.options"
                                :id="field.name"
                                v-model="form[field.name]"
                                :required="field.required"
                                class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground"
                            >
                                <option value="">Pilih Agama</option>
                                <option
                                    v-for="agama in field.options"
                                    :key="agama"
                                    :value="agama"
                                >
                                    {{ agama }}
                                </option>
                            </select>
                            <Input
                                v-else
                                :id="field.name"
                                v-model="form[field.name]"
                                :type="field.type ?? 'text'"
                                :required="field.required"
                                :placeholder="`Masukkan ${field.label.toLowerCase()}`"
                            />
                            <InputError :message="form.errors[field.name]" />
                        </div>
                    </div>
                </section>

                <div class="flex gap-2">
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                    <Button as-child variant="outline">
                        <Link :href="SiswaController.index().url">Batal</Link>
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>
