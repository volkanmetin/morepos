<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import Layout from "@/Layouts/Main.vue";
import PageHeader from "@/Components/page-header.vue";
import ShoppingCard from "@/Components/ShoppingCard.vue";
import { BButton, BRow, BCol, BCard, BCardBody, BModal, BImg, BFormInput, BFormSelect, BSpinner, BTable, BAlert, BFormGroup, BForm } from "bootstrap-vue-next";
import debounce from 'lodash/debounce';
import Select2 from 'vue3-select2-component';
import { useForm } from '@inertiajs/vue3';

const cartItems = ref([]);
const showVariantModal = ref(false);
const selectedProduct = ref(null);

const products = ref([]);
const columns = ref([]);
const links = ref([]);

const showImageModal = ref(false);
const selectedImage = ref('');
const selectedProductName = ref('');

// Yeni değiş
const searchQuery = ref('');
const selectedCategory = ref('');
const categoryOptions = ref([]);
const isLoading = ref(false);
const formattedCategoryOptions = ref([]);

// Yeni ref'ler ekleyin
const selectedBrand = ref('');
const brandOptions = ref([]);
const formattedBrandOptions = ref([]);

// Yeni ref'ler ekleyin
const selectedVendor = ref('');
const vendorOptions = ref([]);
const formattedVendorOptions = ref([]);

// Yeni ref'ler ve değişkenler ekleyin
const selectedCustomer = ref(null);
const customerOptions = ref([]);
const formattedCustomerOptions = ref([]);
const showNewCustomerModal = ref(false);

// Yeni müşteri formu için useForm hook'unu kullanın
const newCustomerForm = useForm({
  name: '',
  email: '',
  phone: '',
  // Diğer gerekli alanları ekleyin
});

const fetchProducts = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(route('pos.products'), {
            params: {
                search: searchQuery.value,
                category: selectedCategory.value,
                brand: selectedBrand.value,
                vendor: selectedVendor.value
            }
        });

        products.value = response.data.data || [];
        
        formattedCategoryOptions.value = [
            { id: '', text: 'Tüm Kategoriler' },
            ...(response.data.categories || []).map(category => ({
                id: category.value,
                text: category.label
            }))
        ];

        formattedBrandOptions.value = [
            { id: '', text: 'Tüm Markalar' },
            ...(response.data.brands || []).map(brand => ({
                id: brand.value,
                text: brand.label
            }))
        ];

        formattedVendorOptions.value = [
            { id: '', text: 'Tüm Tedarikçiler' },
            ...(response.data.vendors || []).map(vendor => ({
                id: vendor.value,
                text: vendor.label
            }))
        ];
        
        formattedCustomerOptions.value = [
            { id: '', text: 'Müşteri Seçin' },
            ...(response.data.customers || []).map(customer => ({
                id: customer.id,
                text: customer.name
            }))
        ];

        categoryOptions.value = response.data.categories || [];
        brandOptions.value = response.data.brands || [];
        vendorOptions.value = response.data.vendors || [];
        customerOptions.value = response.data.customers || [];

    } catch (error) {
        console.error('Ürünler yüklenirken hata oluştu:', error);
    } finally {
        isLoading.value = false;
    }
};

onMounted(fetchProducts);

const debounceSearch = debounce(() => {
    fetchProducts();
}, 300);

watch(selectedCategory, fetchProducts);
watch(selectedBrand, fetchProducts);
watch(selectedVendor, fetchProducts);

const openImageModal = (imageUrl, productName) => {
    selectedImage.value = imageUrl;
    selectedProductName.value = productName;
    showImageModal.value = true;
};

const addToCart = (product) => {
    selectedProduct.value = product;
    showVariantModal.value = true;
};

const addVariantToCart = (variant) => {
    cartItems.value.push({
        ...selectedProduct.value,
        variant: variant,
        price: variant.discounted_price > 0 ? variant.discounted_price : variant.sale_price
    });
    showVariantModal.value = false;
};

const handleCategoryChange = (value) => {
    selectedCategory.value = value;
    fetchProducts();
};

const clearCategory = () => {
    selectedCategory.value = '';
    fetchProducts();
};

const showClearButton = computed(() => selectedCategory.value !== '');

const handleBrandChange = (value) => {
    selectedBrand.value = value;
    fetchProducts();
};

const clearBrand = () => {
    selectedBrand.value = '';
    fetchProducts();
};

const showClearBrandButton = computed(() => selectedBrand.value !== '');

const handleVendorChange = (value) => {
    selectedVendor.value = value;
    fetchProducts();
};

const clearVendor = () => {
    selectedVendor.value = '';
    fetchProducts();
};

const showClearVendorButton = computed(() => selectedVendor.value !== '');

// Varyant tablosu için sütunlar
const variantColumns = [
    { key: 'name', label: 'Varyant Adı' },
    { key: 'purchase_price', label: 'Alış Fiyatı', class: 'text-end' },
    { key: 'sale_price', label: 'Satış Fiyatı', class: 'text-end' },
    { key: 'discounted_price', label: 'İndirimli Fiyat', class: 'text-end' },
    { key: 'sku', label: 'SKU' },
    { key: 'stocks', label: 'Stok', class: 'p-1' }
];

// Yeni müşteri ekleme fonksiyonu
const addNewCustomer = () => {
    newCustomerForm.post(route('customers.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            showNewCustomerModal.value = false;
            fetchProducts(); // Müşteri listesini yenile
            newCustomerForm.reset();
        },
    });
};

</script>

<template>
    <Layout>
        <Head :title="$t('pos.title')"/>
        <BRow>
            <BCol cols="8">
                <BCard no-body class="card-body">
                    <BCardBody class="p-0">
                        
                    <div class="search-form mb-4">
                        <BRow>
                            <BCol cols="3">
                                <BFormInput
                                    v-model="searchQuery"
                                    placeholder="Ürün ara..."
                                    @input="debounceSearch"
                                />
                            </BCol>
                            <BCol cols="3">
                                <div class="position-relative">
                                    <Select2
                                        v-model="selectedCategory"
                                        :options="formattedCategoryOptions"
                                        @change="handleCategoryChange"
                                        :settings="{ width: '100%' }"
                                        placeholder="Kategori seçin veya arayın"
                                    />
                                    <BButton
                                        v-if="showClearButton"
                                        variant="outline-secondary"
                                        size="sm"
                                        class="position-absolute top-50 end-0 translate-middle-y me-1"
                                        @click="clearCategory"
                                    >
                                        <i class="ri-close-line"></i>
                                    </BButton>
                                </div>
                            </BCol>
                            <BCol cols="3">
                                <div class="position-relative">
                                    <Select2
                                        v-model="selectedBrand"
                                        :options="formattedBrandOptions"
                                        @change="handleBrandChange"
                                        :settings="{ width: '100%' }"
                                        placeholder="Marka seçin veya arayın"
                                    />
                                    <BButton
                                        v-if="showClearBrandButton"
                                        variant="outline-secondary"
                                        size="sm"
                                        class="position-absolute top-50 end-0 translate-middle-y me-1"
                                        @click="clearBrand"
                                    >
                                        <i class="ri-close-line"></i>
                                    </BButton>
                                </div>
                            </BCol>
                            <BCol cols="3">
                                <div class="position-relative">
                                    <Select2
                                        v-model="selectedVendor"
                                        :options="formattedVendorOptions"
                                        @change="handleVendorChange"
                                        :settings="{ width: '100%' }"
                                        placeholder="Tedarikçi seçin veya arayın"
                                    />
                                    <BButton
                                        v-if="showClearVendorButton"
                                        variant="outline-secondary"
                                        size="sm"
                                        class="position-absolute top-50 end-0 translate-middle-y me-1"
                                        @click="clearVendor"
                                    >
                                        <i class="ri-close-line"></i>
                                    </BButton>
                                </div>
                            </BCol>
                        </BRow>
                    </div>

                    <div v-if="isLoading" class="text-center my-5">
                        <BSpinner variant="primary" label="Yükleniyor..."></BSpinner>
                    </div>

                    <div v-if="products.length > 0" class="product-grid row">
                        <div v-for="(product, index) in products" :key="index" class="product-item col-lg-3 col-md-4 col-sm-6 col-12 mb-3">
                            <div class="product-box border rounded shadow-sm p-3 text-center">
                                <div class="product-image-container" @click="openImageModal(product.image, product.name)">
                                    <img :src="product.image" :alt="product.name" class="product-image img-fluid mb-2 cursor-pointer">
                                </div>
                                <h3 class="product-name mt-2">{{ product.name }}</h3>
                                <span class="text-decoration-line-through text-muted">{{ product.sale_price }} TL</span><br />
                                <span class="fw-semibold">{{ product.discounted_price }} TL</span><br />
                                <BButton variant="primary" class="btn-sm mt-2" @click="addToCart(product)">Sepete Ekle</BButton>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center my-5">
                        <p class="text-muted">Kriterlerinize uygun ürün bulunamadı.</p>
                    </div>


                    </BCardBody>
                </BCard>
            </BCol>
            <BCol cols="4">
                <div v-if="cartItems.length > 0" class="mb-3">
                    <div class="d-flex">
                        <div class="flex-grow-1 me-2">
                            <Select2
                                v-model="selectedCustomer"
                                :options="formattedCustomerOptions"
                                :settings="{ width: '100%' }"
                                placeholder="Müşteri seçin"
                            />
                        </div>
                        <BButton variant="primary" @click="showNewCustomerModal = true">
                            <i class="ri-add-line"></i>
                        </BButton>
                    </div>
                </div>
                <ShoppingCard :initial-cart-items="cartItems" />
            </BCol>
        </BRow>

        <!-- Varyant Seçim Modalı -->
        <BModal v-model="showVariantModal" title="Varyant Seçimi" size="xl" hide-footer>
            <BTable :items="selectedProduct?.variants || []" :fields="variantColumns" striped hover>
                <template #cell(name)="data">
                    {{ data.item.attribute_values.map(av => av.value).join(' - ') }}
                </template>
                <template #cell(stocks)="data" class="p-1">
                    <table class="table table-sm mb-0 table-hover fs-sm align-middle">
                        <tbody>
                            <tr v-for="stock in data.item.stocks" :key="stock.id">
                                <td>{{ stock.warehouse.name }}</td>
                                <td class="text-end">{{ stock.quantity }}</td>
                                <td class="text-center">
                                    <BButton 
                                        variant="primary" 
                                        size="sm" 
                                        :disabled="stock.quantity <= 0"
                                        @click="addVariantToCart(data.item, stock.warehouse.id)"
                                    >
                                        Ekle
                                    </BButton>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </template>
            </BTable>
        </BModal>

        <!-- Güncellenen BModal bileşeni -->
        <BModal v-model="showImageModal" hide-footer :title="selectedProductName" size="lg" centered>
            <BImg :src="selectedImage" fluid-grow :alt="selectedProductName" class="w-100" />
            <template #modal-header-close>
                <BButton variant="outline-secondary" @click="showImageModal = false">
                    <i class="ri-close-line"></i>
                </BButton>
            </template>
        </BModal>

        <!-- Yeni müşteri ekleme modalı -->
        <BModal v-model="showNewCustomerModal" title="Yeni Müşteri Ekle" @ok="addNewCustomer">
            <BForm @submit.prevent="addNewCustomer">
                <BFormGroup label="Ad Soyad" label-for="customerName">
                    <BFormInput id="customerName" v-model="newCustomerForm.name" required />
                </BFormGroup>
                <BFormGroup label="E-posta" label-for="customerEmail">
                    <BFormInput id="customerEmail" v-model="newCustomerForm.email" type="email" required />
                </BFormGroup>
                <BFormGroup label="Telefon" label-for="customerPhone">
                    <BFormInput id="customerPhone" v-model="newCustomerForm.phone" required />
                </BFormGroup>
                <!-- Diğer gerekli alanları ekleyin -->
            </BForm>
        </BModal>
    </Layout>
</template>

<style scoped>
.product-box {
    transition: box-shadow 0.3s ease-in-out;
}

.product-box:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.product-image-container {
    height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    cursor: pointer; /* Yeni stil */
}

.product-image {
    max-height: 100px;
    max-width: 100px;
    object-fit: contain;
}

.product-name {
    font-size: 1rem;
    margin-bottom: 0.5rem;
}

/* Select2 için ek stiller */
:deep(.select2-container) {
    width: 100% !important;
}

:deep(.select2-selection) {
    height: 38px !important;
    display: flex !important;
    align-items: center !important;
    padding-right: 30px !important; /* Temizleme butonu için yer açın */
}

:deep(.select2-selection__arrow) {
    height: 36px !important;
}

/* Temizleme butonu için stil */
.position-relative {
    display: inline-block;
    width: 100%;
}
</style>