<script setup>
import { ref, watch, computed } from 'vue';
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
    itemData: {
        type: Object,
        default: () => ({})
    },
    isLoading: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue', 'save']);

const { t } = useI18n();

const editForm = ref({});
const editErrors = ref({});
const relationForms = ref({});

const editableColumns = computed(() => {
    return props.columns
        .filter(column => column.editable)
        .map(column => ({
            ...column,
            key: column.key || column.data // Eğer key yoksa data'yı kullan
        }));
});

// draggable özelliğini kontrol etmek için yeni bir hesaplanmış özellik
const draggableColumns = computed(() => {
    return editableColumns.value.filter(column => column.hasRelation && column.draggable);
});

watch(() => props.itemData, (newItemData) => {
    editForm.value = JSON.parse(JSON.stringify(newItemData));
    editErrors.value = [];
    
    // İlişkili alanlar için form oluştur
    editableColumns.value.forEach(column => {
        if (column.hasRelation) {
            relationForms.value[column.key] = (newItemData[column.key] || []).map((item, index) => {
                const newItem = { sort: index + 1 };
                if (column.relationColumns) {
                    column.relationColumns.forEach(relColumn => {
                        newItem[relColumn.key] = item[relColumn.key] || '';
                    });
                } else {
                    newItem.value = item.value || '';
                }
                return newItem;
            });
        }
    });
}, { immediate: true, deep: true });

const updateSort = (columnKey) => {
    if (relationForms.value[columnKey]) {
        relationForms.value[columnKey].forEach((item, index) => {
            item.sort = index + 1;
        });
    }
};

const closeEditModal = () => {
    emit('update:modelValue', false);
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

const submitEdit = () => {
    editErrors.value = [];
    // Form doğrulama
    editableColumns.value.forEach(column => {
        if (column.required && !editForm.value[column.key]) {
            editErrors.value.push(`${column.title} alanı zorunludur.`);
        }
        
        // İlişkili alanlar için doğrulama
        if (column.hasRelation && column.required) {
            const relationData = relationForms.value[column.key];
            if (!relationData || relationData.length === 0) {
                editErrors.value.push(`${column.title} için en az bir değer girilmelidir.`);
            } else {
                relationData.forEach((item, index) => {
                    if (column.relationColumns) {
                        column.relationColumns.forEach(relColumn => {
                            if (relColumn.required && !item[relColumn.key]) {
                                editErrors.value.push(`${column.title} #${index + 1} için ${relColumn.title} zorunludur.`);
                            }
                        });
                    } else if (!item.value) {
                        editErrors.value.push(`${column.title} #${index + 1} için değer zorunludur.`);
                    }
                });
            }
        }
    });

    if (editErrors.value.length === 0) {
        const formData = { 
            ...editForm.value, 
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
        emit('save', formData);
    }
};

const getInputType = (column) => {
    if (column.inputType === 'select' && column.options) {
        return 'select';
    }
    return column.inputType || 'text';
};

const moveRelation = (columnKey, fromIndex, toIndex) => {
    const item = relationForms.value[columnKey].splice(fromIndex, 1)[0];
    relationForms.value[columnKey].splice(toIndex, 0, item);
};

// Her ilişkili alan için sürükle-bırak özelliğini etkinleştir
// initSortable fonksiyonunu kaldırıyoruz çünkü artık vuedraggable kullanacağız

const getRelationInputType = (relColumn) => {
    if (relColumn.type === 'select' && relColumn.options) {
        return 'select';
    }
    return relColumn.type || 'text';
};

</script>

<template>
    <BModal :model-value="modelValue" @update:model-value="(val) => emit('update:modelValue', val)"
            :title="t('global.edit')" hide-footer header-class="p-3 bg-light" class="v-modal-custom">
        <template v-if="isLoading">
            <div class="text-center">
                <BSpinner label="Loading..."></BSpinner>
            </div>
        </template>
        <BForm v-else @submit.prevent="submitEdit" class="tablelist-form" autocomplete="off">
            <div class="mb-3" v-for="column in editableColumns.filter(c => !c.hasRelation)" :key="column.key">
                <label class="form-label" :for="column.key">{{ column.title }}:</label>
                <BFormSelect
                    v-if="getInputType(column) === 'select'"
                    :id="column.key"
                    v-model="editForm[column.key]"
                    :options="column.options"
                    :required="column.required"
                    :state="!column.required || editForm[column.key] ? null : false"
                />
                <BFormInput
                    v-else
                    :id="column.key"
                    v-model="editForm[column.key]"
                    :type="getInputType(column)"
                    :required="column.required"
                    :state="!column.required || editForm[column.key] ? null : false"
                />
                <BFormInvalidFeedback :state="!column.required || editForm[column.key] ? null : false">
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
                                            :required="relColumn.required"
                                            :state="!relColumn.required || element[relColumn.key] ? null : false"
                                            class="me-2"
                                        />
                                        <BFormInput
                                            v-else
                                            v-model="element[relColumn.key]"
                                            :type="getRelationInputType(relColumn)"
                                            :placeholder="relColumn.title"
                                            :required="relColumn.required"
                                            :state="!relColumn.required || element[relColumn.key] ? null : false"
                                            class="me-2"
                                        />
                                        <BFormInvalidFeedback :state="!relColumn.required || element[relColumn.key] ? null : false">
                                            {{ t('global.field-required') }}
                                        </BFormInvalidFeedback>
                                    </div>
                                </template>
                                <BFormInput
                                    v-else
                                    v-model="element.value"
                                    :placeholder="t('global.enter-value')"
                                    :required="column.required"
                                    :state="!column.required || element.value ? null : false"
                                    class="me-2"
                                />
                                <BFormInvalidFeedback :state="!column.required || element.value ? null : false">
                                    {{ t('global.field-required') }}
                                </BFormInvalidFeedback>
                                <BButton @click="removeRelation(column.key, index)" variant="danger" size="sm">
                                    {{ t('global.delete') }}
                                </BButton>
                            </div>
                        </div>
                    </template>
                </draggable>
                <BButton @click="addRelation(column.key)" variant="primary" size="sm" class="mt-2">
                    {{ t('global.add-item') }}
                </BButton>
            </div>

            <div v-if="editErrors.length" class="error-messages mb-3">
                <BAlert show variant="danger">
                    <p v-for="error in editErrors" :key="error" class="mb-0">{{ error }}</p>
                </BAlert>
            </div>

            <div class="hstack gap-2 justify-content-end">
                <BButton type="submit" id="editSaveBtn" variant="success" :disabled="isLoading">{{ t('global.save') }}</BButton>
                <BButton type="button" id="closemodal" variant="light" @click="closeEditModal" :disabled="isLoading">{{ t('global.cancel') }}</BButton>
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
