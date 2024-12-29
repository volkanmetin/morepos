<template>
    <Layout>
        <Head :title="$t('pos.sale')" />
        
        <BRow>
            <BCol cols="8">
                <BCard no-body class="card-body">
                    <BCardBody class="p-0">
                        <div class="search-form mb-4">
                            <BRow>
                                <BCol cols="4">
                                    <BFormInput
                                        v-model="searchQuery"
                                        placeholder="Ürün ara..."
                                        @input="debounceSearch"
                                    />
                                </BCol>
                                <BCol cols="8">
                                    <div class="d-flex gap-2">
                                        <Select2
                                            v-model="selectedCategory"
                                            :options="formattedCategoryOptions"
                                            @change="handleCategoryChange"
                                            :settings="{ width: '200px' }"
                                            placeholder="Kategori"
                                        />
                                        <Select2
                                            v-model="selectedBrand"
                                            :options="formattedBrandOptions"
                                            @change="handleBrandChange"
                                            :settings="{ width: '200px' }"
                                            placeholder="Marka"
                                        />
                                    </div>
                                </BCol>
                            </BRow>
                        </div>

                        <div v-if="isLoading" class="text-center my-5">
                            <BSpinner variant="primary" label="Yükleniyor..."></BSpinner>
                        </div>

                        <div v-if="products.length > 0" class="product-grid row">
                            <div v-for="product in products" :key="product.id" class="product-item col-lg-3 col-md-4 col-sm-6 col-12 mb-3">
                                <div class="product-box border rounded shadow-sm p-3 text-center">
                                    <div class="product-image-container" @click="openImageModal(product)">
                                        <img :src="product.image" :alt="product.name" class="product-image img-fluid mb-2 cursor-pointer">
                                    </div>
                                    <h3 class="product-name mt-2">{{ product.name }}</h3>
                                    <div v-if="product.discounted_price" class="price-info">
                                        <span class="text-decoration-line-through text-muted">{{ formatPrice(product.sale_price) }}</span><br />
                                        <span class="fw-semibold">{{ formatPrice(product.discounted_price) }}</span>
                                    </div>
                                    <div v-else class="price-info">
                                        <span class="fw-semibold">{{ formatPrice(product.sale_price) }}</span>
                                    </div>
                                    <BButton variant="primary" class="btn-sm mt-2" @click="addToCart(product)">
                                        Sepete Ekle
                                    </BButton>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="!isLoading" class="text-center my-5">
                            <p class="text-muted">Ürün bulunamadı.</p>
                        </div>
                    </BCardBody>
                </BCard>
            </BCol>

            <BCol cols="4">
                <BCard>
                    <BCardBody>
                        <div class="customer-info mb-3">
                            <h5 class="mb-2">Müşteri Bilgileri</h5>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-1"><strong>{{ customer.name }}</strong></p>
                                    <p class="mb-1 text-muted small">{{ customer.phone }}</p>
                                    <p class="mb-0 text-muted small">{{ customer.email }}</p>
                                </div>
                                <BButton
                                    variant="outline-primary"
                                    size="sm"
                                    @click="router.visit(route('pos.index'))"
                                >
                                    Değiştir
                                </BButton>
                            </div>
                        </div>

                        <hr>

                        <div class="cart-items">
                            <h5 class="mb-3">Sepet</h5>
                            <div v-if="cartItems.length === 0" class="text-center text-muted py-3">
                                Sepet boş
                            </div>
                            <div v-else class="cart-list">
                                <div v-for="(item, index) in cartItems" :key="index" class="cart-item mb-2 p-2 border rounded">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ item.name }}</h6>
                                            <div class="d-flex align-items-center">
                                                <BButton
                                                    variant="outline-secondary"
                                                    size="sm"
                                                    @click="decreaseQuantity(index)"
                                                >
                                                    -
                                                </BButton>
                                                <span class="mx-2">{{ item.quantity }}</span>
                                                <BButton
                                                    variant="outline-secondary"
                                                    size="sm"
                                                    @click="increaseQuantity(index)"
                                                >
                                                    +
                                                </BButton>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold">{{ formatPrice(item.total) }}</div>
                                            <BButton
                                                variant="link"
                                                size="sm"
                                                class="text-danger p-0"
                                                @click="removeFromCart(index)"
                                            >
                                                Kaldır
                                            </BButton>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="cart-summary">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Ara Toplam:</span>
                                <span>{{ formatPrice(subtotal) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>KDV ({{ taxRate }}%):</span>
                                <span>{{ formatPrice(taxAmount) }}</span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Toplam:</span>
                                <span>{{ formatPrice(total) }}</span>
                            </div>
                        </div>

                        <div class="mt-4">
                            <BButton
                                variant="primary"
                                class="w-100"
                                size="lg"
                                :disabled="cartItems.length === 0"
                                @click="completeSale"
                            >
                                Satışı Tamamla
                            </BButton>
                        </div>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>

        <!-- Ürün Resmi Modal -->
        <BModal v-model="showImageModal" :title="selectedProduct?.name" size="lg" hide-footer centered>
            <img
                v-if="selectedProduct?.image"
                :src="selectedProduct.image"
                :alt="selectedProduct.name"
                class="img-fluid w-100"
            >
        </BModal>

        <!-- Varyant Seçim Modalı -->
        <BModal v-model="showVariantModal" title="Varyant Seçimi" size="lg" hide-footer>
            <BTable
                v-if="selectedProduct?.variants"
                :items="selectedProduct.variants"
                :fields="variantFields"
                striped
                hover
            >
                <template #cell(name)="data">
                    {{ formatVariantName(data.item) }}
                </template>
                <template #cell(price)="data">
                    {{ formatPrice(data.item.sale_price) }}
                </template>
                <template #cell(stock)="data">
                    {{ getTotalStock(data.item) }}
                </template>
                <template #cell(actions)="data">
                    <BButton
                        variant="primary"
                        size="sm"
                        :disabled="getTotalStock(data.item) <= 0"
                        @click="selectVariant(data.item)"
                    >
                        Seç
                    </BButton>
                </template>
            </BTable>
        </BModal>
    </Layout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import Layout from '@/Layouts/Main.vue'
import debounce from 'lodash/debounce'
import Select2 from 'vue3-select2-component'
import {
    BCard,
    BCardBody,
    BRow,
    BCol,
    BFormInput,
    BButton,
    BSpinner,
    BModal,
    BTable
} from 'bootstrap-vue-next'
import axios from 'axios'

const props = defineProps({
    customer: {
        type: Object,
        required: true
    }
})

// Ürün arama ve filtreleme
const searchQuery = ref('')
const selectedCategory = ref('')
const selectedBrand = ref('')
const products = ref([])
const isLoading = ref(false)

// Kategori ve marka seçenekleri
const formattedCategoryOptions = ref([
    { id: '', text: 'Tüm Kategoriler' }
])
const formattedBrandOptions = ref([
    { id: '', text: 'Tüm Markalar' }
])

// Modal durumları
const showImageModal = ref(false)
const showVariantModal = ref(false)
const selectedProduct = ref(null)

// Sepet
const cartItems = ref([])
const taxRate = 18 // KDV oranı

// Varyant tablosu için sütunlar
const variantFields = [
    { key: 'name', label: 'Varyant' },
    { key: 'price', label: 'Fiyat' },
    { key: 'stock', label: 'Stok' },
    { key: 'actions', label: 'İşlem' }
]

// Ürünleri getir
const fetchProducts = async () => {
    isLoading.value = true
    try {
        const response = await axios.get(route('products.search'), {
            params: {
                query: searchQuery.value,
                category: selectedCategory.value,
                brand: selectedBrand.value
            }
        })
        products.value = response.data.data.map(product => ({
            ...product,
            sale_price: product.variants?.[0]?.prices?.[0]?.sellPrice || 0,
            discounted_price: product.variants?.[0]?.prices?.[0]?.discountPrice || 0,
        }))
        
        // Kategori ve marka seçeneklerini güncelle
        if (response.data.categories) {
            formattedCategoryOptions.value = [
                { id: '', text: 'Tüm Kategoriler' },
                ...response.data.categories.map(c => ({
                    id: c.id,
                    text: c.name
                }))
            ]
        }
        
        if (response.data.brands) {
            formattedBrandOptions.value = [
                { id: '', text: 'Tüm Markalar' },
                ...response.data.brands.map(b => ({
                    id: b.id,
                    text: b.name
                }))
            ]
        }
    } catch (error) {
        console.error('Ürünler yüklenirken hata oluştu:', error)
    } finally {
        isLoading.value = false
    }
}

// Debounce ile arama
const debounceSearch = debounce(fetchProducts, 300)

// Sepet işlemleri
const addToCart = (product) => {
    if (product.variants?.length > 0) {
        selectedProduct.value = product
        showVariantModal.value = true
    } else {
        addItemToCart(product)
    }
}

const addItemToCart = (product, variant = null) => {
    const item = {
        id: variant?.id || product.id,
        name: product.name + (variant ? ` - ${formatVariantName(variant)}` : ''),
        price: variant?.sale_price || product.sale_price,
        quantity: 1,
        product_id: product.id,
        variant_id: variant?.id
    }
    
    const existingIndex = cartItems.value.findIndex(i => 
        i.product_id === item.product_id && i.variant_id === item.variant_id
    )
    
    if (existingIndex > -1) {
        cartItems.value[existingIndex].quantity++
        updateItemTotal(existingIndex)
    } else {
        item.total = item.price
        cartItems.value.push(item)
    }
    
    showVariantModal.value = false
}

const removeFromCart = (index) => {
    cartItems.value.splice(index, 1)
}

const increaseQuantity = (index) => {
    cartItems.value[index].quantity++
    updateItemTotal(index)
}

const decreaseQuantity = (index) => {
    if (cartItems.value[index].quantity > 1) {
        cartItems.value[index].quantity--
        updateItemTotal(index)
    }
}

const updateItemTotal = (index) => {
    const item = cartItems.value[index]
    item.total = item.price * item.quantity
}

// Hesaplamalar
const subtotal = computed(() => {
    return cartItems.value.reduce((sum, item) => sum + item.total, 0)
})

const taxAmount = computed(() => {
    return (subtotal.value * taxRate) / 100
})

const total = computed(() => {
    return subtotal.value + taxAmount.value
})

// Yardımcı fonksiyonlar
const formatPrice = (price) => {
    return new Intl.NumberFormat('tr-TR', {
        style: 'currency',
        currency: 'TRY'
    }).format(price)
}

const formatVariantName = (variant) => {
    return variant.attribute_values?.map(av => av.value).join(' - ') || variant.name
}

const getTotalStock = (variant) => {
    return variant.stocks?.reduce((sum, stock) => sum + stock.quantity, 0) || 0
}

const selectVariant = (variant) => {
    addItemToCart(selectedProduct.value, variant)
}

const openImageModal = (product) => {
    selectedProduct.value = product
    showImageModal.value = true
}

// Kategori ve marka değişikliği
const handleCategoryChange = (value) => {
    selectedCategory.value = value
    fetchProducts()
}

const handleBrandChange = (value) => {
    selectedBrand.value = value
    fetchProducts()
}

// Satışı tamamla
const completeSale = async () => {
    try {
        const saleData = {
            customer_id: props.customer.id,
            items: cartItems.value.map(item => ({
                product_id: item.product_id,
                variant_id: item.variant_id,
                quantity: item.quantity,
                price: item.price
            })),
            subtotal: subtotal.value,
            tax_amount: taxAmount.value,
            total: total.value
        }
        
        await axios.post(route('sales.store'), saleData)
        router.visit(route('sales.index'))
    } catch (error) {
        console.error('Satış tamamlanırken hata oluştu:', error)
    }
}

// Sayfa yüklendiğinde ürünleri getir
onMounted(fetchProducts)
</script>

<style scoped>
.product-box {
    transition: all 0.3s ease;
    height: 100%;
}

.product-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.product-image-container {
    height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    cursor: pointer;
}

.product-image {
    max-height: 100px;
    object-fit: contain;
}

.product-name {
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
    height: 2.4rem;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.cart-item {
    transition: background-color 0.2s ease;
}

.cart-item:hover {
    background-color: #f8f9fa;
}

.cart-summary {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
}
</style> 