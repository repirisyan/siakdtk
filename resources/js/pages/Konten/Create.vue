<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

import KontenController from '@/actions/App/Http/Controllers/KontenController';
import KontenForm from '@/components/KontenForm.vue';
import Button from '@/components/ui/button/Button.vue';

const formState = useForm({
    jenis_konten: 'berita',
    judul: '',
    ringkasan: '',
    konten: '',
    thumbnail: null as File | null,
    tanggal_publish: '',
    status: 'draft',
    tanggal_event: '',
    jam_mulai: '',
    jam_selesai: '',
    lokasi: '',
});
const form = computed({
    get: () => formState,
    set: (value) => Object.assign(formState, value),
});
const submit = () =>
    formState.post(KontenController.store().url, { forceFormData: true });
</script>

<template>
    <Head title="Tambah Konten" />
    <div class="max-w-4xl space-y-6 p-4">
        <div>
            <h1 class="text-2xl font-bold text-foreground">Tambah Konten</h1>
            <p class="text-sm text-muted-foreground">
                Buat berita, event, pengumuman, atau galeri sekolah.
            </p>
        </div>
        <form
            class="space-y-6 rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
            @submit.prevent="submit"
        >
            <KontenForm v-model:form="form" />
            <div class="flex gap-2">
                <Button type="submit" :disabled="formState.processing">{{
                    formState.processing ? 'Menyimpan...' : 'Simpan'
                }}</Button
                ><Button as-child variant="outline"
                    ><Link :href="KontenController.index().url"
                        >Batal</Link
                    ></Button
                >
            </div>
        </form>
    </div>
</template>
