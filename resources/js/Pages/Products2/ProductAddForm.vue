<template>
    <form @submit.prevent="submitForm">
        <div class="mb-3">
            <label for="name" class="form-label">Ürün Adı</label>
            <input v-model="form.name" id="name" type="text" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Açıklama</label>
            <textarea v-model="form.description" id="description" class="form-control" rows="3"></textarea>
        </div>
        <div class="mb-3">
            <label for="category" class="form-label">Kategori</label>
            <select v-model="form.category_id" id="category" class="form-select" required>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ category.name }}
                </option>
            </select>
        </div>

        <h3>Varyantlar</h3>
        <div v-for="(variant, variantIndex) in form.variants" :key="variantIndex" class="mb-3 p-3 border rounded">
            <div class="mb-2">
                <label :for="'variant-sku-' + variantIndex" class="form-label">SKU</label>
                <input v-model="variant.sku" :id="'variant-sku-' + variantIndex" type="text" class="form-control" required>
            </div>
            <div class="mb-2">
                <label :for="'variant-price-' + variantIndex" class="form-label">Fiyat</label>
                <input v-model.number="variant.price" :id="'variant-price-' + variantIndex" type="number" step="0.01" class="form-control" required>
            </div>
            <h4>Özellikler</h4>
            <div v-for="(attributeGroup, groupIndex) in attributeGroups" :key="groupIndex" class="mb-2">
                <label :for="'variant-attribute-' + variantIndex + '-' + groupIndex" class="form-label">{{ attributeGroup.name }}</label>
                <select v-model="variant.attributes[attributeGroup.id]" :id="'variant-attribute-' + variantIndex + '-' + groupIndex" class="form-select" required>
                    <option v-for="value in attributeGroup.values" :key="value.id" :value="value.id">
                        {{ value.value }}
                    </option>
                </select>
            </div>
            <button type="button" @click="removeVariant(variantIndex)" class="btn btn-danger btn-sm mt-2">Varyantı Sil</button>
        </div>

        <button type="button" @click="addVariant" class="btn btn-secondary mb-3">Yeni Varyant Ekle</button>

        <button type="submit" class="btn btn-primary">Ürün Ekle</button>
    </form>
</template>

<script>
import { ref } from 'vue';
import { Inertia } from '@inertiajs/inertia';

export default {
    props: {
        categories: Array,
        attributeGroups: Array,
    },
    setup(props) {
        const form = ref({
            name: '',
            description: '',
            category_id: '',
            variants: [{ sku: '', price: null, attributes: {} }]
        });

        const addVariant = () => {
            form.value.variants.push({ sku: '', price: null, attributes: {} });
        };

        const removeVariant = (index) => {
            form.value.variants.splice(index, 1);
        };

        const submitForm = () => {
            Inertia.post(route('products.store'), form.value, {
                onSuccess: () => {
                    form.value = {
                        name: '',
                        description: '',
                        category_id: '',
                        variants: [{sku: '', price: null, attributes: {}}]
                    };
                },
            });
        };

        return {
            form,
            addVariant,
            removeVariant,
            submitForm,
        };
    },
}
</script>
