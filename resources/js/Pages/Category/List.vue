<script setup>
import {Head, usePage} from '@inertiajs/vue3';
import Layout from "@/Layouts/Main.vue";
import PageHeader from "@/Components/page-header.vue";
import {onMounted, ref} from "vue";
import { createDataTableConfig } from '@/utils/datatableUtils';
import { handleAxiosError } from '@/utils/errorHandler';
import EditModal from "@/Components/EditModal.vue";
import {BButton, BTable, BRow, BCard, BCardBody} from "bootstrap-vue-next";
import CreateModal from "@/Components/CreateModal.vue";

const page = usePage();
const categories = ref(page.props.categories);
const expandedCategories = ref(new Set());

const fields = [
    {
        key: 'name',
        label: 'Kategori Adı',
        tdClass: 'align-middle'
    },
    {
        key: 'product_count',
        label: 'Ürün Sayısı',
        tdClass: 'align-middle text-center'
    },
    {
        key: 'actions',
        label: 'İşlemler',
        tdClass: 'align-middle text-end'
    }
];

const toggleCategory = (categoryId) => {
    if (expandedCategories.value.has(categoryId)) {
        expandedCategories.value.delete(categoryId);
    } else {
        expandedCategories.value.add(categoryId);
    }
    updateVisibleCategories();
};

const isExpanded = (categoryId) => {
    return expandedCategories.value.has(categoryId);
};

const hasChildren = (category) => {
    return category.children && category.children.length > 0;
};

const formatCategories = (cats, parentId = null) => {
    return cats.map(category => {
        const formattedCategory = {
            ...category,
            _isChild: parentId !== null,
            _parentId: parentId,
            _showChildren: isExpanded(category.id),
            _hasChildren: hasChildren(category),
            product_count: category.products_count || 0
        };
        
        if (hasChildren(category)) {
            return [
                formattedCategory,
                ...(isExpanded(category.id) ? formatCategories(category.children, category.id) : [])
            ];
        }
        
        return formattedCategory;
    }).flat();
};

const visibleCategories = ref([]);

const updateVisibleCategories = () => {
    visibleCategories.value = formatCategories(categories.value);
};

onMounted(() => {
    updateVisibleCategories();
});

</script>

<template>
    <Layout>
        <Head :title="$t('category.title')"/>
        <PageHeader :title="$t('category.title')" pageTitle="">
            <BButton variant="primary" class="btn-sm" @click="showCreateModal = true">
                <i class="ri-add-box-fill me-1"></i> {{ $t('global.create') }}
            </BButton>
        </PageHeader>
        <BRow>
            <BCard no-body class="card-body">
                <BCardBody>
                    <BTable
                        :items="visibleCategories"
                        :fields="fields"
                        striped
                        hover
                        responsive
                        small
                    >
                        <template #cell(name)="data">
                            <div class="d-flex align-items-center">
                                <button 
                                    v-if="data.item._hasChildren"
                                    class="btn btn-link p-0 me-2"
                                    @click="toggleCategory(data.item.id)"
                                >
                                    <i :class="[
                                        'ri-add-line',
                                        {'ri-subtract-line': isExpanded(data.item.id)}
                                    ]"></i>
                                </button>
                                <span 
                                    :class="{'ps-4': data.item._isChild}"
                                >
                                    {{ data.item.name }}
                                </span>
                            </div>
                        </template>

                        <template #cell(product_count)="data">
                            <span class="badge bg-soft-info text-info">
                                {{ data.item.product_count }}
                            </span>
                        </template>
                        
                        <template #cell(actions)="data">
                            <BButton
                                variant="soft-primary"
                                size="sm"
                                class="me-1"
                                @click="handleEdit(data.item)"
                            >
                                <i class="ri-pencil-fill"></i>
                            </BButton>
                            <BButton
                                variant="soft-danger"
                                size="sm"
                                @click="handleDelete(data.item)"
                            >
                                <i class="ri-delete-bin-fill"></i>
                            </BButton>
                        </template>
                    </BTable>
                </BCardBody>
            </BCard>
        </BRow>
    </Layout>
</template>

<style>
.btn-link {
    text-decoration: none;
    color: inherit;
}
.btn-link:hover {
    color: inherit;
}
.bg-soft-info {
    background-color: rgba(41, 156, 219, 0.1) !important;
}
</style>
