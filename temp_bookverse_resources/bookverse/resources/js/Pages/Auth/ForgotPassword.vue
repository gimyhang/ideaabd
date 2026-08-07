<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import Input from '@/Components/UI/Input.vue';
import Button from '@/Components/UI/Button.vue';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <AuthLayout>
        <template #title>Reset Your Password</template>
        <template #subtitle>Enter your email and we'll send a password reset link</template>

        <Head title="Forgot Password — BookVerse" />

        <div v-if="status" class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs font-semibold text-emerald-400 text-center">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <Input
                v-model="form.email"
                label="Registered Email Address"
                type="email"
                placeholder="name@example.com"
                :error="form.errors.email"
                required
                autofocus
            />

            <Button
                type="submit"
                variant="primary"
                class="w-full"
                :loading="form.processing"
            >
                Send Password Reset Link
            </Button>
        </form>

        <div class="text-center text-xs text-slate-400 pt-2 border-t border-slate-800">
            Remembered your password?
            <Link :href="route('login')" class="text-sky-400 font-bold hover:underline ml-1">
                Back to Sign in
            </Link>
        </div>
    </AuthLayout>
</template>
