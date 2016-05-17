<template>
    <div class="row">
        <div class="col-md-10">
            <!-- Hacky way to enforce Plot.ly's graph width -->
            <div v-el:tgraph id="tgraph" style="width: 83vw; height: 81vh;">
                <div v-if="insufficientVariablesError" class="callout callout-warning variable-error">
                    <h4>Not enough variables!</h4>
                    <p>{{ insufficientVariablesError }}</p>
                </div>

                <div v-if="!graphDrawn" class="callout callout-warning cpu-intensive">
                    <h4>Warning!</h4>
                    <p>3D graphs can be CPU intensive, if you wish to
                    draw a new graph press "Show" in the right panel.</p>
                </div>
            </div>
        </div>
        <div class="col-md-2" style="padding-right: 25px;">
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

                <label>Z axis</label>
                <select v-model="selected.zAxis" class="form-control axis-control">
                    <option>t</option>
                    <option>{{ variables[0] }}</option>
                    <option selected>{{ variables[1] }}</option>
                    <option v-for="name in variables.slice(2)">{{ name }}</option>
                </select>
            </div>

            <!-- Options panel -->
            <strong>Options</strong>
            <div class="checkbox">
                <label>
                    <input v-model="options.freeze" type="checkbox"> Freeze plots
                </label>
            </div>

            <button @click="createGraph(selected.xAxis, selected.yAxis, selected.zAxis)"
                    type="button"
                    class="btn btn-block btn-primary"
            >
                {{ options.freeze ? 'Add' : 'Show' }}
            </button>
        </div>
    </div>
</template>

<script type="text/babel">
    import { PlotStorage } from '../../xpp/plot-storage';
    import { XppToPlotly } from '../../xpp/xpp-plotly-bridge';

    export default {
        props: {
            input: {
                type: String,
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
            this.selected.zAxis = this.variables[1];

            if (this.graphDrawn) {
                this.createGraph('t', this.variables[0], this.variables[1]);
            }
        },

        created() {
            this.storage = new PlotStorage();
            this.bridge = new XppToPlotly(this.variables);
        },

        data() {
            return {
                graphDrawn: false,
                selected: {
                    xAxis: '',
                    yAxis: '',
                    zAxis: ''
                },
                options: {
                    freeze: false
                }
            };
        },

        computed: {
            insufficientVariablesError() {
                if (this.variables.length < 2) {
                    return 'You need at least 2 variables and time to draw a 3D graph.';
                } else {
                    return null;
                }
            }
        },

        events: {
            redraw(variables, files) {
                if (this.graphDrawn) {
                    this.input = files.result;
                    this.variables = variables;
                    this.bridge = new XppToPlotly(this.variables);

                    this.selected.xAxis = this.selected.xAxis || 't';
                    this.selected.yAxis = this.selected.yAxis || this.variables[0];
                    this.selected.zAxis = this.selected.zAxis || this.variables[1];

                    this.createGraph(this.selected.xAxis, this.selected.yAxis, this.selected.zAxis, true);
                }
            }
        },

        methods: {
            createGraph(xName, yName, zName, redraw = false) {
                if (this.variables.length < 2) {
                    return;
                }

                this.graphDrawn = true;

                this.$dispatch('plotting-started');

                if (!this.options.freeze) {
                    this.storage.removeAll();
                }

                this.bridge.prepare3D(this.input, xName, yName, zName).then(result => {
                    // Add results to storage
                    this.storage.add(result);

                    // Specify plot options
                    let graphOptions = {
                        hovermode: 'closest'
                    };

                    // Show axes names and title only if we are not freezing the plot
                    if (!this.options.freeze) {
                        graphOptions = Object.assign(graphOptions, {
                            title: `<i>${xName}</i> vs. <i>${yName}</i> vs <i>${zName}</i>`,
                            xaxis: { title: xName },
                            yaxis: { title: yName },
                            zaxis: { title: zName }
                        });
                    }

                    // Draw / redraw (setTimeout prevents DOM freezing on initial load)
                    if (redraw) {
                        this.$els.tgraph.data = this.storage.all;
                        this.$els.tgraph.layout = Object.assign(this.$els.tgraph.layout, graphOptions);
                        setTimeout(() => {
                            Plotly.redraw(this.$els.tgraph).then(() => {
                                this.$dispatch('plotting-finished');
                            });
                        }, 0);
                    } else {
                        setTimeout(() => {
                            Plotly.newPlot(this.$els.tgraph, this.storage.all, graphOptions, { scrollZoom: true }).then(() => {
                                this.$dispatch('plotting-finished');
                            });
                        }, 0);
                    }
                }).catch(error => {
                    this.$dispatch('plotting-finished');
                    console.error(error); // eslint-disable-line no-console
                });
            }
        }
    };
</script>

<style>
    .axis-control {
        color: rgb(51, 51, 51);
    }

    .cpu-intensive, .variable-error {
        margin-right: 15px;
        margin-left: 15px;
        margin-top: 20px;
    }
</style>
