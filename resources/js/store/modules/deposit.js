const state = {
    loading: false,
    error: null,
}

const mutations = {
    setLoading(state, loading) {
        state.loading = loading
    },
    setError(state, error) {
        state.error = error
    },
}

const actions = {
    async fetchCities({ commit }) {
        commit('setLoading', true)
        commit('setError', null)
        try {
            const response = await axios.get('https://example.com/api/cities')
            commit('setCities', response.data)
        } catch (error) {
            commit('setError', 'An error occurred while fetching cities.')
        } finally {
            commit('setLoading', false)
        }
    },
}

const getters = {
    loading: (state) => state.loading,
    error: (state) => state.error,
}

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters,
}
