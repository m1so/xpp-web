// Init Vue
var Vue = require('vue');
Vue.use(require('vue-resource'));
Vue.config.debug = true;

Vue.http.headers.common['X-CSRF-TOKEN'] = document.querySelector("meta[name='csrf-token']").getAttribute('content');

// Globals
window.Mousetrap = require('mousetrap');

// Components
let components = {
    'editor': require('./components/editor/Editor.vue'),
    'document': require('./components/Document.vue'),
    'create-document-modal': require('./components/DocumentCreateModal.vue')
};

// Root Vue instance
new Vue({
    el: 'body',
    components: components
});
