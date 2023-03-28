/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
require('./sb-admin');
// window.Vue = require('vue');
import Vue from 'vue';

Vue.component('pagination', require('laravel-vue-pagination'));
Vue.component('create-product', require('./components/CreateProduct.vue').default);
Vue.component('display-product', require('./components/DisplayProduct.vue').default);
Vue.component('edit-product', require('./components/EditProduct.vue').default);



/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
});
