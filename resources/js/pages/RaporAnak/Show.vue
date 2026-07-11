<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import RaporAnakController from '@/actions/App/Http/Controllers/RaporAnakController';
import Button from '@/components/ui/button/Button.vue';

interface Rapor {
    thn_ajaran: string;
    keterangan: string;
    validated_at: string | null;
    tema: { nama_tema: string };
    guru: { nama: string; nip: string | null };
    validator: { name: string; email: string } | null;
}

interface Siswa {
    nama: string;
    nis: string | null;
    kelas: { nama_kelas: string } | null;
}

const page = usePage();
const rapor = computed(() => page.props.rapor as Rapor);
const siswa = computed(() => page.props.siswa as Siswa);
</script>

<template>
    <Head :title="`Rapor Anak - ${rapor.tema.nama_tema}`" />
    <div class="max-w-3xl space-y-6 p-4 text-foreground">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Detail Rapor Anak</h1>
                <p class="text-sm text-muted-foreground">
                    Catatan perkembangan siswa yang telah disetujui.
                </p>
            </div>
            <Button as-child variant="outline"
                ><Link :href="RaporAnakController.index().url"
                    >Kembali</Link
                ></Button
            >
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
                    <dt class="text-sm text-muted-foreground">Tema</dt>
                    <dd class="font-medium">{{ rapor.tema.nama_tema }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">Guru Penilai</dt>
                    <dd class="font-medium">
                        {{ rapor.guru.nama
                        }}{{ rapor.guru.nip ? ` - ${rapor.guru.nip}` : '' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">
                        Kepsek Validator
                    </dt>
                    <dd class="font-medium">
                        {{ rapor.validator?.name ?? '-' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">
                        Tanggal Validasi
                    </dt>
                    <dd class="font-medium">
                        {{
                            rapor.validated_at
                                ? new Date(rapor.validated_at).toLocaleString(
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
            <h2 class="font-semibold">Keterangan Perkembangan Siswa</h2>
            <p class="mt-4 leading-7 whitespace-pre-wrap">
                {{ rapor.keterangan }}
            </p>
        </div>
    </div>
</template>
