<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import SppController from '@/actions/App/Http/Controllers/SppController';
import SppPembayaranController from '@/actions/App/Http/Controllers/SppPembayaranController';
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';

interface Payment {
    id: number;
    tanggal_bayar: string;
    jumlah_bayar: string;
    metode_pembayaran: string;
    bukti_pembayaran: string | null;
    keterangan: string | null;
    receiver: { name: string };
    status_verifikasi: 'pending' | 'approved' | 'rejected';
}
interface Spp {
    id: number;
    thn_ajaran: string;
    nominal: string;
    total_dibayar: string | null;
    keterangan: string | null;
    siswa: {
        nama: string;
        nis: string | null;
        kelas: { nama_kelas: string; thn_ajaran: string } | null;
    };
    payments: Payment[];
}
type PaymentForm = {
    tanggal_bayar: string;
    jumlah_bayar: string;
    metode_pembayaran: string;
    bukti_pembayaran: File | null;
    keterangan: string;
};
const page = usePage();
const spp = computed(() => page.props.spp as Spp);
const canManageSpp = computed(() => page.props.canManageSpp as boolean);
const selectedPayment = ref<Payment | null>(null);
const isPaymentModalOpen = ref(false);
const paymentForm = useForm<PaymentForm>({
    tanggal_bayar: new Date().toISOString().slice(0, 10),
    jumlah_bayar: '',
    metode_pembayaran: 'manual',
    bukti_pembayaran: null,
    keterangan: '',
});
const formatCurrency = (value: string | number) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(Number(value));
const totalDibayar = computed(() => Number(spp.value.total_dibayar ?? 0));
const sisaTagihan = computed(() =>
    Math.max(Number(spp.value.nominal) - totalDibayar.value, 0),
);
const openCreatePayment = () => {
    selectedPayment.value = null;
    paymentForm.reset();
    paymentForm.tanggal_bayar = new Date().toISOString().slice(0, 10);
    paymentForm.metode_pembayaran = 'manual';
    paymentForm.clearErrors();
    isPaymentModalOpen.value = true;
};
const openEditPayment = (payment: Payment) => {
    selectedPayment.value = payment;
    paymentForm.tanggal_bayar = payment.tanggal_bayar;
    paymentForm.jumlah_bayar = payment.jumlah_bayar;
    paymentForm.metode_pembayaran = payment.metode_pembayaran;
    paymentForm.keterangan = payment.keterangan ?? '';
    paymentForm.bukti_pembayaran = null;
    paymentForm.clearErrors();
    isPaymentModalOpen.value = true;
};
const closePaymentModal = () => {
    isPaymentModalOpen.value = false;
    selectedPayment.value = null;
    paymentForm.reset();
    paymentForm.clearErrors();
};
const onFileChange = (event: Event) => {
    paymentForm.bukti_pembayaran =
        (event.target as HTMLInputElement).files?.[0] ?? null;
};
const submitPayment = () => {
    const options = {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: closePaymentModal,
    };

    if (selectedPayment.value) {
        paymentForm.put(
            SppPembayaranController.update([
                spp.value.id,
                selectedPayment.value.id,
            ]).url,
            options,
        );

        return;
    }

    paymentForm.post(SppPembayaranController.store(spp.value.id).url, options);
};
const deletePayment = (payment: Payment) => {
    if (confirm('Hapus pembayaran ini?')) {
        router.delete(
            SppPembayaranController.destroy([spp.value.id, payment.id]).url,
            { preserveScroll: true },
        );
    }
};
const verifyPayment = (payment: Payment, action: 'approve' | 'reject') => {
    router.post(
        action === 'approve'
            ? SppPembayaranController.approve([spp.value.id, payment.id]).url
            : SppPembayaranController.reject([spp.value.id, payment.id]).url,
        {},
        { preserveScroll: true },
    );
};
</script>

<template>
    <Head :title="`Detail SPP - ${spp.siswa.nama}`" />
    <div class="space-y-6 bg-background p-4 text-foreground">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Detail SPP</h1>
                <p class="text-sm text-muted-foreground">
                    Tagihan dan riwayat pembayaran cicilan siswa.
                </p>
            </div>
            <Button as-child variant="outline"
                ><Link :href="SppController.index().url">Kembali</Link></Button
            >
        </div>
        <div
            class="rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
        >
            <dl class="grid gap-4 md:grid-cols-3">
                <div>
                    <dt class="text-sm text-muted-foreground">Siswa</dt>
                    <dd class="font-medium">{{ spp.siswa.nama }}</dd>
                    <dd class="text-sm text-muted-foreground">
                        {{ spp.siswa.nis ?? '-' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">
                        Kelas / Tahun Ajaran
                    </dt>
                    <dd class="font-medium">
                        {{ spp.siswa.kelas?.nama_kelas ?? '-' }} /
                        {{ spp.thn_ajaran }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">
                        Nominal Tagihan
                    </dt>
                    <dd class="font-medium">
                        {{ formatCurrency(spp.nominal) }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">Total Dibayar</dt>
                    <dd class="font-medium">
                        {{ formatCurrency(totalDibayar) }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">Sisa Tagihan</dt>
                    <dd class="font-medium">
                        {{ formatCurrency(sisaTagihan) }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-muted-foreground">Status</dt>
                    <dd class="font-medium">
                        {{ sisaTagihan === 0 ? 'Lunas' : 'Belum Lunas' }}
                    </dd>
                </div>
            </dl>
            <p v-if="spp.keterangan" class="mt-4 text-sm text-muted-foreground">
                {{ spp.keterangan }}
            </p>
        </div>
        <div
            class="rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
        >
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold">Riwayat Pembayaran</h2>
                    <p class="text-sm text-muted-foreground">
                        Catat pembayaran cicilan dan unggah bukti pembayaran.
                    </p>
                </div>
                <Button
                    v-if="canManageSpp"
                    :disabled="sisaTagihan === 0"
                    @click="openCreatePayment"
                    >Tambah Pembayaran</Button
                >
            </div>
            <div class="mt-6 overflow-x-auto rounded-lg border border-border">
                <table class="w-full min-w-[900px]">
                    <thead class="bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-medium">
                                Tanggal
                            </th>
                            <th class="px-4 py-3 text-left text-sm font-medium">
                                Jumlah
                            </th>
                            <th class="px-4 py-3 text-left text-sm font-medium">
                                Metode
                            </th>
                            <th class="px-4 py-3 text-left text-sm font-medium">
                                Verifikasi
                            </th>
                            <th class="px-4 py-3 text-left text-sm font-medium">
                                Petugas
                            </th>
                            <th class="px-4 py-3 text-left text-sm font-medium">
                                Bukti
                            </th>
                            <th
                                v-if="canManageSpp"
                                class="px-4 py-3 text-right text-sm font-medium"
                            >
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="payment in spp.payments"
                            :key="payment.id"
                            class="border-t border-border"
                        >
                            <td class="px-4 py-3">
                                {{
                                    new Date(
                                        payment.tanggal_bayar,
                                    ).toLocaleDateString('id-ID')
                                }}
                            </td>
                            <td class="px-4 py-3">
                                {{ formatCurrency(payment.jumlah_bayar) }}
                            </td>
                            <td class="px-4 py-3">
                                {{ payment.metode_pembayaran }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="rounded-full px-2 py-1 text-xs font-medium"
                                    :class="
                                        payment.status_verifikasi === 'approved'
                                            ? 'bg-primary text-primary-foreground'
                                            : 'bg-muted text-muted-foreground'
                                    "
                                    >{{ payment.status_verifikasi }}</span
                                >
                            </td>
                            <td class="px-4 py-3">
                                {{ payment.receiver.name }}
                            </td>
                            <td class="px-4 py-3">
                                <a
                                    v-if="payment.bukti_pembayaran"
                                    :href="`/storage/${payment.bukti_pembayaran}`"
                                    target="_blank"
                                    class="text-primary underline underline-offset-4"
                                    >Lihat Bukti</a
                                ><span v-else class="text-muted-foreground"
                                    >-</span
                                >
                            </td>
                            <td
                                v-if="canManageSpp"
                                class="px-4 py-3 text-right"
                            >
                                <div class="flex justify-end gap-2">
                                    <Button
                                        v-if="
                                            payment.status_verifikasi ===
                                            'pending'
                                        "
                                        size="sm"
                                        @click="
                                            verifyPayment(payment, 'approve')
                                        "
                                        >Approve</Button
                                    ><Button
                                        v-if="
                                            payment.status_verifikasi ===
                                            'pending'
                                        "
                                        size="sm"
                                        variant="outline"
                                        @click="
                                            verifyPayment(payment, 'reject')
                                        "
                                        >Reject</Button
                                    ><Button
                                        size="sm"
                                        variant="outline"
                                        @click="openEditPayment(payment)"
                                        >Edit</Button
                                    ><Button
                                        size="sm"
                                        variant="destructive"
                                        @click="deletePayment(payment)"
                                        >Hapus</Button
                                    >
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!spp.payments.length">
                            <td
                                colspan="6"
                                class="py-8 text-center text-muted-foreground"
                            >
                                Belum ada pembayaran.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div
            v-if="isPaymentModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-muted/80 p-4"
        >
            <form
                class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl border border-border bg-card p-6 text-card-foreground shadow-sm"
                @submit.prevent="submitPayment"
            >
                <h2 class="text-lg font-semibold">
                    {{
                        selectedPayment
                            ? 'Edit Pembayaran SPP'
                            : 'Tambah Pembayaran SPP'
                    }}
                </h2>
                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <label for="tanggal_bayar" class="text-sm font-medium"
                            >Tanggal Bayar</label
                        ><Input
                            id="tanggal_bayar"
                            v-model="paymentForm.tanggal_bayar"
                            type="date"
                        /><InputError
                            :message="paymentForm.errors.tanggal_bayar"
                        />
                    </div>
                    <div class="space-y-2">
                        <label for="jumlah_bayar" class="text-sm font-medium"
                            >Jumlah Bayar</label
                        ><Input
                            id="jumlah_bayar"
                            v-model="paymentForm.jumlah_bayar"
                            type="number"
                            min="0"
                            step="0.01"
                        /><InputError
                            :message="paymentForm.errors.jumlah_bayar"
                        />
                    </div>
            
                    <div class="space-y-2">
                        <label
                            for="bukti_pembayaran"
                            class="text-sm font-medium"
                            >Bukti Pembayaran</label
                        ><input
                            id="bukti_pembayaran"
                            type="file"
                            accept=".jpg,.jpeg,.png,.pdf"
                            class="block w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground"
                            @change="onFileChange"
                        /><InputError
                            :message="paymentForm.errors.bukti_pembayaran"
                        />
                    </div>
                </div>
                <div class="mt-4 space-y-2">
                    <label for="keterangan" class="text-sm font-medium"
                        >Keterangan</label
                    ><textarea
                        id="keterangan"
                        v-model="paymentForm.keterangan"
                        class="min-h-28 w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground"
                    /><InputError :message="paymentForm.errors.keterangan" />
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        :disabled="paymentForm.processing"
                        @click="closePaymentModal"
                        >Batal</Button
                    ><Button type="submit" :disabled="paymentForm.processing">{{
                        paymentForm.processing
                            ? 'Menyimpan...'
                            : 'Simpan Pembayaran'
                    }}</Button>
                </div>
            </form>
        </div>
    </div>
</template>
