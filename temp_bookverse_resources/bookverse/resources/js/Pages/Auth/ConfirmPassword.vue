<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import Input from '@/Components/UI/Input.vue';
import Button from '@/Components/UI/Button.vue';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <AuthLayout>
        <template #title>Confirm Your Password</template>
        <template #subtitle>This is a secure area of the application. Please confirm your password before continuing.</template>

        <Head title="Confirm Password — BookVerse" />

        <form @submit.prevent="submit" class="space-y-4">
            <Input
                v-model="form.password"
                label="Current Password"
                type="password"
                placeholder="Enter password"
                :error="form.errors.password"
                required
                autofocus
            />

            <Button
                type="submit"
                variant="primary"
                class="w-full"
                :loading="form.processing"
            >
                Confirm & Continue
            </Button>
        </form>
    </AuthLayout>
</template>
