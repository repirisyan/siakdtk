<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowLeft, CalendarDays } from '@lucide/vue';
import Button from '@/components/ui/button/Button.vue';
import { home } from '@/routes';

interface Berita {
    judul: string;
    ringkasan: string | null;
    konten: string | null;
    thumbnail: string | null;
    tanggal_publish: string | null;
    user: { name: string } | null;
}
const page = usePage();
const berita = page.props.berita as Berita;
const date = berita.tanggal_publish
    ? new Date(berita.tanggal_publish).toLocaleDateString('id-ID', {
          day: 'numeric',
          month: 'long',
          year: 'numeric',
      })
    : '-';
</script>

<template>
    <Head :title="berita.judul"
        ><meta
            name="description"
            :content="berita.ringkasan ?? berita.judul" /><meta
            property="og:title"
            :content="berita.judul"
    /></Head>
    <main class="min-h-svh bg-background py-10 text-foreground">
        <article class="mx-auto max-w-3xl px-4 sm:px-6">
            <Button as-child variant="outline"
                ><Link :href="home()"
                    ><ArrowLeft class="size-4" /> Kembali ke Beranda</Link
                ></Button
            >
            <p
                class="mt-10 flex items-center gap-2 text-sm text-muted-foreground"
            >
                <CalendarDays class="size-4" />{{ date }}
            </p>
            <h1 class="mt-4 text-4xl font-bold tracking-tight sm:text-5xl">
                {{ berita.judul }}
            </h1>
            <p
                v-if="berita.ringkasan"
                class="mt-5 text-lg leading-8 text-muted-foreground"
            >
                {{ berita.ringkasan }}
            </p>
            <img
                v-if="berita.thumbnail"
                :src="`/storage/${berita.thumbnail}`"
                :alt="berita.judul"
                class="mt-8 aspect-video w-full rounded-3xl border border-border object-cover"
            />
            <div
                class="prose mt-8 max-w-none text-foreground"
                v-html="berita.konten"
            />
            <p
                v-if="berita.user"
                class="mt-10 border-t border-border pt-5 text-sm text-muted-foreground"
            >
                Ditulis oleh {{ berita.user.name }}
            </p>
        </article>
    </main>
</template>
