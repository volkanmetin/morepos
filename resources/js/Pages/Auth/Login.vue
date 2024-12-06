<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '../../Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '../../Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Guest from '@/Layouts/Guest.vue';

import { ref } from 'vue';

const props = defineProps({
    title: String,
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const error = ref(null);

const submit = async () => {
    form.post(route('login'), {
        preserveScroll: true,
        onSuccess: () => form.reset('password'),
        onError: (errors) => {
            error.value = Object.values(errors)[0];
        },
    });
};

</script>

<script>
export default {
    data() {
        return {
            togglePassword: false,
        }
    }
}
</script>

<template>
    <Head :title="title"/>

    <Guest>
        <div class="auth-page-content">
            <BContainer>
                <BRow>
                    <BCol lg="12">
                        <div class="text-center mt-sm-5 mb-4 text-white-50">
                            <div>
                                <Link href="/" class="d-inline-block auth-logo">
                                    <img src="@assets/images/more-logo.png" alt="" height="50">
                                </Link>
                            </div>
                            <!--                            <p class="mt-3 fs-15 fw-medium">Premium Admin & Dashboard Template</p>-->
                        </div>
                    </BCol>
                </BRow>

                <BRow class="justify-content-center">
                    <BCol md="8" lg="6" xl="5">
                        <BCard no-body class="mt-4">

                            <BCardBody class="p-4">
                                <div class="text-center mt-2">
                                    <h5 class="text-primary">{{ $t('auth.welcome-to-panel') }}</h5>
                                    <p class="text-muted">{{ $t('auth.login-helper') }}</p>
                                </div>
                                <div v-if="status" class="alert alert-success text-success">
                                    {{ status }}
                                </div>
                                <!--
                                <div v-if="error" class="alert alert-danger text-danger">
                                    {{ error }}
                                </div>
                                -->
                                <div class="p-2 mt-3">
                                    <form @submit.prevent="submit">

                                        <div class="mb-3">
                                            <InputLabel for="email" :value="$t('auth.email')"/>
                                            <TextInput id="email" v-model="form.email" type="email" class="form-control"
                                                       autofocus :placeholder="$t('auth.email-helper')" autocomplete="email"
                                                       required :class="{ 'is-invalid': form.errors.email }"/>
                                            <InputError :message="form.errors.email"/>
                                        </div>

                                        <div class="mb-3">
                                            <div class="float-end">
                                                <Link v-if="props.canResetPassword" :href="route('password.request')"
                                                      class="text-muted">{{ $t('auth.forgot-password') }}
                                                </Link>
                                            </div>
                                            <InputLabel for="password" :value="$t('auth.password')"/>
                                            <div class="position-relative auth-pass-inputgroup mb-3">
                                                <input :type="togglePassword ? 'text' : 'password'"
                                                       class="form-control pe-5" :placeholder="$t('auth.password-helper')"
                                                       id="password-input" v-model="form.password"
                                                       autocomplete="password" required
                                                       :class="{ 'is-invalid': form.errors.password }">
                                                <BButton variant="link"
                                                         class="position-absolute end-0 top-0 text-decoration-none text-muted shadow-none"
                                                         type="button" id="password-addon"
                                                         @click="togglePassword = !togglePassword">
                                                    <i class="ri-eye-fill align-middle"></i>
                                                </BButton>
                                                <InputError :message="form.errors.password"/>
                                            </div>
                                        </div>

                                        <div class="form-check">
                                            <Checkbox v-model:checked="form.remember" name="remember"
                                                      class="form-check-input" id="auth-remember-check"/>
                                            <label class="form-check-label"
                                                   for="auth-remember-check">{{ $t('auth.remember-me') }}</label>
                                        </div>

                                        <div class="mt-4">
                                            <BButton variant="primary" class="w-100" type="submit"
                                                     :class="{ 'opacity-25': form.processing }"
                                                     :disabled="form.processing">{{ $t('auth.login') }}
                                            </BButton>
                                        </div>
                                    </form>
                                </div>
                            </BCardBody>
                        </BCard>

                        <div class="mt-4 text-center">
                            <p class="mb-0">{{ $t('auth.dont-have-account') }}
                                <Link :href="route('register')"
                                      class="fw-semibold text-primary text-decoration-underline"> {{ $t('auth.register') }}
                                </Link>
                            </p>
                        </div>

                    </BCol>
                </BRow>
            </BContainer>
        </div>

    </Guest>

</template>
