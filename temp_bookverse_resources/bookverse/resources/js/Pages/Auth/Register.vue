<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import Input from '@/Components/UI/Input.vue';
import Button from '@/Components/UI/Button.vue';
import Checkbox from '@/Components/UI/Checkbox.vue';
import Divider from '@/Components/UI/Divider.vue';
import OAuthButton from '@/Components/Auth/OAuthButton.vue';
import PasswordStrengthMeter from '@/Components/Auth/PasswordStrengthMeter.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <AuthLayout>
        <template #title>Join BookVerse Today</template>
        <template #subtitle>Create your account to discover books & publish articles</template>

        <Head title="Create Account — BookVerse" />

        <!-- OAuth Button -->
        <OAuthButton provider="google" label="Sign up with Google" />

        <Divider label="or register with email" />

        <form @submit.prevent="submit" class="space-y-4">
            <Input
                v-model="form.name"
                label="Full Name"
                placeholder="e.g. Kazi Nazrul"
                :error="form.errors.name"
                required
                autofocus
            />

            <Input
                v-model="form.email"
                label="Email Address"
                type="email"
                placeholder="name@example.com"
                :error="form.errors.email"
                required
            />

            <div class="space-y-1">
                <Input
                    v-model="form.password"
                    label="Password"
                    type="password"
                    placeholder="At least 8 characters"
                    :error="form.errors.password"
                    required
                />
                <PasswordStrengthMeter :password="form.password" />
            </div>

            <Input
                v-model="form.password_confirmation"
                label="Confirm Password"
                type="password"
                placeholder="Re-enter your password"
                :error="form.errors.password_confirmation"
                required
            />

            <Checkbox
                v-model="form.terms"
                label="I agree to the Terms of Service & Privacy Policy"
            />

            <Button
                type="submit"
                variant="brand"
                class="w-full mt-2"
                :loading="form.processing"
            >
                Create Free Account
            </Button>
        </form>

        <div class="text-center text-xs text-slate-500 dark:text-zinc-400 pt-3 border-t border-slate-200 dark:border-zinc-800">
            Already have an account?
            <Link :href="route('login')" class="text-sky-600 dark:text-sky-400 font-bold hover:underline ml-1">
                Sign in
            </Link>
        </div>
    </AuthLayout>
</template>
