<script setup>
import { trans } from 'laravel-vue-i18n';
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import draggable from 'vuedraggable';

const props = defineProps({
    columns: {
        type: Array,
        required: true
    },
    modelValue: {
        type: Boolean,
        required: true
    },
    isLoading: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue', 'create']);

const { t } = useI18n();

const createForm = ref({});
const createErrors = ref({});
const relationForms = ref({});
const touchedFields = ref({}); // Yeni: Dokunulan alanları takip etmek için

const editableColumns = computed(() => {
    return props.columns
        .filter(column => column.editable)
        .map(column => ({
            ...column,
            key: column.key || column.data
        }));
});

const draggableColumns = computed(() => {
    return editableColumns.value.filter(column => column.hasRelation && column.draggable);
});

const initForm = () => {
    createForm.value = {};
    createErrors.value = [];
    relationForms.value = {};
    touchedFields.value = {}; // Yeni: Dokunulan alanları sıfırla
    
    editableColumns.value.forEach(column => {
        if (column.hasRelation) {
            relationForms.value[column.key] = [];
        } else {
            createForm.value[column.key] = '';
        }
    });
};

const updateSort = (columnKey) => {
    if (relationForms.value[columnKey]) {
        relationForms.value[columnKey].forEach((item, index) => {
            item.sort = index + 1;
        });
    }
};

const closeCreateModal = () => {
    emit('update:modelValue', false);
    initForm();
};

const addRelation = (columnKey) => {
    const column = editableColumns.value.find(col => col.key === columnKey);
    const newSort = relationForms.value[columnKey].length + 1;
    const newItem = { sort: newSort };
    if (column.relationColumns) {
        column.relationColumns.forEach(relColumn => {
            newItem[relColumn.key] = '';
        });
    } else {
        newItem.value = '';
    }
    relationForms.value[columnKey].push(newItem);
};

const removeRelation = (columnKey, index) => {
    relationForms.value[columnKey].splice(index, 1);
};

const submitCreate = () => {
    createErrors.value = [];
    // Form doğrulama
    editableColumns.value.forEach(column => {
        if (column.required && !createForm.value[column.key]) {
            createErrors.value.push(`${column.label} alanı boş bırakılamaz.`);
        }
        touchedFields.value[column.key] = true; // Yeni: Tüm alanları dokunulmuş olarak işaretle
    });

    if (createErrors.value.length === 0) {
        const formData = { 
            ...createForm.value, 
            ...Object.fromEntries(
                Object.entries(relationForms.value).map(([key, value]) => [
                    key, 
                    value.map((item, index) => ({ 
                        ...item, 
                        value: item.value, 
                        sort: item.sort || index + 1 
                    }))
                ])
            )
        };
        emit('create', formData);
    }
};

const getInputType = (column) => {
    if (column.inputType === 'select' && column.options) {
        return 'select';
    }
    return column.inputType || 'text';
};

const getRelationInputType = (relColumn) => {
    if (relColumn.type === 'select' && relColumn.options) {
        return 'select';
    }
    return relColumn.type || 'text';
};

const handleInputChange = (columnKey) => {
    touchedFields.value[columnKey] = true;
};

initForm();

</script>

<template>
    <BModal :model-value="modelValue" @update:model-value="(val) => emit('update:modelValue', val)"
            :title="t('global.create')" hide-footer header-class="p-3 bg-light" class="v-modal-custom">
        <template v-if="isLoading">
            <div class="text-center">
                <BSpinner label="Loading..."></BSpinner>
            </div>
        </template>
        <BForm v-else @submit.prevent="submitCreate" class="tablelist-form" autocomplete="off">
            <div class="mb-3" v-for="column in editableColumns.filter(c => !c.hasRelation)" :key="column.key">
                <label class="form-label" :for="column.key">{{ column.title }}:</label>
                <BFormSelect
                    v-if="getInputType(column) === 'select'"
                    :id="column.key"
                    v-model="createForm[column.key]"
                    :options="column.options"
                    :required="column.required"
                    :state="column.required ? (!touchedFields[column.key] || createForm[column.key] ? null : false) : null"
                    @change="handleInputChange(column.key)"
                />
                <BFormInput
                    v-else
                    :id="column.key"
                    v-model="createForm[column.key]"
                    :type="getInputType(column)"
                    :required="column.required"
                    :state="column.required ? (!touchedFields[column.key] || createForm[column.key] ? null : false) : null"
                    @input="handleInputChange(column.key)"
                />
                <BFormInvalidFeedback v-if="column.required" :state="!touchedFields[column.key] || createForm[column.key] ? null : false">
                    {{ t('global.field-required') }}
                </BFormInvalidFeedback>
            </div>

            <div v-for="column in editableColumns.filter(c => c.hasRelation)" :key="`relation-${column.key}`" class="mb-3">
                <label class="form-label">{{ column.title }}:</label>
                <draggable 
                    v-model="relationForms[column.key]" 
                    item-key="id"
                    handle=".drag-handle"
                    @end="updateSort(column.key)"
                    :disabled="!column.draggable"
                >
                    <template #item="{ element, index }">
                        <div class="mb-2">
                            <div class="d-flex align-items-center">
                                <div v-if="column.draggable" class="drag-handle me-2">
                                    <i class="fas fa-grip-vertical text-muted"></i>
                                </div>
                                <template v-if="column.relationColumns">
                                    <div v-for="relColumn in column.relationColumns" :key="relColumn.key" class="me-2">
                                        <BFormSelect
                                            v-if="getRelationInputType(relColumn) === 'select'"
                                            v-model="element[relColumn.key]"
                                            :options="relColumn.options"
                                            :placeholder="relColumn.title"
                                            class="me-2"
                                        />
                                        <BFormInput
                                            v-else
                                            v-model="element[relColumn.key]"
                                            :type="getRelationInputType(relColumn)"
                                            :placeholder="relColumn.title"
                                            class="me-2"
                                        />
                                    </div>
                                </template>
                                <BFormInput
                                    v-else
                                    v-model="element.value"
                                    :placeholder="t('global.enter-value')"
                                    class="me-2"
                                />
                                <BButton @click="removeRelation(column.key, index)" variant="danger" size="sm">
                                    {{ t('global.remove') }}
                                </BButton>
                            </div>
                        </div>
                    </template>
                </draggable>
                <BButton @click="addRelation(column.key)" variant="primary" size="sm" class="mt-2">
                    {{ t('global.add-item') }}
                </BButton>
            </div>

            <div v-if="createErrors.length" class="error-messages mb-3">
                <BAlert show variant="danger">
                    <p v-for="error in createErrors" :key="error" class="mb-0">{{ error }}</p>
                </BAlert>
            </div>

            <div class="hstack gap-2 justify-content-end">
                <BButton type="submit" id="createSaveBtn" variant="success" :disabled="isLoading">{{ t('global.save') }}</BButton>
                <BButton type="button" id="closemodal" variant="light" @click="closeCreateModal" :disabled="isLoading">{{ t('global.cancel') }}</BButton>
            </div>
        </BForm>
    </BModal>
</template>

<style scoped>
.cursor-move {
    cursor: move;
}
.sortable-ghost {
    opacity: 0.5;
    background: #c8ebfb;
}
.handle {
    cursor: move;
}
.drag-handle {
    cursor: move;
    padding: 5px;
    background-color: #f8f9fa;
    border-radius: 4px;
}
.drag-handle:hover {
    background-color: #e9ecef;
}
</style>
