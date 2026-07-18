<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import JenisPembayaranController from '@/actions/App/Http/Controllers/JenisPembayaranController';
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
const form = useForm({ nama_jenis: '', deskripsi: '', status: true });
const submit = () => form.post(JenisPembayaranController.store().url);
</script>
<template>
    <Head title="Tambah Jenis Pembayaran" />
    <div class="max-w-2xl space-y-6 bg-background p-4 text-foreground">
        <h1 class="text-2xl font-bold">Tambah Jenis Pembayaran</h1>
        <form
            class="space-y-5 rounded-xl border border-border bg-card p-6 text-card-foreground"
            @submit.prevent="submit"
        >
            <div class="space-y-2">
                <label class="text-sm font-medium">Nama Jenis</label
                ><Input
                    v-model="form.nama_jenis"
                    required
                    autofocus
                /><InputError :message="form.errors.nama_jenis" />
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium">Deskripsi</label
                ><textarea
                    v-model="form.deskripsi"
                    class="min-h-28 w-full rounded-md border border-border bg-background p-3 text-foreground"
                /><InputError :message="form.errors.deskripsi" />
            </div>
            <label class="flex items-center gap-2 text-sm"
                ><input
                    v-model="form.status"
                    type="checkbox"
                    class="size-4 accent-primary"
                />
                Aktif</label
            >
            <div class="flex gap-2">
                <Button type="submit" :disabled="form.processing">Simpan</Button
                ><Button as-child variant="outline"
                    ><Link href="/jenis-pembayaran">Batal</Link></Button
                >
            </div>
        </form>
    </div>
</template>
