<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
const page = usePage();
const subTema = page.props.subTema as {
    nama_sub_tema: string;
    deskripsi: string | null;
    tema: { nama_tema: string; thn_ajaran: string };
};
</script>
<template>
    <Head title="Detail Sub Tema" />
    <div class="max-w-2xl space-y-4 p-4">
        <h1 class="text-2xl font-bold">{{ subTema.nama_sub_tema }}</h1>
        <div class="rounded-xl border border-border bg-card p-6">
            <p>
                <span class="text-muted-foreground">Tema:</span>
                {{ subTema.tema.nama_tema }} · {{ subTema.tema.thn_ajaran }}
            </p>
            <p class="mt-4 whitespace-pre-wrap">
                {{ subTema.deskripsi ?? '-' }}
            </p>
        </div>
        <Button as-child variant="outline"
            ><Link href="/sub-tema">Kembali</Link></Button
        >
    </div>
</template>
