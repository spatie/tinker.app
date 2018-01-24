import 'babel-polyfill';

import Vue from 'vue';

Vue.component('terminal', require('./Components/Terminal'));
Vue.component('file-editor', require('./Components/FileEditor'));

new Vue({
    el: '#app',
});
