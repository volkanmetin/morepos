import { useI18n } from 'vue-i18n'
import { usePage } from '@inertiajs/vue3'

const state = {
    authenticated: false,
    userInfo: null,
    loading: false,
    error: null,
    locale: null,
}

const mutations = {
    setAuthenticated(state, authenticated) {
        state.authenticated = authenticated
    },
    setUserInfo(state, userInfo) {
        state.userInfo = userInfo
    },
    setLoading(state, loading) {
        state.loading = loading
    },
    setError(state, error) {
        state.error = error
    },
    SET_LOCALE(state, locale) {
        state.locale = locale
    },
}

const actions = {
    async login({ commit }, form) {
        commit('setLoading', true)
        commit('setError', null)
        try {
            const response = await form.post(route('login'), {
                //onFinish: () => form.reset('password'),
                headers: {
                    'Accept-Language': sessionStorage.getItem("locale") || 'tr',
                },
            })
            //commit('setUserInfo', response.data)
            commit('setAuthenticated', true);
        } catch (error) {
            console.log(error)
            if (error.response.status && error.response.status === 401) {
                commit('setError', 'Invalid credentials.')
            } else {
                commit('setError', 'An error occurred while logging in1.')
            }
        } finally {
            commit('setLoading', false)
        }
    },
    setLocale({ commit }) {
        const { locale } = usePage().props
        const i18n = useI18n()
        i18n.locale.value = locale
        commit('SET_LOCALE', locale)
    }
}

const getters = {
    authenticated: (state) => state.authenticated,
    userInfo: (state) => state.userInfo,
    loading: (state) => state.loading,
    error: (state) => state.error,
    locale: (state) => state.locale,
}

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters,
}
