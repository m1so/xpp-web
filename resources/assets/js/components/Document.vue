<template>
    <div id="xpp-document-component">
        <div class="col-md-12">
            <!-- Custom Tabs -->
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#tab_editor" data-toggle="tab">Editor</a>
                    </li>
                    <li>
                        <a href="#tab_output" data-toggle="tab">Output</a>
                    </li>
                    <li class="pull-right">
                        <button @click="simpleEditor = !simpleEditor" type="button" class="btn btn-default">
                            {{ simpleEditor ? 'Show' : 'Hide' }} interactive
                        </button>

                        <button @click="postOde" type="button" class="btn btn-success {{ loading ? 'disabled' : null }}">
                            <i class="fa fa-spinner fa-spin" v-show="loading"></i> {{ loading ? 'Running...' : 'Run' }}
                        </button>
                    </li>
                </ul>
                <div class="tab-content">
                    <!-- Editor tab -->
                    <div class="tab-pane active" id="tab_editor">
                        <div v-if="!simpleEditor" class="col-md-8 col-sm-12">
                            <div class="row">
                                <div class="col-md-6">
                                    <!-- Differential equations -->
                                    <input-box title="Differential equations" :data.sync="des"></input-box>
                                </div>
                                <div class="col-md-6">
                                    <!-- Initial conditions -->
                                    <input-box title="Initial conditions" :data.sync="ics"></input-box>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <!-- Parameters -->
                                    <input-box title="Parameters" :data.sync="params"></input-box>
                                </div>
                                <div class="col-md-6">
                                    <!-- Options -->
                                    <input-box title="Options" :data.sync="options"></input-box>
                                </div>
                            </div>
                        </div>

                        <!-- Output part -->
                        <div class="{{ simpleEditor ? 'col-md-12' : 'col-md-4 col-sm-12'}}">
                            <ode-file :ics.sync="ics" :des.sync="des" :params.sync="params" :options.sync="options" :ode.sync="ode" v-ref:ode-file></ode-file>
                        </div>
                    </div>

                    <!-- Output tab -->
                    <div class="tab-pane" id="tab_output">
                        <div class="col-md-4">
                            <h3>Output</h3>
                            <pre v-if="output">{{ output }}</pre>
                        </div>

                        <div class="col-md-4">
                            <h3>Log</h3>
                            <pre v-if="log">{{ log }}</pre>
                        </div>

                        <div class="col-md-4">
                            <h3>Other</h3>
                            <span v-if="status">Return code: {{ status }}</span>
                        </div>
                    </div>
                </div>
                <!-- /.tab-content -->
            </div>
            <!-- nav-tabs-custom -->
        </div>
    </div>

    <alert
            :show.sync="alert.show"
            :duration="3000"
            :type="alert.type"
            width="400px"
            placement="top"
            dismissable
    >
        <span class="icon-ok-circled alert-icon-float-left"></span>
        <strong>{{ alert.title }}</strong>
        <p>{{ alert.message }}</p>
    </alert>
</template>

<script type="text/babel">
    import InputBox from './xpp-document/input-box.vue'
    import OdeFile from './xpp-document/ode-file.vue'

    export default {
        props: ['ode', 'log', 'output', 'data'],

        ready() {
            // Convert from stupid localStorage format
            this.simpleEditor = localStorage.getItem('simpleEditor') === 'true';

            if (! this.simpleEditor) {
                this.parse();
            }
        },

        data() {
            return {
                simpleEditor: null,
                loading: false,

                alert: {
                    show: false,
                    type: 'danger',
                    title: '',
                    message: ''
                },

                status: null,
                lastSent: null,

                ics: [],
                des: [],
                params: [],
                options: []
            }
        },

        methods: {
            postOde() {
                this.lastSent = new Date();
                this.loading = true;

                this.$http.post('/api/xpp', {
                            ode: this.$refs.odeFile.$data.ode,
                            id: this.data.id,
                            withNullAndDir: true,
                            title: this.data.title
                        })
                        .then(function (response) {
                            this.ode = response.data.ode;
                            this.log = response.data.log;
                            this.status = response.data.status;
                            this.output = response.data.output;

                            this.loading = false;
                            this.sendAlert('info', 'Calculations complete', 'XPPAut ran successfully. Check the output tab for more details.')
                        })
                        .catch(function (response) {
                            this.log = null;
                            this.status = null;
                            this.output = null;

                            this.loading = false;

                            console.log('Error posting ode file', response.status);
                        });
            },

            parse() {
                var self = this;

                function extractFromLine(line, prefix, store, description) {
                    if (line.includes(prefix)) {
                        // Remove prefix
                        let paramsString = line.substr(prefix.length);
                        paramsString.split(',').forEach(function (element) {
                            // Get key and value
                            let parts = element.split('=', 2);

                            let row = self[store].find(el => el.key === parts[0].trim());

                            if (row) {
                                row.key = parts[0].trim();
                                row.value = parts[1].trim();
                                row.description = description;
                            } else {
                                self[store].push({
                                    key: parts[0].trim(),
                                    value: parts[1].trim(),
                                    description: description
                                });
                            }
                        })
                    }
                }

                try {
                    // Parse the given ode
                    this.ode.split('\n').forEach(function (wholeLine) {
                        // Get content before comment ('#')
                        let line = wholeLine.split('#')[0];

                        if (line.length) {
                            extractFromLine(line, 'par ', 'params', 'Parameter');

                            extractFromLine(line, 'init ', 'ics', 'Initial condition');

                            extractFromLine(line, '@ ', 'options', 'Option');

                            // Differential equations
                            if (line.includes('/dt ') || line.includes('\'')) {
                                let parts = line.split('=', 2);

                                let row = self.des.find(el => el.key === parts[0].trim());
                                if (row) {
                                    row.key = parts[0].trim();
                                    row.value = parts[1].trim();
                                    row.description = 'Differential equation';
                                } else {
                                    self.des.push({
                                        key: parts[0].trim(),
                                        value: parts[1].trim(),
                                        description: 'Differential equation'
                                    });
                                }
                            }
                        }
                    });
                } catch (error) {
                    console.log('Error parsing ode: ', error.message);

                    this.sendAlert('danger', 'Error parsing ODE file', 'Could not parse the ode file, please try turning off the interactive parsing below the text-area under the editor tab.');
                }
            },

            sendAlert(type, title, message) {
                this.alert = {
                    show: true,
                    type: type,
                    title: title,
                    message: message
                }
            }
        },

        watch: {
            simpleEditor: function (newValue) {
                localStorage.setItem('simpleEditor', newValue);
            }
        },

        components: {
            'input-box': InputBox,
            'ode-file': OdeFile,
            'alert': require('vue-strap/dist/vue-strap.min').alert
        }
    }
</script>

<style>
    .tab-content {
        overflow: auto;
        zoom: 1;
    }

    .nav-tabs-custom > .nav-tabs > li.active {
        border-top-color: #00a65a;
    }
</style>
