<template>
    <div class="row">
        <div class="col-md-10">
            <div v-show="show.interactiveICs" transition="expand" class="callout callout-ics bg-purple">
                <h4>
                    <i v-if="show.interactiveICs" class="fa fa-spin fa-spinner"></i>
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
                <select v-model="selected.xAxis" class="form-control axis-control" :disabled="show.interactiveICs">
                    <option selected>t</option>
                    <option v-for="name in variables">{{ name }}</option>
                </select>

                <label>Y axis</label>
                <select v-model="selected.yAxis" class="form-control axis-control" :disabled="show.interactiveICs">
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
            <strong>Overlay</strong>
            <div class="checkbox">
                <label>
                    <input v-model="overlay.nullclines" type="checkbox"> Nullclines
                </label>
            </div>

            <div class="checkbox">
                <label>
                    <input v-model="overlay.dirField" type="checkbox"> Direction field
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

            <button @click="show.interactiveICs ? stopInteractiveICs() : startInteractiveICs()"
                    :class="show.interactiveICs ? 'btn-warning' : 'bg-purple'"
                    type="button"
                    class="btn btn-block"
            >
                <i v-if="show.interactiveICs" class="fa fa-spin fa-spinner"></i>
                {{ show.interactiveICs ? 'Stop' : 'Start' }} interactive ICs
            </button>

            <button @click="download()"
                    :class="loading.svg ? 'disabled' : ''"
                    type="button"
                    class="btn btn-block btn-default"
            >
                <i v-if="loading.svg" class="fa fa-spin fa-spinner"></i>
                Export{{ loading.svg ? 'ing': '' }} as SVG
            </button>

            <modal title="Confirm selected initial conditions" :show.sync="show.icsModal" effect="zoom">
                <div slot="modal-body" class="modal-body">
                    <p>
                        Initial conditions for other variables will be set from your ODE input,
                        only the selected ones will be used
                        (<em>note that they will not be replaced in your ODE input</em>).
                    </p>
                    <p class="lead">
                        <div class="form-group" v-show="selected.xAxis !== 't'">
                            <label>{{ selected.xAxis }}</label>
                            <input v-model="ics.x" type="number" step="any" class="form-control">
                        </div>
                        <div class="form-group" v-show="selected.yAxis !== 't'">
                            <label>{{ selected.yAxis }}</label>
                            <input v-model="ics.y" type="number" step="any" class="form-control">
                        </div>
                    </p>
                </div>
                <div slot="modal-footer" class="modal-footer">
                    <button type="button" class="btn btn-danger" @click="stopInteractiveICs()">
                        Stop selecting ICs
                    </button>
                    <button type="button" class="btn btn-success"
                            @click="runInteractiveICs()"
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

    const emptyICs = {
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
                    interactiveICs: false,
                    icsModal: false
                },
                ics: emptyICs,
                lastICs: null,
                selected: {
                    xAxis: '',
                    yAxis: '',
                    type: 'markers'
                },
                overlay: {
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

                if (this.overlay.nullclines) {
                    promises.push(this.bridge.prepareNullclines(this.files.nullclines));
                }

                if (this.overlay.dirField) {
                    promises.push(this.bridge.prepareDirField(this.files.directionField));
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

            startInteractiveICs() {
                this.show.interactiveICs = true;

                let canvas = window.$('#graph > div > div > svg:nth-child(1) > g.subplot.xy > rect');
                let graphXAxis = this.$els.graph._fullLayout.xaxis;
                let graphYAxis = this.$els.graph._fullLayout.yaxis;

                let axisValues = {
                    x: {
                        max: graphXAxis.range[1],
                        min: graphXAxis.range[0],
                        length: graphXAxis._length,
                        range: graphXAxis.range[1] + Math.abs(graphXAxis.range[0])
                    },
                    y: {
                        max: graphYAxis.range[1],
                        min: graphYAxis.range[0],
                        length: graphYAxis._length,
                        range: graphYAxis.range[1] + Math.abs(graphYAxis.range[0])
                    }
                };

                canvas.css({
                    'pointer-events': 'all',
                    'cursor': 'crosshair'
                });

                canvas.on('click.xppweb', e => {
                    var clickPoint = {
                        x: parseInt(e.offsetX) - canvas[0].x.baseVal.value,
                        y: parseInt(e.offsetY) - canvas[0].y.baseVal.value
                    };

                    this.ics = emptyICs;

                    if (this.selected.xAxis !== 't') {
                        this.ics.x = clickPoint.x * 1.0 / axisValues.x.length * axisValues.x.range + axisValues.x.min;
                    }

                    if (this.selected.yAxis !== 't') {
                        this.ics.y = axisValues.y.max - clickPoint.y * 1.0 / axisValues.y.length * axisValues.y.range;
                    }

                    this.show.icsModal = true;
                });
            },

            stopInteractiveICs() {
                let canvas = $('#graph > div > div > svg:nth-child(1) > g.subplot.xy > rect');

                canvas.css({
                    'pointer-events': 'none',
                    'cursor': 'auto'
                });

                canvas.unbind('click.xppweb');

                this.show.icsModal = false;
                this.show.interactiveICs = false;
                this.ics = emptyICs;
            },

            runInteractiveICs() {
                let withParams = [];

                if (this.selected.xAxis !== 't' && this.ics.x) {
                    withParams.push({
                        key: this.selected.xAxis,
                        value: parseFloat(this.ics.x)
                    });
                }

                if (this.selected.yAxis !== 't' && this.ics.y) {
                    withParams.push({
                        key: this.selected.yAxis,
                        value: parseFloat(this.ics.y)
                    });
                }

                this.$dispatch('run-with', withParams);

                this.stopInteractiveICs();
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
