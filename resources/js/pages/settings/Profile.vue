<script setup lang="ts">
import { Form, Head, router, useForm, usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
// import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Informasi Profil',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const user = computed(
    () =>
        page.props.auth.user as {
            name: string;
            email: string;
            avatar?: string | null;
            email_verified_at?: string | null;
            role?: { role_name?: string } | null;
        },
);
const profileDescription = computed(() =>
    user.value.role?.role_name === 'Orangtua Siswa'
        ? 'Kelola data akun yang digunakan untuk mengakses informasi anak Anda.'
        : 'Kelola informasi akun dan alamat email yang digunakan untuk masuk ke sistem.',
);
const preview = ref<string | null>(null);
const photoForm = useForm<{ foto_profil: File | null }>({ foto_profil: null });
const onPhotoChange = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    photoForm.foto_profil = file;
    preview.value = file ? URL.createObjectURL(file) : null;
};
const uploadPhoto = () =>
    photoForm.post(ProfileController.updatePhoto().url, {
        forceFormData: true,
        onSuccess: () => {
            preview.value = null;
            photoForm.reset();
        },
    });
const deletePhoto = () => router.delete(ProfileController.destroyPhoto().url);
</script>

<template>
    <Head title="Informasi Profil" />

    <h1 class="sr-only">Informasi Profil</h1>

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="Informasi Profil"
            :description="profileDescription"
        />

        <div
            class="rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
        >
            <h2 class="font-semibold">Foto Profil</h2>
            <p class="mt-1 text-sm text-muted-foreground">
                Unggah foto profil untuk mempermudah identifikasi akun Anda.
            </p>
            <div class="mt-4 flex flex-wrap items-center gap-4">
                <img
                    v-if="preview || user.avatar"
                    :src="preview ?? user.avatar ?? undefined"
                    :alt="user.name"
                    class="h-20 w-20 rounded-full object-cover"
                />
                <div
                    v-else
                    class="flex h-20 w-20 items-center justify-center rounded-full bg-muted text-muted-foreground"
                >
                    {{ user.name?.slice(0, 1) }}
                </div>
                <form class="space-y-2" @submit.prevent="uploadPhoto">
                    <input
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp"
                        class="block w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground"
                        @change="onPhotoChange"
                    />
                    <InputError :message="photoForm.errors.foto_profil" />
                    <div class="flex gap-2">
                        <Button
                            type="submit"
                            :disabled="
                                !photoForm.foto_profil || photoForm.processing
                            "
                            >{{
                                photoForm.processing
                                    ? 'Mengunggah...'
                                    : 'Ganti Foto'
                            }}</Button
                        ><Button
                            v-if="user.avatar"
                            type="button"
                            variant="outline"
                            @click="deletePhoto"
                            >Hapus Foto</Button
                        >
                    </div>
                </form>
            </div>
        </div>

        <Form
            v-bind="ProfileController.update.form()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="name">Nama Lengkap</Label>
                <Input
                    id="name"
                    class="mt-1 block w-full"
                    name="name"
                    :default-value="user.name"
                    required
                    autocomplete="name"
                    placeholder="Masukkan nama lengkap"
                />
                <InputError class="mt-2" :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    name="email"
                    :default-value="user.email"
                    required
                    autocomplete="username"
                    placeholder="Masukkan email"
                />
                <InputError class="mt-2" :message="errors.email" />
            </div>

            <div v-if="page.props.mustVerifyEmail && !user.email_verified_at">
                <p class="-mt-4 text-sm text-muted-foreground">
                    Alamat email Anda belum diverifikasi. Silakan lakukan
                    verifikasi email untuk mengakses seluruh fitur sistem.
                    <Link
                        :href="send()"
                        as="button"
                        class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                    >
                        Kirim ulang email verifikasi.
                    </Link>
                </p>

                <div
                    v-if="page.props.status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    Tautan verifikasi baru sedang diproses dan akan segera
                    dikirim ke email Anda.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing" data-test="update-profile-button"
                    >Simpan Perubahan</Button
                >
            </div>
        </Form>
    </div>

    <!-- <DeleteUser /> -->
</template>
