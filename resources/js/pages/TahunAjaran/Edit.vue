<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

import TahunAjaranController from '@/actions/App/Http/Controllers/TahunAjaranController';
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';

interface TahunAjaran {
    id: number;
    tahun_ajaran: string;
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Tahun Ajaran', href: TahunAjaranController.index().url },
            { title: 'Edit', href: '#' },
        ],
    },
});

const page = usePage();
const tahunAjaran = computed(() => page.props.tahunAjaran as TahunAjaran);
const form = useForm({ tahun_ajaran: tahunAjaran.value.tahun_ajaran });

watch(
    tahunAjaran,
    (value) => {
        form.tahun_ajaran = value.tahun_ajaran;
    },
    { immediate: true },
);

const submit = () =>
    form.put(TahunAjaranController.update(tahunAjaran.value.id).url);
</script>

<template>
    <Head :title="`Edit Tahun Ajaran - ${tahunAjaran.tahun_ajaran}`" />
    <div class="max-w-2xl space-y-6 bg-background p-4 text-foreground">
        <div>
            <h1 class="text-2xl font-bold">Edit Tahun Ajaran</h1>
            <p class="text-sm text-muted-foreground">
                Perbarui tahun ajaran beserta kelas yang terhubung.
            </p>
        </div>
        <form
            class="space-y-6 rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
            @submit.prevent="submit"
        >
            <div class="space-y-2">
                <label for="tahun_ajaran" class="text-sm font-medium"
                    >Tahun Ajaran</label
                >
                <Input
                    id="tahun_ajaran"
                    v-model="form.tahun_ajaran"
                    inputmode="numeric"
                    maxlength="4"
                />
                <InputError :message="form.errors.tahun_ajaran" />
            </div>
            <div class="flex gap-2">
                <Button type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                </Button>
                <Button as-child variant="outline">
                    <Link :href="TahunAjaranController.index().url">Batal</Link>
                </Button>
            </div>
        </form>
    </div>
</template>
