import {createI18n} from 'vue-i18n'
import {ref, watch} from 'vue'

import tr from '../../lang/php_tr.json'
import en from '../../lang/php_en.json'

const messages = {
    tr,
    en
}

const storedLocale = sessionStorage.getItem("locale");
const currentLocale = ref(storedLocale || import.meta.env.VITE_APP_LOCALE || "tr");

const i18n = createI18n({
    legacy: false, // Vue 3 Composition API için
    globalInjection: true, // global olarak $t kullanımı için
    locale: currentLocale.value,
    fallbackLocale: import.meta.env.VITE_APP_FALLBACK_LOCALE || "tr",
    messages: messages
});

// Dil değişikliğini izle ve uygula
watch(currentLocale, (newLocale) => {
    i18n.global.locale.value = newLocale;
    sessionStorage.setItem("locale", newLocale);

    
    axios.post(route('setLocale'), {locale: newLocale});

    // eğer sayfada datatable varsa table yenile id = data-table

    //const dataTables = document.querySelectorAll('#data-table');
    //dataTables.forEach(table => {
    //    table.DataTable().destroy();
    //    table.DataTable();
    //});
});

// Dil değiştirme fonksiyonu
export function changeLanguage(lang) {
    currentLocale.value = lang;
}

export { currentLocale };
export default i18n;
