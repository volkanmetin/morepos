<script setup>
import {layoutComputed} from "@/store/helpers";
import {computed} from "vue";
import { changeLanguage, currentLocale } from "@/i18n.js";

computed(() => layoutComputed);

</script>

<template>
    <div class="auth-page-wrapper pt-5">
        <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
            <div class="bg-overlay"></div>

            <div class="shape">
                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink"
                     viewBox="0 0 1440 120">
                    <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
                </svg>
            </div>
        </div>
        <slot/>
        <footer class="footer">
            <BContainer>
                <BRow>
                    <BCol lg="12">
                        <div class="text-center">
                            <p class="mb-0 text-muted">&copy; {{ new Date().getFullYear() }} MorePOS. All rights
                                reserved &middot;
                                <a href="" v-show="currentLocale !== 'tr'" @click.prevent="changeLanguage('tr')">Türkçe</a>
                                <a href="" v-show="currentLocale !== 'en'" @click.prevent="changeLanguage('en')">English</a>
                            </p>
                        </div>
                    </BCol>
                </BRow>
            </BContainer>
        </footer>
    </div>
</template>
