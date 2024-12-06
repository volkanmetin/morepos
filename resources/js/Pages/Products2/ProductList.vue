<template>
    <div>
        <div class="mb-3">
            <input v-model="search" class="form-control" placeholder="Ürün ara...">
        </div>
        <div class="mb-3">
            <select v-model="selectedCategory" class="form-select">
                <option value="">Tüm Kategoriler</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ category.name }}
                </option>
            </select>
        </div>
        <table class="table table-striped">
            <thead>
            <tr>
                <th>ID</th>
                <th>Ürün Adı</th>
                <th>Kategori</th>
                <th>Varyantlar</th>
                <th>İşlemler</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="product in filteredProducts" :key="product.id">
                <td>{{ product.id }}</td>
                <td>{{ product.name }}</td>
                <td>{{ product.category.name }}</td>
                <td>
                    <ul>
                        <li v-for="variant in product.variants" :key="variant.id">
                            SKU: {{ variant.sku }}, Fiyat: {{ variant.price }}
                            <br>
                            Özellikler:
                            <span v-for="attr in variant.attribute_values" :key="attr.id">
                  {{ attr.attribute_group.name }}: {{ attr.value }},
                </span>
                        </li>
                    </ul>
                </td>
                <td>
                    <button @click="editProduct(product)" class="btn btn-sm btn-primary me-2">Düzenle</button>
                    <button @click="confirmDelete(product)" class="btn btn-sm btn-danger">Sil</button>
                </td>
            </tr>
            </tbody>
        </table>
        <nav>
            <ul class="pagination">
                <li v-for="page in totalPages" :key="page" class="page-item" :class="{ active: page === currentPage }">
                    <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
                </li>
            </ul>
        </nav>

        <ProductEditModal
            v-if="showEditModal"
            :product="selectedProduct"
            :categories="categories"
            :attributeGroups="attributeGroups"
            @close="showEditModal = false"
            @update="updateProduct"
        />

        <DeleteConfirmationModal
            v-if="showDeleteModal"
            :product="selectedProduct"
            @close="showDeleteModal = false"
            @confirm="deleteProduct"
        />
    </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import ProductEditModal from './ProductEditModal.vue';
import DeleteConfirmationModal from './DeleteConfirmationModal.vue';

export default {
    components: {
        ProductEditModal,
        DeleteConfirmationModal,
    },
    props: {
        initialProducts: Array,
        categories: Array,
        attributeGroups: Array,
    },
    setup(props) {
        const products = ref(props.initialProducts);
        const search = ref('');
        const selectedCategory = ref('');
        const currentPage = ref(1);
        const itemsPerPage = 10;

        const showEditModal = ref(false);
        const showDeleteModal = ref(false);
        const selectedProduct = ref(null);

        const filteredProducts = computed(() => {
            return (products.value || [])
                .filter(product =>
                    product.name.toLowerCase().includes(search.value.toLowerCase()) &&
                    (selectedCategory.value === '' || product.category.id === parseInt(selectedCategory.value))
                )
                .slice((currentPage.value - 1) * itemsPerPage, currentPage.value * itemsPerPage);
        });

        const totalPages = computed(() =>
            Math.ceil(products.value.length / itemsPerPage)
        );

        const changePage = (page) => {
            currentPage.value = page;
        };

        const editProduct = (product) => {
            selectedProduct.value = JSON.parse(JSON.stringify(product)); // Deep clone to avoid modifying the original
            showEditModal.value = true;
        };

        const updateProduct = (updatedProduct) => {
            const index = products.value.findIndex(p => p.id === updatedProduct.id);
            if (index !== -1) {
                products.value[index] = updatedProduct;
            }
            showEditModal.value = false;
        };

        const confirmDelete = (product) => {
            selectedProduct.value = product;
            showDeleteModal.value = true;
        };

        const deleteProduct = () => {
            Inertia.delete(route('products.destroy', selectedProduct.value.id), {
                onSuccess: () => {
                    products.value = products.value.filter(p => p.id !== selectedProduct.value.id);
                    showDeleteModal.value = false;
                },
            });
        };

        return {
            products,
            search,
            selectedCategory,
            currentPage,
            filteredProducts,
            totalPages,
            changePage,
            showEditModal,
            showDeleteModal,
            selectedProduct,
            attributeGroups: props.attributeGroups,
            editProduct,
            updateProduct,
            confirmDelete,
            deleteProduct,
        };
    },
}
</script>
