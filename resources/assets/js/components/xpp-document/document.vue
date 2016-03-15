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
                        <button @click="switchEditor" type="button" class="btn btn-default">
                            Switch editor
                        </button>

                        <button @click="postOde" type="button" class="btn btn-success">
                            Run
                        </button>
                    </li>
                </ul>
                <div class="tab-content">
                    <!-- Editor tab -->
                    <div class="tab-pane active" id="tab_editor">
                        <div class="col-md-8 col-sm-12">
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
                        <div class="col-md-4 col-sm-12">
                            <ode-file :ics.sync="ics" :des.sync="des" :params.sync="params" :options.sync="options" v-ref:ode-file></ode-file>
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
</template>

<script type="text/babel">
    import tabset from 'vue-strap/src/Tabset.vue'
    import tab from 'vue-strap/src/Tab.vue'

    export default {
        props: ['ode', 'log', 'output', 'title', 'id'],

        compiled() {
            this.parse();
            //this.$refs.odeFile.generate();
        },

        data() {
            return {
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

                this.$http.post('/api/xpp', {
                    ode: this.$refs.odeFile.$data.ode,
                    id: this.id,
                    title: this.title
                })
                    .then(function (response) {
                        this.ode = response.data.ode;
                        this.log = response.data.log;
                        this.status = response.data.status;
                        this.output = response.data.output;
                    })
                    .catch(function (response) {
                        this.log = null;
                        this.status = null;
                        this.output = null;

                        console.log("Error");
                    });
            },

            switchEditor() {
                this.ode = this.$refs.odeFile.ode;
                this.parse();
                this.$parent.$data.simpleEditor = ! this.$parent.$data.simpleEditor;
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
                })
            }
        },

        events: {
            'ode-text-edited': function(odeText) {
                this.ode = odeText;
                this.parse();
            }
        },

        components: {
            'ode-file': require('./ode-file.vue'),
            'input-box': require('./input-box.vue'),
            'tabs': tabset,
            'tab': tab
        }
    }
</script>

<style>
    table td {
        border-top: none !important;
    }

    .col-top-pad-15 {
        padding-top: 15px;
    }

    .fa-left-pad-7 {
        padding-left: 7px;
        padding-top: 10px;
        vertical-align: middle;
        font-size: 16px;
    }

    .td-action {
        width: 10%;
    }

    .td-value {
        width: 65%;
    }

    .td-key {
        width: 25%;
    }

    .box-half-height {
        height: 38vh;
    }

    .tab-content {
        overflow: auto;
        zoom: 1;
    }

    .nav-tabs-custom > .nav-tabs > li.active {
        border-top-color: #00a65a;
    }

</style>