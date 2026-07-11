<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import InputError from '@/components/InputError.vue';
const page = usePage();
const subTema = page.props.subTema as {
    id: number;
    tema_id: number;
    nama_sub_tema: string;
    deskripsi: string | null;
};
const temas = page.props.temas as {
    id: number;
    nama_tema: string;
    thn_ajaran: string;
}[];
const form = useForm({
    tema_id: String(subTema.tema_id),
    nama_sub_tema: subTema.nama_sub_tema,
    deskripsi: subTema.deskripsi ?? '',
});
</script>
<template>
    <Head title="Edit Sub Tema" />
    <div class="max-w-2xl space-y-5 p-4">
        <h1 class="text-2xl font-bold">Edit Sub Tema</h1>
        <form
            class="space-y-4 rounded-xl border border-border bg-card p-6"
            @submit.prevent="form.put(`/sub-tema/${subTema.id}`)"
        >
            <div>
                <label>Tema</label
                ><select
                    v-model="form.tema_id"
                    class="mt-1 h-10 w-full rounded-md border border-border bg-background px-3"
                >
                    <option
                        v-for="tema in temas"
                        :key="tema.id"
                        :value="String(tema.id)"
                    >
                        {{ tema.nama_tema }} · {{ tema.thn_ajaran }}
                    </option>
                </select>
            </div>
            <div>
                <label>Nama Sub Tema</label
                ><Input v-model="form.nama_sub_tema" /><InputError
                    :message="form.errors.nama_sub_tema"
                />
            </div>
            <div>
                <label>Deskripsi</label
                ><textarea
                    v-model="form.deskripsi"
                    class="mt-1 min-h-24 w-full rounded-md border border-border bg-background p-3"
                />
            </div>
            <Button :disabled="form.processing">Simpan Perubahan</Button
            ><Button as-child variant="outline" class="ml-2"
                ><Link href="/sub-tema">Batal</Link></Button
            >
        </form>
    </div>
</template>
