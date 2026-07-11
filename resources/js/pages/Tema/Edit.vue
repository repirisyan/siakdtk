<script setup lang="ts">
import { computed, watch } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

import TemaController from '@/actions/App/Http/Controllers/TemaController';

import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';

interface Tema {
    id: number;
    nama_tema: string;
    thn_ajaran: string;
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Tema',
                href: TemaController.index().url,
            },
            {
                title: 'Edit',
                href: '#',
            },
        ],
    },
});

const page = usePage();

const tema = computed(() => page.props.tema as Tema);

const form = useForm({
    nama_tema: tema.value?.nama_tema ?? '',
    thn_ajaran: tema.value?.thn_ajaran ?? '',
});

watch(
    tema,
    (value) => {
        form.nama_tema = value?.nama_tema ?? '';
        form.thn_ajaran = value?.thn_ajaran ?? '';
    },
    { immediate: true },
);

const submit = () => {
    form.put(TemaController.update(tema.value.id).url);
};
</script>

<template>
    <Head :title="`Edit Tema - ${tema.nama_tema}`" />

    <div class="max-w-2xl space-y-6 p-4">
        <div>
            <h1 class="text-2xl font-bold">Edit Tema</h1>

            <p class="text-sm text-muted-foreground">Perbarui data tema.</p>
        </div>

        <div
            class="rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
        >
            <form class="space-y-6" @submit.prevent="submit">
                <div class="space-y-2">
                    <label for="nama_tema" class="text-sm font-medium">
                        Nama Tema
                    </label>

                    <Input
                        id="nama_tema"
                        v-model="form.nama_tema"
                        placeholder="Masukkan nama tema"
                    />

                    <InputError :message="form.errors.nama_tema" />
                </div>
                <div class="space-y-2">
                    <label for="thn_ajaran" class="text-sm font-medium"
                        >Tahun Ajaran</label
                    ><Input
                        id="thn_ajaran"
                        v-model="form.thn_ajaran"
                        type="number"
                    /><InputError :message="form.errors.thn_ajaran" />
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
                        <Link :href="TemaController.index().url"> Batal </Link>
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>
