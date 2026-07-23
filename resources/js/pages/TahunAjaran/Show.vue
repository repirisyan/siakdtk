<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import TahunAjaranController from '@/actions/App/Http/Controllers/TahunAjaranController';
import Button from '@/components/ui/button/Button.vue';

interface TahunAjaran {
    id: number;
    tahun_ajaran: string;
    kelas_count: number;
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Tahun Ajaran', href: TahunAjaranController.index().url },
            { title: 'Detail', href: '#' },
        ],
    },
});

const page = usePage();
const tahunAjaran = computed(() => page.props.tahunAjaran as TahunAjaran);
</script>

<template>
    <Head :title="`Tahun Ajaran - ${tahunAjaran.tahun_ajaran}`" />
    <div class="max-w-2xl space-y-6 bg-background p-4 text-foreground">
        <div>
            <h1 class="text-2xl font-bold">Detail Tahun Ajaran</h1>
            <p class="text-sm text-muted-foreground">
                Informasi tahun ajaran dan kelas yang terhubung.
            </p>
        </div>
        <div
            class="rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
        >
            <dl class="space-y-4">
                <div>
                    <dt class="text-sm text-muted-foreground">Tahun Ajaran</dt>
                    <dd class="font-medium">{{ tahunAjaran.tahun_ajaran }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">Jumlah Kelas</dt>
                    <dd class="font-medium">{{ tahunAjaran.kelas_count }}</dd>
                </div>
            </dl>
            <div class="mt-6 flex gap-2">
                <Button as-child variant="secondary">
                    <Link :href="TahunAjaranController.edit(tahunAjaran.id).url"
                        >Edit</Link
                    >
                </Button>
                <Button as-child variant="outline">
                    <Link :href="TahunAjaranController.index().url"
                        >Kembali</Link
                    >
                </Button>
            </div>
        </div>
    </div>
</template>
