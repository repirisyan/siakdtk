<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import InputError from '@/components/InputError.vue';
import KomponenPenilaianController from '@/actions/App/Http/Controllers/KomponenPenilaianController';

interface KomponenPenilaian {
    id: number;
    sub_tema_id: number;
    nama_komponen: string;
    deskripsi: string | null;
    status: boolean;
    sub_tema: { nama_sub_tema: string; tema: { nama_tema: string } };
}
const page = usePage();
const component = computed(
    () => page.props.komponenPenilaian as KomponenPenilaian,
);
const form = useForm({
    sub_tema_id: String(component.value.sub_tema_id),
    nama_komponen: component.value.nama_komponen,
    deskripsi: component.value.deskripsi ?? '',
    status: component.value.status,
});
const submit = () =>
    form.put(KomponenPenilaianController.update(component.value.id).url);
</script>

<template>
    <Head title="Edit Komponen Penilaian" />
    <div class="max-w-2xl space-y-6 bg-background p-4 text-foreground">
        <div>
            <h1 class="text-2xl font-bold">Edit Komponen Penilaian</h1>
            <p class="text-sm text-muted-foreground">
                {{ component.sub_tema.tema.nama_tema }} ·
                {{ component.sub_tema.nama_sub_tema }}
            </p>
        </div>
        <form
            class="space-y-5 rounded-xl border border-border bg-card p-6 text-card-foreground"
            @submit.prevent="submit"
        >
            <div class="space-y-2">
                <label for="nama_komponen" class="text-sm font-medium"
                    >Nama Komponen</label
                ><Input
                    id="nama_komponen"
                    v-model="form.nama_komponen"
                    required
                    autofocus
                /><InputError :message="form.errors.nama_komponen" />
            </div>
            <div class="space-y-2">
                <label for="deskripsi" class="text-sm font-medium"
                    >Deskripsi</label
                ><textarea
                    id="deskripsi"
                    v-model="form.deskripsi"
                    class="min-h-28 w-full rounded-md border border-border bg-background p-3 text-foreground"
                /><InputError :message="form.errors.deskripsi" />
            </div>
            <label class="flex items-center gap-2 text-sm"
                ><input
                    v-model="form.status"
                    type="checkbox"
                    class="size-4 accent-primary"
                />
                Aktif</label
            ><InputError :message="form.errors.sub_tema_id" />
            <div class="flex gap-2">
                <Button :disabled="form.processing" type="submit">{{
                    form.processing ? 'Menyimpan...' : 'Simpan Perubahan'
                }}</Button
                ><Button as-child variant="outline"
                    ><Link
                        :href="`/komponen-penilaian?sub_tema_id=${component.sub_tema_id}`"
                        >Batal</Link
                    ></Button
                >
            </div>
        </form>
    </div>
</template>
