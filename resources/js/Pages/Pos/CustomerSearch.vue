<template>
    <Layout>
        <Head :title="$t('pos.customer_search')" />
        
        <BCard>
            <BCardBody>
                <div class="mb-4">
                    <BRow>
                        <BCol md="6">
                            <BFormInput
                                v-model="searchQuery"
                                placeholder="Müşteri adı veya telefon numarası ile arama yapın..."
                                @input="debounceSearch"
                            />
                        </BCol>
                        <BCol md="6" class="text-end">
                            <BButton variant="primary" @click="showNewCustomerModal = true">
                                <i class="ri-add-line me-1"></i> Yeni Müşteri
                            </BButton>
                        </BCol>
                    </BRow>
                </div>

                <div v-if="isLoading" class="text-center my-5">
                    <BSpinner variant="primary" label="Yükleniyor..."></BSpinner>
                </div>

                <div v-else-if="customers.length > 0">
                    <BTable
                        :items="customers"
                        :fields="fields"
                        striped
                        hover
                        responsive
                    >
                        <template #cell(actions)="{ item }">
                            <BButton
                                variant="primary"
                                size="sm"
                                @click="selectCustomer(item)"
                            >
                                Seç
                            </BButton>
                        </template>
                    </BTable>
                </div>
                <div v-else-if="searchQuery" class="text-center my-5">
                    <p class="text-muted mb-3">Arama kriterlerinize uygun müşteri bulunamadı.</p>
                    <BButton variant="primary" @click="showNewCustomerModal = true">
                        Yeni Müşteri Ekle
                    </BButton>
                </div>
            </BCardBody>
        </BCard>

        <!-- Yeni Müşteri Modal -->
        <BModal
            v-model="showNewCustomerModal"
            title="Yeni Müşteri Ekle"
            @ok="createCustomer"
            :ok-title="'Kaydet'"
            :cancel-title="'İptal'"
        >
            <BForm @submit.prevent="createCustomer">
                <BFormGroup
                    label="Ad Soyad"
                    label-for="name"
                    :state="!errors.name"
                    :invalid-feedback="errors.name"
                >
                    <BFormInput
                        id="name"
                        v-model="form.name"
                        :state="!errors.name"
                        required
                    />
                </BFormGroup>

                <BFormGroup
                    label="Telefon"
                    label-for="phone"
                    :state="!errors.phone"
                    :invalid-feedback="errors.phone"
                >
                    <BFormInput
                        id="phone"
                        v-model="form.phone"
                        :state="!errors.phone"
                        required
                    />
                </BFormGroup>

                <BFormGroup
                    label="E-posta"
                    label-for="email"
                    :state="!errors.email"
                    :invalid-feedback="errors.email"
                >
                    <BFormInput
                        id="email"
                        v-model="form.email"
                        type="email"
                    />
                </BFormGroup>

                <BFormGroup
                    label="Adres"
                    label-for="address"
                    :state="!errors.address"
                    :invalid-feedback="errors.address"
                >
                    <BFormTextarea
                        id="address"
                        v-model="form.address"
                        rows="3"
                    />
                </BFormGroup>
            </BForm>
        </BModal>
    </Layout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import Layout from '@/Layouts/Main.vue'
import debounce from 'lodash/debounce'
import {
    BCard,
    BCardBody,
    BRow,
    BCol,
    BFormInput,
    BButton,
    BSpinner,
    BTable,
    BModal,
    BForm,
    BFormGroup,
    BFormTextarea
} from 'bootstrap-vue-next'
import axios from 'axios'

const searchQuery = ref('')
const customers = ref([])
const isLoading = ref(false)
const showNewCustomerModal = ref(false)
const errors = ref({})

const fields = [
    { key: 'name', label: 'Ad Soyad' },
    { key: 'phone', label: 'Telefon' },
    { key: 'email', label: 'E-posta' },
    { key: 'actions', label: 'İşlemler' }
]

const form = ref({
    name: '',
    phone: '',
    email: '',
    address: ''
})

const searchCustomers = async () => {
    if (!searchQuery.value) {
        customers.value = []
        return
    }

    isLoading.value = true
    try {
        const response = await axios.get(route('pos.search-customers'), {
            params: { query: searchQuery.value }
        })
        customers.value = response.data
    } catch (error) {
        console.error('Müşteri arama hatası:', error)
    } finally {
        isLoading.value = false
    }
}

const debounceSearch = debounce(searchCustomers, 300)

const createCustomer = async () => {
    try {
        const response = await axios.post(route('pos.create-customer'), form.value)
        if (response.data.success) {
            showNewCustomerModal.value = false
            form.value = { name: '', phone: '', email: '', address: '' }
            errors.value = {}
            selectCustomer(response.data.customer)
        }
    } catch (error) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors
        }
    }
}

const selectCustomer = (customer) => {
    router.visit(route('pos.sale', { customerId: customer.id }))
}
</script>

<style scoped>
.card {
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}
</style> 