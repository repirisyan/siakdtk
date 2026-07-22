<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import JadwalController from '@/actions/App/Http/Controllers/JadwalController';
import Button from '@/components/ui/button/Button.vue';

interface Jadwal {
    id: number;
    tanggal: string;
    jam_mulai: string;
    jam_selesai: string;
    kelas: { nama_kelas: string; thn_ajaran: string };
    guru: { nama: string; nip: string };
    tema: { nama_tema: string };
}
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Jadwal', href: JadwalController.index().url },
            { title: 'Detail', href: '#' },
        ],
    },
});
const page = usePage();
const jadwal = computed(() => page.props.jadwal as Jadwal);
</script>

<template>
    <Head title="Detail Jadwal" />
    <div class="max-w-2xl space-y-6 bg-background p-4 text-foreground">
        <div>
            <h1 class="text-2xl font-bold">Detail Jadwal</h1>
            <p class="text-sm text-muted-foreground">
                Informasi jadwal pembelajaran.
            </p>
        </div>
        <div
            class="rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
        >
            <dl class="grid gap-4 md:grid-cols-2">
                <div>
                    <dt class="text-sm text-muted-foreground">Tanggal</dt>
                    <dd class="font-medium">{{ jadwal.tanggal }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">Jam</dt>
                    <dd class="font-medium">
                        {{ jadwal.jam_mulai }} - {{ jadwal.jam_selesai }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">Kelas</dt>
                    <dd class="font-medium">
                        {{ jadwal.kelas.nama_kelas }} -
                        {{ jadwal.kelas.thn_ajaran }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">Guru</dt>
                    <dd class="font-medium">
                        {{ jadwal.guru.nama }} ({{ jadwal.guru.nip }})
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">Tema</dt>
                    <dd class="font-medium">{{ jadwal.tema.nama_tema }}</dd>
                </div>
            </dl>
            <div class="mt-6 flex gap-2">
                <Button as-child variant="secondary"
                    ><Link :href="JadwalController.edit(jadwal.id).url"
                        >Edit</Link
                    ></Button
                ><Button as-child variant="outline"
                    ><Link :href="JadwalController.index().url"
                        >Kembali</Link
                    ></Button
                >
            </div>
        </div>
    </div>
</template>
