// Init Vue
var Vue = require('vue');
Vue.use(require('vue-resource'));
Vue.config.debug = true;

Vue.http.headers.common['X-CSRF-TOKEN'] = document.querySelector("meta[name='csrf-token']").getAttribute('content');

// Components
let components = {
    'document-switcher': require('./components/gui/document-switcher.vue')
};

// Root Vue instance
new Vue({
    el: 'body',
    components: components
});