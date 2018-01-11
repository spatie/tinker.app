import 'babel-polyfill';

import Vue from 'vue';

Vue.component('terminal', require('./Components/Terminal'));

new Vue({
    el: '#app',
});
