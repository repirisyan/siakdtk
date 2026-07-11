<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Button from '@/components/ui/button/Button.vue';

const page = usePage();
const subTemas = computed(
    () =>
        page.props.subTemas as {
            data: {
                id: number;
                nama_sub_tema: string;
                deskripsi: string | null;
                tema: { nama_tema: string; thn_ajaran: string };
            }[];
            links: { url: string | null; label: string; active: boolean }[];
        },
);
const temas = computed(
    () => page.props.temas as { id: number; nama_tema: string }[],
);
const filters = computed(
    () => page.props.filters as { tema_id: string | null },
);
const remove = (id: number) => {
    if (confirm('Hapus sub tema ini?')) router.delete(`/sub-tema/${id}`);
};
</script>
<template>
    <Head title="Kelola Sub Tema" />
    <div class="space-y-5 bg-background p-4 text-foreground">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Kelola Sub Tema</h1>
                <p class="text-sm text-muted-foreground">
                    Kelola sub tema pembelajaran di bawah setiap tema.
                </p>
            </div>
            <Button as-child
                ><Link href="/sub-tema/create">Tambah Sub Tema</Link></Button
            >
        </div>
        <select
            :value="filters.tema_id ?? ''"
            class="h-9 rounded-md border border-border bg-background px-3 text-sm"
            @change="
                router.get('/sub-tema', {
                    tema_id: ($event.target as HTMLSelectElement).value,
                })
            "
        >
            <option value="">Semua Tema</option>
            <option v-for="tema in temas" :key="tema.id" :value="tema.id">
                {{ tema.nama_tema }}
            </option>
        </select>
        <div class="overflow-x-auto rounded-xl border border-border bg-card">
            <table class="w-full">
                <thead class="bg-muted/50">
                    <tr>
                        <th class="px-4 py-3 text-left">Tema</th>
                        <th class="px-4 py-3 text-left">Sub Tema</th>
                        <th class="px-4 py-3 text-left">Deskripsi</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="subTema in subTemas.data"
                        :key="subTema.id"
                        class="border-t border-border"
                    >
                        <td class="px-4 py-3">
                            {{ subTema.tema.nama_tema }} ·
                            {{ subTema.tema.thn_ajaran }}
                        </td>
                        <td class="px-4 py-3 font-medium">
                            {{ subTema.nama_sub_tema }}
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ subTema.deskripsi ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <Button
                                    variant="outline"
                                    @click="
                                        router.visit(
                                            `/sub-tema/${subTema.id}/edit`,
                                        )
                                    "
                                    >Edit</Button
                                ><Button
                                    variant="destructive"
                                    @click="remove(subTema.id)"
                                    >Hapus</Button
                                >
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!subTemas.data.length">
                        <td
                            colspan="4"
                            class="p-8 text-center text-muted-foreground"
                        >
                            Belum ada sub tema.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
