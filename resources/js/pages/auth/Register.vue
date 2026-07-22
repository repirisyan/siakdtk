<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    CheckCircle2,
    ClipboardList,
    LockKeyhole,
    School,
    ShieldCheck,
    Upload,
} from '@lucide/vue';
import {
    computed,
    ref
    
    
} from 'vue';
import type {Component, ComponentPublicInstance} from 'vue';

import AppLogoIcon from '@/components/AppLogoIcon.vue';
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';

interface RegistrationForm {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    foto_profil: File | null;
    akta_kelahiran_file: File | null;
    kartu_keluarga_file: File | null;
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
    nohp_wali: string;
    ttl_wali: string;
    agama_wali: string;
    pekerjaan_wali: string;
    penghasilan_wali: string;
    alamat_wali: string;
    desa: string;
    kecamatan: string;
    kabupaten: string;
    provinsi: string;
}
type RegistrationField = Exclude<
    keyof RegistrationForm,
    'foto_profil' | 'akta_kelahiran_file' | 'kartu_keluarga_file'
>;
interface Field {
    name: RegistrationField;
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
const JENIS_KELAMIN_OPTIONS = ['Laki-laki', 'Perempuan'] as const;
const studentFields: Field[] = [
    { name: 'nik', label: 'NIK', required: true },
    { name: 'nomor_kk', label: 'Nomor KK', required: true },
    { name: 'nama', label: 'Nama Lengkap', required: true },
    { name: 'nama_pgl', label: 'Nama Panggilan' },
    { name: 'tmp_lahir', label: 'Tempat Lahir', required: true },
    { name: 'tgl_lahir', label: 'Tanggal Lahir', type: 'date', required: true },
    {
        name: 'jk',
        label: 'Jenis Kelamin',
        required: true,
        options: JENIS_KELAMIN_OPTIONS,
    },
    { name: 'agama', label: 'Agama', required: true, options: AGAMA_OPTIONS },
    { name: 'tinggi_bdn', label: 'Tinggi Badan (cm)', type: 'number' },
    { name: 'berat_bdn', label: 'Berat Badan (kg)', type: 'number' },
    { name: 'anak_ke', label: 'Anak Ke' },
    { name: 'jml_sdr', label: 'Jumlah Saudara' },
];
const fatherFields: Field[] = [
    { name: 'nama_ayah', label: 'Nama Ayah' },
    { name: 'nohp_ayah', label: 'No. HP Ayah', type: 'tel' },
    { name: 'pekerjaan', label: 'Pekerjaan Ayah' },
    { name: 'penghasilan', label: 'Penghasilan Ayah' },
    { name: 'ttl_ayah', label: 'Tanggal Lahir Ayah', type: 'date' },
    { name: 'agama_ayah', label: 'Agama Ayah', options: AGAMA_OPTIONS },
];
const motherFields: Field[] = [
    { name: 'nama_ibu', label: 'Nama Ibu' },
    { name: 'nohp_ibu', label: 'No. HP Ibu', type: 'tel' },
    { name: 'pekerjaan_ibu', label: 'Pekerjaan Ibu' },
    { name: 'penghasilan_ibu', label: 'Penghasilan Ibu' },
    { name: 'ttl_ibu', label: 'Tanggal Lahir Ibu', type: 'date' },
    { name: 'agama_ibu', label: 'Agama Ibu', options: AGAMA_OPTIONS },
];
const guardianFields: Field[] = [
    { name: 'nama_wali', label: 'Nama Wali' },
    { name: 'nohp_wali', label: 'No. HP Wali', type: 'tel' },
    { name: 'ttl_wali', label: 'Tanggal Lahir Wali', type: 'date' },
    { name: 'agama_wali', label: 'Agama Wali', options: AGAMA_OPTIONS },
    { name: 'pekerjaan_wali', label: 'Pekerjaan Wali' },
    { name: 'penghasilan_wali', label: 'Penghasilan Wali' },
    { name: 'desa', label: 'Desa / Kelurahan' },
    { name: 'kecamatan', label: 'Kecamatan' },
    { name: 'kabupaten', label: 'Kabupaten / Kota' },
    { name: 'provinsi', label: 'Provinsi' },
];
const registrationSteps: { label: string; icon: Component }[] = [
    { label: 'Data Akun', icon: LockKeyhole },
    { label: 'Data Siswa', icon: School },
    { label: 'Orang Tua', icon: ClipboardList },
    { label: 'Upload Foto', icon: Upload },
];

const page = usePage();
const registrationOpen = computed(
    () => (page.props.registrationOpen as boolean) ?? true,
);
const existingParent = computed(
    () => (page.props.existingParent as boolean) ?? false,
);
const school = computed(
    () =>
        page.props.school as {
            nama_sekolah?: string;
            tagline?: string | null;
            logo_url?: string | null;
        } | null,
);
const form = useForm<RegistrationForm>({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    foto_profil: null,
    akta_kelahiran_file: null,
    kartu_keluarga_file: null,
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
    nohp_wali: '',
    ttl_wali: '',
    agama_wali: '',
    pekerjaan_wali: '',
    penghasilan_wali: '',
    alamat_wali: '',
    desa: '',
    kecamatan: '',
    kabupaten: '',
    provinsi: '',
});
const photoPreview = ref<string | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);
const onPhotoChange = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    form.foto_profil = file;
    photoPreview.value = file ? URL.createObjectURL(file) : null;
};
const onDocumentChange = (
    field: 'akta_kelahiran_file' | 'kartu_keluarga_file',
    event: Event,
) => {
    form[field] = (event.target as HTMLInputElement).files?.[0] ?? null;
};
const inputClass = (field: RegistrationField) =>
    form.errors[field]
        ? 'border-destructive focus-visible:ring-destructive/20'
        : '';

/**
 * ----- Stepper state & logic -----
 */
const currentStep = ref(0);
const totalSteps = registrationSteps.length;
const stepRefs = ref<(HTMLElement | null)[]>([]);

const setStepRef =
    (index: number) => (el: Element | ComponentPublicInstance | null) => {
        stepRefs.value[index] = (el as HTMLElement) ?? null;
    };

/** Validates only the fields visible in the given step, using native HTML validity. */
const isStepValid = (index: number) => {
    const container = stepRefs.value[index];

    if (!container) {
return true;
}

    const fields = container.querySelectorAll<
        HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement
    >('input, select, textarea');

    for (const field of fields) {
        if (!field.checkValidity()) {
            field.reportValidity();

            return false;
        }
    }

    return true;
};

const nextStep = () => {
    if (!isStepValid(currentStep.value)) {
return;
}

    if (currentStep.value < totalSteps - 1) {
currentStep.value += 1;
}
};
const prevStep = () => {
    if (currentStep.value > 0) {
currentStep.value -= 1;
}
};
/** Only allow jumping back to a step already completed, not skipping ahead. */
const goToStep = (index: number) => {
    if (index < currentStep.value) {
currentStep.value = index;
}
};

/**
 * ----- Submit & success handling -----
 */
const showSuccess = ref(false);
let successTimeout: ReturnType<typeof setTimeout> | null = null;

const submit = () => {
    if (!registrationOpen.value) {
return;
}

    if (!isStepValid(currentStep.value)) {
return;
}

    form.post(existingParent.value ? '/tambah-anak' : store().url, {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            form.clearErrors();
            photoPreview.value = null;

            if (fileInputRef.value) {
fileInputRef.value.value = '';
}

            currentStep.value = 0;
            showSuccess.value = true;

            if (successTimeout) {
clearTimeout(successTimeout);
}

            successTimeout = setTimeout(() => {
                showSuccess.value = false;
            }, 6000);
        },
    });
};
</script>

<template>
    <Head title="Pendaftaran Siswa Baru" />
    <main class="min-h-svh bg-background p-4 text-foreground md:p-8">
        <div
            class="mx-auto grid min-h-[calc(100svh-2rem)] max-w-360 overflow-hidden rounded-3xl border border-border bg-card shadow-xl shadow-primary/5 lg:grid-cols-[.85fr_1.15fr]"
        >
            <aside
                class="relative overflow-hidden bg-primary p-8 text-primary-foreground md:p-12"
            >
                <div
                    class="absolute -top-24 -right-20 size-72 rounded-full bg-primary-foreground/10"
                />
                <div class="relative flex h-full flex-col">
                    <Link :href="login()" class="flex items-center gap-3"
                        ><span
                            class="flex size-11 items-center justify-center rounded-2xl bg-primary-foreground/15"
                            ><img
                                v-if="school?.logo_url"
                                :src="school.logo_url"
                                :alt="school.nama_sekolah ?? 'Logo sekolah'"
                                class="size-7 rounded-lg object-contain" /><AppLogoIcon
                                v-else
                                class="size-6 fill-current" /></span
                        ><span
                            ><strong class="block text-lg">{{
                                school?.nama_sekolah ?? 'SIAKDTK'
                            }}</strong
                            ><span class="text-sm text-primary-foreground/75">{{
                                school?.tagline ??
                                'Sistem Informasi Akademik TK'
                            }}</span></span
                        ></Link
                    >
                    <div class="my-auto py-14">
                        <p
                            class="mb-4 text-sm font-semibold tracking-widest text-primary-foreground/75 uppercase"
                        >
                            Pendaftaran Siswa Baru
                        </p>
                        <h1
                            class="max-w-md text-3xl font-bold tracking-tight md:text-4xl"
                        >
                            Mulai perjalanan belajar anak bersama kami.
                        </h1>
                        <p
                            class="mt-5 max-w-lg leading-7 text-primary-foreground/80"
                        >
                            Pendaftaran siswa baru dapat dilakukan secara
                            online. Lengkapi data calon siswa dengan benar agar
                            proses verifikasi berjalan lancar.
                        </p>
                        <ul class="mt-10 space-y-4 text-sm">
                            <li
                                v-for="item in [
                                    'Pendaftaran online yang mudah',
                                    'Data tersimpan dengan aman',
                                    'Verifikasi oleh pihak sekolah',
                                    'Informasi pendaftaran dapat dipantau',
                                ]"
                                :key="item"
                                class="flex items-center gap-3"
                            >
                                <CheckCircle2 class="size-5 shrink-0" />{{
                                    item
                                }}
                            </li>
                        </ul>
                    </div>
                    <p
                        v-if="!existingParent"
                        class="text-sm text-primary-foreground/75"
                    >
                        Sudah memiliki akun?
                        <Link
                            :href="login()"
                            class="font-semibold text-primary-foreground underline underline-offset-4"
                            >Masuk</Link
                        >
                    </p>
                </div>
            </aside>
            <section class="bg-background p-5 sm:p-8 md:p-10">
                <div class="mx-auto max-w-4xl">
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold">
                            Form Pendaftaran Calon Siswa
                        </h2>
                        <p class="mt-2 text-sm text-muted-foreground">
                            Isi data secara lengkap. Field bertanda
                            <span class="font-semibold text-destructive"
                                >*</span
                            >
                            wajib diisi.
                        </p>
                    </div>
                    <div
                        v-if="!registrationOpen"
                        class="mb-6 rounded-xl border border-destructive/30 bg-destructive/10 p-4 text-sm text-destructive"
                    >
                        Pendaftaran siswa saat ini sedang ditutup oleh pihak
                        sekolah. Form dapat dibaca, tetapi belum dapat dikirim.
                    </div>
                    <div
                        v-if="showSuccess"
                        class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200"
                    >
                        <CheckCircle2 class="mt-0.5 size-5 shrink-0" />
                        <div>
                            <p class="font-semibold">
                                Pendaftaran berhasil dikirim!
                            </p>
                            <p class="text-emerald-700 dark:text-emerald-300">
                                Data calon siswa telah kami terima dan akan
                                diverifikasi oleh Admin atau Staff Akademik.
                                Anda bisa mengisi form baru jika perlu
                                mendaftarkan siswa lain.
                            </p>
                        </div>
                        <button
                            type="button"
                            class="ml-auto text-emerald-700 hover:text-emerald-900 dark:text-emerald-300"
                            aria-label="Tutup notifikasi"
                            @click="showSuccess = false"
                        >
                            &times;
                        </button>
                    </div>
                    <div
                        class="mb-8 grid grid-cols-4 gap-2 text-center text-xs sm:text-sm"
                    >
                        <button
                            v-for="(step, index) in registrationSteps"
                            :key="step.label"
                            type="button"
                            class="space-y-2"
                            :class="
                                index <= currentStep
                                    ? 'cursor-pointer'
                                    : 'cursor-not-allowed opacity-50'
                            "
                            @click="goToStep(index)"
                        >
                            <div
                                class="mx-auto flex size-9 items-center justify-center rounded-full transition-colors"
                                :class="
                                    index === currentStep
                                        ? 'bg-primary text-primary-foreground'
                                        : index < currentStep
                                          ? 'bg-primary/15 text-primary'
                                          : 'bg-secondary text-secondary-foreground'
                                "
                            >
                                <CheckCircle2
                                    v-if="index < currentStep"
                                    class="size-4"
                                />
                                <component
                                    :is="step.icon"
                                    v-else
                                    class="size-4"
                                />
                            </div>
                            <p
                                class="font-medium"
                                :class="
                                    index === currentStep
                                        ? 'text-foreground'
                                        : 'text-muted-foreground'
                                "
                            >
                                {{ step.label }}
                            </p>
                        </button>
                    </div>
                    <form class="space-y-9" @submit.prevent="submit">
                        <section
                            v-show="currentStep === 0"
                            :ref="setStepRef(0)"
                            class="space-y-5"
                        >
                            <div
                                v-if="existingParent"
                                class="rounded-xl border border-border bg-muted/50 p-4 text-sm text-muted-foreground"
                            >
                                Anda menggunakan akun orang tua yang sedang
                                login. Data akun tidak perlu dibuat kembali
                                untuk mendaftarkan anak berikutnya.
                            </div>
                            <div v-else>
                                <h3 class="text-lg font-semibold">
                                    1. Data Akun Orang Tua
                                </h3>
                                <p class="text-sm text-muted-foreground">
                                    Akun dapat digunakan setelah pendaftaran
                                    disetujui sekolah.
                                </p>
                            </div>
                            <div
                                v-if="!existingParent"
                                class="grid gap-5 md:grid-cols-2"
                            >
                                <div class="space-y-2">
                                    <label
                                        for="name"
                                        class="text-sm font-medium"
                                        >Nama Akun
                                        <span class="text-destructive"
                                            >*</span
                                        ></label
                                    ><Input
                                        id="name"
                                        v-model="form.name"
                                        required
                                        autofocus
                                        :aria-invalid="
                                            Boolean(form.errors.name)
                                        "
                                        :class="inputClass('name')"
                                    /><InputError :message="form.errors.name" />
                                </div>
                                <div class="space-y-2">
                                    <label
                                        for="email"
                                        class="text-sm font-medium"
                                        >Email
                                        <span class="text-destructive"
                                            >*</span
                                        ></label
                                    ><Input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        required
                                        :aria-invalid="
                                            Boolean(form.errors.email)
                                        "
                                        :class="inputClass('email')"
                                    /><InputError
                                        :message="form.errors.email"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <label
                                        for="password"
                                        class="text-sm font-medium"
                                        >Password
                                        <span class="text-destructive"
                                            >*</span
                                        ></label
                                    ><Input
                                        id="password"
                                        v-model="form.password"
                                        type="password"
                                        required
                                        :aria-invalid="
                                            Boolean(form.errors.password)
                                        "
                                        :class="inputClass('password')"
                                    /><InputError
                                        :message="form.errors.password"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <label
                                        for="password_confirmation"
                                        class="text-sm font-medium"
                                        >Konfirmasi Password
                                        <span class="text-destructive"
                                            >*</span
                                        ></label
                                    ><Input
                                        id="password_confirmation"
                                        v-model="form.password_confirmation"
                                        type="password"
                                        required
                                        :aria-invalid="
                                            Boolean(
                                                form.errors
                                                    .password_confirmation,
                                            )
                                        "
                                        :class="
                                            inputClass('password_confirmation')
                                        "
                                    /><InputError
                                        :message="
                                            form.errors.password_confirmation
                                        "
                                    />
                                </div>
                            </div>
                        </section>
                        <section
                            v-show="currentStep === 1"
                            :ref="setStepRef(1)"
                            class="pt-2"
                        >
                            <div class="mb-5">
                                <h3 class="text-lg font-semibold">
                                    2. Data Siswa
                                </h3>
                                <p class="text-sm text-muted-foreground">
                                    Informasi identitas calon siswa.
                                </p>
                            </div>
                            <div class="grid gap-5 md:grid-cols-2">
                                <template
                                    v-for="field in studentFields"
                                    :key="field.name"
                                    ><div class="space-y-2">
                                        <label
                                            :for="field.name"
                                            class="text-sm font-medium"
                                            >{{ field.label }}
                                            <span
                                                v-if="field.required"
                                                class="text-destructive"
                                                >*</span
                                            ></label
                                        ><select
                                            v-if="field.options"
                                            :id="field.name"
                                            v-model="form[field.name]"
                                            :required="field.required"
                                            class="h-10 w-full rounded-lg border border-border bg-background px-3 text-sm text-foreground"
                                            :class="inputClass(field.name)"
                                        >
                                            <option value="">
                                                Pilih {{ field.label }}
                                            </option>
                                            <option
                                                v-for="option in field.options"
                                                :key="option"
                                                :value="option"
                                            >
                                                {{ option }}
                                            </option></select
                                        ><Input
                                            v-else
                                            :id="field.name"
                                            v-model="form[field.name]"
                                            :type="field.type ?? 'text'"
                                            :required="field.required"
                                            :placeholder="`Masukkan ${field.label.toLowerCase()}`"
                                            :aria-invalid="
                                                Boolean(form.errors[field.name])
                                            "
                                            :class="inputClass(field.name)"
                                        /><InputError
                                            :message="form.errors[field.name]"
                                        /></div
                                ></template>
                                <div class="space-y-2 md:col-span-2">
                                    <label
                                        for="alamat"
                                        class="text-sm font-medium"
                                        >Alamat
                                        <span class="text-destructive"
                                            >*</span
                                        ></label
                                    ><textarea
                                        id="alamat"
                                        v-model="form.alamat"
                                        required
                                        class="min-h-28 w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50"
                                        :class="inputClass('alamat')"
                                        placeholder="Masukkan alamat lengkap"
                                    /><InputError
                                        :message="form.errors.alamat"
                                    />
                                </div>
                            </div>
                        </section>
                        <section
                            v-show="currentStep === 2"
                            :ref="setStepRef(2)"
                            class="pt-2"
                        >
                            <div class="mb-5">
                                <h3 class="text-lg font-semibold">
                                    3. Data Orang Tua
                                </h3>
                                <p class="text-sm text-muted-foreground">
                                    Lengkapi data ayah dan ibu calon siswa.
                                </p>
                            </div>
                            <div class="grid gap-6 lg:grid-cols-2">
                                <div
                                    class="space-y-5 rounded-2xl bg-muted/60 p-5"
                                >
                                    <h4 class="font-semibold">Data Ayah</h4>
                                    <div
                                        v-for="field in fatherFields"
                                        :key="field.name"
                                        class="space-y-2"
                                    >
                                        <label
                                            :for="field.name"
                                            class="text-sm font-medium"
                                            >{{ field.label }}</label
                                        ><select
                                            v-if="field.options"
                                            :id="field.name"
                                            v-model="form[field.name]"
                                            class="h-10 w-full rounded-lg border border-border bg-background px-3 text-sm text-foreground"
                                            :class="inputClass(field.name)"
                                        >
                                            <option value="">
                                                Pilih {{ field.label }}
                                            </option>
                                            <option
                                                v-for="option in field.options"
                                                :key="option"
                                                :value="option"
                                            >
                                                {{ option }}
                                            </option></select
                                        ><Input
                                            v-else
                                            :id="field.name"
                                            v-model="form[field.name]"
                                            :type="field.type ?? 'text'"
                                            :aria-invalid="
                                                Boolean(form.errors[field.name])
                                            "
                                            :class="inputClass(field.name)"
                                        /><InputError
                                            :message="form.errors[field.name]"
                                        />
                                    </div>
                                </div>
                                <div
                                    class="space-y-5 rounded-2xl bg-muted/60 p-5"
                                >
                                    <h4 class="font-semibold">Data Ibu</h4>
                                    <div
                                        v-for="field in motherFields"
                                        :key="field.name"
                                        class="space-y-2"
                                    >
                                        <label
                                            :for="field.name"
                                            class="text-sm font-medium"
                                            >{{ field.label }}</label
                                        ><select
                                            v-if="field.options"
                                            :id="field.name"
                                            v-model="form[field.name]"
                                            class="h-10 w-full rounded-lg border border-border bg-background px-3 text-sm text-foreground"
                                            :class="inputClass(field.name)"
                                        >
                                            <option value="">
                                                Pilih {{ field.label }}
                                            </option>
                                            <option
                                                v-for="option in field.options"
                                                :key="option"
                                                :value="option"
                                            >
                                                {{ option }}
                                            </option></select
                                        ><Input
                                            v-else
                                            :id="field.name"
                                            v-model="form[field.name]"
                                            :type="field.type ?? 'text'"
                                            :aria-invalid="
                                                Boolean(form.errors[field.name])
                                            "
                                            :class="inputClass(field.name)"
                                        /><InputError
                                            :message="form.errors[field.name]"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="mt-6 rounded-2xl bg-muted/60 p-5">
                                <h4 class="font-semibold">Data Wali</h4>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    Isi apabila wali siswa berbeda dari ayah
                                    atau ibu.
                                </p>
                                <div class="mt-5 grid gap-5 md:grid-cols-2">
                                    <div
                                        v-for="field in guardianFields"
                                        :key="field.name"
                                        class="space-y-2"
                                    >
                                        <label
                                            :for="field.name"
                                            class="text-sm font-medium"
                                            >{{ field.label }}</label
                                        >
                                        <select
                                            v-if="field.options"
                                            :id="field.name"
                                            v-model="form[field.name]"
                                            class="h-10 w-full rounded-lg border border-border bg-background px-3 text-sm text-foreground"
                                            :class="inputClass(field.name)"
                                        >
                                            <option value="">
                                                Pilih {{ field.label }}
                                            </option>
                                            <option
                                                v-for="option in field.options"
                                                :key="option"
                                                :value="option"
                                            >
                                                {{ option }}
                                            </option>
                                        </select>
                                        <Input
                                            v-else
                                            :id="field.name"
                                            v-model="form[field.name]"
                                            :type="field.type ?? 'text'"
                                            :class="inputClass(field.name)"
                                        />
                                        <InputError
                                            :message="form.errors[field.name]"
                                        />
                                    </div>
                                    <div class="space-y-2 md:col-span-2">
                                        <label
                                            for="alamat_wali"
                                            class="text-sm font-medium"
                                            >Alamat Wali</label
                                        >
                                        <textarea
                                            id="alamat_wali"
                                            v-model="form.alamat_wali"
                                            class="min-h-24 w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground"
                                            :class="inputClass('alamat_wali')"
                                        />
                                        <InputError
                                            :message="form.errors.alamat_wali"
                                        />
                                    </div>
                                </div>
                            </div>
                        </section>
                        <section
                            v-show="currentStep === 3"
                            :ref="setStepRef(3)"
                            class="pt-2"
                        >
                            <h3 class="text-lg font-semibold">
                                4. Foto dan Dokumen Siswa
                            </h3>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Foto membantu sekolah mengidentifikasi calon
                                siswa saat verifikasi.
                            </p>
                            <div class="mt-5 grid gap-6 md:grid-cols-2">
                                <div class="space-y-3">
                                    <label
                                        for="foto_profil"
                                        class="text-sm font-medium"
                                        >Foto Siswa</label
                                    >
                                    <div class="flex items-center gap-4">
                                        <img
                                            v-if="photoPreview"
                                            :src="photoPreview"
                                            alt="Preview foto siswa"
                                            class="size-16 rounded-2xl border border-border object-cover"
                                        />
                                        <div
                                            v-else
                                            class="flex size-16 items-center justify-center rounded-2xl bg-muted text-muted-foreground"
                                        >
                                            <Upload class="size-5" />
                                        </div>
                                        <input
                                            id="foto_profil"
                                            ref="fileInputRef"
                                            type="file"
                                            accept=".jpg,.jpeg,.png,.webp"
                                            class="block w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground"
                                            @change="onPhotoChange"
                                        />
                                    </div>
                                    <p class="text-xs text-muted-foreground">
                                        JPG, JPEG, PNG, atau WEBP. Maksimal 5
                                        MB.
                                    </p>
                                    <InputError
                                        :message="form.errors.foto_profil"
                                    />
                                </div>
                            </div>
                            <div class="mt-6 grid gap-5 md:grid-cols-2">
                                <div class="space-y-2">
                                    <label
                                        for="akta_kelahiran_file"
                                        class="text-sm font-medium"
                                        >Akta Kelahiran
                                        <span class="text-destructive"
                                            >*</span
                                        ></label
                                    >
                                    <input
                                        id="akta_kelahiran_file"
                                        type="file"
                                        required
                                        accept=".pdf,.jpg,.jpeg,.png,.webp"
                                        class="block w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground"
                                        @change="
                                            onDocumentChange(
                                                'akta_kelahiran_file',
                                                $event,
                                            )
                                        "
                                    />
                                    <InputError
                                        :message="
                                            form.errors.akta_kelahiran_file
                                        "
                                    />
                                </div>
                                <div class="space-y-2">
                                    <label
                                        for="kartu_keluarga_file"
                                        class="text-sm font-medium"
                                        >Kartu Keluarga
                                        <span class="text-destructive"
                                            >*</span
                                        ></label
                                    >
                                    <input
                                        id="kartu_keluarga_file"
                                        type="file"
                                        required
                                        accept=".pdf,.jpg,.jpeg,.png,.webp"
                                        class="block w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground"
                                        @change="
                                            onDocumentChange(
                                                'kartu_keluarga_file',
                                                $event,
                                            )
                                        "
                                    />
                                    <InputError
                                        :message="
                                            form.errors.kartu_keluarga_file
                                        "
                                    />
                                </div>
                            </div>
                            <p class="mt-3 text-xs text-muted-foreground">
                                Dokumen dapat berupa PDF, JPG, JPEG, PNG, atau
                                WEBP dengan ukuran maksimal 5 MB per file.
                            </p>
                            <div
                                class="mt-6 flex gap-3 rounded-2xl border border-border bg-muted/60 p-4 text-sm text-muted-foreground"
                            >
                                <ShieldCheck
                                    class="mt-0.5 size-5 shrink-0 text-primary"
                                />
                                <p>
                                    Setelah pendaftaran berhasil, data akan
                                    diverifikasi oleh Admin atau Staff Akademik
                                    sebelum akun diaktifkan.
                                </p>
                            </div>
                        </section>
                        <div
                            class="flex items-center justify-between gap-3 border-t border-border pt-6"
                        >
                            <Button
                                v-if="currentStep > 0"
                                type="button"
                                variant="outline"
                                class="h-11"
                                @click="prevStep"
                                >Kembali</Button
                            >
                            <span v-else />
                            <Button
                                v-if="currentStep < totalSteps - 1"
                                type="button"
                                class="h-11"
                                @click="nextStep"
                                >Lanjutkan</Button
                            >
                            <Button
                                v-else
                                type="submit"
                                class="h-11"
                                :disabled="form.processing || !registrationOpen"
                                >{{
                                    form.processing
                                        ? 'Mengirim Pendaftaran...'
                                        : 'Kirim Pendaftaran Siswa'
                                }}</Button
                            >
                        </div>
                        <p class="text-center text-sm text-muted-foreground">
                            Sudah memiliki akun?
                            <Link
                                :href="login()"
                                class="font-semibold text-primary underline underline-offset-4"
                                >Masuk</Link
                            >
                        </p>
                    </form>
                </div>
            </section>
        </div>
    </main>
</template>
