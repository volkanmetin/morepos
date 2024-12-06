<script setup>
import {Head, usePage, Link, useForm, router} from '@inertiajs/vue3';
import Layout from "@/Layouts/Main.vue";
import PageHeader from "@/Components/page-header.vue";
import {ref, reactive, computed} from "vue";
import { handleAxiosError } from '@/utils/errorHandler';
import { BButton, BRow, BCol, BCard, BCardBody, BCardHeader, BForm, BSpinner, BModal, BFormCheckbox, BTable, BFormInput, BFormGroup, BInputGroup, BInputGroupPrepend, BInputGroupText } from "bootstrap-vue-next";

import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';

const props = defineProps({
  categories: Array,
  vendors: Array,
  brands: Array, // Brand listesi için yeni prop
  variantOptions: {
    type: Array,
    default: () => []
  },
  warehouses: {
    type: Array,
    default: () => []
  }
});

const product = useForm({
  name: '',
  category_id: '',
  vendor_id: '',
  brand_id: '', // Brand seçimi için yeni alan
  description: '',
  purchase_price: '',
  sale_price: '',
  discounted_price: '',
  variants: [],
});

const editor = useEditor({
  content: product.description,
  extensions: [
    StarterKit,
  ],
  onUpdate: ({ editor }) => {
    product.description = editor.getHTML();
  },
});

const addVariant = () => {
  product.variants.push({ option_id: '' });
};

const removeVariant = (index) => {
  product.variants.splice(index, 1);
};

const isLoading = ref(false);

const saveProduct = async (e) => {
  e.preventDefault();
  isLoading.value = true;

  // Fiyatları ve stokları sayıya çevir
  const formattedVariants = product.variants.map(variant => ({
    ...variant,
    purchase_price: parseFloat(variant.purchase_price) || 0,
    sale_price: parseFloat(variant.sale_price) || 0,
    stock: Object.fromEntries(
      Object.entries(variant.stock).map(([id, value]) => [id, parseInt(value) || 0])
    )
  }));

  const formData = {
    ...product,
    purchase_price: parseFloat(product.purchase_price) || 0,
    sale_price: parseFloat(product.sale_price) || 0,
    discounted_price: parseFloat(product.discounted_price) || 0,
    variants: formattedVariants
  };

  try {
    const response = await axios.post(route('product.create'), formData);
    console.log('Sunucu yanıtı:', response.data);
    router.visit(route('product.index'));
  } catch (error) {
    handleAxiosError(error, "Ürün oluşturulurken bir hata oluştu");
  } finally {
    isLoading.value = false;
  }
};

const showVariantModal = ref(false);
const selectedValues = reactive({});
const selectedVariants = ref([]);

const variantFields = computed(() => [
  { key: 'image', label: 'Görsel' },
  { key: 'variant', label: 'Varyant' },
  { key: 'purchasePrice', label: 'Alış Fiyatı' },
  { key: 'salePrice', label: 'Satış Fiyatı' },
  { key: 'discountedPrice', label: 'İndirimli Fiyat' }, // Yeni eklenen alan
  { key: 'stock', label: 'Stok' },
]);

const openVariantModal = () => {
  console.log('Modal açılıyor, variantOptions:', props.variantOptions);
  // Her seçenek için bir dizi oluştur veya mevcut diziyi koru
  props.variantOptions.forEach(option => {
    if (!selectedValues[option.id]) {
      selectedValues[option.id] = [];
    }
  });
  showVariantModal.value = true;
};

const closeVariantModal = () => {
  showVariantModal.value = false;
};

const generateVariants = () => {
  console.log(selectedValues);
  const selectedOptions = Object.entries(selectedValues)
    .filter(([_, values]) => values.length > 0)
    .map(([optionId, values]) => {
      const option = props.variantOptions.find(opt => opt.id === parseInt(optionId));
      return values.map(valueId => ({
        id: parseInt(valueId),
        value: option.values.find(val => val.id === valueId).value
      }));
    });

  selectedVariants.value = cartesianProduct(selectedOptions).map(variant => ({
    image: null, // Yeni eklenen alan
    variant: Object.fromEntries(variant.map(v => [v.id, v.value])),
    purchasePrice: '0',
    salePrice: '0',
    discountedPrice: '0',
    stock: Object.fromEntries(props.warehouses.map(w => [w.id, '0']))
  }));

  product.variants = selectedVariants.value.map(variant => ({
    image: variant.image,
    variant: variant.variant,
    purchase_price: variant.purchasePrice,
    sale_price: variant.salePrice,
    discounted_price: variant.discountedPrice,
    stock: variant.stock
  }));

  closeVariantModal();
};

const cartesianProduct = (arrays) => {
  return arrays.reduce((acc, curr) => 
    acc.flatMap(x => curr.map(y => [...x, y])),
    [[]]
  );
};

const variantGroupFields = [
    { key: 'name', label: 'Varyant Grubu' },
    { key: 'values', label: 'Seçilen Değerler' }
];

const selectedVariantGroups = computed(() => {
    return Object.entries(selectedValues)
        .filter(([_, values]) => values.length > 0)
        .map(([optionId, values]) => {
            const option = props.variantOptions.find(opt => opt.id === parseInt(optionId));
            return {
                name: option.name,
                values: values.map(valueId => option.values.find(val => val.id === valueId).value)
            };
        });
});

const uploadImage = async (event, variantIndex) => {
  const file = event.target.files[0];
  if (!file) return;

  const formData = new FormData();
  formData.append('image', file);

  try {
    const response = await axios.post(route('product.upload-image'), formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    selectedVariants.value[variantIndex].image = response.data.url;
    product.variants[variantIndex].image = response.data.name; // Ürün varyantlarını da güncelle
  } catch (error) {
    console.error('Görsel yükleme hatası:', error);
  }
};

const removeImage = (variantIndex) => {
  selectedVariants.value[variantIndex].image = null;
  product.variants[variantIndex].image = null; // Ürün varyantlarını da güncelle
};
</script>

<template>
    <Layout>
        <Head :title="$t('product.create-title')"/>
        <PageHeader :title="$t('product.create-title')" pageTitle="">
            <Link :href="route('product.index')" class="btn btn-primary btn-sm">
                <i class="ri-arrow-left-line me-1"></i> {{ $t('global.back-to-list') }}
            </Link>
        </PageHeader>
        <BForm @submit="saveProduct">
            <BRow>
                <BCol cols="9">
                    <!-- Ürün Adı ve Açıklama Card'ı -->
                    <BCard no-body class="mb-3">
                        <BCardHeader>
                            <h2 class="card-title mb-0">Ürün Bilgileri</h2>
                        </BCardHeader>
                        <BCardBody>
                            <div class="mb-4">
                                <label for="productName" class="form-label">Ürün Adı</label>
                                <input v-model="product.name" id="productName" type="text" class="form-control" required :disabled="isLoading" />
                            </div>

                            <BRow class="mb-4">
                                <BCol cols="4">
                                    <BFormGroup label="Alış Fiyatı" label-for="purchasePrice">
                                        <BFormInput
                                            id="purchasePrice"
                                            v-model="product.purchase_price"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            :disabled="isLoading"
                                            placeholder="Alış Fiyatı"
                                        />
                                    </BFormGroup>
                                </BCol>
                                <BCol cols="4">
                                    <BFormGroup label="Satış Fiyatı" label-for="salePrice">
                                        <BFormInput
                                            id="salePrice"
                                            v-model="product.sale_price"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            :disabled="isLoading"
                                            placeholder="Satış Fiyatı"
                                        />
                                    </BFormGroup>
                                </BCol>
                                <BCol cols="4">
                                    <BFormGroup label="İndirimli Fiyat" label-for="discountedPrice">
                                        <BFormInput
                                            id="discountedPrice"
                                            v-model="product.discounted_price"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            :disabled="isLoading"
                                            placeholder="İndirimli Fiyat"
                                        />
                                    </BFormGroup>
                                </BCol>
                            </BRow>


                            <div class="mb-4">
                                <label for="description" class="form-label">Açıklama</label>
                                <div class="border rounded-md p-2">
                                    <editor-content :editor="editor" :disabled="isLoading" />
                                </div>
                            </div>

                        </BCardBody>
                    </BCard>

                    <!-- Varyantlar Card'ı -->
                    <BCard no-body class="mb-3">
                        <BCardHeader>
                            <h2 class="card-title mb-0">Varyant Seçimi</h2>
                        </BCardHeader>
                        <BCardBody>
                            <BRow>
                                <BCol cols="3">
                                    <BButton variant="primary" @click="openVariantModal" :disabled="isLoading" class="w-100">
                                        Varyant Seç
                                    </BButton>
                                </BCol>
                                <BCol cols="9">
                                    <BTable v-if="selectedVariantGroups.length > 0" striped hover responsive :items="selectedVariantGroups" :fields="variantGroupFields" small>
                                        <template #cell(values)="data">
                                            <span v-for="(value, index) in data.item.values" :key="index" class="badge bg-light text-dark me-1">
                                                {{ value }}
                                            </span>
                                        </template>
                                    </BTable>
                                </BCol>
                            </BRow>
                        </BCardBody>
                    </BCard>
                </BCol>

                <BCol cols="3">
                    <!-- Kategori, Vendor ve Brand Seçimi Card'ı -->
                    <BCard no-body class="mb-3">
                        <BCardHeader>
                            <h2 class="card-title mb-0">Ürün Tanımları</h2>
                        </BCardHeader>
                        <BCardBody>
                            <div class="mb-4">
                                <label for="category" class="form-label">Kategori:</label>
                                <select v-model="product.category_id" id="category" class="form-select" required :disabled="isLoading">
                                    <option value="">Kategori Seçin</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="vendor" class="form-label">Tedarikçi:</label>
                                <select v-model="product.vendor_id" id="vendor" class="form-select" :disabled="isLoading">
                                    <option value="">Tedarikçi Seçin</option>
                                    <option v-for="vendor in vendors" :key="vendor.id" :value="vendor.id">
                                        {{ vendor.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="brand" class="form-label">Marka:</label>
                                <select v-model="product.brand_id" id="brand" class="form-select" :disabled="isLoading">
                                    <option value="">Marka Seçin</option>
                                    <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                                        {{ brand.name }}
                                    </option>
                                </select>
                            </div>
                        </BCardBody>
                    </BCard>

                    <!-- Ürün Ekle Butonu Card'ı -->
                    <BCard no-body class="mb-3">
                        <BCardBody>
                            <button type="submit" class="btn btn-success w-100" :disabled="isLoading">
                                <template v-if="isLoading">
                                    <BSpinner small class="me-2"></BSpinner>
                                    Lütfen bekleyin...
                                </template>
                                <template v-else>
                                    Ürünü Kaydet
                                </template>
                            </button>
                        </BCardBody>
                    </BCard>
                </BCol>
            </BRow>
        </BForm>

        <!-- BModal ile Varyant Modal -->
        <BModal v-model="showVariantModal" title="Varyant Seçenekleri" @hidden="closeVariantModal" size="lg" header-class="bg-body pb-3" hide-footer>
          <div v-if="variantOptions && variantOptions.length">
            <div v-for="option in variantOptions" :key="option.id" class="mb-4">
              <h3 class="font-medium mb-2 fs-5 pb-2 border-bottom border-light">{{ option.name }}</h3>
              <BRow>
                <BCol v-for="value in option.values" :key="value.id" cols="3" class="mb-2">
                  <BFormCheckbox
                    v-model="selectedValues[option.id]"
                    :value="value.id"
                    :id="`value-${value.id}`"
                    :name="`option-${option.id}`"
                  >
                    {{ value.value }}
                  </BFormCheckbox>
                </BCol>
              </BRow>
            </div>
          </div>
          <div v-else>
            Varyant seçeneği bulunamadı.
          </div>
          <div class="w-100 text-end">
            <BButton variant="primary" @click="generateVariants" :disabled="isLoading" class="me-2">Varyantları Oluştur</BButton>
            <BButton variant="secondary" @click="closeVariantModal" :disabled="isLoading">
              İptal
            </BButton>
          </div>
        </BModal>

        <!-- Seçilen varyantları göster -->
        <BCard v-if="selectedVariants.length" class="mt-4" no-body>
            <BCardHeader>
                <h2 class="card-title mb-0">Varyantlar</h2>
            </BCardHeader>
            <BCardBody>
                <BTable 
                    striped 
                    hover 
                    responsive 
                    :items="selectedVariants" 
                    :fields="variantFields" 
                    small
                    table-class="align-middle"
                >
                    <template #cell(image)="data">
                        <div class="d-flex flex-column align-items-center">
                            <img v-if="data.item.image" :src="data.item.image" alt="Varyant görseli" class="img-thumbnail mb-2" style="width: 50px; height: 50px; object-fit: cover;">
                            <div v-else class="bg-light d-flex justify-content-center align-items-center mb-2" style="width: 50px; height: 50px; border: 1px dotted #c971ff">
                                <span class="fs-xs text-center text-muted">Görsel yok</span>
                            </div>
                            <input type="file" :id="`variant-image-${data.index}`" @change="(e) => uploadImage(e, data.index)" accept="image/*" class="d-none">
                            <div class="btn-group" role="group">
                                <label :for="`variant-image-${data.index}`" class="btn btn-sm btn-outline-primary">
                                    {{ data.item.image ? 'Değiştir' : 'Ekle' }}
                                </label>
                                <button v-if="data.item.image" @click="removeImage(data.index)" class="btn btn-sm btn-outline-danger">
                                    Sil
                                </button>
                            </div>
                        </div>
                    </template>
                    <template #cell(variant)="data">
                        {{ Object.values(data.item.variant).join(' - ') }}
                    </template>
                    <template #cell(purchasePrice)="data">
                        <BFormInput 
                            type="number" 
                            v-model="data.item.purchasePrice" 
                            size="sm"
                            :disabled="isLoading"
                            min="0"
                            step="0.01"
                        />
                    </template>
                    <template #cell(salePrice)="data">
                        <BFormInput 
                            type="number" 
                            v-model="data.item.salePrice" 
                            size="sm"
                            :disabled="isLoading"
                            min="0"
                            step="0.01"
                        />
                    </template>
                    <template #cell(discountedPrice)="data">
                        <BFormInput 
                            type="number" 
                            v-model="data.item.discountedPrice" 
                            size="sm"
                            :disabled="isLoading"
                            min="0"
                            step="0.01"
                        />
                    </template>
                    <template #cell(stock)="data">
                        <div v-for="warehouse in props.warehouses" :key="warehouse.id" class="mb-2">
                            <BInputGroup size="sm">
                                <BInputGroupPrepend>
                                    <BInputGroupText>{{ warehouse.name }}</BInputGroupText>
                                </BInputGroupPrepend>
                                <BFormInput
                                    type="number"
                                    v-model="data.item.stock[warehouse.id]"
                                    :disabled="isLoading"
                                    min="0"
                                    step="1"
                                />
                            </BInputGroup>
                        </div>
                    </template>
                </BTable>
            </BCardBody>
        </BCard>
    </Layout>
</template>