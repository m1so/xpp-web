<template>
<div id="editor">
    <div class="container-fluid">
        <div class="row">
            <div class="page-header" style="margin-top: 0px; margin-bottom: 0px;">
                <div class="btn-toolbar pull-right">
                    <button @click="run()"
                            type="button"
                            class="btn btn-primary"
                            :class="loading ? 'disabled' : ''"
                    >
                        Save & Run
                    </button>
                </div>
                <h3>
                    <i v-if="loading" class="fa fa-spin fa-spinner"></i>
                    {{ document.title }}
                    <span v-if="lastWith" class="label label-default bg-purple">{{ lastWith }}</span>
                </h3>
            </div>
        </div>

        <!-- Editor  -->
        <div class="row">
            <!-- Custom Tabs -->
            <div class="nav-tabs-custom">
            <tabset :active="activeTabIndex">
                <tab header="Editor">
                    <div class="row">
                    <!-- Left part -->
                    <div v-if="show.interactive" class="col-lg-9 col-md-12">
                        <!-- Interactive editor 1st row -->
                        <div class="row">
                            <!-- Equations -->
                            <div class="col-sm-6 col-xs-12">
                                <input-box title="Equations"
                                           :type="constants.DIFFERENTIAL_EQUATION"
                                           :data="parser.des"
                                           :items-per-page="show.itemsPerPage"
                                           :parser="parser"
                                ></input-box>
                            </div>
                            <!-- Parameters -->
                            <div class="col-sm-6 col-xs-12">
                                <input-box title="Parameters"
                                           :type="constants.PARAMETER"
                                           :data="parser.params"
                                           :items-per-page="show.itemsPerPage"
                                           :parser="parser"
                                ></input-box>
                            </div>
                        </div>

                        <!-- Interactive editor 2nd row -->
                        <div class="row">
                            <!-- Initial conditions -->
                            <div class="col-sm-6 col-xs-12">
                                <input-box title="Initial conditions"
                                           :type="constants.IC"
                                           :data="parser.ics"
                                           :items-per-page="show.itemsPerPage"
                                           :parser="parser"
                                ></input-box>
                            </div>
                            <!-- Options -->
                            <div class="col-sm-6 col-xs-12">
                                <input-box title="Options"
                                           :type="constants.OPTION"
                                           :data="parser.options"
                                           :items-per-page="show.itemsPerPage"
                                           :parser="parser"
                                ></input-box>
                            </div>
                        </div>
                    </div>

                    <!-- Right part -->
                    <div class="col-md-12" :class="[show.interactive ? 'col-lg-3' : 'col-lg-12']">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    ODE File
                                </h3>
                                <div class="box-tools pull-right">
                                    <button @click="show.sidebar = true" type="button" class="btn btn-box-tool">
                                        <i class="fa fa-cog"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="box-body">
                                <textarea v-model="input" @blur="callParse" v-el:editor lazy class="ode-textarea"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                </tab><!-- end editor tab -->

                <tab header="Output">
                    <pre>
<!--                 -->{{ document.files.log }}
                    </pre>
                </tab>

                <tab header="Graphs">
                    <tabset>
                        <tab header="2D">
                            <graph :files="document.files"
                                   :variables="variables"
                            ></graph>
                        </tab>
                        <tab header="3D">
                            <three-dim-graph :input="document.files.result"
                                             :variables="variables"
                            ></three-dim-graph>
                        </tab>
                    </tabset>
                </tab>
            </tabset>
            </div>
        </div>
    </div>

    <!-- ~~~~~~~ Non-visible components ~~~~~~~ -->

    <!-- Sidebar -->
    <sidebar :show.sync="show.sidebar" :placement="show.sidebarPlacement" header="Sidebar (W)" :width="350">
        <!-- Buttons -->
        <div class="row">
            <div class="col-xs-12">
                <button @click="run()" type="button" class="btn btn-block btn-primary">Save & Run (R)</button>
            </div>
        </div>
        <hr>

        <!-- Editor settings -->
        <h4>Editor settings</h4>
        <div class="form-group">
            <input v-model="show.itemsPerPage" number type="number" style="width: 2em">
            <label>Items per page in input boxes</label>
        </div>
        <div class="form-group">
            <label>
                <input v-model="show.interactive" type="checkbox"> Interactive editor
            </label>
        </div>
        <hr>
    </sidebar>

    <!-- Popup alert -->
    <alert
        :show.sync="alert.show"
        :duration="3000"
        :type="alert.type"
        width="400px"
        placement="top-right"
        dismissable
    >
        <strong>{{ alert.title }}</strong>
        <p>{{ alert.content }}</p>
    </alert>
</div>
</template>

<script type="text/babel">
import { tabset, tab, aside, alert } from 'vue-strap';

import Parser from './../../xpp/parser.js';
import * as parserConstants from './../../xpp/constants';

import Input from './InteractiveInput.vue';
import Graph from './../graphs/Graph.vue';
import ThreeDimensionalGraph from './../graphs/3DGraph.vue';

export default {
    props: {
        document: {
            type: Object,
            required: true
        }
    },

    created() {
        this.initKeyboard();

        let storeSettings = JSON.parse(localStorage.getItem('show'));

        if (storeSettings && Object.keys(storeSettings).length === Object.keys(this.show).length) {
            this.show = storeSettings;
        }

        let lastWithStorage = localStorage.getItem(`lastWith-${this.document.id}`);

        if (lastWithStorage && lastWithStorage !== 'null') {
            this.lastWith = lastWithStorage;
        }

        this.parser.parse();
    },

    ready() {
        this.show.sidebar = false;

        // Escape => blur text area (so other keyboard shortcuts are usable)
        $(this.$els.editor).keydown((e) => {
            if (e.which === 27) {
                this.$els.editor.blur();
            }
        });
    },

    beforeDestroy() {
        window.Mousetrap.reset();
    },

    data() {
        var parser = new Parser(this.document.files.ode);

        return {
            parser: parser,
            input: this.document.files.ode,
            constants: parserConstants,
            show: {
                interactive: true,
                editorSettings: false,
                itemsPerPage: 6,
                sidebar: false,
                sidebarPlacement: 'left'
            },
            alert: {
                show: false,
                type: '',
                title: '',
                content: ''
            },
            lastWith: null,
            loading: false,
            activeTabIndex: 0
        };
    },

    computed: {
        variables() {
            return this.parser.variables;
        }
    },

    methods: {
        initKeyboard() {
            let shortcuts = window.Mousetrap;
            /* eslint-disable no-unused-vars */
            // W => Toggle sidebar
            shortcuts.bind(['x w'], (e) => { this.show.sidebar = !this.show.sidebar; });

            // I => Toggle interactive
            shortcuts.bind(['x i'], (e) => { this.show.interactive = !this.show.interactive; });

            // R => Run
            shortcuts.bind(['x r'], (e) => { this.run(); });

            // E => Focus editor
            shortcuts.bind(['x e'], (e) => { this.$els.editor.focus(); return false; });

            // T E => Tab editor (1st)
            shortcuts.bind(['x t e'], (e) => { this.activeTabIndex = 0; });

            // T O => Tab output (2nd)
            shortcuts.bind(['x t o'], (e) => { this.activeTabIndex = 1; });

            // T G => Tab graphs (3rd)
            shortcuts.bind(['x t g'], (e) => { this.activeTabIndex = 2; });
            /* eslint-enable no-unused-vars */
        },

        callParse() {
            this.parser.reparse(this.input);
        },

        run(options = {}) {
            this.showAlert('success', 'Running!', 'Your file is being computed right now.');

            this.loading = true;

            this.lastWith = null;

            this.$http.post('/api/xpp', {
                document: {
                    input: this.input,
                    id: this.document.id
                },
                options: Object.assign({}, {
                    nullclines: true,
                    directionField: true
                }, options)
            }).then(response => {
                this.document = response.data.document;

                if (options.with) {
                    this.lastWith = options.with.reduce((carry, item) => {
                        return carry + item.key + '=' + item.value + ', ';
                    }, '').replace(/,\s*$/, '');
                }

                this.$broadcast('redraw', this.variables, response.data.document.files);

                this.showAlert('success', 'Finished!', 'Computations have successfully finished.');

                this.loading = false;
            }).catch(response => {
                console.log('Failed to run', response); // eslint-disable-line no-console

                this.showAlert('danger', 'Error!', 'Could not run your project.');

                this.loading = false;
            });
        },

        showAlert(type, title, content) {
            // Show new alert
            this.alert.type = type;
            this.alert.title = title;
            this.alert.content = content;
            this.alert.show = true;
        }
    },

    watch: {
        'parser.tokens': {
            handler() {
                this.input = this.parser.generate();
            },
            deep: true
        },
        'show': {
            handler(newValue) {
                // console.log('Saving options to localStorage: ', JSON.stringify(newValue));
                localStorage.setItem('show', JSON.stringify(newValue));
            },
            deep: true
        },
        'lastWith': {
            handler(newValue) {
                localStorage.setItem(`lastWith-${this.document.id}`, newValue);
            }
        }
    },

    events: {
        'generate-input'() {
            this.input = this.parser.generate();
        },
        'run-with'(data) {
            this.run({ with: data });
        },
        'plotting-started'() {
            console.log('loading');
            this.loading = true;
        },
        'plotting-finished'() {
            console.log('finished');
            this.loading = false;
        }
    },

    components: {
        tabset,
        tab,
        alert,
        'sidebar': aside,
        'input-box': Input,
        'graph': Graph,
        'three-dim-graph': ThreeDimensionalGraph
    }
};

</script>

<style>
    .ode-textarea {
        width: 100%;
        height: 72vh;
        border-style: none;
    }
</style>
