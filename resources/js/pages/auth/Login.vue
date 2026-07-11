<script setup lang="ts">
import { Form, Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    CheckCircle2,
    GraduationCap,
    LockKeyhole,
    Receipt,
    School,
    ShieldCheck,
} from '@lucide/vue';

import AppLogoIcon from '@/components/AppLogoIcon.vue';
import InputError from '@/components/InputError.vue';
import PasskeyVerify from '@/components/PasskeyVerify.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Toaster } from '@/components/ui/sonner';
import { Spinner } from '@/components/ui/spinner';
import { login, register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineProps<{ status?: string; canResetPassword: boolean }>();

const email = ref('');
const resendForm = useForm({ email: '' });
const resendVerification = () => {
    resendForm.email = email.value;
    resendForm.post('/email/verification-notification-public');
};

const benefits = [
    { label: 'Manajemen Data Siswa', icon: GraduationCap },
    { label: 'Penilaian dan Rapor Digital', icon: School },
    { label: 'Monitoring Pembayaran SPP', icon: Receipt },
    { label: 'Akses Orang Tua Secara Online', icon: ShieldCheck },
];
</script>

<template>
    <Head title="Masuk" />
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
                    <Link :href="login()" class="flex items-center gap-3">
                        <span
                            class="flex size-11 items-center justify-center rounded-2xl bg-primary-foreground/15"
                            ><AppLogoIcon class="size-6 fill-current"
                        /></span>
                        <span
                            ><strong class="block text-lg">SIAKDTK</strong
                            ><span class="text-sm text-primary-foreground/75"
                                >Sistem Informasi Akademik TK</span
                            ></span
                        >
                    </Link>
                    <div class="my-auto py-14">
                        <p
                            class="mb-4 text-sm font-semibold tracking-widest text-primary-foreground/75 uppercase"
                        >
                            Portal Sekolah
                        </p>
                        <h1
                            class="max-w-lg text-3xl font-bold tracking-tight md:text-4xl"
                        >
                            Mendampingi tumbuh kembang anak, bersama sekolah dan
                            keluarga.
                        </h1>
                        <p
                            class="mt-5 max-w-lg leading-7 text-primary-foreground/80"
                        >
                            Mendukung proses administrasi, pembelajaran,
                            penilaian, rapor, dan pembayaran sekolah secara
                            terintegrasi.
                        </p>
                        <div class="mt-10 grid gap-3 sm:grid-cols-2">
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
                        <CheckCircle2 class="size-4" /> Aman, terintegrasi, dan
                        mudah digunakan.
                    </div>
                </div>
            </aside>

            <section class="flex items-center bg-background p-5 sm:p-8 md:p-10">
                <div class="mx-auto w-full max-w-xl">
                    <div class="mb-8">
                        <div
                            class="mb-4 flex size-11 items-center justify-center rounded-2xl bg-secondary text-secondary-foreground"
                        >
                            <LockKeyhole class="size-5" />
                        </div>
                        <h2 class="text-3xl font-bold tracking-tight">
                            Selamat Datang
                        </h2>
                        <p class="mt-2 text-sm leading-6 text-muted-foreground">
                            Silakan masuk ke akun Anda untuk melanjutkan.
                        </p>
                    </div>
                    <div
                        v-if="status"
                        class="mb-6 rounded-xl border border-border bg-secondary p-4 text-sm font-medium text-secondary-foreground"
                    >
                        {{ status }}
                    </div>
                    <PasskeyVerify />
                    <Form
                        v-bind="store.form()"
                        :reset-on-success="['password']"
                        v-slot="{ errors, processing }"
                        class="space-y-6"
                    >
                        <div class="space-y-5">
                            <div class="space-y-2">
                                <Label for="email">Email</Label
                                ><Input
                                    id="email"
                                    v-model="email"
                                    type="email"
                                    name="email"
                                    required
                                    autofocus
                                    :tabindex="1"
                                    autocomplete="email"
                                    placeholder="nama@email.com"
                                    :aria-invalid="Boolean(errors.email)"
                                    :class="
                                        errors.email
                                            ? 'border-destructive focus-visible:ring-destructive/20'
                                            : ''
                                    "
                                /><InputError :message="errors.email" />
                                <button
                                    v-if="
                                        errors.email ===
                                        'Silakan verifikasi email terlebih dahulu.'
                                    "
                                    type="button"
                                    class="text-left text-sm font-semibold text-primary underline underline-offset-4"
                                    :disabled="resendForm.processing"
                                    @click="resendVerification"
                                >
                                    {{
                                        resendForm.processing
                                            ? 'Mengirim ulang...'
                                            : 'Kirim ulang email verifikasi'
                                    }}
                                </button>
                            </div>
                            <div class="space-y-2">
                                <div
                                    class="flex items-center justify-between gap-3"
                                >
                                    <Label for="password">Password</Label
                                    ><Link
                                        v-if="canResetPassword"
                                        :href="request()"
                                        class="text-sm font-semibold text-primary underline underline-offset-4"
                                        :tabindex="5"
                                        >Lupa password?</Link
                                    >
                                </div>
                                <PasswordInput
                                    id="password"
                                    name="password"
                                    required
                                    :tabindex="2"
                                    autocomplete="current-password"
                                    placeholder="Masukkan password"
                                    :aria-invalid="Boolean(errors.password)"
                                    :class="
                                        errors.password
                                            ? 'border-destructive focus-visible:ring-destructive/20'
                                            : ''
                                    "
                                /><InputError :message="errors.password" />
                            </div>
                            <Label
                                for="remember"
                                class="flex items-center space-x-3 text-muted-foreground"
                                ><Checkbox
                                    id="remember"
                                    name="remember"
                                    :tabindex="3"
                                /><span
                                    >Ingat saya di perangkat ini</span
                                ></Label
                            >
                        </div>
                        <Button
                            type="submit"
                            class="h-11 w-full"
                            :tabindex="4"
                            :disabled="processing"
                            data-test="login-button"
                            ><Spinner v-if="processing" />{{
                                processing
                                    ? 'Memproses...'
                                    : 'Masuk ke Portal Sekolah'
                            }}</Button
                        >
                    </Form>
                    <div
                        class="mt-8 rounded-2xl border border-border bg-muted/60 p-4"
                    >
                        <p class="text-sm font-semibold">
                            Akses tersedia untuk
                        </p>
                        <p class="mt-1 text-sm leading-6 text-muted-foreground">
                            Guru, staff sekolah, kepala sekolah, dan orang tua
                            siswa.
                        </p>
                    </div>
                    <p class="mt-6 text-center text-sm text-muted-foreground">
                        Belum memiliki akun?
                        <Link
                            :href="register()"
                            class="font-semibold text-primary underline underline-offset-4"
                            >Daftarkan Siswa Baru</Link
                        >
                    </p>
                </div>
            </section>
        </div>
    </main>
    <Toaster />
</template>
