<template>
    <div class="table-responsive table-card">
        <table class="table align-middle table-nowrap">
            <thead class="table-light text-muted">
            <tr>
                <th v-for="column in columns" :key="column.key" :class="column.class">{{ column.label }}</th>
                <th v-if="showActions" v-text="$t('t-actions')"></th>
            </tr>
            </thead>
            <tbody class="list form-check-all">
            <tr v-for="item in items" :key="item[itemKey]">
                <td v-for="column in columns" :key="column.key">
                    {{ item[column.key] }}
                </td>
                <td v-if="showActions">
                    <ul class="list-inline hstack gap-2 mb-0">
                        <li class="list-inline-item edit" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" :title="$t('t-edit')">
                            <BLink variant="primary" class="d-inline-block edit-item-btn" @click="openEditModal(item)">
                                <i class="ri-pencil-fill fs-16"></i>
                            </BLink>
                        </li>
                        <li class="list-inline-item" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" :title="$t('t-delete')">
                            <BLink class="text-danger d-inline-block remove-item-btn" @click="openDeleteModal(item)">
                                <i class="ri-delete-bin-5-fill fs-16"></i>
                            </BLink>
                        </li>
                    </ul>
                </td>
            </tr>
            </tbody>
        </table>


        <BModal v-model="showDeleteModal" :title="$t('t-confirmation-title')" hide-footer header-class="bg-warning p-3">
            <p class="text-muted">Kaydı silmek istediğinize emin misiniz? Bu işlem geri alınamaz.</p>
            <div class="mb-3">

            </div>

            <div class="text-end">
                <BButton variant="danger" class="me-1"
                         id="deleteConfirmBtn"
                         :disabled="isDeleting"
                         :class="{ 'deleting': isDeleting }"
                         @click="confirmDelete">Confirm
                </BButton>
                <BButton variant="secondary" @click="closeDeleteModal">Cancel</BButton>
            </div>
        </BModal>


        <BModal v-model="showEditModal" :title="$t('t-edit')" hide-footer header-class="p-3 bg-light"
                class="v-modal-custom">
            <BForm @submit.prevent="submitEdit" class="tablelist-form" autocomplete="off">

                <div class="mb-3" v-for="column in editableColumns" :key="column.key">
                    <label class="form-label" :for="column.key">{{ column.label }}:</label>
                    <input
                        :id="column.key"
                        class="form-control"
                        :class="{'is-invalid': !editForm[column.key]}"
                        v-model="editForm[column.key]"
                        :type="column.type || 'text'"
                        :required="column.required"
                    >
                    <div class="invalid-feedback">Boş bırakamazsınız</div>
                </div>

                <div v-if="editErrors.length" class="error-messages">
                    <p v-for="error in editErrors" :key="error">{{ error }}</p>
                </div>

                <div class="hstack gap-2 justify-content-end">
                    <BButton type="submit" id="editSaveBtn" variant="success" v-text="$t('t-save')"></BButton>
                    <BButton type="button" id="closemodal" variant="light" @click="closeEditModal"
                             v-text="$t('t-cancel')"></BButton>
                </div>
            </BForm>
        </BModal>

    </div>

</template>

<script setup>
import {ref, reactive, computed} from 'vue'
import {Link} from "@inertiajs/vue3";
import {BButton} from "bootstrap-vue-next";

const props = defineProps({
    items: {
        type: Array,
        required: true
    },
    columns: {
        type: Array,
        required: true
    },
    itemKey: {
        type: String,
        default: 'id'
    },
    canEdit: {
        type: Boolean,
        default: true
    },
    canDelete: {
        type: Boolean,
        default: true
    }
})

const emit = defineEmits(['update:items', 'edit', 'delete'])

const showDeleteModal = ref(false)
const showEditModal = ref(false)
const itemToDelete = ref(null)
const isDeleting = ref(false)
const itemToEdit = ref(null)
const editForm = reactive({})
const editErrors = ref([])

const showActions = computed(() => props.canEdit || props.canDelete)

const editableColumns = computed(() =>
    props.columns.filter(column => column.editable !== false)
)

const openDeleteModal = (item) => {
    itemToDelete.value = item
    showDeleteModal.value = true

    setTimeout(() => document.getElementById('deleteConfirmBtn').focus(), 250);
}

const closeDeleteModal = () => {
    showDeleteModal.value = false
    itemToDelete.value = null
}

const confirmDelete = () => {
    if (!itemToDelete.value) {
        return;
    }

    try {
        const updatedItems = props.items.filter(item => item[props.itemKey] !== itemToDelete.value[props.itemKey])
        emit('delete', [itemToDelete.value, updatedItems])
        //emit('update:items', updatedItems)
    } catch (error) {
        console.error('Delete operation failed:', error)
        // Hata durumunda kullanıcıya bilgi verebilirsiniz
    } finally {
        isDeleting.value = false
        closeDeleteModal()
    }
}

const openEditModal = (item) => {
    itemToEdit.value = item
    editableColumns.value.forEach(column => {
        editForm[column.key] = item[column.key]
    })
    showEditModal.value = true

    // first item of editForm is focused
    setTimeout(() => document.getElementById('editSaveBtn').focus(), 250);
}

const closeEditModal = () => {
    showEditModal.value = false
    itemToEdit.value = null
    Object.keys(editForm).forEach(key => delete editForm[key])
    editErrors.value = []
}

const validateForm = () => {
    editErrors.value = []
    editableColumns.value.forEach(column => {
        if (column.required && !editForm[column.key]) {
            editErrors.value.push(`${column.label} is required`)
        }
        if (column.type === 'email' && !/\S+@\S+\.\S+/.test(editForm[column.key])) {
            editErrors.value.push(`${column.label} is not a valid email`)
        }
    })
    return editErrors.value.length === 0
}

const submitEdit = () => {
    if (validateForm()) {
        const updatedItem = {...itemToEdit.value, ...editForm}
        //emit('edit', updatedItem)
        const updatedItems = props.items.map(item =>
            item[props.itemKey] === updatedItem[props.itemKey] ? updatedItem : item
        )
        emit('edit', [updatedItem, updatedItems])
        //emit('update:items', updatedItems)
        closeEditModal()
    }
}
</script>
