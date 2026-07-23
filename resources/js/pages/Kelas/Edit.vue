<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

import KelasController from '@/actions/App/Http/Controllers/KelasController';

import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';

interface Kelas {
    id: number;
    nama_kelas: string;
    thn_ajaran: string;
    tahun_ajaran_id: number | null;
    semester: number;
}
interface TahunAjaran {
    id: number;
    tahun_ajaran: string;
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Kelas', href: KelasController.index().url },
            { title: 'Edit', href: '#' },
        ],
    },
});

const page = usePage();
const kelas = computed(() => page.props.kelas as Kelas);
const tahunAjarans = computed(() => page.props.tahunAjarans as TahunAjaran[]);

const form = useForm({
    nama_kelas: kelas.value?.nama_kelas ?? '',
    tahun_ajaran_id: kelas.value?.tahun_ajaran_id
        ? String(kelas.value.tahun_ajaran_id)
        : '',
    semester: kelas.value?.semester ? String(kelas.value.semester) : '',
});

watch(
    kelas,
    (value) => {
        form.nama_kelas = value?.nama_kelas ?? '';
        form.tahun_ajaran_id = value?.tahun_ajaran_id
            ? String(value.tahun_ajaran_id)
            : '';
        form.semester = value?.semester ? String(value.semester) : '';
    },
    { immediate: true },
);

const submit = () => {
    form.put(KelasController.update(kelas.value.id).url);
};
</script>

<template>
    <Head :title="`Edit Kelas - ${kelas.nama_kelas}`" />

    <div class="max-w-2xl space-y-6 bg-background p-4 text-foreground">
        <div>
            <h1 class="text-2xl font-bold">Edit Kelas</h1>
            <p class="text-sm text-muted-foreground">Perbarui data kelas.</p>
        </div>

        <div
            class="rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
        >
            <form class="space-y-6" @submit.prevent="submit">
                <div class="space-y-2">
                    <label for="nama_kelas" class="text-sm font-medium"
                        >Nama Kelas</label
                    >
                    <Input
                        id="nama_kelas"
                        v-model="form.nama_kelas"
                        placeholder="Masukkan nama kelas"
                    />
                    <InputError :message="form.errors.nama_kelas" />
                </div>

                <div class="space-y-2">
                    <label for="tahun_ajaran_id" class="text-sm font-medium"
                        >Tahun Ajaran</label
                    >
                    <select
                        id="tahun_ajaran_id"
                        v-model="form.tahun_ajaran_id"
                        class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground"
                    >
                        <option value="">Pilih tahun ajaran</option>
                        <option
                            v-for="item in tahunAjarans"
                            :key="item.id"
                            :value="String(item.id)"
                        >
                            {{ item.tahun_ajaran }}
                        </option>
                    </select>
                    <InputError :message="form.errors.tahun_ajaran_id" />
                </div>

                <div class="space-y-2">
                    <label for="semester" class="text-sm font-medium"
                        >Semester</label
                    >
                    <select
                        id="semester"
                        v-model="form.semester"
                        class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground"
                    >
                        <option value="">Pilih semester</option>
                        <option value="1">Semester 1</option>
                        <option value="2">Semester 2</option>
                    </select>
                    <InputError :message="form.errors.semester" />
                </div>

                <div class="flex gap-2">
                    <Button type="submit" :disabled="form.processing">
                        {{
                            form.processing
                                ? 'Menyimpan...'
                                : 'Simpan Perubahan'
                        }}
                    </Button>
                    <Button as-child variant="outline">
                        <Link :href="KelasController.index().url">Batal</Link>
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>
