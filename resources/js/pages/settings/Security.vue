<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import type { Props as ManagePasskeysProps } from '@/components/ManagePasskeys.vue';
import ManagePasskeys from '@/components/ManagePasskeys.vue';
import type { Props as ManageTwoFactorProps } from '@/components/ManageTwoFactor.vue';
import ManageTwoFactor from '@/components/ManageTwoFactor.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/security';

type Props = {
    passwordRules: string;
} & ManagePasskeysProps &
    ManageTwoFactorProps;

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Keamanan Akun',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Keamanan Akun" />

    <h1 class="sr-only">Keamanan Akun</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Ubah Password"
            description="Gunakan password yang kuat untuk menjaga keamanan akun Anda."
        />

        <Form
            v-bind="SecurityController.update.form()"
            :options="{
                preserveScroll: true,
            }"
            reset-on-success
            :reset-on-error="[
                'password',
                'password_confirmation',
                'current_password',
            ]"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="current_password">Password Saat Ini</Label>
                <PasswordInput
                    id="current_password"
                    name="current_password"
                    class="mt-1 block w-full"
                    autocomplete="current-password"
                    placeholder="Masukkan password saat ini"
                />
                <InputError :message="errors.current_password" />
            </div>

            <div class="grid gap-2">
                <Label for="password">Password Baru</Label>
                <PasswordInput
                    id="password"
                    name="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                    placeholder="Masukkan password baru"
                    :passwordrules="props.passwordRules"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation"
                    >Konfirmasi Password Baru</Label
                >
                <PasswordInput
                    id="password_confirmation"
                    name="password_confirmation"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                    placeholder="Ulangi password baru"
                    :passwordrules="props.passwordRules"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <div class="flex items-center gap-4">
                <Button
                    :disabled="processing"
                    data-test="update-password-button"
                >
                    Simpan Password
                </Button>
            </div>
        </Form>
    </div>

    <ManageTwoFactor
        :canManageTwoFactor="canManageTwoFactor"
        :requiresConfirmation="requiresConfirmation"
        :twoFactorEnabled="twoFactorEnabled"
    />

    <ManagePasskeys
        :canManagePasskeys="canManagePasskeys"
        :passkeys="passkeys"
    />
</template>
