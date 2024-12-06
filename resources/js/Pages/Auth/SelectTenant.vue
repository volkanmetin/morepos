<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Guest from '@/Layouts/Guest.vue';
import axios from 'axios';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    tenants: {
        type: Array,
        required: true
    },
});

const loading = ref(false);
const error = ref(null);

const selectTenant = async (tenant) => {
    loading.value = true;
    error.value = null;

    try {
        const response = await axios.post(route('select-tenant', { tenantId: tenant.id }));
        if (response.data && response.data.url) {
            window.location.href = response.data.url;
        } else {
            throw new Error(t('auth.select-tenant-error'));
        }
    } catch (err) {
        error.value = t('auth.select-tenant-error');
        console.error('Mağaza seçme hatası:', err);
    } finally {
        loading.value = false;
    }
};

</script>

<template>
    <Head title="Log in"/>

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
                        </div>
                    </BCol>
                </BRow>

                <BRow class="justify-content-center">
                    <BCol md="8" lg="6" xl="5">
                        <BCard no-body class="mt-4">

                            <BCardBody class="p-4">
                                    
                                <div class="text-center mt-2">
                                    <h5 class="text-primary">{{ $t('auth.select-tenant') }}</h5>
                                    <p class="text-muted">{{ $t('auth.select-tenant-helper') }}</p>
                                </div>
                                
                                <div v-if="error" class="alert alert-danger mt-3">{{ error }}</div>

                                <div class="mt-4">
                                    <div v-for="tenant in tenants" :key="tenant.id" class="card mb-3">
                                        <div class="card-body d-flex justify-content-between align-items-center">
                                            <h5 class="card-title mb-0">{{ tenant.name }}</h5>
                                            <BButton variant="primary" @click="selectTenant(tenant)" :disabled="loading">
                                                {{ loading ? $t('auth.select-tenant-loading') : $t('auth.select') }}
                                            </BButton>
                                        </div>
                                    </div>
                                </div>

                            </BCardBody>
                        </BCard>
                    </BCol>
                </BRow>
            </BContainer>
        </div>

    </Guest>

</template>
