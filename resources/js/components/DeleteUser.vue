<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { useTemplateRef } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';

const passwordInput = useTemplateRef('passwordInput');
</script>

<template>
    <div class="space-y-6">
        <Heading
            variant="small"
            title="Hapus Akun"
            description="Menghapus akun akan menghapus akses login akun tersebut dari sistem."
        />
        <div
            class="space-y-4 rounded-lg border border-destructive/30 bg-destructive/10 p-4"
        >
            <div class="relative space-y-0.5 text-destructive">
                <p class="font-medium">Perhatian</p>
                <p class="text-sm">
                    Tindakan ini tidak dapat dibatalkan. Pastikan Anda telah
                    mempertimbangkannya dengan baik.
                </p>
            </div>
            <Dialog>
                <DialogTrigger as-child>
                    <Button variant="destructive" data-test="delete-user-button"
                        >Hapus Akun</Button
                    >
                </DialogTrigger>
                <DialogContent>
                    <Form
                        v-bind="ProfileController.destroy.form()"
                        reset-on-success
                        @error="() => passwordInput?.focus()"
                        :options="{
                            preserveScroll: true,
                        }"
                        class="space-y-6"
                        v-slot="{ errors, processing, reset, clearErrors }"
                    >
                        <DialogHeader class="space-y-3">
                            <DialogTitle
                                >Yakin ingin menghapus akun?</DialogTitle
                            >
                            <DialogDescription>
                                Setelah akun dihapus, akses login dan data
                                terkait dapat ikut terhapus secara permanen.
                                Masukkan password untuk mengonfirmasi
                                penghapusan akun.
                            </DialogDescription>
                        </DialogHeader>

                        <div class="grid gap-2">
                            <Label for="password" class="sr-only"
                                >Password</Label
                            >
                            <PasswordInput
                                id="password"
                                name="password"
                                ref="passwordInput"
                                placeholder="Masukkan password"
                            />
                            <InputError :message="errors.password" />
                        </div>

                        <DialogFooter class="gap-2">
                            <DialogClose as-child>
                                <Button
                                    variant="secondary"
                                    @click="
                                        () => {
                                            clearErrors();
                                            reset();
                                        }
                                    "
                                >
                                    Batal
                                </Button>
                            </DialogClose>

                            <Button
                                type="submit"
                                variant="destructive"
                                :disabled="processing"
                                data-test="confirm-delete-user-button"
                            >
                                Hapus Akun
                            </Button>
                        </DialogFooter>
                    </Form>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>
