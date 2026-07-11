<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import JadwalController from '@/actions/App/Http/Controllers/JadwalController';

import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';

interface Kelas {
    id: number;
    nama_kelas: string;
    thn_ajaran: string;
}
interface Guru {
    id: number;
    nama: string;
    nip: string;
}
interface Tema {
    id: number;
    nama_tema: string;
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Jadwal', href: JadwalController.index().url },
            { title: 'Create', href: JadwalController.create().url },
        ],
    },
});

const page = usePage();
const kelas = computed(() => page.props.kelas as Kelas[]);
const gurus = computed(() => page.props.gurus as Guru[]);
const temas = computed(() => page.props.temas as Tema[]);
const canSelectGuru = computed(() => page.props.canSelectGuru as boolean);
const currentGuru = computed(() => page.props.currentGuru as Guru | null);
const form = useForm({
    kelas_id: '',
    guru_id: '',
    tema_id: '',
    tanggal: '',
    jam_mulai: '',
    jam_selesai: '',
});
const submit = () => form.post(JadwalController.store().url);
</script>

<template>
    <Head title="Tambah Jadwal" />
    <div class="max-w-2xl space-y-6 bg-background p-4 text-foreground">
        <div>
            <h1 class="text-2xl font-bold">Tambah Jadwal</h1>
            <p class="text-sm text-muted-foreground">Tambahkan jadwal baru.</p>
        </div>
        <div
            class="rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
        >
            <form class="space-y-6" @submit.prevent="submit">
                <div class="space-y-2">
                    <label for="kelas_id" class="text-sm font-medium"
                        >Kelas</label
                    ><select
                        id="kelas_id"
                        v-model="form.kelas_id"
                        class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground"
                    >
                        <option value="">Pilih kelas</option>
                        <option
                            v-for="item in kelas"
                            :key="item.id"
                            :value="String(item.id)"
                        >
                            {{ item.nama_kelas }} - {{ item.thn_ajaran }}
                        </option></select
                    ><InputError :message="form.errors.kelas_id" />
                </div>
                <div v-if="canSelectGuru" class="space-y-2">
                    <label for="guru_id" class="text-sm font-medium">Guru</label
                    ><select
                        id="guru_id"
                        v-model="form.guru_id"
                        class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground"
                    >
                        <option value="">Pilih guru</option>
                        <option
                            v-for="item in gurus"
                            :key="item.id"
                            :value="String(item.id)"
                        >
                            {{ item.nama }} - {{ item.nip }}
                        </option></select
                    ><InputError :message="form.errors.guru_id" />
                </div>
                <div v-else class="space-y-2">
                    <label class="text-sm font-medium">Guru</label>
                    <div
                        class="h-9 rounded-md border border-border bg-muted px-3 py-2 text-sm text-foreground"
                    >
                        {{ currentGuru?.nama }} - {{ currentGuru?.nip }}
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Guru ditentukan dari akun yang sedang login.
                    </p>
                </div>
                <div class="space-y-2">
                    <label for="tema_id" class="text-sm font-medium">Tema</label
                    ><select
                        id="tema_id"
                        v-model="form.tema_id"
                        class="h-9 w-full rounded-md border border-border bg-background px-3 text-sm text-foreground"
                    >
                        <option value="">Pilih tema</option>
                        <option
                            v-for="item in temas"
                            :key="item.id"
                            :value="String(item.id)"
                        >
                            {{ item.nama_tema }}
                        </option></select
                    ><InputError :message="form.errors.tema_id" />
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="space-y-2">
                        <label for="tanggal" class="text-sm font-medium"
                            >Tanggal</label
                        ><Input
                            id="tanggal"
                            v-model="form.tanggal"
                            type="date"
                        /><InputError :message="form.errors.tanggal" />
                    </div>
                    <div class="space-y-2">
                        <label for="jam_mulai" class="text-sm font-medium"
                            >Jam Mulai</label
                        ><Input
                            id="jam_mulai"
                            v-model="form.jam_mulai"
                            type="time"
                        /><InputError :message="form.errors.jam_mulai" />
                    </div>
                    <div class="space-y-2">
                        <label for="jam_selesai" class="text-sm font-medium"
                            >Jam Selesai</label
                        ><Input
                            id="jam_selesai"
                            v-model="form.jam_selesai"
                            type="time"
                        /><InputError :message="form.errors.jam_selesai" />
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button type="submit" :disabled="form.processing">{{
                        form.processing ? 'Menyimpan...' : 'Simpan'
                    }}</Button
                    ><Button as-child variant="outline"
                        ><Link :href="JadwalController.index().url"
                            >Batal</Link
                        ></Button
                    >
                </div>
            </form>
        </div>
    </div>
</template>
