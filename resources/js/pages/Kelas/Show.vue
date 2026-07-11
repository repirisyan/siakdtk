<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import KelasController from '@/actions/App/Http/Controllers/KelasController';

import Button from '@/components/ui/button/Button.vue';

interface Kelas {
    id: number;
    nama_kelas: string;
    thn_ajaran: string;
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Kelas', href: KelasController.index().url },
            { title: 'Detail', href: '#' },
        ],
    },
});

const page = usePage();
const kelas = computed(() => page.props.kelas as Kelas);
</script>

<template>
    <Head :title="`Detail Kelas - ${kelas.nama_kelas}`" />

    <div class="max-w-2xl space-y-6 bg-background p-4 text-foreground">
        <div>
            <h1 class="text-2xl font-bold">Detail Kelas</h1>
            <p class="text-sm text-muted-foreground">Informasi data kelas.</p>
        </div>

        <div
            class="rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
        >
            <dl class="space-y-4">
                <div>
                    <dt class="text-sm text-muted-foreground">Nama Kelas</dt>
                    <dd class="font-medium">{{ kelas.nama_kelas }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">Tahun Ajaran</dt>
                    <dd class="font-medium">{{ kelas.thn_ajaran }}</dd>
                </div>
            </dl>

            <div class="mt-6 flex gap-2">
                <Button as-child variant="secondary">
                    <Link :href="KelasController.edit(kelas.id).url">Edit</Link>
                </Button>
                <Button as-child variant="outline">
                    <Link :href="KelasController.index().url">Kembali</Link>
                </Button>
            </div>
        </div>
    </div>
</template>
