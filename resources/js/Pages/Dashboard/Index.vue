<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { changeLanguage } from "@/i18n.js";
import Layout from "@/Layouts/Main.vue";
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <Layout>
        <Head title="Dashboard" />
        <p>selam</p>

        <p>{{ t("dashboard.title") }}</p>
        
        <a href="" @click.prevent="changeLanguage('tr')">Türkçe</a> &middot; <a href="" @click.prevent="changeLanguage('en')">English</a>


        <hr />

        <p>SA test</p>
    </Layout>
</template>
