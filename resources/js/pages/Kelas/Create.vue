<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

import KelasController from '@/actions/App/Http/Controllers/KelasController';

import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Kelas', href: KelasController.index().url },
            { title: 'Create', href: KelasController.create().url },
        ],
    },
});

const form = useForm({
    nama_kelas: '',
    thn_ajaran: '',
});

const submit = () => {
    form.post(KelasController.store().url);
};
</script>

<template>
    <Head title="Tambah Kelas" />

    <div class="max-w-2xl space-y-6 bg-background p-4 text-foreground">
        <div>
            <h1 class="text-2xl font-bold">Tambah Kelas</h1>
            <p class="text-sm text-muted-foreground">
                Tambahkan data kelas baru.
            </p>
        </div>

        <div
            class="rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
        >
            <form class="space-y-6" @submit.prevent="submit">
                <div class="space-y-2">
                    <label for="nama_kelas" class="text-sm font-medium"
                        >Nama Kelas</label
                    >
                    <Input
                        id="nama_kelas"
                        v-model="form.nama_kelas"
                        placeholder="Masukkan nama kelas"
                    />
                    <InputError :message="form.errors.nama_kelas" />
                </div>

                <div class="space-y-2">
                    <label for="thn_ajaran" class="text-sm font-medium"
                        >Tahun Ajaran</label
                    >
                    <Input
                        id="thn_ajaran"
                        v-model="form.thn_ajaran"
                        inputmode="numeric"
                        maxlength="4"
                        placeholder="Contoh: 2026"
                    />
                    <InputError :message="form.errors.thn_ajaran" />
                </div>

                <div class="flex gap-2">
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                    <Button as-child variant="outline">
                        <Link :href="KelasController.index().url">Batal</Link>
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>
