<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import UserController from '@/actions/App/Http/Controllers/UserController';
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';

interface Role {
    id: number;
    role_name: string;
}

const page = usePage();
const roles = computed(() => page.props.roles as Role[]);
const form = useForm({
    name: '',
    email: '',
    role_id: '',
    password: '',
    password_confirmation: '',
    status: '1',
    foto_profil: null as File | null,
});

const onPhotoChange = (event: Event) => {
    form.foto_profil = (event.target as HTMLInputElement).files?.[0] ?? null;
};
const submit = () =>
    form.post(UserController.store().url, { forceFormData: true });
</script>

<template>
    <Head title="Tambah User" />
    <div class="max-w-2xl space-y-6 p-4">
        <div>
            <h1 class="text-2xl font-bold">Tambah User</h1>
            <p class="text-sm text-muted-foreground">
                Tambahkan akun login baru.
            </p>
        </div>
        <div
            class="rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
        >
            <form class="space-y-6" @submit.prevent="submit">
                <div class="space-y-2">
                    <label for="name" class="text-sm font-medium">Nama</label>
                    <Input
                        id="name"
                        v-model="form.name"
                        placeholder="Masukkan nama"
                    />
                    <InputError :message="form.errors.name" />
                </div>
                <div class="space-y-2">
                    <label for="foto_profil" class="text-sm font-medium"
                        >Foto Profil</label
                    ><input
                        id="foto_profil"
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp"
                        class="block w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground"
                        @change="onPhotoChange"
                    /><InputError :message="form.errors.foto_profil" />
                </div>
                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium">Email</label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="Masukkan email"
                    />
                    <InputError :message="form.errors.email" />
                </div>
                <div class="space-y-2">
                    <label for="role_id" class="text-sm font-medium"
                        >Role</label
                    >
                    <select
                        id="role_id"
                        v-model="form.role_id"
                        class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground"
                    >
                        <option value="">Pilih role</option>
                        <option
                            v-for="role in roles"
                            :key="role.id"
                            :value="String(role.id)"
                        >
                            {{ role.role_name }}
                        </option>
                    </select>
                    <InputError :message="form.errors.role_id" />
                </div>
                <div class="space-y-2">
                    <label for="status" class="text-sm font-medium"
                        >Status</label
                    >
                    <select
                        id="status"
                        v-model="form.status"
                        class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground"
                    >
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                    <InputError :message="form.errors.status" />
                </div>
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <label for="password" class="text-sm font-medium"
                            >Password</label
                        >
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            placeholder="Minimal 8 karakter"
                        />
                        <InputError :message="form.errors.password" />
                    </div>
                    <div class="space-y-2">
                        <label
                            for="password_confirmation"
                            class="text-sm font-medium"
                            >Konfirmasi Password</label
                        >
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            placeholder="Ulangi password"
                        />
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button type="submit" :disabled="form.processing">{{
                        form.processing ? 'Menyimpan...' : 'Simpan'
                    }}</Button>
                    <Button as-child variant="outline"
                        ><Link :href="UserController.index().url"
                            >Batal</Link
                        ></Button
                    >
                </div>
            </form>
        </div>
    </div>
</template>
