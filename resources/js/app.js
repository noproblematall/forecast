/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');
import axios from 'axios';
import Routes from './routes.js';
import VueAxios from 'vue-axios';
require('es6-promise').polyfill();
import App from './components/App.vue';
Vue.use(VueAxios, axios);


export const eventBus = new Vue({
    methods:{
        detailData(){
            this.$emit('initPage');
        },
        viewAllData(){
            this.$emit('viewAllData')
        }
    }
});


const app = new Vue({
    el: '#app',
    router: Routes,
    render: h => h(App),
});
