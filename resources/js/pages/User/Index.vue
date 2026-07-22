<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import type { ColumnDef } from '@tanstack/vue-table';
import { computed, ref } from 'vue';

import UserController from '@/actions/App/Http/Controllers/UserController';
import SortableTableHeader from '@/components/SortableTableHeader.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';

interface User {
    id: number;
    name: string;
    email: string;
    status: boolean;
    email_verified_at: string | null;
    created_at: string;
    role: { role_name: string } | null;
    guru: { nama: string; nip: string | null } | null;
    siswa: { nama: string; nis: string | null } | null;
    siswas_count: number;
    kontens_count: number;
    guru_exists: boolean;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

const page = usePage();
const users = computed(
    () =>
        page.props.users as {
            data: User[];
            links: PaginationLink[];
        },
);
const filters = computed(
    () =>
        page.props.filters as {
            search: string | null;
            sort: string;
            direction: string;
        },
);
const search = ref(filters.value.search ?? '');

const visit = (url?: string | null) => {
    if (url) {
        router.visit(url, { preserveState: true, preserveScroll: true });
    }
};

const applySearch = () =>
    router.get(
        UserController.index().url,
        {
            search: search.value,
            sort: filters.value.sort,
            direction: filters.value.direction,
        },
        { preserveState: true, replace: true },
    );

const changeSort = (column: string) => {
    const direction =
        filters.value.sort === column && filters.value.direction === 'asc'
            ? 'desc'
            : 'asc';

    router.get(
        UserController.index().url,
        { search: search.value, sort: column, direction },
        { preserveState: true, replace: true },
    );
};

const relationType = (user: User) => {
    if (user.guru) {
        return 'Guru';
    }

    if (user.siswa) {
        return 'Siswa';
    }

    return 'User Biasa';
};

const deleteUser = (user: User) => {
    if (confirm(`Hapus user ${user.name}?`)) {
        router.delete(UserController.destroy(user.id).url);
    }
};

const toggleStatus = (user: User) => {
    const action = user.status ? 'nonaktifkan' : 'aktifkan';

    if (confirm(`${action} user ${user.name}?`)) {
        router.patch(UserController.toggleStatus(user.id).url);
    }
};
const verifyEmail = (user: User) => {
    if (confirm(`Verifikasi email ${user.email} secara manual?`)) {
        router.post(UserController.verifyEmail(user.id).url);
    }
};
const resendVerificationEmail = (user: User) => {
    if (confirm(`Kirim ulang email verifikasi ke ${user.email}?`)) {
        router.post(UserController.resendVerificationEmail(user.id).url);
    }
};

const columns: ColumnDef<User>[] = [
    { accessorKey: 'id', header: 'ID' },
    { accessorKey: 'name', header: 'Nama' },
    { accessorKey: 'email', header: 'Email' },
    {
        accessorFn: (row) => row.role?.role_name,
        id: 'role_name',
        header: 'Role',
    },
    { accessorKey: 'status', header: 'Status' },
    { accessorKey: 'email_verified_at', header: 'Status Email' },
    { id: 'relation_type', header: 'Tipe Relasi' },
    { accessorKey: 'siswas_count', header: 'Jumlah Anak' },
    { accessorKey: 'created_at', header: 'Dibuat' },
];
const tableColumns = [
    { key: 'id', label: 'ID' },
    { key: 'name', label: 'Nama' },
    { key: 'email', label: 'Email' },
    { key: 'role_name', label: 'Role' },
    { key: 'status', label: 'Status' },
    { key: 'email_verified_at', label: 'Status Email' },
    { key: 'relation_type', label: 'Tipe Relasi' },
    { key: 'siswas_count', label: 'Jumlah Anak' },
    { key: 'created_at', label: 'Dibuat' },
];
const table = useVueTable({
    get data() {
        return users.value.data;
    },
    columns,
    getCoreRowModel: getCoreRowModel(),
});
</script>

<template>
    <Head title="Kelola User" />
    <div class="space-y-4 bg-background p-4 text-foreground">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Kelola User</h1>
                <p class="text-sm text-muted-foreground">
                    Kelola akun login dan akses pengguna sistem.
                </p>
            </div>
            <Button @click="router.visit(UserController.create().url)"
                >Tambah User</Button
            >
        </div>

        <div class="flex gap-2">
            <Input
                v-model="search"
                placeholder="Cari user..."
                @keyup.enter="applySearch"
            />
            <Button @click="applySearch">Cari</Button>
        </div>

        <div
            class="overflow-x-auto rounded-xl border border-border bg-card text-card-foreground"
        >
            <table class="w-full min-w-max">
                <thead class="bg-muted/50">
                    <tr>
                        <SortableTableHeader
                            v-for="column in tableColumns"
                            :key="column.key"
                            :label="column.label"
                            :column="column.key"
                            :sort="filters.sort"
                            :direction="filters.direction"
                            @sort="changeSort"
                        />
                        <th class="px-4 py-3 text-left text-sm font-medium">
                            Action
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="row in table.getRowModel().rows"
                        :key="row.id"
                        class="border-t border-border hover:bg-muted/50"
                    >
                        <td class="px-4 py-3">{{ row.original.id }}</td>
                        <td class="px-4 py-3">{{ row.original.name }}</td>
                        <td class="px-4 py-3">{{ row.original.email }}</td>
                        <td class="px-4 py-3">
                            {{ row.original.role?.role_name ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="rounded-full px-2 py-1 text-xs font-medium"
                                :class="
                                    row.original.status
                                        ? 'bg-primary text-primary-foreground'
                                        : 'bg-muted text-muted-foreground'
                                "
                            >
                                {{ row.original.status ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="rounded-full px-2 py-1 text-xs font-medium"
                                :class="
                                    row.original.email_verified_at
                                        ? 'bg-secondary text-secondary-foreground'
                                        : 'bg-muted text-muted-foreground'
                                "
                                >{{
                                    row.original.email_verified_at
                                        ? 'Terverifikasi'
                                        : 'Belum Terverifikasi'
                                }}</span
                            >
                        </td>
                        <td class="px-4 py-3">
                            {{ relationType(row.original) }}
                        </td>
                        <td class="px-4 py-3">
                            {{
                                row.original.role?.role_name ===
                                'Orangtua Siswa'
                                    ? row.original.siswas_count
                                    : '-'
                            }}
                        </td>
                        <td class="px-4 py-3">
                            {{
                                new Date(
                                    row.original.created_at,
                                ).toLocaleString('id-ID')
                            }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                <Button
                                    variant="secondary"
                                    @click="
                                        router.visit(
                                            UserController.show(row.original.id)
                                                .url,
                                        )
                                    "
                                >
                                    Detail
                                </Button>
                                <Button
                                    variant="outline"
                                    @click="
                                        router.visit(
                                            UserController.edit(row.original.id)
                                                .url,
                                        )
                                    "
                                >
                                    Edit
                                </Button>
                                <Button
                                    v-if="!row.original.email_verified_at"
                                    variant="outline"
                                    @click="verifyEmail(row.original)"
                                    >Verifikasi Email</Button
                                >
                                <Button
                                    v-if="!row.original.email_verified_at"
                                    variant="outline"
                                    @click="
                                        resendVerificationEmail(row.original)
                                    "
                                    >Kirim Ulang Verifikasi</Button
                                >
                                <Button
                                    variant="outline"
                                    @click="toggleStatus(row.original)"
                                >
                                    {{
                                        row.original.status
                                            ? 'Nonaktifkan'
                                            : 'Aktifkan'
                                    }}
                                </Button>
                                <Button
                                    variant="destructive"
                                    :disabled="
                                        row.original.guru_exists ||
                                        row.original.siswas_count > 0 ||
                                        row.original.kontens_count > 0
                                    "
                                    :title="
                                        row.original.guru_exists ||
                                        row.original.siswas_count > 0 ||
                                        row.original.kontens_count > 0
                                            ? 'User memiliki data profil atau konten dan tidak dapat dihapus'
                                            : 'Hapus user'
                                    "
                                    @click="deleteUser(row.original)"
                                >
                                    Hapus
                                </Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!table.getRowModel().rows.length">
                        <td
                            colspan="9"
                            class="py-8 text-center text-muted-foreground"
                        >
                            Tidak ada data user
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap gap-2">
            <button
                v-for="link in users.links"
                :key="link.label"
                :disabled="!link.url"
                class="inline-flex h-9 items-center justify-center rounded-md border border-border px-3 text-sm"
                :class="{
                    'bg-primary text-primary-foreground': link.active,
                    'opacity-50': !link.url,
                }"
                @click="visit(link.url)"
                v-html="link.label"
            />
        </div>
    </div>
</template>
