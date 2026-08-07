<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import Button from '@/Components/UI/Button.vue';

const props = defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <AuthLayout>
        <template #title>Verify Your Email</template>
        <template #subtitle>Please verify your email address to access all features</template>

        <Head title="Email Verification — BookVerse" />

        <p class="text-xs text-slate-300 leading-relaxed">
            Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
        </p>

        <div v-if="verificationLinkSent" class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs font-semibold text-emerald-400 text-center">
            A new verification link has been sent to the email address you provided during registration.
        </div>

        <form @submit.prevent="submit" class="space-y-4 pt-2">
            <Button
                type="submit"
                variant="brand"
                class="w-full"
                :loading="form.processing"
            >
                Resend Verification Email
            </Button>

            <div class="flex items-center justify-between text-xs text-slate-400 pt-2 border-t border-slate-800">
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-rose-400 font-semibold hover:underline"
                >
                    Log Out
                </Link>
            </div>
        </form>
    </AuthLayout>
</template>
