<template>
    <div class="container">
        <h1 class="my-4">Ürün Yönetimi</h1>

        <div class="row">
            <div class="col-md-8">
                <ProductList :initialProducts="initialProducts" :categories="categories" />
            </div>
            <div class="col-md-4">
                <h2>Yeni Ürün Ekle</h2>
                <ProductAddForm :categories="categories" :attributeGroups="attributeGroups" />
            </div>
        </div>
    </div>
</template>

<script>
import { defineComponent } from 'vue';
import { Head } from '@inertiajs/vue3';
import ProductList from './ProductList.vue';
import ProductAddForm from './ProductAddForm.vue';

export default defineComponent({
    components: {
        Head,
        ProductList,
        ProductAddForm,
    },
    props: {
        initialProducts: {
            type: Array,
            default: () => []
        },
        categories: {
            type: Array,
            default: () => []
        },
        attributeGroups: {
            type: Array,
            default: () => []
        },
    },
});
</script>
