<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import KontenController from '@/actions/App/Http/Controllers/KontenController';
import Button from '@/components/ui/button/Button.vue';

interface Konten {
    id: number;
    jenis_konten: string;
    judul: string;
    ringkasan: string | null;
    konten: string | null;
    thumbnail: string | null;
    tanggal_publish: string | null;
    status: string;
    tanggal_event: string | null;
    jam_mulai: string | null;
    jam_selesai: string | null;
    lokasi: string | null;
    user: { name: string };
    galeris: { id: number; gambar: string; caption: string | null }[];
}
const page = usePage();
const konten = computed(() => page.props.konten as Konten);
const date = (value: string | null) =>
    value ? new Date(value).toLocaleString('id-ID') : '-';
</script>

<template>
    <Head :title="konten.judul" />
    <div class="mx-auto max-w-4xl space-y-6 p-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm text-muted-foreground capitalize">
                    {{ konten.jenis_konten }}
                </p>
                <h1 class="text-3xl font-bold text-foreground">
                    {{ konten.judul }}
                </h1>
            </div>
            <Button as-child variant="outline"
                ><Link :href="KontenController.index().url"
                    >Kembali</Link
                ></Button
            >
        </div>
        <article
            class="space-y-6 rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
        >
            <img
                v-if="konten.thumbnail"
                :src="`/storage/${konten.thumbnail}`"
                :alt="konten.judul"
                class="max-h-96 w-full rounded-lg object-cover"
            />
            <p v-if="konten.ringkasan" class="text-muted-foreground">
                {{ konten.ringkasan }}
            </p>
            <div
                class="prose max-w-none text-foreground"
                v-html="konten.konten"
            />
            <dl class="grid gap-4 text-sm md:grid-cols-2">
                <div>
                    <dt class="text-muted-foreground">Penulis</dt>
                    <dd>{{ konten.user.name }}</dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">Tanggal Publish</dt>
                    <dd>{{ date(konten.tanggal_publish) }}</dd>
                </div>
                <template v-if="konten.jenis_konten === 'event'"
                    ><div>
                        <dt class="text-muted-foreground">Tanggal Event</dt>
                        <dd>{{ konten.tanggal_event ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Waktu</dt>
                        <dd>
                            {{ konten.jam_mulai ?? '-' }} -
                            {{ konten.jam_selesai ?? '-' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Lokasi</dt>
                        <dd>{{ konten.lokasi ?? '-' }}</dd>
                    </div></template
                >
            </dl>
            <div
                v-if="konten.galeris.length"
                class="grid gap-4 sm:grid-cols-2 md:grid-cols-3"
            >
                <figure v-for="galeri in konten.galeris" :key="galeri.id">
                    <img
                        :src="`/storage/${galeri.gambar}`"
                        :alt="galeri.caption ?? 'Foto galeri'"
                        class="h-48 w-full rounded-lg object-cover"
                    />
                    <figcaption class="mt-2 text-sm text-muted-foreground">
                        {{ galeri.caption }}
                    </figcaption>
                </figure>
            </div>
        </article>
    </div>
</template>
