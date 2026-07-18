<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import Button from '@/components/ui/button/Button.vue';

interface Props {
    siswa: { id: number; nama: string; nis: string | null };
    kelas: { id: number; nama_kelas: string; thn_ajaran: string };
    tema: { id: number; nama_tema: string };
    subTema: { id: number; nama_sub_tema: string };
    components: {
        id: number;
        nama_komponen: string;
        keterangan: string;
    }[];
    backUrl: string;
}

defineProps<Props>();
</script>

<template>
    <Head title="Detail Summary Penilaian" />
    <div class="space-y-6 bg-background p-4 text-foreground">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Detail Summary Penilaian</h1>
                <p class="text-sm text-muted-foreground">
                    Keterangan penilaian siswa berdasarkan komponen.
                </p>
            </div>
            <Button variant="outline" as-child>
                <Link :href="backUrl">Kembali</Link>
            </Button>
        </div>

        <dl
            class="grid gap-4 rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm sm:grid-cols-2 lg:grid-cols-4"
        >
            <div>
                <dt class="text-sm text-muted-foreground">Nama Siswa</dt>
                <dd class="font-medium">{{ siswa.nama }}</dd>
                <dd class="text-sm text-muted-foreground">
                    NIS: {{ siswa.nis ?? '-' }}
                </dd>
            </div>
            <div>
                <dt class="text-sm text-muted-foreground">Kelas</dt>
                <dd class="font-medium">{{ kelas.nama_kelas }}</dd>
                <dd class="text-sm text-muted-foreground">
                    {{ kelas.thn_ajaran }}
                </dd>
            </div>
            <div>
                <dt class="text-sm text-muted-foreground">Tema</dt>
                <dd class="font-medium">{{ tema.nama_tema }}</dd>
            </div>
            <div>
                <dt class="text-sm text-muted-foreground">Sub Tema</dt>
                <dd class="font-medium">{{ subTema.nama_sub_tema }}</dd>
            </div>
        </dl>

        <div
            class="overflow-x-auto rounded-xl border border-border bg-card text-card-foreground"
        >
            <table class="w-full min-w-max">
                <thead class="bg-muted/50">
                    <tr>
                        <th
                            v-for="component in components"
                            :key="component.id"
                            class="px-4 py-3 text-left text-sm font-medium"
                        >
                            {{ component.nama_komponen }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="components.length" class="border-t border-border">
                        <td
                            v-for="component in components"
                            :key="component.id"
                            class="max-w-sm px-4 py-3 align-top text-sm"
                        >
                            {{ component.keterangan }}
                        </td>
                    </tr>
                    <tr v-else>
                        <td class="py-8 text-center text-muted-foreground">
                            Belum ada keterangan penilaian
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
