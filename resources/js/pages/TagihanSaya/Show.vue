<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import TagihanSayaController from '@/actions/App/Http/Controllers/TagihanSayaController';
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
interface Payment {
    id: number;
    tanggal_bayar: string;
    jumlah_bayar: string;
    metode_pembayaran: string;
    status_verifikasi: string;
    bukti_pembayaran: string | null;
    keterangan: string | null;
}
interface Spp {
    id: number;
    jenis_pembayaran: string;
    nominal: string;
    total_dibayar: string | null;
    tanggal_tagihan: string;
    jatuh_tempo: string;
    payments: Payment[];
}
const page = usePage();
const spp = computed(() => page.props.spp as Spp);
const clientKey = computed(() => page.props.midtransClientKey as string);
const processingMidtrans = ref(false);
const form = useForm({
    tanggal_bayar: new Date().toISOString().slice(0, 10),
    jumlah_bayar: '',
    bukti_pembayaran: null as File | null,
    keterangan: '',
});
const money = (value: string | number) =>
    `Rp${Math.round(Number(value))
        .toString()
        .replace(/\B(?=(\d{3})+(?!\d))/g, '.')}`;
const total = computed(() => Number(spp.value.total_dibayar ?? 0));
const sisa = computed(() => Number(spp.value.nominal) - total.value);
const file = (e: Event) =>
    (form.bukti_pembayaran = (e.target as HTMLInputElement).files?.[0] ?? null);
const submit = () =>
    form.post(TagihanSayaController.storeManualPayment(spp.value.id).url, {
        forceFormData: true,
    });
const payMidtrans = async () => {
    processingMidtrans.value = true;

    try {
        const response = await fetch(
            TagihanSayaController.createMidtransTransaction(spp.value.id).url,
            {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') ?? '',
                },
            },
        );

        if (!response.ok) {
            throw new Error('Gagal membuat transaksi Midtrans.');
        }

        const data = await response.json();
        const script = document.createElement('script');
        script.src = 'https://app.sandbox.midtrans.com/snap/snap.js';
        script.dataset.clientKey = clientKey.value;
        script.onload = () => {
            (
                window as unknown as Window & {
                    snap: { pay: (token: string) => void };
                }
            ).snap.pay(data.snap_token);
        };
        document.head.appendChild(script);
    } catch {
        form.setError(
            'jumlah_bayar',
            'Gagal membuat transaksi Midtrans. Silakan coba lagi.',
        );
    } finally {
        processingMidtrans.value = false;
    }
};
</script>
<template>
    <Head title="Detail Tagihan" />
    <div class="max-w-4xl space-y-6 bg-background p-4 text-foreground">
        <div class="flex justify-between">
            <div>
                <h1 class="text-2xl font-bold">{{ spp.jenis_pembayaran }}</h1>
                <p class="text-sm text-muted-foreground">
                    Tagihan {{ money(spp.nominal) }} · Sisa {{ money(sisa) }}
                </p>
            </div>
            <Button as-child variant="outline"
                ><Link :href="TagihanSayaController.index().url"
                    >Kembali</Link
                ></Button
            >
        </div>
        <div
            class="rounded-xl border border-border bg-card p-6 text-card-foreground"
        >
            <div class="flex items-center justify-between gap-4">
                <h2 class="font-semibold">Upload Bukti Pembayaran</h2>
                <Button
                    :disabled="sisa <= 0 || processingMidtrans"
                    @click="payMidtrans"
                    >{{
                        processingMidtrans
                            ? 'Memproses...'
                            : 'Bayar dengan Midtrans'
                    }}</Button
                >
            </div>
            <form
                class="mt-4 grid gap-4 md:grid-cols-2"
                @submit.prevent="submit"
            >
                <div>
                    <label class="text-sm">Tanggal Pembayaran</label
                    ><Input
                        v-model="form.tanggal_bayar"
                        type="date"
                    /><InputError :message="form.errors.tanggal_bayar" />
                </div>
                <div>
                    <label class="text-sm">Nominal Pembayaran</label
                    ><Input
                        v-model="form.jumlah_bayar"
                        type="number"
                    /><InputError :message="form.errors.jumlah_bayar" />
                </div>
                <div>
                    <label class="text-sm">Bukti Pembayaran</label
                    ><input
                        type="file"
                        accept=".jpg,.jpeg,.png,.pdf"
                        class="block w-full rounded-md border border-border bg-background px-3 py-2 text-sm"
                        @change="file"
                    /><InputError :message="form.errors.bukti_pembayaran" />
                </div>
                <div>
                    <label class="text-sm">Keterangan</label
                    ><Input v-model="form.keterangan" />
                </div>
                <Button type="submit" :disabled="form.processing"
                    >Kirim Pembayaran</Button
                >
            </form>
        </div>
        <div
            class="rounded-xl border border-border bg-card p-6 text-card-foreground"
        >
            <h2 class="font-semibold">Riwayat Pembayaran</h2>
            <div
                v-for="payment in spp.payments"
                :key="payment.id"
                class="mt-3 border-t border-border pt-3"
            >
                <p>
                    {{ payment.tanggal_bayar }} ·
                    {{ money(payment.jumlah_bayar) }} ·
                    {{ payment.metode_pembayaran }}
                </p>
                <p class="text-sm text-muted-foreground">
                    {{ payment.status_verifikasi }}
                </p>
            </div>
        </div>
    </div>
</template>
