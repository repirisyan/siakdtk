<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import KontenController from '@/actions/App/Http/Controllers/KontenController';
import InputError from '@/components/InputError.vue';
import KontenForm from '@/components/KontenForm.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';

interface Galeri {
    id: number;
    gambar: string;
    caption: string | null;
    urutan: number;
}
interface Konten {
    id: number;
    jenis_konten: string;
    judul: string;
    ringkasan: string | null;
    konten: string | null;
    tanggal_publish: string | null;
    status: string;
    tanggal_event: string | null;
    jam_mulai: string | null;
    jam_selesai: string | null;
    lokasi: string | null;
    galeris: Galeri[];
}
const page = usePage();
const konten = computed(() => page.props.konten as Konten);
const dateTimeLocal = (value: string | null) =>
    value ? value.slice(0, 16) : '';
const formState = useForm({
    jenis_konten: konten.value.jenis_konten,
    judul: konten.value.judul,
    ringkasan: konten.value.ringkasan ?? '',
    konten: konten.value.konten ?? '',
    thumbnail: null as File | null,
    tanggal_publish: dateTimeLocal(konten.value.tanggal_publish),
    status: konten.value.status,
    tanggal_event: konten.value.tanggal_event ?? '',
    jam_mulai: konten.value.jam_mulai?.slice(0, 5) ?? '',
    jam_selesai: konten.value.jam_selesai?.slice(0, 5) ?? '',
    lokasi: konten.value.lokasi ?? '',
});
const form = computed({
    get: () => formState,
    set: (value) => Object.assign(formState, value),
});
const galleryForm = useForm({
    gambar: [] as File[],
    caption: [] as string[],
    urutan: [] as number[],
});
const galleryFiles = ref<File[]>([]);
const setGalleryFiles = (event: Event) => {
    galleryFiles.value = Array.from(
        (event.target as HTMLInputElement).files ?? [],
    );
    galleryForm.gambar = galleryFiles.value;
    galleryForm.caption = galleryFiles.value.map(() => '');
    galleryForm.urutan = galleryFiles.value.map((_, index) => index + 1);
};
const submit = () =>
    formState.put(KontenController.update(konten.value.id).url, {
        forceFormData: true,
    });
const uploadGallery = () =>
    galleryForm.post(`/konten/${konten.value.id}/galeri`, {
        forceFormData: true,
        onSuccess: () => {
            galleryFiles.value = [];
        },
    });
const removeGallery = (id: number) => {
    if (confirm('Hapus foto ini?')) {
        router.delete(`/konten/${konten.value.id}/galeri/${id}`);
    }
};
</script>

<template>
    <Head title="Edit Konten" />
    <div class="max-w-4xl space-y-6 p-4">
        <div>
            <h1 class="text-2xl font-bold text-foreground">Edit Konten</h1>
            <p class="text-sm text-muted-foreground">
                Perbarui informasi konten sekolah.
            </p>
        </div>
        <form
            class="space-y-6 rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
            @submit.prevent="submit"
        >
            <KontenForm v-model:form="form" />
            <div class="flex gap-2">
                <Button type="submit" :disabled="formState.processing">{{
                    formState.processing ? 'Menyimpan...' : 'Simpan Perubahan'
                }}</Button
                ><Button as-child variant="outline"
                    ><Link :href="KontenController.index().url"
                        >Batal</Link
                    ></Button
                >
            </div>
        </form>
        <section
            v-if="konten.jenis_konten === 'galeri'"
            class="space-y-5 rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
        >
            <div>
                <h2 class="text-lg font-semibold">Foto Galeri</h2>
                <p class="text-sm text-muted-foreground">
                    Tambahkan beberapa foto untuk galeri ini.
                </p>
            </div>
            <form class="space-y-4" @submit.prevent="uploadGallery">
                <Input
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    multiple
                    @change="setGalleryFiles"
                />
                <div
                    v-for="(_, index) in galleryFiles"
                    :key="index"
                    class="grid gap-3 sm:grid-cols-2"
                >
                    <div class="space-y-2">
                        <label class="text-sm font-medium"
                            >Caption foto {{ index + 1 }}</label
                        ><Input v-model="galleryForm.caption[index]" />
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Urutan</label>
                        <Input
                            v-model.number="galleryForm.urutan[index]"
                            type="number"
                            min="0"
                        />
                    </div>
                </div>
                <InputError :message="galleryForm.errors.gambar" /><Button
                    type="submit"
                    :disabled="galleryForm.processing || !galleryFiles.length"
                    >{{
                        galleryForm.processing ? 'Mengunggah...' : 'Upload Foto'
                    }}</Button
                >
            </form>
            <div
                v-if="konten.galeris.length"
                class="grid gap-4 sm:grid-cols-2 md:grid-cols-3"
            >
                <article
                    v-for="galeri in konten.galeris"
                    :key="galeri.id"
                    class="overflow-hidden rounded-lg border border-border"
                >
                    <img
                        :src="`/storage/${galeri.gambar}`"
                        :alt="galeri.caption ?? 'Foto galeri'"
                        class="h-36 w-full object-cover"
                    />
                    <div class="space-y-2 p-3">
                        <p class="text-sm text-muted-foreground">
                            {{ galeri.caption || 'Tanpa caption' }}
                        </p>
                        <Button
                            variant="destructive"
                            size="sm"
                            @click="removeGallery(galeri.id)"
                            >Hapus</Button
                        >
                    </div>
                </article>
            </div>
        </section>
    </div>
</template>
