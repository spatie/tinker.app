import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        darkMode: false,
    },
    mutations: {
        toggleDarkMode(state) {
            state.darkMode = ! state.darkMode;
        },
    },
});
