<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import SppController from '@/actions/App/Http/Controllers/SppController';
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';

interface Siswa {
    id: number;
    nama: string;
    nis: string | null;
    kelas: { nama_kelas: string; thn_ajaran: string } | null;
}
const page = usePage();
const siswas = computed(() => page.props.siswas as Siswa[]);
const jenisPembayarans = computed(
    () => page.props.jenisPembayarans as { id: number; nama_jenis: string }[],
);
const form = useForm({
    siswa_id: '',
    thn_ajaran: '',
    jenis_pembayaran_id: '',
    tanggal_tagihan: '',
    jatuh_tempo: '',
    nominal: '',
    keterangan: '',
});
const submit = () => form.post(SppController.store().url);
</script>

<template>
    <Head title="Tambah Pembayaran" />
    <div class="max-w-2xl space-y-6 p-4">
        <div>
            <h1 class="text-2xl font-bold">Tambah Pembayaran</h1>
            <p class="text-sm text-muted-foreground">
                Buat tagihan pembayaran baru untuk siswa.
            </p>
        </div>
        <div
            class="rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
        >
            <form class="space-y-6" @submit.prevent="submit">
                <div class="space-y-2">
                    <label for="siswa_id" class="text-sm font-medium"
                        >Siswa</label
                    ><select
                        id="siswa_id"
                        v-model="form.siswa_id"
                        class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground"
                    >
                        <option value="">Pilih siswa</option>
                        <option
                            v-for="siswa in siswas"
                            :key="siswa.id"
                            :value="String(siswa.id)"
                        >
                            {{ siswa.nama }} · {{ siswa.nis ?? '-' }} ·
                            {{ siswa.kelas?.nama_kelas ?? '-' }}
                        </option></select
                    ><InputError :message="form.errors.siswa_id" />
                </div>
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <label for="thn_ajaran" class="text-sm font-medium"
                            >Tahun Ajaran</label
                        ><Input
                            id="thn_ajaran"
                            v-model="form.thn_ajaran"
                            placeholder="Contoh: 2026"
                        /><InputError :message="form.errors.thn_ajaran" />
                    </div>
                    <div class="space-y-2">
                        <label
                            for="jenis_pembayaran_id"
                            class="text-sm font-medium"
                            >Jenis Pembayaran</label
                        ><select
                            id="jenis_pembayaran_id"
                            v-model="form.jenis_pembayaran_id"
                            class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground"
                        >
                            <option value="">Pilih jenis pembayaran</option>
                            <option
                                v-for="jenis in jenisPembayarans"
                                :key="jenis.id"
                                :value="String(jenis.id)"
                            >
                                {{ jenis.nama_jenis }}
                            </option></select
                        ><InputError
                            :message="form.errors.jenis_pembayaran_id"
                        />
                    </div>
                    <div class="space-y-2">
                        <label for="tanggal_tagihan" class="text-sm font-medium"
                            >Tanggal Tagihan</label
                        ><Input
                            id="tanggal_tagihan"
                            v-model="form.tanggal_tagihan"
                            type="date"
                        /><InputError :message="form.errors.tanggal_tagihan" />
                    </div>
                    <div class="space-y-2">
                        <label for="jatuh_tempo" class="text-sm font-medium"
                            >Jatuh Tempo</label
                        ><Input
                            id="jatuh_tempo"
                            v-model="form.jatuh_tempo"
                            type="date"
                        /><InputError :message="form.errors.jatuh_tempo" />
                    </div>
                    <div class="space-y-2">
                        <label for="nominal" class="text-sm font-medium"
                            >Nominal Tagihan</label
                        ><Input
                            id="nominal"
                            v-model="form.nominal"
                            type="number"
                            min="0"
                            step="0.01"
                            placeholder="0"
                        /><InputError :message="form.errors.nominal" />
                    </div>
                </div>
                <div class="space-y-2">
                    <label for="keterangan" class="text-sm font-medium"
                        >Keterangan</label
                    ><textarea
                        id="keterangan"
                        v-model="form.keterangan"
                        class="min-h-28 w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground"
                        placeholder="Keterangan tagihan (opsional)"
                    /><InputError :message="form.errors.keterangan" />
                </div>
                <div class="flex gap-2">
                    <Button type="submit" :disabled="form.processing">{{
                        form.processing ? 'Menyimpan...' : 'Simpan'
                    }}</Button
                    ><Button as-child variant="outline"
                        ><Link :href="SppController.index().url"
                            >Batal</Link
                        ></Button
                    >
                </div>
            </form>
        </div>
    </div>
</template>
