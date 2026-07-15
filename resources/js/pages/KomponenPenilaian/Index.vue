<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Button from '@/components/ui/button/Button.vue';

interface KomponenPenilaian {
    id: number;
    nama_komponen: string;
    deskripsi: string | null;
    status: boolean;
}

const page = usePage();
const subTema = computed(
    () =>
        page.props.subTema as {
            id: number;
            nama_sub_tema: string;
            tema: { nama_tema: string; thn_ajaran: string };
        } | null,
);
const components = computed(
    () =>
        page.props.komponenPenilaians as {
            data: KomponenPenilaian[];
            links: { url: string | null; label: string; active: boolean }[];
        },
);
const remove = (id: number) => {
    if (window.confirm('Hapus komponen penilaian ini?'))
        router.delete(`/komponen-penilaian/${id}`);
};
</script>

<template>
    <Head title="Komponen Penilaian" />
    <div class="space-y-6 bg-background p-4 text-foreground">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Komponen Penilaian</h1>
                <p class="text-sm text-muted-foreground">
                    {{
                        subTema
                            ? `${subTema.tema.nama_tema} · ${subTema.nama_sub_tema}`
                            : 'Pilih Sub Tema untuk mengelola komponen penilaian.'
                    }}
                </p>
            </div>
            <div class="flex gap-2">
                <Button as-child variant="outline"
                    ><Link href="/sub-tema">Kembali ke Sub Tema</Link></Button
                ><Button v-if="subTema" as-child
                    ><Link
                        :href="`/komponen-penilaian/create?sub_tema_id=${subTema.id}`"
                        >Tambah Komponen Penilaian</Link
                    ></Button
                >
            </div>
        </div>
        <div
            class="overflow-x-auto rounded-xl border border-border bg-card text-card-foreground"
        >
            <table class="w-full">
                <thead class="bg-muted/50">
                    <tr>
                        <th class="px-4 py-3 text-left">Komponen</th>
                        <th class="px-4 py-3 text-left">Deskripsi</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="component in components.data"
                        :key="component.id"
                        class="border-t border-border"
                    >
                        <td class="px-4 py-3 font-medium">
                            {{ component.nama_komponen }}
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ component.deskripsi ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="rounded-md px-2 py-1 text-sm"
                                :class="
                                    component.status
                                        ? 'bg-primary/15 text-foreground'
                                        : 'bg-muted text-muted-foreground'
                                "
                                >{{
                                    component.status ? 'Aktif' : 'Nonaktif'
                                }}</span
                            >
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <Button as-child variant="outline" size="sm"
                                    ><Link
                                        :href="`/komponen-penilaian/${component.id}/edit`"
                                        >Edit</Link
                                    ></Button
                                ><Button
                                    variant="destructive"
                                    size="sm"
                                    @click="remove(component.id)"
                                    >Hapus</Button
                                >
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!components.data.length">
                        <td
                            colspan="4"
                            class="p-8 text-center text-muted-foreground"
                        >
                            Belum ada komponen penilaian.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
