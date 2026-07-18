<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import { dashboard } from '@/routes';

interface SchoolSetting {
    nama_sekolah: string;
    logo_url: string | null;
    alamat: string | null;
    desa: string | null;
    kecamatan: string | null;
    kabupaten: string | null;
    provinsi: string | null;
    nomor_telepon: string | null;
    email: string | null;
    website: string | null;
    visi: string | null;
    misi: string | null;
    tentang: string | null;
    sejarah_singkat: string | null;
    tagline: string | null;
    pendaftaran_dibuka: boolean;
}

const page = usePage();
const setting = computed(() => page.props.setting as SchoolSetting);
const logoPreview = ref<string | null>(setting.value.logo_url);
const form = useForm({
    nama_sekolah: setting.value.nama_sekolah,
    logo: null as File | null,
    alamat: setting.value.alamat ?? '',
    desa: setting.value.desa ?? '',
    kecamatan: setting.value.kecamatan ?? '',
    kabupaten: setting.value.kabupaten ?? '',
    provinsi: setting.value.provinsi ?? '',
    nomor_telepon: setting.value.nomor_telepon ?? '',
    email: setting.value.email ?? '',
    website: setting.value.website ?? '',
    visi: setting.value.visi ?? '',
    misi: setting.value.misi ?? '',
    tentang: setting.value.tentang ?? '',
    sejarah_singkat: setting.value.sejarah_singkat ?? '',
    tagline: setting.value.tagline ?? '',
    pendaftaran_dibuka: setting.value.pendaftaran_dibuka,
});

const onLogoChange = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    form.logo = file;
    logoPreview.value = file
        ? URL.createObjectURL(file)
        : setting.value.logo_url;
};

const submit = () => {
    form.put('/pengaturan-sekolah', { forceFormData: true });
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Pengaturan Sekolah', href: '/pengaturan-sekolah' },
        ],
    },
});
</script>

<template>
    <Head title="Pengaturan Sekolah" />
    <div class="mx-auto max-w-5xl space-y-6 bg-background p-4 text-foreground">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Pengaturan Sekolah</h1>
                <p class="text-sm text-muted-foreground">
                    Kelola identitas sekolah dan status pendaftaran siswa.
                </p>
            </div>
            <Button as-child variant="outline"
                ><Link :href="dashboard()">Kembali</Link></Button
            >
        </div>

        <form class="space-y-6" @submit.prevent="submit">
            <section
                class="rounded-2xl border border-border bg-card p-6 text-card-foreground shadow-sm"
            >
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="font-semibold">Status Pendaftaran</h2>
                        <p class="text-sm text-muted-foreground">
                            Tentukan apakah calon siswa dapat mengirim
                            pendaftaran baru.
                        </p>
                    </div>
                    <label
                        class="inline-flex cursor-pointer items-center gap-3 text-sm font-medium"
                    >
                        <input
                            v-model="form.pendaftaran_dibuka"
                            type="checkbox"
                            class="size-4 rounded border-border text-primary focus:ring-ring"
                        />
                        {{
                            form.pendaftaran_dibuka
                                ? 'Pendaftaran Dibuka'
                                : 'Pendaftaran Ditutup'
                        }}
                    </label>
                </div>
                <InputError :message="form.errors.pendaftaran_dibuka" />
            </section>

            <section
                class="rounded-2xl border border-border bg-card p-6 text-card-foreground shadow-sm"
            >
                <h2 class="mb-5 font-semibold">Identitas Sekolah</h2>
                <div class="grid gap-5 md:grid-cols-2">
                    <div class="space-y-2">
                        <label for="nama_sekolah" class="text-sm font-medium"
                            >Nama Sekolah</label
                        >
                        <Input
                            id="nama_sekolah"
                            v-model="form.nama_sekolah"
                            :class="
                                form.errors.nama_sekolah && 'border-destructive'
                            "
                        />
                        <InputError :message="form.errors.nama_sekolah" />
                    </div>
                    <div class="space-y-2">
                        <label for="tagline" class="text-sm font-medium"
                            >Tagline</label
                        >
                        <Input
                            id="tagline"
                            v-model="form.tagline"
                            :class="form.errors.tagline && 'border-destructive'"
                        />
                        <InputError :message="form.errors.tagline" />
                    </div>
                    <div class="space-y-2 md:col-span-2">
                        <label for="logo" class="text-sm font-medium"
                            >Logo Sekolah</label
                        >
                        <div class="flex items-center gap-4">
                            <img
                                v-if="logoPreview"
                                :src="logoPreview"
                                alt="Logo sekolah"
                                class="size-16 rounded-xl border border-border object-cover"
                            />
                            <div
                                v-else
                                class="flex size-16 items-center justify-center rounded-xl bg-muted text-sm text-muted-foreground"
                            >
                                Logo
                            </div>
                            <Input
                                id="logo"
                                type="file"
                                accept=".jpg,.jpeg,.png,.webp"
                                @change="onLogoChange"
                            />
                        </div>
                        <InputError :message="form.errors.logo" />
                    </div>
                    <div class="space-y-2">
                        <label for="email" class="text-sm font-medium"
                            >Email Sekolah</label
                        ><Input
                            id="email"
                            v-model="form.email"
                            type="email"
                        /><InputError :message="form.errors.email" />
                    </div>
                    <div class="space-y-2">
                        <label for="nomor_telepon" class="text-sm font-medium"
                            >Nomor Telepon</label
                        ><Input
                            id="nomor_telepon"
                            v-model="form.nomor_telepon"
                        /><InputError :message="form.errors.nomor_telepon" />
                    </div>
                    <div class="space-y-2 md:col-span-2">
                        <label for="website" class="text-sm font-medium"
                            >Website</label
                        ><Input
                            id="website"
                            v-model="form.website"
                            type="url"
                        /><InputError :message="form.errors.website" />
                    </div>
                    <div class="space-y-2 md:col-span-2">
                        <label for="alamat" class="text-sm font-medium"
                            >Alamat</label
                        ><textarea
                            id="alamat"
                            v-model="form.alamat"
                            class="min-h-24 w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground"
                        /><InputError :message="form.errors.alamat" />
                    </div>
                    <div class="space-y-2">
                        <label for="desa" class="text-sm font-medium"
                            >Desa / Kelurahan</label
                        >
                        <Input id="desa" v-model="form.desa" />
                        <InputError :message="form.errors.desa" />
                    </div>
                    <div class="space-y-2">
                        <label for="kecamatan" class="text-sm font-medium"
                            >Kecamatan</label
                        >
                        <Input id="kecamatan" v-model="form.kecamatan" />
                        <InputError :message="form.errors.kecamatan" />
                    </div>
                    <div class="space-y-2">
                        <label for="kabupaten" class="text-sm font-medium"
                            >Kabupaten / Kota</label
                        >
                        <Input id="kabupaten" v-model="form.kabupaten" />
                        <InputError :message="form.errors.kabupaten" />
                    </div>
                    <div class="space-y-2">
                        <label for="provinsi" class="text-sm font-medium"
                            >Provinsi</label
                        >
                        <Input id="provinsi" v-model="form.provinsi" />
                        <InputError :message="form.errors.provinsi" />
                    </div>
                </div>
            </section>

            <section
                class="rounded-2xl border border-border bg-card p-6 text-card-foreground shadow-sm"
            >
                <h2 class="mb-5 font-semibold">Profil Sekolah</h2>
                <div class="space-y-5">
                    <div class="space-y-2">
                        <label for="visi" class="text-sm font-medium"
                            >Visi</label
                        ><textarea
                            id="visi"
                            v-model="form.visi"
                            class="min-h-24 w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground"
                        /><InputError :message="form.errors.visi" />
                    </div>
                    <div class="space-y-2">
                        <label for="misi" class="text-sm font-medium"
                            >Misi</label
                        ><textarea
                            id="misi"
                            v-model="form.misi"
                            class="min-h-32 w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground"
                        /><InputError :message="form.errors.misi" />
                    </div>
                    <div class="space-y-2">
                        <label for="tentang" class="text-sm font-medium"
                            >Tentang Sekolah</label
                        ><textarea
                            id="tentang"
                            v-model="form.tentang"
                            class="min-h-32 w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground"
                        /><InputError :message="form.errors.tentang" />
                    </div>
                    <div class="space-y-2">
                        <label for="sejarah_singkat" class="text-sm font-medium"
                            >Sejarah Singkat</label
                        ><textarea
                            id="sejarah_singkat"
                            v-model="form.sejarah_singkat"
                            class="min-h-32 w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground"
                        /><InputError :message="form.errors.sejarah_singkat" />
                    </div>
                </div>
            </section>
            <div class="flex justify-end">
                <Button type="submit" :disabled="form.processing">{{
                    form.processing ? 'Menyimpan...' : 'Simpan Pengaturan'
                }}</Button>
            </div>
        </form>
    </div>
</template>
