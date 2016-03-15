<template>
    <div id="xpp-document" class="row">
        <document v-if="! simpleEditor"
                  :ode.sync="ode"
                  :log.sync="log"
                  :output.sync="output"
                  :title.sync="title"
                  :id.sync="id"
        >
        </document>

        <plain-document v-if="simpleEditor"
                        :ode.sync="ode"
                        :log.sync="log"
                        :output.sync="output"
                        :title.sync="title"
                        :id.sync="id"
        >
        </plain-document>
    </div>
</template>

<script>
    export default {
        props: ['ode', 'log', 'output', 'title', 'id'],

        ready() {
            // Convert from stupid localStorage format
            this.simpleEditor = localStorage.getItem('simpleEditor') === 'true';
        },

        data() {
            return {
                simpleEditor: null
            }
        },

        watch: {
            simpleEditor: function (newValue) {
                localStorage.setItem('simpleEditor', newValue);
            }
        },

        components: {
            'document': require('./../xpp-document/document.vue'),
            'plain-document': require('./../xpp-document/plain.vue'),
        }
    }
</script>
