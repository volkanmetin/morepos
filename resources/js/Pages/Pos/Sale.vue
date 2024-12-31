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
                                <BCol cols="4">
                                    <Select2
                                        v-model="selectedCategory"
                                        :options="formattedCategoryOptions"
                                        @select="handleCategoryChange"
                                        :settings="{ width: '100%' }"
                                        placeholder="Kategori"
                                    />
                                </BCol>
                                <BCol cols="4">
                                    <Select2
                                        v-model="selectedBrand"
                                        :options="formattedBrandOptions"
                                        @select="handleBrandChange"
                                        :settings="{ width: '100%' }"
                                        placeholder="Marka"
                                    />
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
                                    <h3 class="product-name mt-2">
                                        <a :href="route('product.show', product.id)" target="_blank">{{ product.name }}</a>
                                    </h3>
                                    <div v-if="product.discounted_price && product.discounted_price > 0" class="price-info">
                                        <span class="text-decoration-line-through text-muted">{{ formatPrice(product.sale_price) }}</span><br />
                                        <span class="fw-semibold">{{ formatPrice(product.discounted_price) }}</span>
                                    </div>
                                    <div v-else class="price-info">
                                        <span class="fw-semibold">{{ formatPrice(product.sale_price) }}</span>
                                    </div>
                                    <div :class="{'text-danger': product.stock <= 0, 'text-muted': product.stock > 0}" class="small">
                                        Stok: {{ product.stock }}
                                    </div>
                                    <BButton 
                                        variant="primary" 
                                        class="btn-sm mt-2" 
                                        @click="addToCart(product)"
                                        :disabled="product.stock <= 0"
                                    >
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
                                                    :disabled="item.quantity <= 1"
                                                >
                                                    -
                                                </BButton>
                                                <span class="mx-2">{{ item.quantity }}</span>
                                                <BButton
                                                    variant="outline-secondary"
                                                    size="sm"
                                                    @click="increaseQuantity(index)"
                                                    :disabled="item.quantity >= item.max_stock"
                                                >
                                                    +
                                                </BButton>
                                            </div>
                                            <div class="small text-muted mt-1">
                                                Stok: {{ item.quantity }}/{{ item.max_stock }}
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

                        <hr v-if="cartItems.length > 0">

                        <!-- Ödeme Yöntemi -->
                        <div class="payment-method mb-3" v-if="cartItems.length > 0">
                            <h6 class="mb-2">Ödeme Yöntemi</h6>
                            <div class="payment-options d-flex gap-2">
                                <div
                                    v-for="method in paymentMethods"
                                    :key="method.value"
                                    class="payment-option flex-1"
                                >
                                    <input
                                        type="radio"
                                        :id="method.value"
                                        v-model="selectedPaymentMethod"
                                        :value="method.value"
                                        class="d-none"
                                    >
                                    <label
                                        :for="method.value"
                                        class="payment-label w-100 text-center p-2 rounded border"
                                        :class="{ 'active': selectedPaymentMethod === method.value }"
                                    >
                                        <i :class="method.icon" class="fs-4 d-block mb-1"></i>
                                        {{ method.text }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Kupon Kodu -->
                        <div class="coupon-code mb-3" v-if="cartItems.length > 0">
                            <h6 class="mb-2">Kupon Kodu</h6>
                            <BInputGroup>
                                <BFormInput
                                    v-model="couponCode"
                                    placeholder="Kupon kodu girin"
                                />
                                <BButton
                                    variant="primary"
                                    @click="applyCoupon"
                                    :disabled="!couponCode"
                                >
                                    Uygula
                                </BButton>
                            </BInputGroup>
                            <div v-if="couponError" class="text-danger small mt-1">
                                {{ couponError }}
                            </div>
                            <div v-if="appliedCoupon" class="text-success small mt-1 d-flex justify-content-between align-items-center">
                                <span>
                                    Kupon uygulandı: {{ appliedCoupon.code }} ({{ appliedCoupon.discount_type === 'percentage' ? '%' + appliedCoupon.amount : formatPrice(appliedCoupon.amount) }})
                                </span>
                                <BButton
                                    variant="link"
                                    size="sm"
                                    class="text-danger p-0"
                                    @click="removeCoupon"
                                >
                                    <i class="ri-delete-bin-line"></i>
                                </BButton>
                            </div>
                        </div>

                        <!-- Manuel İndirim -->
                        <div class="manual-discount mb-3" v-if="cartItems.length > 0">
                            <h6 class="mb-2">İndirim Uygula</h6>
                            <div class="d-flex gap-2">
                                <BFormSelect
                                    v-model="discountType"
                                    :options="discountTypes"
                                    style="width: 150px"
                                />
                                <BFormInput
                                    v-model.number="discountAmount"
                                    type="number"
                                    min="0"
                                    :max="discountType === 'percentage' ? 100 : subtotal"
                                    placeholder="Değer"
                                />
                                <BButton
                                    variant="primary"
                                    @click="applyDiscount"
                                    :disabled="!discountAmount"
                                >
                                    Uygula
                                </BButton>
                            </div>
                        </div>

                        <hr>

                        <!-- Özet -->
                        <div class="cart-summary">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Ara Toplam:</span>
                                <span>{{ formatPrice(subtotal) }}</span>
                            </div>
                            
                            <template v-if="appliedCoupon">
                                <div class="d-flex justify-content-between mb-2 text-success">
                                    <span>Kupon İndirimi:</span>
                                    <span>-{{ formatPrice(couponDiscountAmount) }}</span>
                                </div>
                            </template>

                            <template v-if="manualDiscount">
                                <div class="d-flex justify-content-between mb-2 text-success">
                                    <div class="d-flex align-items-center">
                                        <span>Manuel İndirim:</span>
                                        <BButton
                                            variant="link"
                                            size="sm"
                                            class="text-danger p-0 ms-1"
                                            @click="removeManualDiscount"
                                        >
                                            <i class="ri-delete-bin-line"></i>
                                        </BButton>
                                    </div>
                                    <span>-{{ formatPrice(manualDiscountAmount) }}</span>
                                </div>
                            </template>

                            <div class="d-flex justify-content-between mb-2">
                                <span>KDV ({{ props.taxRate }}%):</span>
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
                    {{ formatPrice(data.item.price) }}
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
    BTable,
    BFormSelect,
    BInputGroup
} from 'bootstrap-vue-next'
import axios from 'axios'

const props = defineProps({
    customer: {
        type: Object,
        required: true
    },
    taxRate: {
        type: Number,
        required: true,
        default: 18
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

// Varyant tablosu için sütunlar
const variantFields = [
    { key: 'name', label: 'Varyant' },
    { key: 'price', label: 'Fiyat' },
    { key: 'stock', label: 'Stok' },
    { key: 'actions', label: 'İşlem' }
]

// Ödeme yöntemi
const selectedPaymentMethod = ref('cash')
const paymentMethods = [
    { value: 'cash', text: 'NAKİT', icon: 'ri-money-dollar-circle-line' },
    { value: 'pos1', text: 'POS 1', icon: 'ri-bank-card-line' },
    { value: 'pos2', text: 'POS 2', icon: 'ri-bank-card-2-line' },
]

// Kupon kodu
const couponCode = ref('')
const couponError = ref('')
const appliedCoupon = ref(null)

// İndirim
const discountType = ref('percentage')
const discountAmount = ref(null)
const manualDiscount = ref(null)
const discountTypes = [
    { value: 'percentage', text: 'Yüzde (%)' },
    { value: 'fixed', text: 'Sabit Tutar' },
]

// Ürünleri getir
const fetchProducts = async () => {
    isLoading.value = true
    try {
        const response = await axios.get(route('product.search'), {
            params: {
                query: searchQuery.value,
                category: selectedCategory.value,
                brand: selectedBrand.value
            }
        })
        products.value = response.data.data
        
        // Kategori ve marka seçeneklerini sadece ilk yüklemede güncelle
        if (!formattedCategoryOptions.value.length || formattedCategoryOptions.value.length === 1) {
            formattedCategoryOptions.value = [
                { id: '', text: 'Tüm Kategoriler' },
                ...response.data.categories.map(c => ({
                    id: c.id,
                    text: c.name
                }))
            ]
        }
        
        if (!formattedBrandOptions.value.length || formattedBrandOptions.value.length === 1) {
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
        // Ana ürün için stok kontrolü
        if (getCartItemQuantity(product.id) >= product.stock) {
            return
        }
        addItemToCart(product)
    }
}

const addItemToCart = (product, variant = null) => {
    const item = {
        id: variant?.id || product.id,
        name: product.name + (variant ? ` - ${formatVariantName(variant)}` : ''),
        price: variant?.price || product.sale_price || product.price,
        quantity: 1,
        product_id: product.id,
        variant_id: variant?.id,
        max_stock: variant ? getTotalStock(variant) : product.stock
    }
    
    const existingIndex = cartItems.value.findIndex(i => 
        i.product_id === item.product_id && i.variant_id === item.variant_id
    )
    
    if (existingIndex > -1) {
        // Stok kontrolü
        if (cartItems.value[existingIndex].quantity >= cartItems.value[existingIndex].max_stock) {
            return
        }
        cartItems.value[existingIndex].quantity++
        updateItemTotal(existingIndex)
    } else {
        item.total = parseFloat(item.price) * item.quantity
        cartItems.value.push(item)
    }
    
    showVariantModal.value = false
}

const removeFromCart = (index) => {
    cartItems.value.splice(index, 1)
}

const increaseQuantity = (index) => {
    const item = cartItems.value[index]
    if (item.quantity < item.max_stock) {
        item.quantity++
        updateItemTotal(index)
    }
}

const decreaseQuantity = (index) => {
    if (cartItems.value[index].quantity > 1) {
        cartItems.value[index].quantity--
        updateItemTotal(index)
    }
}

const updateItemTotal = (index) => {
    const item = cartItems.value[index]
    item.total = parseFloat(item.price) * item.quantity
}

// Hesaplamalar
const subtotal = computed(() => {
    return cartItems.value.reduce((sum, item) => sum + parseFloat(item.total), 0)
})

const discountedSubtotal = computed(() => {
    return subtotal.value - couponDiscountAmount.value - manualDiscountAmount.value
})

const taxAmount = computed(() => {
    return (discountedSubtotal.value * props.taxRate) / 100
})

const total = computed(() => {
    return discountedSubtotal.value + taxAmount.value
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
const handleCategoryChange = (e) => {
    selectedCategory.value = e.id
    fetchProducts()
}

const handleBrandChange = (e) => {
    selectedBrand.value = e.id
    fetchProducts()
}

// Kupon uygulama
const applyCoupon = async () => {
    try {
        const response = await axios.post(route('pos.check-coupon'), {
            code: couponCode.value
        });
        
        if (response.data.success) {
            appliedCoupon.value = response.data.coupon;
            couponError.value = '';
            couponCode.value = '';
        } else {
            // Başarısız yanıt durumunda
            couponError.value = response.data.message;
            appliedCoupon.value = null;
        }
    } catch (error) {
        // HTTP hatası durumunda (404, 400 vb.)
        couponError.value = error.response?.data?.message || 'Kupon kodu geçersiz';
        appliedCoupon.value = null;
    }
}

// Manuel indirim uygulama
const applyDiscount = () => {
    if (discountAmount.value > 0) {
        manualDiscount.value = {
            type: discountType.value,
            amount: discountAmount.value
        }
        discountAmount.value = null
    }
}

// İndirim hesaplamaları
const couponDiscountAmount = computed(() => {
    if (!appliedCoupon.value) return 0
    
    return appliedCoupon.value.discount_type === 'percentage'
        ? (subtotal.value * appliedCoupon.value.amount) / 100
        : appliedCoupon.value.amount
})

const manualDiscountAmount = computed(() => {
    if (!manualDiscount.value) return 0
    
    return manualDiscount.value.type === 'percentage'
        ? (subtotal.value * manualDiscount.value.amount) / 100
        : manualDiscount.value.amount
})

// Satış tamamlama fonksiyonunu güncelle
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
            payment_method: selectedPaymentMethod.value,
            coupon_id: appliedCoupon.value?.id,
            manual_discount: manualDiscount.value,
            subtotal: subtotal.value,
            discount_amount: couponDiscountAmount.value + manualDiscountAmount.value,
            tax_amount: taxAmount.value,
            total: total.value
        }
        
        await axios.post(route('sales.store'), saleData)
        router.visit(route('sales.index'))
    } catch (error) {
        console.error('Satış tamamlanırken hata oluştu:', error)
    }
}

// Sepetteki ürün miktarını hesapla
const getCartItemQuantity = (productId, variantId = null) => {
    const item = cartItems.value.find(i => 
        i.product_id === productId && i.variant_id === variantId
    )
    return item ? item.quantity : 0
}

// Kupon silme
const removeCoupon = () => {
    appliedCoupon.value = null;
    couponError.value = '';
}

// Manuel indirim silme
const removeManualDiscount = () => {
    manualDiscount.value = null;
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

.payment-option {
    flex: 1;
}

.payment-label {
    cursor: pointer;
    transition: all 0.2s ease;
    border: 2px solid #dee2e6;
    background-color: white;
}

.payment-label:hover {
    border-color: var(--bs-primary);
    background-color: #f8f9fa;
}

.payment-label.active {
    border-color: var(--bs-primary);
    background-color: #56499f;
    color: white;
}

.search-form {
    .form-control,
    .select2-container .select2-selection {
        height: 38px !important;
    }

    .select2-container .select2-selection {
        display: flex;
        align-items: center;
    }

    .select2-container .select2-selection__arrow {
        top: 5px !important;
    }
}
</style> 