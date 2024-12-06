import { createStore } from 'vuex'
import layout from './modules/layout'
import auth from './modules/auth'
import deposit from './modules/deposit'

export default createStore({
    modules: {
        layout,
        auth,
        deposit,
    }
})
