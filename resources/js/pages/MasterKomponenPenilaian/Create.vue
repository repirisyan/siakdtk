<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import MasterKomponenPenilaianController from '@/actions/App/Http/Controllers/MasterKomponenPenilaianController';
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';

const form = useForm({ nama_komponen: '', deskripsi: '', status: true });
const submit = () => form.post(MasterKomponenPenilaianController.store().url);
</script>

<template>
    <Head title="Tambah Master Komponen Penilaian" />

    <div class="max-w-2xl space-y-6 bg-background p-4 text-foreground">
        <div>
            <h1 class="text-2xl font-bold">Tambah Master Komponen Penilaian</h1>
            <p class="text-sm text-muted-foreground">
                Data aktif akan digunakan saat membuat Sub Tema baru.
            </p>
        </div>
        <form
            class="space-y-5 rounded-xl border border-border bg-card p-6 text-card-foreground"
            @submit.prevent="submit"
        >
            <div class="space-y-2">
                <label class="text-sm font-medium" for="nama_komponen"
                    >Nama Komponen</label
                >
                <Input
                    id="nama_komponen"
                    v-model="form.nama_komponen"
                    autofocus
                    required
                />
                <InputError :message="form.errors.nama_komponen" />
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium" for="deskripsi"
                    >Deskripsi</label
                >
                <textarea
                    id="deskripsi"
                    v-model="form.deskripsi"
                    class="min-h-28 w-full rounded-md border border-border bg-background p-3 text-foreground"
                />
                <InputError :message="form.errors.deskripsi" />
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input
                    v-model="form.status"
                    type="checkbox"
                    class="size-4 accent-primary"
                />
                Aktif
            </label>
            <div class="flex gap-2">
                <Button type="submit" :disabled="form.processing"
                    >Simpan</Button
                >
                <Button as-child variant="outline"
                    ><Link :href="MasterKomponenPenilaianController.index().url"
                        >Batal</Link
                    ></Button
                >
            </div>
        </form>
    </div>
</template>
