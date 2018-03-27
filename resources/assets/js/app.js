import 'babel-polyfill';

import Vue from 'vue';
import Vuex from 'vuex';
import store from './store';

Vue.use(Vuex);

Vue.component('terminal', require('./terminal/Terminal'));
Vue.component('file-editor', require('./editor/FileEditor'));
Vue.component('modal', require('./ui/Modal'));
Vue.component('dark-mode-toggle', require('./ui/DarkModeToggle'));

new Vue({
    el: '#app',
    store,
});
