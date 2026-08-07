<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import Input from '@/Components/UI/Input.vue';
import Button from '@/Components/UI/Button.vue';
import Checkbox from '@/Components/UI/Checkbox.vue';
import Divider from '@/Components/UI/Divider.vue';
import OAuthButton from '@/Components/Auth/OAuthButton.vue';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <AuthLayout>
        <template #title>Welcome Back to BookVerse</template>
        <template #subtitle>Sign in to your account to access your library & magazine</template>

        <Head title="Sign In — BookVerse" />

        <div v-if="status" class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs font-semibold text-emerald-400 text-center">
            {{ status }}
        </div>

        <!-- Google OAuth Button -->
        <OAuthButton provider="google" label="Sign in with Google" />

        <Divider label="or sign in with email" />

        <form @submit.prevent="submit" class="space-y-4">
            <Input
                v-model="form.email"
                label="Email Address"
                type="email"
                placeholder="name@example.com"
                :error="form.errors.email"
                required
                autofocus
            />

            <div class="space-y-1">
                <Input
                    v-model="form.password"
                    label="Password"
                    type="password"
                    placeholder="••••••••"
                    :error="form.errors.password"
                    required
                />

                <div class="flex items-center justify-between text-xs pt-1">
                    <Checkbox
                        v-model="form.remember"
                        label="Remember me"
                    />

                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sky-600 dark:text-sky-400 hover:text-sky-500 font-semibold transition"
                    >
                        Forgot password?
                    </Link>
                </div>
            </div>

            <Button
                type="submit"
                variant="primary"
                class="w-full mt-2"
                :loading="form.processing"
            >
                Sign In
            </Button>
        </form>

        <div class="text-center text-xs text-slate-500 dark:text-zinc-400 pt-3 border-t border-slate-200 dark:border-zinc-800">
            Don't have a BookVerse account?
            <Link :href="route('register')" class="text-sky-600 dark:text-sky-400 font-bold hover:underline ml-1">
                Create an account
            </Link>
        </div>
    </AuthLayout>
</template>
