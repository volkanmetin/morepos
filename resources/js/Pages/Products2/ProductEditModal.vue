<template>
    <div class="modal" tabindex="-1" style="display: block; background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ürün Düzenle</h5>
                    <button type="button" class="btn-close" @click="$emit('close')"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="submitForm">
                        <div class="mb-3">
                            <label for="edit-name" class="form-label">Ürün Adı</label>
                            <input v-model="form.name" id="edit-name" type="text" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-description" class="form-label">Açıklama</label>
                            <textarea v-model="form.description" id="edit-description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit-category" class="form-label">Kategori</label>
                            <select v-model="form.category_id" id="edit-category" class="form-select" required>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                        </div>

                        <h3>Varyantlar</h3>
                        <div v-for="(variant, variantIndex) in form.variants" :key="variantIndex" class="mb-3 p-3 border rounded">
                            <div class="mb-2">
                                <label :for="'edit-variant-sku-' + variantIndex" class="form-label">SKU</label>
                                <input v-model="variant.sku" :id="'edit-variant-sku-' + variantIndex" type="text" class="form-control" required>
                            </div>
                            <div class="mb-2">
                                <label :for="'edit-variant-price-' + variantIndex" class="form-label">Fiyat</label>
                                <input v-model.number="variant.price" :id="'edit-variant-price-' + variantIndex" type="number" step="0.01" class="form-control" required>
                            </div>
                            <h4>Özellikler</h4>
                            <div v-for="attributeGroup in attributeGroups" :key="attributeGroup.id" class="mb-2">
                                <label :for="'edit-variant-attribute-' + variantIndex + '-' + attributeGroup.id" class="form-label">{{ attributeGroup.name }}</label>
                                <select
                                    v-model="variant.attributes[attributeGroup.id]"
                                    :id="'edit-variant-attribute-' + variantIndex + '-' + attributeGroup.id"
                                    class="form-select"
                                    required
                                >
                                    <option v-for="value in attributeGroup.values" :key="value.id" :value="value.id">
                                        {{ value.value }}
                                    </option>
                                </select>
                            </div>
                            <button type="button" @click="removeVariant(variantIndex)" class="btn btn-danger btn-sm mt-2">Varyantı Sil</button>
                        </div>

                        <button type="button" @click="addVariant" class="btn btn-secondary mb-3">Yeni Varyant Ekle</button>

                        <button type="submit" class="btn btn-primary">Güncelle</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue';
import { Inertia } from '@inertiajs/inertia';

export default {
    props: {
        product: Object,
        categories: Array,
        attributeGroups: Array,
    },
    emits: ['close', 'update'],
    setup(props, { emit }) {
        const form = ref({
            name: '',
            description: '',
            category_id: '',
            variants: []
        });

        const initForm = () => {
            if (props.product) {
                form.value = {
                    ...props.product,
                    variants: (props.product.variants || []).map(variant => ({
                        ...variant,
                        attributes: Object.fromEntries(
                            (variant.attribute_values || []).map(attr => [attr.attribute_group_id, attr.id])
                        )
                    }))
                };
            }
        };

        onMounted(initForm);

        watch(() => props.product, initForm, { deep: true });

        const addVariant = () => {
            form.value.variants.push({
                sku: '',
                price: null,
                attributes: Object.fromEntries(props.attributeGroups.map(group => [group.id, '']))
            });
        };

        const removeVariant = (index) => {
            form.value.variants.splice(index, 1);
        };

        const submitForm = () => {
            Inertia.put(route('products.update', props.product.id), form.value, {
                onSuccess: () => {
                    emit('update', form.value);
                    emit('close');
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
