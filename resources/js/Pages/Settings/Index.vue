<template>
    <Layout>
        <Head :title="$t('settings.title')" />

        <BCard>
            <BCardBody>
                <form @submit.prevent="updateSettings">
                    <div v-for="(group, groupKey) in groups" :key="groupKey" class="mb-5">
                        <h3 class="mb-4">{{ group.title }}</h3>

                        <div class="row g-3">
                            <div v-for="(setting, key) in group.settings" :key="key" class="col-md-6">
                                <div class="form-group">
                                    <label :for="key" class="form-label">{{ setting.title }}</label>

                                    <!-- Text Input -->
                                    <BFormInput
                                        v-if="setting.type === 'text' || setting.type === 'email' || setting.type === 'number'"
                                        :id="key"
                                        v-model="form[key]"
                                        :type="setting.type"
                                        :placeholder="setting.title"
                                    />

                                    <!-- Textarea -->
                                    <BFormTextarea
                                        v-else-if="setting.type === 'textarea'"
                                        :id="key"
                                        v-model="form[key]"
                                        :placeholder="setting.title"
                                        rows="3"
                                    />

                                    <!-- Select -->
                                    <BFormSelect
                                        v-else-if="setting.type === 'select'"
                                        :id="key"
                                        v-model="form[key]"
                                        :options="Object.entries(setting.options).map(([value, text]) => ({ value, text }))"
                                    />

                                    <!-- Boolean -->
                                    <BFormCheckbox
                                        v-else-if="setting.type === 'boolean'"
                                        :id="key"
                                        v-model="form[key]"
                                        :value="true"
                                        :unchecked-value="false"
                                    >
                                        {{ setting.title }}
                                    </BFormCheckbox>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <BButton
                            type="submit"
                            variant="primary"
                            :disabled="processing"
                        >
                            <BSpinner v-if="processing" small />
                            {{ processing ? t('settings.saving') : t('settings.save') }}
                        </BButton>
                    </div>
                </form>
            </BCardBody>
        </BCard>
    </Layout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import Layout from '@/Layouts/Main.vue'
import {
    BCard,
    BCardBody,
    BButton,
    BSpinner,
    BFormInput,
    BFormTextarea,
    BFormSelect,
    BFormCheckbox,
} from 'bootstrap-vue-next'
import Swal from 'sweetalert2'

const { t } = useI18n()

const props = defineProps({
    groups: {
        type: Object,
        required: true
    },
    settings: {
        type: Object,
        required: true
    }
})

const processing = ref(false)
const form = ref({ ...props.settings })

const updateSettings = async () => {
    processing.value = true

    try {
        await router.post(route('settings.update'), {
            settings: form.value
        }, {
            onSuccess: () => {
                Swal.fire({
                    title: 'Başarılı!',
                    text: t('settings.saved'),
                    icon: 'success',
                    confirmButtonText: 'Tamam'
                })
            },
            onError: (errors) => {
                Swal.fire({
                    title: 'Hata!',
                    text: 'Ayarlar kaydedilirken bir hata oluştu.',
                    icon: 'error',
                    confirmButtonText: 'Tamam'
                })
            }
        })
    } finally {
        processing.value = false
    }
}
</script> 