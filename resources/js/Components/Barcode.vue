<template>
    <svg :id="svgId" :width="width" :height="height" class="barcode-svg"></svg>
</template>

<script setup>
import { onMounted, watch } from 'vue';
import JsBarcode from 'jsbarcode';

const props = defineProps({
    value: {
        type: String,
        required: true,
        default: ''
    },
    width: {
        type: Number,
        default: 150
    },
    height: {
        type: Number,
        default: 40
    },
    format: {
        type: String,
        default: 'CODE128'
    },
    displayValue: {
        type: Boolean,
        default: false
    }
});

// Benzersiz bir ID oluştur
const svgId = 'barcode-' + Math.random().toString(36).substr(2, 9);

const generateBarcode = () => {
    if (!props.value) return;
    
    try {
        JsBarcode(`#${svgId}`, props.value, {
            format: props.format,
            width: 2,
            height: props.height,
            displayValue: props.displayValue,
            margin: 5,
            background: '#ffffff',
            lineColor: '#000000',
            fontSize: 12,
            valid: (valid) => {
                if (!valid) {
                    console.warn('Geçersiz barkod değeri:', props.value);
                }
            }
        });
    } catch (error) {
        console.error('Barkod oluşturma hatası:', error);
    }
};

onMounted(() => {
    generateBarcode();
});

// value prop'u değiştiğinde barkodu yeniden oluştur
watch(() => props.value, () => {
    generateBarcode();
});
</script>

<style scoped>
.barcode-svg {
    display: inline-block;
}
</style> 