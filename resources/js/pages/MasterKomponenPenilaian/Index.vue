<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import MasterKomponenPenilaianController from '@/actions/App/Http/Controllers/MasterKomponenPenilaianController';
import SortableTableHeader from '@/components/SortableTableHeader.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';

interface MasterKomponenPenilaian {
    id: number;
    nama_komponen: string;
    deskripsi: string | null;
    status: boolean;
    created_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

const page = usePage();
const masterKomponenPenilaians = computed(
    () =>
        page.props.masterKomponenPenilaians as {
            data: MasterKomponenPenilaian[];
            links: PaginationLink[];
        },
);
const filters = computed(
    () =>
        page.props.filters as {
            search: string | null;
            sort: string;
            direction: string;
            status: string | null;
        },
);
const search = ref(filters.value.search ?? '');
const status = ref(filters.value.status ?? '');

const applyFilters = () => {
    router.get(
        MasterKomponenPenilaianController.index().url,
        {
            search: search.value,
            status: status.value,
            sort: filters.value.sort,
            direction: filters.value.direction,
        },
        { preserveState: true, replace: true },
    );
};

const changeSort = (column: string) => {
    const direction =
        filters.value.sort === column && filters.value.direction === 'asc'
            ? 'desc'
            : 'asc';

    router.get(
        MasterKomponenPenilaianController.index().url,
        { search: search.value, status: status.value, sort: column, direction },
        { preserveState: true, replace: true },
    );
};

const remove = (item: MasterKomponenPenilaian) => {
    if (window.confirm(`Hapus master komponen ${item.nama_komponen}?`)) {
        router.delete(MasterKomponenPenilaianController.destroy(item.id).url);
    }
};
</script>

<template>
    <Head title="Master Komponen Penilaian" />

    <div class="space-y-6 bg-background p-4 text-foreground">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Master Komponen Penilaian</h1>
                <p class="text-sm text-muted-foreground">
                    Komponen aktif otomatis disalin saat Sub Tema baru dibuat.
                </p>
            </div>
            <Button
                @click="
                    router.visit(MasterKomponenPenilaianController.create().url)
                "
            >
                Tambah Komponen
            </Button>
        </div>

        <div class="flex flex-wrap gap-2">
            <Input
                v-model="search"
                class="max-w-sm"
                placeholder="Cari komponen penilaian..."
                @keyup.enter="applyFilters"
            />
            <select
                v-model="status"
                class="h-10 rounded-md border border-border bg-background px-3 text-sm text-foreground"
                @change="applyFilters"
            >
                <option value="">Semua Status</option>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
            </select>
            <Button variant="outline" @click="applyFilters">Cari</Button>
        </div>

        <div
            class="overflow-x-auto rounded-xl border border-border bg-card text-card-foreground"
        >
            <table class="w-full">
                <thead class="bg-muted/50">
                    <tr>
                        <SortableTableHeader
                            label="ID"
                            column="id"
                            :sort="filters.sort"
                            :direction="filters.direction"
                            @sort="changeSort"
                        />
                        <SortableTableHeader
                            label="Nama Komponen"
                            column="nama_komponen"
                            :sort="filters.sort"
                            :direction="filters.direction"
                            @sort="changeSort"
                        />
                        <th class="px-4 py-3 text-left">Deskripsi</th>
                        <SortableTableHeader
                            label="Status"
                            column="status"
                            :sort="filters.sort"
                            :direction="filters.direction"
                            @sort="changeSort"
                        />
                        <SortableTableHeader
                            label="Dibuat"
                            column="created_at"
                            :sort="filters.sort"
                            :direction="filters.direction"
                            @sort="changeSort"
                        />
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="item in masterKomponenPenilaians.data"
                        :key="item.id"
                        class="border-t border-border hover:bg-muted/50"
                    >
                        <td class="px-4 py-3">{{ item.id }}</td>
                        <td class="px-4 py-3 font-medium">
                            {{ item.nama_komponen }}
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ item.deskripsi ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="rounded-full px-2 py-1 text-xs font-medium"
                                :class="
                                    item.status
                                        ? 'bg-primary text-primary-foreground'
                                        : 'bg-muted text-muted-foreground'
                                "
                            >
                                {{ item.status ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            {{
                                new Date(item.created_at).toLocaleString(
                                    'id-ID',
                                )
                            }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <Button
                                    size="sm"
                                    variant="outline"
                                    @click="
                                        router.visit(
                                            MasterKomponenPenilaianController.show(
                                                item.id,
                                            ).url,
                                        )
                                    "
                                    >Detail</Button
                                >
                                <Button
                                    size="sm"
                                    variant="outline"
                                    @click="
                                        router.visit(
                                            MasterKomponenPenilaianController.edit(
                                                item.id,
                                            ).url,
                                        )
                                    "
                                    >Edit</Button
                                >
                                <Button
                                    size="sm"
                                    variant="destructive"
                                    @click="remove(item)"
                                    >Hapus</Button
                                >
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!masterKomponenPenilaians.data.length">
                        <td
                            colspan="6"
                            class="p-8 text-center text-muted-foreground"
                        >
                            Belum ada master komponen penilaian.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            class="flex flex-wrap gap-2"
            v-if="masterKomponenPenilaians.links.length > 3"
        >
            <Button
                v-for="link in masterKomponenPenilaians.links"
                :key="link.label"
                size="sm"
                :variant="link.active ? 'default' : 'outline'"
                :disabled="!link.url"
                @click="
                    link.url &&
                    router.visit(link.url, {
                        preserveState: true,
                        preserveScroll: true,
                    })
                "
            >
                <span v-html="link.label" />
            </Button>
        </div>
    </div>
</template>
