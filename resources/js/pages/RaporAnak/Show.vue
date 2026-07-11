<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import RaporAnakController from '@/actions/App/Http/Controllers/RaporAnakController';
import Button from '@/components/ui/button/Button.vue';

interface Rapor {
    thn_ajaran: string;
    approved_at: string | null;
    details: {
        keterangan: string;
        tema: { nama_tema: string };
        guru: { nama: string; nip: string | null };
    }[];
    approver: { name: string; email: string } | null;
}

interface Siswa {
    nama: string;
    nis: string | null;
    kelas: { nama_kelas: string } | null;
}

const page = usePage();
const rapor = computed(() => page.props.rapor as Rapor);
const siswa = computed(() => page.props.siswa as Siswa);
const printRapor = () => window.print();
</script>

<template>
    <Head title="Rapor Anak" />
    <div class="max-w-3xl space-y-6 p-4 text-foreground">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Detail Rapor Anak</h1>
                <p class="text-sm text-muted-foreground">
                    Catatan perkembangan siswa yang telah disetujui.
                </p>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" @click="printRapor"
                    >Cetak / Simpan PDF</Button
                ><Button as-child variant="outline"
                    ><Link :href="RaporAnakController.index().url"
                        >Kembali</Link
                    ></Button
                >
            </div>
        </div>

        <div
            class="rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
        >
            <dl class="grid gap-4 md:grid-cols-2">
                <div>
                    <dt class="text-sm text-muted-foreground">Nama Siswa</dt>
                    <dd class="font-medium">{{ siswa.nama }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">NIS</dt>
                    <dd class="font-medium">{{ siswa.nis ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">Kelas</dt>
                    <dd class="font-medium">
                        {{ siswa.kelas?.nama_kelas ?? '-' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">Tahun Ajaran</dt>
                    <dd class="font-medium">{{ rapor.thn_ajaran }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">
                        Kepala Sekolah
                    </dt>
                    <dd class="font-medium">
                        {{ rapor.approver?.name ?? '-' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">
                        Tanggal Validasi
                    </dt>
                    <dd class="font-medium">
                        {{
                            rapor.approved_at
                                ? new Date(rapor.approved_at).toLocaleString(
                                      'id-ID',
                                  )
                                : '-'
                        }}
                    </dd>
                </div>
            </dl>
        </div>

        <div
            class="rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
        >
            <h2 class="font-semibold">Keterangan Perkembangan per Tema</h2>
            <div
                v-for="detail in rapor.details"
                :key="detail.tema.nama_tema"
                class="mt-5 border-t border-border pt-4 first:border-0 first:pt-0"
            >
                <p class="font-medium">{{ detail.tema.nama_tema }}</p>
                <p class="mt-1 text-sm text-muted-foreground">
                    Guru Penilai: {{ detail.guru.nama }}
                </p>
                <p class="mt-3 leading-7 whitespace-pre-wrap">
                    {{ detail.keterangan }}
                </p>
            </div>
        </div>
    </div>
</template>
