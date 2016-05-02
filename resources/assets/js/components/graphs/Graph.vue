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
                <select v-model="selected.xAxis" class="form-control axis-control">
                    <option selected>t</option>
                    <option v-for="name in variables">{{ name }}</option>
                </select>

                <label>Y axis</label>
                <select v-model="selected.yAxis" class="form-control axis-control">
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

            <!-- Buttons -->
            <button @click="createGraph(selected.xAxis, selected.yAxis, selected.type)"
                    type="button"
                    class="btn btn-block btn-primary"
            >
                Show
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
                    <div v-if="selected.xAxis === 't' || selected.yAxis === 't'" class="callout callout-warning">
                        <h4>Watch out!</h4>
                        <p>
                            One of your axes is set to display <strong>t</strong>. Are you sure you did not want
                            to select <strong>{{ variables.join(' or ') }}</strong>?
                        </p>
                    </div>
                    <p>
                        Initial conditions for other variables will be set from your ODE input,
                        only the selected ones will be used
                        (<em>note that they will not be replaced in your ODE input</em>).
                    </p>
                    <p class="lead">
                        <strong>{{ selected.xAxis }}</strong>: {{ ics ? ics.x.toFixed(3) : '' }} <br>
                        <strong>{{ selected.yAxis }}</strong>: {{ ics ? ics.y.toFixed(3) : '' }}
                    </p>
                    <p>
                        If you wish to change {{ selected.xAxis }} or {{ selected.yAxis }} values,
                        close this window by pressing x in the upper right corner or click on the plot again.
                    </p>
                </div>
                <div slot="modal-footer" class="modal-footer">
                    <button type="button" class="btn btn-danger" @click="stopInteractiveICs()">
                        Stop selecting ICs
                    </button>
                    <button type="button" class="btn btn-success"
                            @click="runInteractiveICs()"
                            :disabled="selected.xAxis === 't' || selected.yAxis === 't'"
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
            // Graph data does not have to be observed
            this.data = {
                graph: {},
                nullclines: {
                    x: [[], []],
                    y: [[], []]
                },
                dirField: {
                    x: [],
                    y: []
                }
            };
        },

        data() {
            return {
                show: {
                    interactiveICs: false,
                    icsModal: false
                },
                ics: null,
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
                loading: {
                    svg: false
                }
            }
        },

        computed: {
            insufficientVariablesError() {
                if (this.variables.length < 1) {
                    return "You need at least 1 variable and time to draw a 2D graph."
                } else {
                    return null;
                }
            }
        },

        events: {
            redraw(variables, files) {
                let hasContent = !!this.input;

                this.files = files;
                this.variables = variables;

                let xAxis = this.selected.xAxis || 't';
                let yAxis = this.selected.yAxis || this.variables[0];

                this.createGraph(xAxis, yAxis, this.selected.type, hasContent);
            }
        },

        methods: {
            createGraph(xName, yName, mode = "markers", redraw = false) {
                if (this.variables.length < 1) {
                    return;
                }

                // Parse data before plotting
                this.parse2D();

                let graphData = {
                    x: this.data.graph[xName].values,
                    y: this.data.graph[yName].values,
                    mode: mode,
                    line: {
                        color: 'rgb(77, 32, 16)',
                        width: 2
                    },
                    marker: {
                        color: 'rgb(16, 32, 77)',
                        size: 4,
                        opacity: 0.4
                    },
                    name: `${xName} vs. ${yName}`
                };

                // Combine all the traces for plotting
                let plotData = [graphData];

                if (this.overlay.nullclines) {
                    this.parseNullclines();

                    let nullclineData = [
                        {
                            x: this.data.nullclines.x[0],
                            y: this.data.nullclines.y[0],
                            name: `${this.variables[0]} nullcline`,
                            type: 'scatter',
                            mode: 'lines'
                        },
                        {
                            x: this.data.nullclines.x[1],
                            y: this.data.nullclines.y[1],
                            name: `${this.variables[1]} nullcline`,
                            type: 'scatter',
                            mode: 'lines'
                        }
                    ];

                    plotData.push(nullclineData[0]);
                    plotData.push(nullclineData[1]);
                }

                if (this.overlay.dirField) {
                    this.parseDirField();

                    let dirFieldData = {
                        x: this.data.dirField.x,
                        y: this.data.dirField.y,
                        type: 'scatter',
                        mode: 'lines',
                        name: 'Direction field'
                    };

                    plotData.push(dirFieldData);
                }

                // Specify plot options
                let graphOptions = {
                    title: `<i>${xName}</i> vs. <i>${yName}</i>`,
                    xaxis: { title: xName },
                    yaxis: { title: yName },
                    hovermode: 'closest'
                };

                // Draw / redraw
                if (redraw) {
                    this.$els.graph.data = plotData;
                    this.$els.graph.layout = Object.assign(this.$els.graph.layout, graphOptions);
                    Plotly.redraw(this.$els.graph);
                } else {
                    Plotly.newPlot(this.$els.graph, plotData, graphOptions, { scrollZoom: true });
                }
            },

            parse2D() {
                // Bootstrap variables + time
                this.data.graph['t'] = { name: 't', values: [] };
                this.variables.forEach(v => {
                    this.data.graph[v] = { name: v, values: [] };
                });

                // Grab the data
                this.files.result.split('\n').forEach(line => {
                    const lineValues = line.trim().split(' ');

                    lineValues.forEach((value, index) => {
                        // Time values are always in the first column
                        if (index === 0) {
                            this.data.graph['t'].values.push(value);

                            return;
                        }

                        // The rest is ordered by dif. equations appearance in ODE file
                        const name = this.variables[index - 1];
                        this.data.graph[name].values.push(value);
                    })
                });
            },

            parseNullclines() {
                let previousTrace = 0;

                this.files.nullclines.split('\n').forEach((line) => {
                    // Format: x1 y1
                    let row = line.trim().split(' ');

                    let x = parseFloat(row[0]);
                    let y = parseFloat(row[1]);
                    let trace = parseInt(row[2]) - 1 || previousTrace;
                    previousTrace = trace;

                    this.data.nullclines.x[trace].push(x);
                    this.data.nullclines.y[trace].push(y);
                });
            },

            parseDirField() {
                this.files.directionField.split('\n').forEach((line) => {
                    // Format: x1 y1 x2 y2
                    let row = line.split(' ');

                    // Add x and y coordinates
                    this.data.dirField.x.push(row[0], row[2], null);
                    this.data.dirField.y.push(row[1], row[3], null);
                });
            },

            startInteractiveICs() {
                this.show.interactiveICs = true;

                let canvas = $('#graph > div > div > svg:nth-child(1) > g.subplot.xy > rect');
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

                    this.ics = {
                        x: clickPoint.x * 1.0 / axisValues.x.length * axisValues.x.range + axisValues.x.min,
                        y: axisValues.y.max - clickPoint.y * 1.0 / axisValues.y.length * axisValues.y.range
                    };

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
                this.ics = null;
            },

            runInteractiveICs() {
                this.$dispatch('run-with', [
                    {
                        key: this.selected.xAxis,
                        value: this.ics.x.toFixed(3)
                    },
                    {
                        key: this.selected.yAxis,
                        value: this.ics.y.toFixed(3)
                    }
                ]);

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
    }
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
