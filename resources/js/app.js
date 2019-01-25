import 'babel-polyfill';

import Vue from 'vue';
import Vuex from 'vuex';
import store from './store';
import VueClipboard from 'vue-clipboard2';

Vue.use(VueClipboard);
Vue.use(Vuex);

Vue.component('terminal', require('./terminal/Terminal'));
Vue.component('file-editor', require('./editor/FileEditor'));
Vue.component('modal', require('./ui/Modal'));
Vue.component('dark-mode-toggle', require('./ui/DarkModeToggle'));
Vue.component('share-button', require('./ui/ShareButton'));

new Vue({
    el: '#app',
    store,
});
