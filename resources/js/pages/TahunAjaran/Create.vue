<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

import TahunAjaranController from '@/actions/App/Http/Controllers/TahunAjaranController';
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Tahun Ajaran', href: TahunAjaranController.index().url },
            { title: 'Tambah', href: TahunAjaranController.create().url },
        ],
    },
});

const form = useForm({ tahun_ajaran: '' });
const submit = () => form.post(TahunAjaranController.store().url);
</script>

<template>
    <Head title="Tambah Tahun Ajaran" />
    <div class="max-w-2xl space-y-6 bg-background p-4 text-foreground">
        <div>
            <h1 class="text-2xl font-bold">Tambah Tahun Ajaran</h1>
            <p class="text-sm text-muted-foreground">
                Tambahkan tahun ajaran untuk digunakan pada kelas.
            </p>
        </div>
        <form
            class="space-y-6 rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
            @submit.prevent="submit"
        >
            <div class="space-y-2">
                <label for="tahun_ajaran" class="text-sm font-medium"
                    >Tahun Ajaran</label
                >
                <Input
                    id="tahun_ajaran"
                    v-model="form.tahun_ajaran"
                    inputmode="numeric"
                    maxlength="4"
                    placeholder="Contoh: 2026"
                />
                <InputError :message="form.errors.tahun_ajaran" />
            </div>
            <div class="flex gap-2">
                <Button type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                </Button>
                <Button as-child variant="outline">
                    <Link :href="TahunAjaranController.index().url">Batal</Link>
                </Button>
            </div>
        </form>
    </div>
</template>
