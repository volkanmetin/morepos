<script setup>
import { ref, computed } from 'vue';
import { BCard, BCardBody, BForm, BFormInput, BFormSelect, BTable, BButton, BInputGroup, BInputGroupAppend, BInputGroupPrepend, BSpinner } from "bootstrap-vue-next";
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
    initialCartItems: {
        type: Array,
        default: () => []
    }
});

const cartItems = ref(props.initialCartItems);
const selectedShipping = ref('free');
const discountCode = ref('');
const specialDiscount = ref({ type: 'percentage', value: 0 });
const isLoading = ref(false); // isLoading'i burada tanımlıyoruz

const shippingOptions = [
    { value: 'free', text: 'Ücretsiz Kargo', price: 0 },
    { value: 'standard', text: 'Standart Kargo', price: 100 },
    { value: 'express', text: 'Hızlı Kargo', price: 150 },
];

const subtotal = computed(() => {
    return cartItems.value.reduce((total, item) => total + Number(item.price) * item.quantity, 0);
});

const shippingCost = computed(() => {
    const selectedOption = shippingOptions.find(option => option.value === selectedShipping.value);
    return selectedOption ? selectedOption.price : 0;
});

const discountAmount = computed(() => {
    if (specialDiscount.value.type === 'percentage') {
        return subtotal.value * (specialDiscount.value.value / 100);
    } else {
        return specialDiscount.value.value;
    }
});

const taxRate = 0.18; // %18 KDV
const taxAmount = computed(() => (subtotal.value - discountAmount.value + shippingCost.value) * taxRate);

const total = computed(() => subtotal.value - discountAmount.value + shippingCost.value + taxAmount.value);

const updateQuantity = (item, newQuantity) => {
    item.quantity = Math.max(1, parseInt(newQuantity) || 1);
};

const applyCoupon = async () => {
    // Backend kontrolü burada yapılacak
    console.log('İndirim kodu uygulandı:', discountCode.value);
    isLoading.value = true;
    try {
        const response = await axios.post(route('pos.apply-coupon'), {
            discountCode: discountCode.value,
        });
        console.log('İndirim kodu uygulandı:', response.data);
    } catch (error) {
        console.error('İndirim kodu uygulama hatası:', error);
    } finally {
        isLoading.value = false;
    }
};

const discountTypes = [
    { value: 'percentage', text: 'Yüzde' },
    { value: 'fixed', text: 'Sabit Tutar' },
];

const checkout = async () => {
    isLoading.value = true;
    try {
        // Burada gerçek endpoint'inizi kullanın
        const response = await axios.post(route('pos.order'), {
            cartItems: cartItems.value,
            shipping: selectedShipping.value,
            discountCode: discountCode.value,
            specialDiscount: specialDiscount.value,
            total: total.value
        });
        console.log('Checkout başarılı:', response.data);
        // Başarılı checkout işlemi sonrası yapılacak işlemler
    } catch (error) {
        console.error('Checkout hatası:', error);
        // Hata durumunda yapılacak işlemler
    } finally {
        isLoading.value = false;
    }
};

const fields = [
    { key: 'name', label: 'Ürün Adı' },
    { key: 'variant', label: 'Varyant' },
    { key: 'price', label: 'Fiyat', class: 'text-end' },
    { key: 'quantity', label: 'Miktar' },
    { key: 'total', label: 'Toplam', class: 'text-end' }
];

const removeItem = async (item) => {
    const result = await Swal.fire({
        title: 'Emin misiniz?',
        text: `${item.name} ürününü sepetten çıkarmak istediğinizden emin misiniz?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Evet, çıkar',
        cancelButtonText: 'İptal'
    });

    if (result.isConfirmed) {
        const index = cartItems.value.findIndex(cartItem => cartItem.id === item.id);
        if (index !== -1) {
            cartItems.value.splice(index, 1);
        }
    }
};
</script>

<template>
  <BCard no-body class="mb-3">
    <div class="card-header border-bottom-dashed">
      <h5 class="card-title mb-0">Alışveriş Sepeti</h5>
    </div>
    <BCardBody>
      <div v-for="item in cartItems" :key="item.id" class="card mb-3 position-relative">
        <button @click="removeItem(item)" class="btn btn-sm btn-link text-danger p-2 position-absolute top-0 end-0 mt-1 me-1 card-remove-item-btn" title="Ürünü Sil">
          <i class="ri-close-line"></i>
        </button>
        <div class="row g-0">
          <div class="col-3">
            <img v-if="item.image" :src="item.image" class="img-fluid rounded-start" :alt="item.name">
            <img v-else src="@assets/images/no-image.png" class="img-fluid rounded-start" :alt="item.name">
          </div>
          <div class="col-9">
            <div class="card-body">
              <h5 class="card-title">{{ item.name }}</h5>
              <div class="d-flex justify-content-between align-items-center mb-2">
                <p class="card-text mb-0">Varyant: {{ item.variant }}</p>
                <BInputGroup size="xs" style="width: auto;">
                  <BButton size="xs" @click="updateQuantity(item, item.quantity - 1)" :disabled="isLoading">-</BButton>
                  <BFormInput
                    v-model.number="item.quantity"
                    type="number"
                    min="1"
                    :disabled="isLoading"
                    class="text-center form-control-xs"
                    style="width: 60px;"
                    readonly
                  />
                  <BButton size="xs" @click="updateQuantity(item, item.quantity + 1)" :disabled="isLoading">+</BButton>
                </BInputGroup>
              </div>
              <div class="d-flex justify-content-between align-items-center">
                <p class="card-text mb-0"><span class="fs-sm">Fiyat: {{ Number(item.price).toFixed(2) }} TL</span></p>
                <p class="card-text mb-0"><span class="fs-sm fw-medium">Toplam: {{ (item.price * item.quantity).toFixed(2) }} TL</span></p>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div v-if="cartItems.length === 0">
        <p class="text-center">Sepette henüz ürün yok.</p>
      </div>

      <div v-else>
        <BForm @submit.prevent="checkout" :disabled="isLoading">
          <div class="mb-2">
          <BInputGroup size="sm">
            <BInputGroupPrepend>
              <BInputGroupText>Kargo Seçeneği:</BInputGroupText>
            </BInputGroupPrepend>
            <BFormSelect
              v-model="selectedShipping"
              :options="shippingOptions"
              :disabled="isLoading"
            ></BFormSelect>
          </BInputGroup>
        </div>
        
        <div class="mb-2">
          <BInputGroup size="sm">
            <BFormInput v-model="discountCode" placeholder="İndirim Kodu" :disabled="isLoading" />
            <BInputGroupAppend>
              <BButton @click="applyCoupon" variant="primary" :disabled="isLoading">Uygula</BButton>
            </BInputGroupAppend>
          </BInputGroup>
        </div>
        
        <div class="mb-2">
          <BInputGroup size="sm">
            <BInputGroupPrepend>
              <BInputGroupText>Özel İndirim:</BInputGroupText>
            </BInputGroupPrepend>
            <BFormSelect v-model="specialDiscount.type" :options="discountTypes" :disabled="isLoading" />
            <BFormInput v-model.number="specialDiscount.value" type="number" :placeholder="specialDiscount.type === 'percentage' ? 'Yüzde' : 'Tutar'" min="0" :disabled="isLoading"/>
          </BInputGroup>
        </div>
        
        <div class="mt-3">
          <div class="d-flex justify-content-between checkout-summary-item">
            <span>Ara Toplam:</span>
            <strong>{{ subtotal.toFixed(2) }} TL</strong>
          </div>
          <div class="d-flex justify-content-between checkout-summary-item">
            <span>Kargo:</span>
            <strong>{{ shippingCost.toFixed(2) }} TL</strong>
          </div>
          <div class="d-flex justify-content-between checkout-summary-item">
            <span>İndirim:</span>
            <strong>{{ discountAmount.toFixed(2) }} TL</strong>
          </div>
          <div class="d-flex justify-content-between checkout-summary-item">
            <span>KDV (18%):</span>
            <strong>{{ taxAmount.toFixed(2) }} TL</strong>
          </div>
          <div class="d-flex justify-content-between mt-2 checkout-summary-item">
            <h5>Genel Toplam:</h5>
            <h5><strong>{{ total.toFixed(2) }} TL</strong></h5>
          </div>
        </div>

        <div class="mt-2">
          <BButton 
            type="submit" 
            variant="success" 
            size="md"
            class="w-100"
            :disabled="isLoading"
          >
            <BSpinner small v-if="isLoading"></BSpinner>
            {{ isLoading ? 'İşlem yapılıyor...' : 'Satış Oluştur' }}
            </BButton>
          </div>
        </BForm>
      </div>

    </BCardBody>
  </BCard>
</template>

<style scoped>
.card {
  overflow: hidden;
}
.ri-close-line {
  font-size: 1.2rem;
}
</style>