<template>
    <div class="row">
        <div class="col-md-10">
            <div v-show="show.ics.interactive" transition="expand" class="callout callout-ics bg-purple">
                <h4>
                    <i v-if="show.ics.interactive" class="fa fa-spin fa-spinner"></i>
                    Interactive ICs in progress
                </h4>
                <p>Please click somewhere in the plot to continue.</p>
            </div>
            <!-- Hacky way to enforce Plot.ly's graph width -->
            <div v-el:graph id="graph" style="width: 83vw; height: 81vh;">
                <div v-if="insufficientVariablesError" class="callout callout-warning variable-error">
                    <h4>Not enough variables!</h4>
                    <p>{{ insufficientVariablesError }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-2" style="padding-right: 25px;">
            <!-- Axis control -->
            <div class="form-group">
                <label>X axis</label>
                <select v-model="selected.xAxis" class="form-control axis-control" :disabled="show.ics.interactive || show.equil.interactive">
                    <option selected>t</option>
                    <option v-for="name in variables">{{ name }}</option>
                </select>

                <label>Y axis</label>
                <select v-model="selected.yAxis" class="form-control axis-control" :disabled="show.ics.interactive || show.equil.interactive">
                    <option>t</option>
                    <option selected>{{ variables[0] }}</option>
                    <option v-for="name in variables.slice(1)">{{ name }}</option>
                </select>
            </div>

            <!-- Modes panel -->
            <strong>Mode</strong>
            <div class="radio">
                <label>
                    <input v-model="selected.type" type="radio" id="discrete" value="markers">
                    Discrete
                </label>
            </div>

            <div class="radio">
                <label>
                    <input v-model="selected.type" type="radio" id="continuous" value="line">
                    Continuous
                </label>
            </div>

            <div class="radio">
                <label>
                    <input v-model="selected.type" type="radio" id="mixed" value="lines+markers">
                    Mixed
                </label>
            </div>

            <!-- Overlay panel -->
            <strong v-if="files.nullclines || files.directionField || files.equilibria">
                Overlay
            </strong>
            <div class="checkbox" v-if="files.nullclines">
                <label>
                    <input v-model="overlay.nullclines" type="checkbox"> Nullclines
                </label>
            </div>

            <div class="checkbox" v-if="files.directionField">
                <label>
                    <input v-model="overlay.dirField" type="checkbox"> Direction field
                </label>
            </div>

            <div class="checkbox" v-if="files.equilibria">
                <label>
                    <input v-model="overlay.equilibrium" type="checkbox"> Equilibria
                </label>
            </div>

            <!-- Options panel -->
            <strong>Options</strong>
            <div class="checkbox">
                <label>
                    <input v-model="options.freeze" type="checkbox"> Freeze plots
                </label>
            </div>

            <!-- Buttons -->
            <button @click="createGraph(selected.xAxis, selected.yAxis, selected.type, { redraw: options.freeze })"
                    type="button"
                    class="btn btn-block btn-primary"
            >
                {{ options.freeze ? 'Add' : 'Show' }}
            </button>

            <button @click="show.ics.interactive ? stopInteractiveClick('ics') : startInteractiveClick('ics')"
                    :class="show.ics.interactive ? 'btn-warning' : 'bg-purple'"
                    type="button"
                    class="btn btn-block"
            >
                <i v-if="show.ics.interactive" class="fa fa-spin fa-spinner"></i>
                {{ show.ics.interactive ? 'Stop' : 'Start' }} interactive ICs
            </button>

            <button @click="show.equil.interactive ? stopInteractiveClick('equil') : startInteractiveClick('equil')"
                    :class="show.equil.interactive ? 'btn-warning' : 'bg-teal'"
                    type="button"
                    class="btn btn-block"
            >
                <i v-if="show.equil.interactive" class="fa fa-spin fa-spinner"></i>
                {{ show.equil.interactive ? 'Stop' : 'Start' }} equilibria
                <template v-if=""></template>
            </button>

            <button @click="download()"
                    :class="loading.svg ? 'disabled' : ''"
                    type="button"
                    class="btn btn-block btn-default"
            >
                <i v-if="loading.svg" class="fa fa-spin fa-spinner"></i>
                Export{{ loading.svg ? 'ing': '' }} as SVG
            </button>

            <!-- Interactive INITIAL CONDITIONS modal -->
            <modal title="Confirm selected initial conditions" :show.sync="show.ics.modal" effect="zoom">
                <div slot="modal-body" class="modal-body">
                    <p>
                        Initial conditions for other variables will be set from your ODE input,
                        only the selected ones will be used
                        (<em>note that they will not be replaced in your ODE input</em>).
                    </p>
                    <p class="lead">
                        <div class="form-group" v-show="selected.xAxis !== 't'">
                            <label>{{ selected.xAxis }}</label>
                            <input v-model="coordinates.x" type="number" step="any" class="form-control">
                        </div>
                        <div class="form-group" v-show="selected.yAxis !== 't'">
                            <label>{{ selected.yAxis }}</label>
                            <input v-model="coordinates.y" type="number" step="any" class="form-control">
                        </div>
                    </p>
                </div>
                <div slot="modal-footer" class="modal-footer">
                    <button type="button" class="btn btn-danger" @click="stopInteractiveClick('ics')">
                        Stop selecting ICs
                    </button>
                    <button type="button" class="btn btn-success"
                            @click="runInteractiveClick('ics')"
                    >
                        Run
                    </button>
                </div>
            </modal>

            <!-- Interactive EQUILIB. modal -->
            <modal title="Confirm selected coordinates" :show.sync="show.equil.modal" effect="zoom">
                <div slot="modal-body" class="modal-body">
                    <p class="lead">
                        <div class="form-group" v-show="selected.xAxis !== 't'">
                            <label>{{ selected.xAxis }}</label>
                            <input v-model="coordinates.x" type="number" step="any" class="form-control">
                        </div>
                        <div class="form-group" v-show="selected.yAxis !== 't'">
                            <label>{{ selected.yAxis }}</label>
                            <input v-model="coordinates.y" type="number" step="any" class="form-control">
                        </div>
                    </p>
                </div>
                <div slot="modal-footer" class="modal-footer">
                    <button type="button" class="btn btn-danger" @click="stopInteractiveClick('equil')">
                        Stop selecting
                    </button>
                    <button type="button" class="btn btn-success"
                            @click="runInteractiveClick('equil')"
                    >
                        Run
                    </button>
                </div>
            </modal>
        </div>
    </div>
</template>

<script type="text/babel">
    import { modal } from 'vue-strap';
    import { PlotStorage } from '../../xpp/plot-storage';
    import { XppToPlotly } from '../../xpp/xpp-plotly-bridge';
    import { clickHandler, clickUnregister } from '../../xpp/interactive-click';

    const emptyCoordinates = {
        x: null,
        y: null
    };

    export default {
        props: {
            files: {
                type: Object,
                required: true
            },
            variables: {
                type: Array,
                required: true
            }
        },

        ready() {
            this.selected.xAxis = 't';
            this.selected.yAxis = this.variables[0];

            this.createGraph('t', this.variables[0], this.selected.type);
        },

        created() {
            this.storage = new PlotStorage();
            this.bridge = new XppToPlotly(this.variables);
        },

        data() {
            return {
                show: {
                    ics: {
                        interactive: false,
                        modal: false
                    },
                    equil: {
                        interactive: false,
                        modal: false
                    }
                },
                coordinates: emptyCoordinates,
                selected: {
                    xAxis: '',
                    yAxis: '',
                    type: 'markers'
                },
                overlay: {
                    equilibrium: false,
                    nullclines: false,
                    dirField: false
                },
                options: {
                    freeze: false
                },
                loading: {
                    svg: false
                }
            };
        },

        computed: {
            insufficientVariablesError() {
                if (this.variables.length < 1) {
                    return 'You need at least 1 variable and time to draw a 2D graph.';
                } else {
                    return null;
                }
            }
        },

        events: {
            redraw(variables, files, options) {
                this.files = files;
                this.variables = variables;
                this.bridge = new XppToPlotly(this.variables);
                this.overlay.equilibrium = Boolean(files.equilibria);

                this.selected.xAxis = this.selected.xAxis || 't';
                this.selected.yAxis = this.selected.yAxis || this.variables[0];

                this.createGraph(this.selected.xAxis, this.selected.yAxis, this.selected.type, {
                    redraw: true,
                    lastWith: options.lastWith
                });
            }
        },

        methods: {
            createGraph(xName, yName, mode = 'markers', { redraw = false, lastWith = '' } = {}) {
                if (this.variables.length < 1) {
                    return;
                }

                this.$dispatch('plotting-started');

                // Remove storage content and only save the latest plot if we are not freezing the plot
                if (!this.options.freeze) {
                    this.storage.removeAll();
                }

                let promises = [
                    this.bridge.prepare2D(this.files.result, xName, yName, mode, lastWith)
                ];

                if (this.overlay.nullclines && this.files.nullclines) {
                    promises.push(this.bridge.prepareNullclines(this.files.nullclines));
                }

                if (this.overlay.dirField && this.files.directionField) {
                    promises.push(this.bridge.prepareDirField(this.files.directionField));
                }

                if (this.overlay.equilibrium && this.files.equilibria) {
                    promises.push(this.bridge.prepareEquilibria(this.files.equilibria));
                }

                // Parse and transform data for plotting asynchronously
                Promise.all(promises).then(results => {
                    // Add results to storage
                    this.storage.add(results);

                    // Specify plot options
                    let graphOptions = {
                        hovermode: 'closest'
                    };

                    // Show axes names and title only if we are not freezing the plot
                    if (!this.options.freeze) {
                        graphOptions = Object.assign(graphOptions, {
                            title: `<i>${xName}</i> vs. <i>${yName}</i>`,
                            xaxis: { title: xName },
                            yaxis: { title: yName }
                        });
                    }

                    // Draw / redraw (setTimeout prevents DOM freezing on initial load)
                    if (redraw) {
                        this.$els.graph.data = this.storage.all;
                        this.$els.graph.layout = Object.assign(this.$els.graph.layout, graphOptions);
                        setTimeout(() => {
                            Plotly.redraw(this.$els.graph).then(() => {
                                this.$dispatch('plotting-finished');
                            });
                        }, 0);
                    } else {
                        setTimeout(() => {
                            Plotly.newPlot(this.$els.graph, this.storage.all, graphOptions, { scrollZoom: true }).then(() => {
                                this.$dispatch('plotting-finished');
                            });
                        }, 0);
                    }
                }).catch(error => {
                    this.$dispatch('plotting-finished');
                    console.error(error); // eslint-disable-line no-console
                });
            },

            startInteractiveClick(name) {
                this.show[name].interactive = true;

                clickHandler.call(this, (e, clickCoordinate) => {
                    this.coordinates = clickCoordinate;

                    this.show[name].modal = true;
                });
            },

            stopInteractiveClick(name) {
                clickUnregister();

                this.show[name].modal = false;
                this.show[name].interactive = false;
                this.coordinates = emptyCoordinates;
            },

            runInteractiveClick(name) {
                let withParams = [];

                if (this.selected.xAxis !== 't' && this.coordinates.x) {
                    withParams.push({
                        key: this.selected.xAxis,
                        value: parseFloat(this.coordinates.x)
                    });
                }

                if (this.selected.yAxis !== 't' && this.coordinates.y) {
                    withParams.push({
                        key: this.selected.yAxis,
                        value: parseFloat(this.coordinates.y)
                    });
                }

                this.$dispatch('run-with', withParams, {
                    equilibria: name === 'equil'
                });

                this.stopInteractiveClick(name);
            },

            /**
             * Hook into Plotly and force download SVG file
             * NOTE: Might only work in Chrome and Firefox
             */
            download() {
                this.loading.svg = true;

                Plotly.toImage(this.$els.graph, { format: 'svg' }).then(data => {
                    let a = document.createElement('a');
                    a.href = 'data:image/svg+xml;base64,\n' + btoa(unescape(encodeURIComponent(data)));
                    a.download = 'XPPWeb Graph.svg';
                    a.target = '_blank';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);

                    this.loading.svg = false;
                });
            }
        },

        components: {
            modal
        }
    };
</script>

<style>
    .axis-control {
        color: rgb(51, 51, 51);
    }

    .variable-error {
        margin-right: 15px;
        margin-left: 15px;
        margin-top: 20px;
    }

    .callout-ics {
        border-left: 5px solid #2d2975;
        margin-left: 15px;
        margin-right: 15px;
    }

    /* always present */
    .expand-transition {
        transition: all .3s ease;
    }

    /* .expand-enter defines the starting state for entering */
    /* .expand-leave defines the ending state for leaving */
    .expand-enter, .expand-leave {
        height: 0;
        padding: 0px;
        opacity: 0;
    }
</style>
