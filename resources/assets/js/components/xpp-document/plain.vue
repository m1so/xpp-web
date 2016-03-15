<template>
    <div id="xpp-document-plain-component">
        <div class="col-md-12">
            <!-- Custom Tabs -->
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#tab_editor_simple" data-toggle="tab">Editor</a>
                    </li>
                    <li>
                        <a href="#tab_output_simple" data-toggle="tab">Output</a>
                    </li>
                    <li class="pull-right">
                        <button @click="$parent.$data.simpleEditor = false" type="button" class="btn btn-default">
                            Switch editor
                        </button>

                        <button @click="postOde" type="button" class="btn btn-success">
                            Run
                        </button>
                    </li>
                </ul>
                <div class="tab-content">
                    <!-- Editor tab -->
                    <div class="tab-pane active" id="tab_editor_simple">
                        <div class="col-md-12">
                            <textarea class="form-control" style="height: 75vh" v-model="ode"></textarea>
                        </div>
                    </div>

                    <!-- Output tab -->
                    <div class="tab-pane" id="tab_output_simple">
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
    export default {
        props: ['ode', 'log', 'output', 'title', 'id'],

        data() {
            return {
                lastSent: null
            }
        },

        methods: {
            postOde() {
                this.lastSent = new Date();

                this.$http.post('/api/xpp', {
                    ode: this.ode,
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
            }
        }
    }
</script>
