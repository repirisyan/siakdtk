<script setup lang="ts">
import { computed } from 'vue';

import InputError from '@/components/InputError.vue';
import KontenEditor from '@/components/KontenEditor.vue';
import Input from '@/components/ui/input/Input.vue';

interface KontenFormModel {
    jenis_konten: string;
    judul: string;
    ringkasan: string;
    konten: string;
    thumbnail: File | null;
    tanggal_publish: string;
    status: string;
    tanggal_event: string;
    jam_mulai: string;
    jam_selesai: string;
    lokasi: string;
    errors: Record<string, string | undefined>;
}

const form = defineModel<KontenFormModel>('form', { required: true });

const fieldModel = <K extends keyof KontenFormModel>(field: K) =>
    computed({
        get: () => form.value[field],
        set: (value: KontenFormModel[K]) => {
            form.value = { ...form.value, [field]: value };
        },
    });

const jenisKonten = fieldModel('jenis_konten');
const judul = fieldModel('judul');
const ringkasan = fieldModel('ringkasan');
const konten = fieldModel('konten');
const tanggalPublish = fieldModel('tanggal_publish');
const status = fieldModel('status');
const tanggalEvent = fieldModel('tanggal_event');
const jamMulai = fieldModel('jam_mulai');
const jamSelesai = fieldModel('jam_selesai');
const lokasi = fieldModel('lokasi');
const isEvent = computed(() => jenisKonten.value === 'event');
const setThumbnail = (event: Event) => {
    form.value = {
        ...form.value,
        thumbnail: (event.target as HTMLInputElement).files?.[0] ?? null,
    };
};
</script>

<template>
    <div class="grid gap-6 md:grid-cols-2">
        <div class="space-y-2">
            <label for="jenis_konten" class="text-sm font-medium"
                >Jenis Konten</label
            >
            <select
                id="jenis_konten"
                v-model="jenisKonten"
                class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground"
            >
                <option value="berita">Berita</option>
                <option value="event">Event</option>
                <option value="pengumuman">Pengumuman</option>
                <option value="galeri">Galeri</option></select
            ><InputError :message="form.errors.jenis_konten" />
        </div>
        <div class="space-y-2">
            <label for="status" class="text-sm font-medium">Status</label
            ><select
                id="status"
                v-model="status"
                class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground"
            >
                <option value="draft">Draft</option>
                <option value="published">Published</option></select
            ><InputError :message="form.errors.status" />
        </div>
    </div>
    <div class="space-y-2">
        <label for="judul" class="text-sm font-medium">Judul</label
        ><Input id="judul" v-model="judul" /><InputError
            :message="form.errors.judul"
        />
    </div>
    <div class="space-y-2">
        <label for="ringkasan" class="text-sm font-medium">Ringkasan</label
        ><textarea
            id="ringkasan"
            v-model="ringkasan"
            class="min-h-24 w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground"
        /><InputError :message="form.errors.ringkasan" />
    </div>
    <div class="space-y-2">
        <label class="text-sm font-medium">Konten</label
        ><KontenEditor v-model="konten" /><InputError
            :message="form.errors.konten"
        />
    </div>
    <div class="grid gap-6 md:grid-cols-2">
        <div class="space-y-2">
            <label for="thumbnail" class="text-sm font-medium">Thumbnail</label
            ><Input
                id="thumbnail"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                @change="setThumbnail"
            />
            <p class="text-xs text-muted-foreground">
                JPG, PNG, atau WebP (maks. 5 MB). Gambar dikonversi ke WebP.
            </p>
            <InputError :message="form.errors.thumbnail" />
        </div>
        <div class="space-y-2">
            <label for="tanggal_publish" class="text-sm font-medium"
                >Tanggal Publish</label
            ><Input
                id="tanggal_publish"
                v-model="tanggalPublish"
                type="datetime-local"
            /><InputError :message="form.errors.tanggal_publish" />
        </div>
    </div>
    <div
        v-if="isEvent"
        class="space-y-6 rounded-xl border border-border bg-muted/40 p-4"
    >
        <h2 class="font-semibold text-foreground">Informasi Event</h2>
        <div class="grid gap-6 md:grid-cols-2">
            <div class="space-y-2">
                <label for="tanggal_event" class="text-sm font-medium"
                    >Tanggal Event</label
                ><Input
                    id="tanggal_event"
                    v-model="tanggalEvent"
                    type="date"
                /><InputError :message="form.errors.tanggal_event" />
            </div>
            <div class="space-y-2">
                <label for="lokasi" class="text-sm font-medium">Lokasi</label
                ><Input id="lokasi" v-model="lokasi" /><InputError
                    :message="form.errors.lokasi"
                />
            </div>
            <div class="space-y-2">
                <label for="jam_mulai" class="text-sm font-medium"
                    >Jam Mulai</label
                ><Input
                    id="jam_mulai"
                    v-model="jamMulai"
                    type="time"
                /><InputError :message="form.errors.jam_mulai" />
            </div>
            <div class="space-y-2">
                <label for="jam_selesai" class="text-sm font-medium"
                    >Jam Selesai</label
                ><Input
                    id="jam_selesai"
                    v-model="jamSelesai"
                    type="time"
                /><InputError :message="form.errors.jam_selesai" />
            </div>
        </div>
    </div>
</template>
