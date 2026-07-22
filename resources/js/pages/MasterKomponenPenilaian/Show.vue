<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import MasterKomponenPenilaianController from '@/actions/App/Http/Controllers/MasterKomponenPenilaianController';
import Button from '@/components/ui/button/Button.vue';

const masterKomponenPenilaian = usePage().props.masterKomponenPenilaian as {
    nama_komponen: string;
    deskripsi: string | null;
    status: boolean;
};
</script>

<template>
    <Head title="Detail Master Komponen Penilaian" />

    <div class="max-w-2xl space-y-6 bg-background p-4 text-foreground">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">
                    Detail Master Komponen Penilaian
                </h1>
                <p class="text-sm text-muted-foreground">
                    Template komponen untuk Sub Tema baru.
                </p>
            </div>
            <Button as-child variant="outline"
                ><Link :href="MasterKomponenPenilaianController.index().url"
                    >Kembali</Link
                ></Button
            >
        </div>
        <dl
            class="space-y-4 rounded-xl border border-border bg-card p-6 text-card-foreground"
        >
            <div>
                <dt class="text-sm text-muted-foreground">Nama Komponen</dt>
                <dd class="mt-1 font-medium">
                    {{ masterKomponenPenilaian.nama_komponen }}
                </dd>
            </div>
            <div>
                <dt class="text-sm text-muted-foreground">Deskripsi</dt>
                <dd class="mt-1 whitespace-pre-line">
                    {{ masterKomponenPenilaian.deskripsi ?? '-' }}
                </dd>
            </div>
            <div>
                <dt class="text-sm text-muted-foreground">Status</dt>
                <dd class="mt-1">
                    {{ masterKomponenPenilaian.status ? 'Aktif' : 'Nonaktif' }}
                </dd>
            </div>
        </dl>
    </div>
</template>
