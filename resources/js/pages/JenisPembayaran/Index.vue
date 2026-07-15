<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Button from '@/components/ui/button/Button.vue';

const page = usePage();
const items = computed(
    () =>
        page.props.jenisPembayarans as {
            data: {
                id: number;
                nama_jenis: string;
                deskripsi: string | null;
                status: boolean;
            }[];
        },
);
const remove = (id: number) => {
    if (window.confirm('Hapus jenis pembayaran ini?'))
        router.delete(`/jenis-pembayaran/${id}`);
};
</script>
<template>
    <Head title="Jenis Pembayaran" />
    <div class="space-y-6 bg-background p-4 text-foreground">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Jenis Pembayaran</h1>
                <p class="text-sm text-muted-foreground">
                    Master jenis tagihan yang digunakan pada Kelola Pembayaran.
                </p>
            </div>
            <div class="flex gap-2">
                <Button as-child variant="outline"
                    ><Link href="/spp"
                        >Kembali ke Kelola Pembayaran</Link
                    ></Button
                ><Button as-child
                    ><Link href="/jenis-pembayaran/create"
                        >Tambah Jenis Pembayaran</Link
                    ></Button
                >
            </div>
        </div>
        <div
            class="overflow-x-auto rounded-xl border border-border bg-card text-card-foreground"
        >
            <table class="w-full">
                <thead class="bg-muted/50">
                    <tr>
                        <th class="px-4 py-3 text-left">Jenis Pembayaran</th>
                        <th class="px-4 py-3 text-left">Deskripsi</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="item in items.data"
                        :key="item.id"
                        class="border-t border-border"
                    >
                        <td class="px-4 py-3 font-medium">
                            {{ item.nama_jenis }}
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ item.deskripsi ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="rounded-md px-2 py-1 text-sm"
                                :class="
                                    item.status
                                        ? 'bg-primary/15 text-foreground'
                                        : 'bg-muted text-muted-foreground'
                                "
                                >{{ item.status ? 'Aktif' : 'Nonaktif' }}</span
                            >
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <Button as-child size="sm" variant="outline"
                                    ><Link
                                        :href="`/jenis-pembayaran/${item.id}/edit`"
                                        >Edit</Link
                                    ></Button
                                ><Button
                                    size="sm"
                                    variant="destructive"
                                    @click="remove(item.id)"
                                    >Hapus</Button
                                >
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!items.data.length">
                        <td
                            colspan="4"
                            class="p-8 text-center text-muted-foreground"
                        >
                            Belum ada jenis pembayaran.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
