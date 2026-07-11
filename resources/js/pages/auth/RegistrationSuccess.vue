<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle2, Clock3 } from '@lucide/vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Button from '@/components/ui/button/Button.vue';
import { login } from '@/routes';
</script>

<template>
    <Head title="Pendaftaran Berhasil" />
    <main class="flex min-h-svh items-center justify-center bg-background p-4">
        <section
            class="w-full max-w-xl rounded-3xl border border-border bg-card p-8 text-center text-card-foreground shadow-xl shadow-primary/5 sm:p-12"
        >
            <div
                class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-secondary text-secondary-foreground"
            >
                <CheckCircle2 class="size-9" />
            </div>
            <div
                class="mt-6 flex items-center justify-center gap-2 text-sm font-semibold text-primary"
            >
                <AppLogoIcon class="size-5 fill-current" /> SIAKDTK
            </div>
            <h1 class="mt-4 text-3xl font-bold tracking-tight">
                Pendaftaran Berhasil
            </h1>
            <p class="mt-4 leading-7 text-muted-foreground">
                Terima kasih telah melakukan pendaftaran. Data calon siswa telah
                berhasil dikirim. Silakan cek email Anda untuk melakukan
                verifikasi akun sebelum dapat digunakan.
            </p>
            <div
                class="mt-7 flex gap-3 rounded-2xl bg-muted p-4 text-left text-sm text-muted-foreground"
            >
                <Clock3 class="mt-0.5 size-5 shrink-0 text-primary" />
                <p>
                    Setelah email terverifikasi, akun tetap menunggu aktivasi
                    oleh pihak sekolah. Silakan menunggu informasi lebih lanjut.
                </p>
            </div>
            <Button as-child class="mt-8 w-full"
                ><Link :href="login()">Kembali ke Halaman Masuk</Link></Button
            >
        </section>
    </main>
</template>
