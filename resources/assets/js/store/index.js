import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        darkMode: false,
        session: null,
    },

    mutations: {
        toggleDarkMode(state) {
            state.darkMode = ! state.darkMode;
        },

        setSession(state, session) {
            history.replaceState(null, null, session.id);

            state.session = session;
        },
    },

    actions: {
        async fetchSession(context) {
            const sessionId = window.location.pathname.replace(/^\//, '') || '';

            const response = await fetch(`./api/session/${sessionId}`);

            const session = await response.json();

            context.commit('setSession', session);
        },
    },
});
