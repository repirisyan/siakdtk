<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import {
    CheckCircle2,
    Clock3,
    KeyRound,
    LockKeyhole,
    MailCheck,
    ShieldCheck,
} from '@lucide/vue';

import AppLogoIcon from '@/components/AppLogoIcon.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Toaster } from '@/components/ui/sonner';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { email } from '@/routes/password';

defineProps<{ status?: string }>();

const benefits = [
    { label: 'Aman dan terenkripsi', icon: ShieldCheck },
    { label: 'Tautan reset memiliki masa berlaku', icon: Clock3 },
    { label: 'Untuk guru, orang tua, dan staf', icon: CheckCircle2 },
];
</script>

<template>
    <Head title="Lupa Password" />
    <main class="min-h-svh bg-background p-4 text-foreground md:p-8">
        <div
            class="mx-auto grid min-h-[calc(100svh-2rem)] max-w-360 overflow-hidden rounded-3xl border border-border bg-card shadow-xl shadow-primary/5 lg:grid-cols-[.9fr_1.1fr]"
        >
            <aside
                class="relative overflow-hidden bg-primary p-8 text-primary-foreground md:p-12"
            >
                <div
                    class="absolute -top-20 -right-12 size-64 rounded-full bg-primary-foreground/10"
                />
                <div
                    class="absolute -bottom-28 -left-20 size-80 rounded-full border-24 border-primary-foreground/10"
                />
                <div class="relative flex h-full flex-col">
                    <Link :href="login()" class="flex items-center gap-3"
                        ><span
                            class="flex size-11 items-center justify-center rounded-2xl bg-primary-foreground/15"
                            ><AppLogoIcon class="size-6 fill-current" /></span
                        ><span
                            ><strong class="block text-lg">SIAKDTK</strong
                            ><span class="text-sm text-primary-foreground/75"
                                >Sistem Informasi Akademik TK</span
                            ></span
                        ></Link
                    >
                    <div class="my-auto py-14">
                        <p
                            class="mb-4 text-sm font-semibold tracking-widest text-primary-foreground/75 uppercase"
                        >
                            Bantuan Akun
                        </p>
                        <h1
                            class="max-w-lg text-3xl font-bold tracking-tight md:text-4xl"
                        >
                            Lupa password bukan masalah.
                        </h1>
                        <p
                            class="mt-5 max-w-lg leading-7 text-primary-foreground/80"
                        >
                            Masukkan email yang terdaftar dan kami akan
                            mengirimkan tautan untuk membuat password baru.
                        </p>
                        <div class="mt-10 space-y-3">
                            <div
                                v-for="benefit in benefits"
                                :key="benefit.label"
                                class="flex items-center gap-3 rounded-2xl bg-primary-foreground/10 p-3 text-sm"
                            >
                                <component
                                    :is="benefit.icon"
                                    class="size-5 shrink-0"
                                />{{ benefit.label }}
                            </div>
                        </div>
                    </div>
                    <div
                        class="flex items-center gap-2 text-sm text-primary-foreground/75"
                    >
                        <KeyRound class="size-4" /> Proses pemulihan akun yang
                        mudah dan aman.
                    </div>
                </div>
            </aside>
            <section class="flex items-center bg-background p-5 sm:p-8 md:p-10">
                <div class="mx-auto w-full max-w-xl">
                    <div class="mb-8">
                        <div
                            class="mb-4 flex size-11 items-center justify-center rounded-2xl bg-secondary text-secondary-foreground"
                        >
                            <MailCheck class="size-5" />
                        </div>
                        <h1 class="text-3xl font-bold tracking-tight">
                            Lupa Password?
                        </h1>
                        <p class="mt-2 text-sm leading-6 text-muted-foreground">
                            Masukkan email akun Anda untuk menerima tautan reset
                            password.
                        </p>
                    </div>
                    <div
                        v-if="status"
                        class="mb-6 flex gap-3 rounded-2xl border border-border bg-secondary p-4 text-sm leading-6 text-secondary-foreground"
                    >
                        <CheckCircle2 class="mt-0.5 size-5 shrink-0" />
                        <p>
                            Tautan reset password berhasil diproses dan akan
                            segera dikirim ke email Anda.
                        </p>
                    </div>
                    <Form
                        v-bind="email.form()"
                        v-slot="{ errors, processing }"
                        class="space-y-6"
                        ><div class="space-y-2">
                            <Label for="email">Email</Label
                            ><Input
                                id="email"
                                type="email"
                                name="email"
                                autocomplete="email"
                                autofocus
                                required
                                placeholder="nama@email.com"
                                :aria-invalid="Boolean(errors.email)"
                                :class="
                                    errors.email
                                        ? 'border-destructive focus-visible:ring-destructive/20'
                                        : ''
                                "
                            /><InputError :message="errors.email" />
                        </div>
                        <p
                            class="rounded-xl bg-muted/60 p-4 text-sm leading-6 text-muted-foreground"
                        >
                            Pastikan Anda menggunakan email yang terdaftar pada
                            sistem.
                        </p>
                        <Button
                            type="submit"
                            class="h-11 w-full"
                            :disabled="processing"
                            data-test="email-password-reset-link-button"
                            ><Spinner v-if="processing" />{{
                                processing
                                    ? 'Memproses...'
                                    : 'Kirim Tautan Reset Password'
                            }}</Button
                        ></Form
                    ><Button as-child variant="outline" class="mt-4 h-11 w-full"
                        ><Link :href="login()"
                            ><LockKeyhole class="size-4" /> Kembali ke
                            Login</Link
                        ></Button
                    >
                </div>
            </section>
        </div>
    </main>
    <Toaster />
</template>
