import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        darkMode: false,
        sessionId: null,
    },

    mutations: {
        toggleDarkMode(state) {
            state.darkMode = ! state.darkMode;
        },

        setSessionId(state, sessionId) {
            history.replaceState(null, null, sessionId);

            state.sessionId = sessionId;
        },
    },

    actions: {
        async getSessionId(context) {
            const sessionId = window.location.pathname.replace(/^\//, '') || null;

            if (sessionId) {
                context.commit('setSessionId', sessionId);

                return sessionId;
            }

            const response = await fetch('./api/session');

            const session = await response.json();

            context.commit('setSessionId', session.sessionId);

            return sessionId;
        },
    },
});
