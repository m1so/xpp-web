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

            <button @click="createGraph(selected.xAxis, selected.yAxis, selected.zAxis)"
                    type="button"
                    class="btn btn-block btn-primary"
            >
                Show
            </button>
        </div>
    </div>
</template>

<script type="text/babel">
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
            this.parse();
        },

        data() {
            return {
                graphDrawn: false,
                selected: {
                    xAxis: '',
                    yAxis: '',
                    zAxis: ''
                },
                data: {}
            }
        },

        computed: {
            insufficientVariablesError() {
                if (this.variables.length < 2) {
                    return "You need at least 2 variables and time to draw a 3D graph."
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

                    this.parse();

                    this.createGraph(this.selected.xAxis, this.selected.yAxis, this.selected.zAxis, true);
                }
            }
        },

        methods: {
            createGraph(xName, yName, zName, redraw = false) {
                if (this.variables.length < 2) {
                    return;
                }

                let graphData = {
                    x: this.data[xName].values,
                    y: this.data[yName].values,
                    z: this.data[zName].values,
                    type: 'scatter3d',
                    mode: 'lines'
                };

                let graphOptions = {
                    title: `"${xName}" vs. "${yName}" vs. "${zName}"`,
                    xaxis: { title: xName },
                    yaxis: { title: yName },
                    zaxis: { title: zName },
                    hovermode: 'closest'
                };

                if (redraw) {
                    // Only redraw if 3D graph was created by user to save CPU
                    if (this.graphDrawn) {
                        this.$els.tgraph.data[0] = graphData;
                        this.$els.tgraph.layout = Object.assign(this.$els.tgraph.layout, graphOptions);
                        Plotly.redraw(this.$els.tgraph);
                        this.graphDrawn = true;
                    }
                } else {
                    Plotly.newPlot(this.$els.tgraph, [graphData], graphOptions);
                    this.graphDrawn = true;
                }
            },

            parse() {
                // Bootstrap variables + time
                this.data['t'] = { name: 't', values: [] };
                this.variables.forEach(v => {
                    this.data[v] = { name: v, values: [] };
                });

                // Grab the data
                this.input.split('\n').forEach(line => {
                    const lineValues = line.trim().split(' ');

                    lineValues.forEach((value, index) => {
                        // Time values are always in the first column
                        if (index === 0) {
                            this.data['t'].values.push(value);

                            return;
                        }

                        // The rest is ordered by dif. equations appearance in ODE file
                        const name = this.variables[index - 1];
                        this.data[name].values.push(value);
                    })
                });
            }
        }
    }
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
