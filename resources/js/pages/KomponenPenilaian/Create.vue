<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import KomponenPenilaianController from '@/actions/App/Http/Controllers/KomponenPenilaianController';
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';

const page = usePage();
const subTema = computed(
    () =>
        page.props.subTema as {
            id: number;
            nama_sub_tema: string;
            tema: { nama_tema: string; thn_ajaran: string };
        } | null,
);
const form = useForm({
    sub_tema_id: subTema.value ? String(subTema.value.id) : '',
    nama_komponen: '',
    deskripsi: '',
    status: true,
});
const submit = () => form.post(KomponenPenilaianController.store().url);
</script>

<template>
    <Head title="Tambah Komponen Penilaian" />
    <div class="max-w-2xl space-y-6 bg-background p-4 text-foreground">
        <div>
            <h1 class="text-2xl font-bold">Tambah Komponen Penilaian</h1>
            <p class="text-sm text-muted-foreground">
                {{
                    subTema
                        ? `${subTema.tema.nama_tema} · ${subTema.nama_sub_tema}`
                        : 'Sub Tema tidak ditemukan.'
                }}
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
                <Button
                    :disabled="form.processing || !form.sub_tema_id"
                    type="submit"
                    >{{ form.processing ? 'Menyimpan...' : 'Simpan' }}</Button
                ><Button as-child variant="outline"
                    ><Link
                        :href="
                            subTema
                                ? `/komponen-penilaian?sub_tema_id=${subTema.id}`
                                : '/sub-tema'
                        "
                        >Batal</Link
                    ></Button
                >
            </div>
        </form>
    </div>
</template>
