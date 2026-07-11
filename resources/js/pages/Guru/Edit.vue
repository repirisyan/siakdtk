<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

import GuruController from '@/actions/App/Http/Controllers/GuruController';

import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';

interface GuruForm {
    name: string;
    user_email: string;
    password: string;
    password_confirmation: string;
    nama: string;
    nip: string;
    tmp_lhr: string;
    tgl_lahir: string;
    alamat: string;
    agama: string;
    nohp_guru: string;
    email: string;
    jk: string;
    jenis_ptk: string;
    nuptk: string;
    pendidikan: string;
    stts_kepegawaian: string;
    sk_cpns: string;
    tgl_cpns: string;
    sk_pengangkatan: string;
    tmt_pengangkatan: string;
    pangkat_golongan: string;
    tmt_pns: string;
    npwp: string;
    foto: string;
}

interface Guru extends Omit<
    GuruForm,
    'name' | 'user_email' | 'password' | 'password_confirmation'
> {
    id: number;
    user: { name: string; email: string };
}

type GuruField = Exclude<
    keyof GuruForm,
    'name' | 'user_email' | 'password' | 'password_confirmation'
>;

interface Field {
    name: GuruField;
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
            { title: 'Guru', href: GuruController.index().url },
            { title: 'Edit', href: '#' },
        ],
    },
});

const fields: Field[] = [
    { name: 'nama', label: 'Nama Guru', required: true },
    { name: 'nip', label: 'NIP', required: true },
    { name: 'tmp_lhr', label: 'Tempat Lahir', required: true },
    { name: 'tgl_lahir', label: 'Tanggal Lahir', type: 'date', required: true },
    { name: 'alamat', label: 'Alamat', required: true },
    { name: 'agama', label: 'Agama', options: AGAMA_OPTIONS },
    { name: 'nohp_guru', label: 'Nomor HP', type: 'tel' },
    { name: 'email', label: 'Email Guru', type: 'email' },
    { name: 'jk', label: 'Jenis Kelamin' },
    { name: 'jenis_ptk', label: 'Jenis PTK' },
    { name: 'nuptk', label: 'NUPTK' },
    { name: 'pendidikan', label: 'Pendidikan' },
    { name: 'stts_kepegawaian', label: 'Status Kepegawaian' },
    { name: 'sk_cpns', label: 'SK CPNS' },
    { name: 'tgl_cpns', label: 'Tanggal CPNS', type: 'date' },
    { name: 'sk_pengangkatan', label: 'SK Pengangkatan' },
    { name: 'tmt_pengangkatan', label: 'TMT Pengangkatan', type: 'date' },
    { name: 'pangkat_golongan', label: 'Pangkat/Golongan' },
    { name: 'tmt_pns', label: 'TMT PNS', type: 'date' },
    { name: 'npwp', label: 'NPWP' },
    { name: 'foto', label: 'Path Foto' },
];

const page = usePage();
const guru = computed(() => page.props.guru as Guru);
const emptyForm: GuruForm = {
    name: '',
    user_email: '',
    password: '',
    password_confirmation: '',
    nama: '',
    nip: '',
    tmp_lhr: '',
    tgl_lahir: '',
    alamat: '',
    agama: '',
    nohp_guru: '',
    email: '',
    jk: '',
    jenis_ptk: '',
    nuptk: '',
    pendidikan: '',
    stts_kepegawaian: '',
    sk_cpns: '',
    tgl_cpns: '',
    sk_pengangkatan: '',
    tmt_pengangkatan: '',
    pangkat_golongan: '',
    tmt_pns: '',
    npwp: '',
    foto: '',
};
const form = useForm<GuruForm>({ ...emptyForm });

watch(
    guru,
    (value) => {
        for (const field of fields) {
            form[field.name] =
                value?.[field.name] == null ? '' : String(value[field.name]);
        }

        form.name = value?.user.name ?? '';
        form.user_email = value?.user.email ?? '';
        form.password = '';
        form.password_confirmation = '';
    },
    { immediate: true },
);

const submit = () => {
    form.put(GuruController.update(guru.value.id).url);
};
</script>

<template>
    <Head :title="`Edit Guru - ${guru.nama}`" />
    <div class="max-w-4xl space-y-6 bg-background p-4 text-foreground">
        <div>
            <h1 class="text-2xl font-bold">Edit Guru</h1>
            <p class="text-sm text-muted-foreground">Perbarui data guru.</p>
        </div>
        <div
            class="rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
        >
            <form class="space-y-6" @submit.prevent="submit">
                <section class="space-y-4">
                    <div>
                        <h2 class="text-lg font-semibold">Akun Login</h2>
                        <p class="text-sm text-muted-foreground">
                            Perbarui akun login guru.
                        </p>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <label for="name" class="text-sm font-medium"
                                >Username</label
                            ><Input
                                id="name"
                                v-model="form.name"
                                placeholder="Masukkan username"
                            /><InputError :message="form.errors.name" />
                        </div>
                        <div class="space-y-2">
                            <label for="user_email" class="text-sm font-medium"
                                >Email Akun</label
                            ><Input
                                id="user_email"
                                v-model="form.user_email"
                                type="email"
                                placeholder="Masukkan email akun"
                            /><InputError :message="form.errors.user_email" />
                        </div>
                        <div class="space-y-2">
                            <label for="password" class="text-sm font-medium"
                                >Password Baru</label
                            ><Input
                                id="password"
                                v-model="form.password"
                                type="password"
                                placeholder="Kosongkan jika tidak diubah"
                            /><InputError :message="form.errors.password" />
                        </div>
                        <div class="space-y-2">
                            <label
                                for="password_confirmation"
                                class="text-sm font-medium"
                                >Konfirmasi Password Baru</label
                            ><Input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                placeholder="Konfirmasi password baru"
                            />
                        </div>
                    </div>
                </section>
                <section class="space-y-4 border-t border-border pt-6">
                    <h2 class="text-lg font-semibold">Data Guru</h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div
                            v-for="field in fields"
                            :key="field.name"
                            class="space-y-2"
                        >
                            <label :for="field.name" class="text-sm font-medium"
                                >{{ field.label
                                }}<span v-if="field.required"> *</span></label
                            >
                            <select
                                v-if="field.options"
                                :id="field.name"
                                v-model="form[field.name]"
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
                    <Button type="submit" :disabled="form.processing">{{
                        form.processing ? 'Menyimpan...' : 'Simpan Perubahan'
                    }}</Button
                    ><Button as-child variant="outline"
                        ><Link :href="GuruController.index().url"
                            >Batal</Link
                        ></Button
                    >
                </div>
            </form>
        </div>
    </div>
</template>
