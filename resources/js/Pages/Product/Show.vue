<script setup>
import {Head, Link} from '@inertiajs/vue3';
import Layout from "@/Layouts/Main.vue";
import PageHeader from "@/Components/page-header.vue";
import { BRow, BCol, BCard, BCardBody, BCardHeader, BTable, BNavbarNav, BFormSelect } from "bootstrap-vue-next";
import { ref, onMounted, computed } from 'vue';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Navigation, Pagination } from 'swiper/modules';
import { BModal } from 'bootstrap-vue-next';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import Barcode from '@/Components/Barcode.vue';

const variantColumns = [
    {
        key: 'image',
        label: 'Resim',
    },
    {
        key: 'name',
        label: 'Varyant Adı',
    },
    {
        key: 'sku',
        label: 'Stok Kodu',
    },
    {
        key: 'barcode',
        label: 'Barkod',
    },
    {
        key: 'purchase_price',
        label: 'Alış Fiyatı',
        formatter: value => value ? `${value} ₺` : '-'
    },
    {
        key: 'sale_price',
        label: 'Satış Fiyatı', 
        formatter: value => value ? `${value} ₺` : '-'
    },
    {
        key: 'discounted_price',
        label: 'İndirimli Fiyat',
        formatter: value => value ? `${value} ₺` : '-'
    },
    {
        key: 'stock',
        label: 'Toplam Stok',
    },
    {
        key: 'actions',
        label: 'İşlemler',
    }
];

const props = defineProps({
    product: {
        type: Object,
        required: true
    },
    warehouses: {
        type: Array,
        required: true
    },
    variants: {
        type: Array,
        required: true
    }
});

const printCount = ref(1);
const printOptions = Array.from({length: 10}, (_, i) => ({
    value: i + 1,
    text: i + 1
}));

const printLabels = (variant) => {
    const printWindow = window.open('', '_blank');
    
    const labels = Array(printCount.value).fill().map(() => `
        <div class="label-container">
            <div class="product-name">${props.product.name}</div>
            <div class="product-price">${props.product.sale_price} ₺</div>
            <div class="barcode">*${props.product.id}*</div>
        </div>
    `).join('');

    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Etiket Yazdır - ${props.product.name}</title>
            <style>
                @media print {
                    @page {
                        size: 80mm 40mm;  /* Xprinter XP-470B için standart etiket boyutu */
                        margin: 0;
                    }
                    body {
                        margin: 0;
                        padding: 0;
                        width: 80mm;  /* Yazıcı genişliği */
                    }
                    .label-container {
                        page-break-after: always;  /* Her etiketten sonra sayfa sonu */
                        page-break-inside: avoid;
                    }
                }
                
                .label-container {
                    width: 80mm;  /* Etiket genişliği */
                    height: 40mm; /* Etiket yüksekliği */
                    padding: 2mm;
                    box-sizing: border-box;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                }
                
                .product-name {
                    font-size: 12pt;
                    font-weight: bold;
                    margin-bottom: 2mm;
                    text-align: center;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
                
                .product-price {
                    font-size: 14pt;
                    font-weight: bold;
                    text-align: center;
                    margin-bottom: 2mm;
                }
                
                .barcode {
                    margin-top: 2mm;
                    text-align: center;
                    font-family: 'Courier New', monospace;
                    font-size: 12pt;
                }

                /* Yazdırma önizlemesi için stil */
                @media screen {
                    body {
                        background: #f0f0f0;
                        padding: 20px;
                    }
                    .label-container {
                        background: white;
                        margin: 0 auto 10px auto;
                        box-shadow: 0 0 5px rgba(0,0,0,0.1);
                    }
                }
            </style>
        </head>
        <body>
            ${labels}
        </body>
        </html>
    `;

    printWindow.document.write(printContent);
    
    const printScript = document.createElement('script');
    printScript.textContent = `
        window.onload = function() {
            const mediaQueryList = window.matchMedia('print');
            mediaQueryList.addListener(function(mql) {
                if (!mql.matches) {
                    window.close();
                }
            });
            
            window.print();
        };
    `;
    printWindow.document.body.appendChild(printScript);
    printWindow.document.close();
};

const selectedImage = ref(null);
const showImageModal = ref(false);

const openImageModal = (image) => {
    selectedImage.value = image;
    showImageModal.value = true;
};

// Varyant filtrelerini tutacak reactive state
const selectedFilters = ref(new Set());
const attributeGroups = ref(new Map());

// Varyant değerlerini gruplandır ve başlangıçta hepsini seçili yap
onMounted(() => {
    props.variants.forEach(variant => {
        variant.attribute_values?.forEach(attr => {
            if (!attributeGroups.value.has(attr.attribute_group_id)) {
                attributeGroups.value.set(attr.attribute_group_id, new Set());
            }
            attributeGroups.value.get(attr.attribute_group_id).add(attr.id);
            selectedFilters.value.add(attr.id);
        });
    });
});

// Filtre butonuna tıklandığında
const toggleFilter = (attributeValueId) => {
    if (selectedFilters.value.has(attributeValueId)) {
        selectedFilters.value.delete(attributeValueId);
    } else {
        selectedFilters.value.add(attributeValueId);
    }
};

// Varyant filtrelerini gruplandıralım
const groupedAttributes = computed(() => {
    const groups = new Map();
    
    props.variants.forEach(variant => {
        variant.attribute_values?.forEach(attr => {
            if (!groups.has(attr.attribute_group_id)) {
                groups.set(attr.attribute_group_id, {
                    name: attr.attribute_group.name,
                    values: new Map()
                });
            }
            groups.get(attr.attribute_group_id).values.set(attr.id, {
                id: attr.id,
                value: attr.value
            });
        });
    });
    
    return new Map(
        Array.from(groups.entries()).map(([groupId, group]) => [
            groupId,
            {
                name: group.name,
                values: Array.from(group.values.values())
            }
        ])
    );
});

// Filtrelenmiş varyantları hesapla
const filteredVariants = computed(() => {
    // Eğer hiçbir filtre seçili değilse boş bir liste döndür
    if (selectedFilters.value.size === 0) return [];
    
    return props.variants.filter(variant => {
        return variant.attribute_values?.some(attr => 
            selectedFilters.value.has(attr.id)
        );
    });
});
</script>

<template>
    <Layout>
        <Head :title="props.product.name"/>
        <PageHeader :title="props.product.name" pageTitle="">
            <div class="d-flex gap-2">
                <Link :href="route('product.index')" class="btn btn-primary btn-sm">
                    <i class="ri-arrow-left-line me-1"></i> {{ $t('global.back-to-list') }}
                </Link>
                <Link 
                    :href="route('product.edit', {id: props.product.id})" 
                    class="btn btn-warning btn-sm"
                >
                    <i class="ri-edit-line me-1"></i> {{ $t('global.edit') }}
                </Link>
            </div>
        </PageHeader>

        <BRow>
            <BCol cols="9">
                <!-- Ürün Bilgileri Card'ı -->
                <BCard no-body class="mb-3">
                    <BCardHeader>
                        <h2 class="card-title mb-0">Ürün Bilgileri</h2>
                    </BCardHeader>
                    <BCardBody>
                        <BRow>
                            <!-- Ürün Fotoğrafı -->
                            <BCol cols="3">
                                <div class="product-gallery">
                                    <Swiper
                                        :modules="[Navigation, Pagination]"
                                        :slides-per-view="1"
                                        :navigation="true"
                                        :pagination="{ clickable: true }"
                                        :allow-touch-move="true"
                                        :space-between="0"
                                        class="product-swiper mb-2"
                                    >
                                        <SwiperSlide 
                                            v-for="media in props.product.media" 
                                            :key="media.id"
                                            @click="openImageModal(media)"
                                        >
                                            <div class="d-flex align-items-center justify-content-center">
                                                <img 
                                                    :src="media.original_url" 
                                                    :alt="props.product.name"
                                                    class="rounded cursor-pointer"
                                                />
                                            </div>
                                        </SwiperSlide>
                                        
                                        <!-- Eğer hiç görsel yoksa placeholder göster -->
                                        <SwiperSlide v-if="!props.product.media?.length">
                                            <div class="w-100 h-100 d-flex align-items-center justify-content-center">
                                                <img 
                                                    src="/images/placeholder.png" 
                                                    :alt="props.product.name"
                                                    class="rounded"
                                                />
                                            </div>
                                        </SwiperSlide>
                                    </Swiper>
                                    
                                    <!-- Küçük önizleme resimleri -->
                                    <div class="product-thumbnails d-flex gap-2 overflow-auto">
                                        <div 
                                            v-for="media in props.product.media" 
                                            :key="media.id"
                                            class="thumbnail-item"
                                            @click="openImageModal(media)"
                                        >
                                            <img 
                                                :src="media.original_url" 
                                                :alt="props.product.name"
                                                class="img-fluid rounded cursor-pointer"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </BCol>
                            
                            <!-- Ürün Detayları -->
                            <BCol cols="9">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Ürün Adı</label>
                                    <p>{{ props.product.name }}</p>
                                </div>

                                <BRow class="mb-4">
                                    <BCol cols="4">
                                        <label class="form-label fw-bold">Alış Fiyatı</label>
                                        <p>{{ props.product.purchase_price || '-' }} ₺</p>
                                    </BCol>
                                    <BCol cols="4">
                                        <label class="form-label fw-bold">Satış Fiyatı</label>
                                        <p>{{ props.product.sale_price || '-' }} ₺</p>
                                    </BCol>
                                    <BCol cols="4">
                                        <label class="form-label fw-bold">İndirimli Fiyat</label>
                                        <p>{{ props.product.discounted_price || '-' }} ₺</p>
                                    </BCol>
                                </BRow>

                                <div class="mb-4">
                                    <label class="form-label fw-bold">Açıklama</label>
                                    <div class="border rounded-md p-3 bg-light" v-html="props.product.description || '-'"></div>
                                </div>
                            </BCol>
                        </BRow>
                    </BCardBody>
                </BCard>

                <!-- Ürün Bilgileri Card'ından sonra -->
                <BCard no-body class="mb-3">
                    <BCardHeader class="d-flex justify-content-between align-items-center">
                        <h2 class="card-title mb-0">Varyantlar ({{ filteredVariants.length }})</h2>
                        
                        <!-- Varyant Filtreleri -->
                        <div class="d-flex gap-2">
                            <div 
                                v-for="[groupId, group] in groupedAttributes" 
                                :key="groupId" 
                                class="filter-group"
                            >
                                <small class="text-muted">{{ group.name }}</small>
                                <div class="d-flex flex-wrap gap-1">
                                    <div
                                        v-for="value in group.values"
                                        :key="value.id"
                                        class="form-check form-check-inline"
                                    >
                                        <input
                                            type="checkbox"
                                            class="form-check-input"
                                            :id="`filter-${value.id}`"
                                            :value="value.id"
                                            v-model="selectedFilters"
                                        />
                                        <label class="form-check-label ms-1 pt-1" :for="`filter-${value.id}`">
                                            {{ value.value }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </BCardHeader>
                    <BCardBody>
                        <BTable
                            :items="filteredVariants"
                            :fields="variantColumns"
                            responsive
                            striped
                            hover
                        >
                            <template #cell(image)="row">
                                <img 
                                    :src="row.item.media[0]?.original_url" 
                                    :alt="row.item.name"
                                    class="rounded variant-thumbnail cursor-pointer"
                                    style="width: 50px; height: 50px; object-fit: contain;"
                                    @click="openImageModal(row.item.media[0])"
                                />
                            </template>

                            <template #cell(name)="row">
                                {{ row.item.attribute_values?.map(attr => attr.value).join(' - ') || '-' }}
                            </template>

                            <template #cell(stock)="row">
                                <div v-for="stock in row.item.stocks" :key="stock.id">
                                    <span class="" :class="{'text-danger': stock.quantity === 0}">
                                        {{ stock.warehouse?.name || '-' }}: 
                                        {{ stock.quantity }}
                                    </span>
                                </div>
                            </template>

                            <template #cell(barcode)="row">
                                <div class="">
                                    <span class="mb-1">{{ row.item.barcode || '-' }}</span><br />
                                    <Barcode 
                                        :value="row.item.barcode"
                                        :width="120"
                                        :height="30"
                                        format="CODE128"
                                    />
                                </div>
                            </template>

                            <template #cell(actions)="row">
                                <div class="d-flex gap-2">
                                    <button 
                                        class="btn btn-info btn-sm"
                                        @click="() => printLabels(row.item)"
                                    >
                                        <i class="ri-printer-line"></i>
                                    </button>
                                </div>
                            </template>
                        </BTable>
                    </BCardBody>
                </BCard>
            </BCol>

            <BCol cols="3">
                <!-- Ürün Tanımları Card'ı -->
                <BCard no-body class="mb-3">
                    <BCardHeader>
                        <h2 class="card-title mb-0">Ürün Tanımları</h2>
                    </BCardHeader>
                    <BCardBody>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Kategori</label>
                            <p>{{ props.product.category?.name || '-' }}</p>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Tedarikçi</label>
                            <p>{{ props.product.vendor?.name || '-' }}</p>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Marka</label>
                            <p>{{ props.product.brand?.name || '-' }}</p>
                        </div>
                    </BCardBody>
                </BCard>

                <!-- Yazdırma Kartı -->
                <BCard no-body class="mb-3">
                    <BCardHeader>
                        <h2 class="card-title mb-0">Etiket Yazdır</h2>
                    </BCardHeader>
                    <BCardBody>
                        <div class="d-flex gap-2 align-items-end">
                            <div class="flex-grow-1">
                                <label class="form-label fw-bold">Adet</label>
                                <BFormSelect
                                    v-model="printCount"
                                    :options="printOptions"
                                    class="form-select"
                                />
                            </div>
                            <button @click="printLabels" class="btn btn-primary">
                                <i class="ri-printer-line me-1"></i> Yazdır
                            </button>
                        </div>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>
    </Layout>

    <!-- Görsel Modal -->
    <BModal 
        v-model="showImageModal"
        size="fullscreen"
        centered
        title="Ürün Görseli"
        body-class="p-0"
        dialog-class="m-0"
        content-class="h-100"
    >
        <div class="d-flex align-items-center justify-content-center h-100 bg-dark image-modal-container">
            <img 
                v-if="selectedImage"
                :src="selectedImage.original_url" 
                :alt="props.product.name"
                class="modal-image"
            />
        </div>
    </BModal>
</template>

<style scoped>
.product-gallery {
    position: relative;
}

.product-swiper {
    border-radius: 0.5rem;
    overflow: hidden;
    aspect-ratio: 1/1;
}

:deep(.swiper) {
    height: 100%;
    width: 100%;
}

:deep(.swiper-slide) {
    height: 100%;
    width: 100%;
    overflow: hidden;
}

:deep(.swiper-slide > div) {
    height: 100%;
    width: 100%;
    background: #f8f9fa;
}

:deep(.swiper-slide img) {
    max-width: 100%;
    max-height: 100%;
    width: auto;
    height: auto;
    object-fit: contain;
    display: block;
    margin: auto;
}

.cursor-pointer {
    cursor: pointer;
}

.product-thumbnails {
    margin-top: 1rem;
    padding-bottom: 0.5rem;
}

.thumbnail-item {
    flex: 0 0 auto;
    width: 60px;
    height: 60px;
    border-radius: 0.25rem;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.2s;
}

.thumbnail-item:hover {
    border-color: var(--bs-primary);
}

.thumbnail-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Swiper özelleştirmeleri */
:deep(.swiper-button-next),
:deep(.swiper-button-prev) {
    color: var(--bs-primary);
    background: rgba(255, 255, 255, 0.8);
    width: 32px;
    height: 32px;
    border-radius: 50%;
}

:deep(.swiper-button-next:after),
:deep(.swiper-button-prev:after) {
    font-size: 16px;
}

:deep(.swiper-pagination-bullet-active) {
    background: var(--bs-primary);
}

.variant-thumbnail {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
}

.image-modal-container {
    padding: 20px;
    overflow: auto;
}

.modal-image {
    max-width: 100vw;
    max-height: 100vh;
    object-fit: contain;
    margin: auto;
}

.filter-group {
    border: 1px solid #dee2e6;
    padding: 10px;
    border-radius: 0.25rem;
    background-color: #f8f9fa;
}

.form-check-inline {
    display: flex;
    align-items: center;
    margin-right: 10px;
}
</style>
