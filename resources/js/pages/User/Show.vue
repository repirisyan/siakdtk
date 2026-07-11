<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import UserController from '@/actions/App/Http/Controllers/UserController';
import Button from '@/components/ui/button/Button.vue';

interface User {
    id: number;
    name: string;
    email: string;
    status: boolean;
    created_at: string;
    updated_at: string;
    avatar: string | null;
    role: { role_name: string } | null;
    guru: {
        nama: string;
        nip: string | null;
        email: string | null;
        nohp_guru: string | null;
    } | null;
    siswa: {
        nama: string;
        nis: string | null;
        nisn: string | null;
    } | null;
}

const page = usePage();
const user = computed(() => page.props.user as User);
</script>

<template>
    <Head :title="`Detail User - ${user.name}`" />
    <div class="max-w-3xl space-y-6 p-4">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Detail User</h1>
                <p class="text-sm text-muted-foreground">
                    Informasi akun login pengguna.
                </p>
            </div>
            <Button as-child variant="outline"
                ><Link :href="UserController.index().url">Kembali</Link></Button
            >
        </div>

        <div
            class="rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
        >
            <div class="mb-6 flex items-center gap-4">
                <img
                    v-if="user.avatar"
                    :src="user.avatar"
                    :alt="user.name"
                    class="h-20 w-20 rounded-full object-cover"
                />
                <div
                    v-else
                    class="flex h-20 w-20 items-center justify-center rounded-full bg-muted text-muted-foreground"
                >
                    {{ user.name.slice(0, 1) }}
                </div>
                <Button
                    v-if="user.avatar"
                    variant="outline"
                    @click="
                        router.delete(UserController.destroyPhoto(user.id).url)
                    "
                    >Hapus Foto</Button
                >
            </div>
            <h2 class="font-semibold">Data User</h2>
            <dl class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <dt class="text-sm text-muted-foreground">Nama</dt>
                    <dd class="font-medium">{{ user.name }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">Email</dt>
                    <dd class="font-medium">{{ user.email }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">Role</dt>
                    <dd class="font-medium">
                        {{ user.role?.role_name ?? '-' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">Status</dt>
                    <dd class="font-medium">
                        {{ user.status ? 'Aktif' : 'Nonaktif' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">
                        Tanggal Dibuat
                    </dt>
                    <dd class="font-medium">
                        {{ new Date(user.created_at).toLocaleString('id-ID') }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">
                        Tanggal Diupdate
                    </dt>
                    <dd class="font-medium">
                        {{ new Date(user.updated_at).toLocaleString('id-ID') }}
                    </dd>
                </div>
            </dl>
        </div>

        <div
            v-if="user.guru"
            class="rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
        >
            <h2 class="font-semibold">Informasi Guru</h2>
            <dl class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <dt class="text-sm text-muted-foreground">Nama</dt>
                    <dd class="font-medium">{{ user.guru.nama }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">NIP</dt>
                    <dd class="font-medium">{{ user.guru.nip ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">Email Guru</dt>
                    <dd class="font-medium">{{ user.guru.email ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">No. HP</dt>
                    <dd class="font-medium">
                        {{ user.guru.nohp_guru ?? '-' }}
                    </dd>
                </div>
            </dl>
        </div>

        <div
            v-if="user.siswa"
            class="rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
        >
            <h2 class="font-semibold">Informasi Siswa</h2>
            <dl class="mt-4 grid gap-4 md:grid-cols-3">
                <div>
                    <dt class="text-sm text-muted-foreground">Nama</dt>
                    <dd class="font-medium">{{ user.siswa.nama }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">NIS</dt>
                    <dd class="font-medium">{{ user.siswa.nis ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">NISN</dt>
                    <dd class="font-medium">{{ user.siswa.nisn ?? '-' }}</dd>
                </div>
            </dl>
        </div>
    </div>
</template>
