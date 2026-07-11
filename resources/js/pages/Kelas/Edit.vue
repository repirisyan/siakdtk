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

const form = useForm({
    nama_kelas: kelas.value?.nama_kelas ?? '',
    thn_ajaran: kelas.value?.thn_ajaran ?? '',
});

watch(
    kelas,
    (value) => {
        form.nama_kelas = value?.nama_kelas ?? '';
        form.thn_ajaran = value?.thn_ajaran ?? '';
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
                    <label for="thn_ajaran" class="text-sm font-medium"
                        >Tahun Ajaran</label
                    >
                    <Input
                        id="thn_ajaran"
                        v-model="form.thn_ajaran"
                        inputmode="numeric"
                        maxlength="4"
                        placeholder="Contoh: 2026"
                    />
                    <InputError :message="form.errors.thn_ajaran" />
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
