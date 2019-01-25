import Vue from 'vue';
import Vuex from 'vuex';
import { WebSocketConnection } from '../WebSocketConnection';

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        darkMode: false,
        sessionId: null,
        code: null,
    },

    mutations: {
        toggleDarkMode(state) {
            state.darkMode = ! state.darkMode;
        },

        setSession(state, session) {
            history.replaceState(null, null, session.sessionId);

            state.sessionId = session.sessionId;
            state.code = session.code;
        },
    },

    actions: {
        async startSession() {
            const sessionId = window.location.pathname.replace(/^\//, '') || '';

            WebSocketConnection.send('session-start', sessionId);
        },

        updateSession({ commit }, sessionData) {
            commit('setSession', sessionData);
        },
    },
});
