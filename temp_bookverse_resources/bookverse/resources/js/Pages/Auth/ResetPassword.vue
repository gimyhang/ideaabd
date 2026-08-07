<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import Input from '@/Components/UI/Input.vue';
import Button from '@/Components/UI/Button.vue';
import PasswordStrengthMeter from '@/Components/Auth/PasswordStrengthMeter.vue';

const props = defineProps<{
    email: string;
    token: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <AuthLayout>
        <template #title>Set New Password</template>
        <template #subtitle>Choose a strong password for your account security</template>

        <Head title="Reset Password — BookVerse" />

        <form @submit.prevent="submit" class="space-y-4">
            <Input
                v-model="form.email"
                label="Email Address"
                type="email"
                :error="form.errors.email"
                required
                disabled
            />

            <div class="space-y-1">
                <Input
                    v-model="form.password"
                    label="New Password"
                    type="password"
                    placeholder="Enter new password"
                    :error="form.errors.password"
                    required
                    autofocus
                />
                <PasswordStrengthMeter :password="form.password" />
            </div>

            <Input
                v-model="form.password_confirmation"
                label="Confirm New Password"
                type="password"
                placeholder="Re-enter new password"
                :error="form.errors.password_confirmation"
                required
            />

            <Button
                type="submit"
                variant="brand"
                class="w-full"
                :loading="form.processing"
            >
                Reset & Update Password
            </Button>
        </form>
    </AuthLayout>
</template>
