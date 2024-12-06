<script setup>
import {Head, usePage, Link} from '@inertiajs/vue3';
import Layout from "@/Layouts/Main.vue";
import PageHeader from "@/Components/page-header.vue";
import {onMounted, ref} from "vue";
import { createDataTableConfig } from '@/utils/datatableUtils';
import { handleAxiosError } from '@/utils/errorHandler';
import EditModal from "@/Components/EditModal.vue";
import {BButton, BModal, BImg} from "bootstrap-vue-next";
import CreateModal from "@/Components/CreateModal.vue";

const page = usePage();
const columns = ref(page.props.columns);

const dtTable = ref(null);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const currentEditItem = ref(null);
const isLoading = ref(false);

const showImageModal = ref(false);
const currentImage = ref('');

const handleEdit = async (id) => {
    isLoading.value = true;
    try {
        const response = await axios.get(route('product.show', { id: id }));
        currentEditItem.value = response.data;
        showEditModal.value = true;
    } catch (error) {
        handleAxiosError(error, "Error fetching item data");
    } finally {
        isLoading.value = false;
    }
};

const handleUpdate = async (updatedData) => {
    isLoading.value = true;
    try {
        await axios.put(route('product.update', { id: updatedData.id }), updatedData)
        dtTable.value.ajax.reload();
        showEditModal.value = false;
    } catch (error) {
        handleAxiosError(error, "Error updating item");
    } finally {
        isLoading.value = false;
    }
};

const handleCreate = async (createdData) => {
    isLoading.value = true;
    try {
        await axios.post(route('product.store'), createdData)
        dtTable.value.ajax.reload();
        showCreateModal.value = false;
    } catch (error) {
        handleAxiosError(error, "Error creating item");
    } finally {
        isLoading.value = false;
    }
};

const handleDelete = async (id) => {
    isLoading.value = true;
    try {
        await axios.delete(route('product.destroy', { id: id }))
        dtTable.value.ajax.reload();
    } catch (error) {
        handleAxiosError(error, "Error deleting item");
    } finally {
        isLoading.value = false;
    }
}

const handleImageClick = (imageUrl) => {
    currentImage.value = imageUrl;
    showImageModal.value = true;
};

const initDataTable = () => {
    const config = createDataTableConfig({
        dataUrl: route('product.data'),
        columns: columns,
        onEdit: handleEdit,
        onDelete: handleDelete,
        onDeleteConfirmation: true,
    });

    // DataTable'ı başlattıktan sonra özel olay dinleyicisi ekleyin
    dtTable.value = $('#data-table').DataTable(config);
    
    $('#data-table').on('click', '.show-image-modal', function(e) {
        e.preventDefault();
        const imageUrl = $(this).attr('src');
        handleImageClick(imageUrl);
    });
};

onMounted(() => {
    initDataTable();
});

</script>

<template>
    <Layout>
        <Head :title="$t('product.title')"/>
        <PageHeader :title="$t('product.title')" pageTitle="">
            <Link :href="route('product.create')" class="btn btn-primary btn-sm">
                <i class="ri-add-box-fill me-1"></i> {{ $t('global.create') }}
            </Link>
        </PageHeader>
        <BRow>
            <BCard no-body class="card-body">
                <BCardBody class="p-0">

                    <table id="data-table" class="table table-hover">
                        <thead>
                        <tr>
                            <th v-for="column in columns" :key="column.data" :class="column.class">{{ column.title }}</th>
                        </tr>
                        </thead>
                    </table>

                    <CreateModal
                        v-model="showCreateModal"
                        :columns="columns"
                        :isLoading="isLoading"
                        @create="handleCreate"
                    />

                    <EditModal
                        v-model="showEditModal"
                        :columns="columns"
                        :itemData="currentEditItem || {}"
                        :isLoading="isLoading"
                        @save="handleUpdate"
                    />

                </BCardBody>
            </BCard>
        </BRow>

        <BModal v-model="showImageModal" title="Resim Görüntüleyici" size="lg" centered hide-footer="true">
            <BImg :src="currentImage" fluid alt="Ürün Resmi" />

            <div class="d-flex justify-content-end mt-2">
                <BButton variant="secondary" @click="showImageModal = false">Kapat</BButton>
            </div>
        </BModal>

    </Layout>
</template>

<style>
/* Resim tıklanabilir olduğunu göstermek için stil ekleyin */
.show-image-modal {
    cursor: pointer;
}
</style>
