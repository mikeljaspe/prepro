require('./bootstrap');

window.Vue = require('vue');

import '@fortawesome/fontawesome-free/css/all.css';
import VueRouter from 'vue-router';
import routes from './routes';
import vuetifyex from './vuetifyex';
import Vuetify from 'vuetify';

Vue.use(VueRouter);
Vue.use(Vuetify);

import App from './components/App';


const app = new Vue({
    el: '#app',
    vuetify: new Vuetify(vuetifyex),
    router: new VueRouter(routes),
    render: h => h(App)
});
