<template>
    <div class="row">
        <div class="col-md-10">
            <!-- Hacky way to enforce Plot.ly's graph width -->
            <div v-el:graph id="graph" style="width: 83vw; height: 81vh;">
                <div v-if="insufficientVariablesError" class="callout callout-warning variable-error">
                    <h4>Not enough variables!</h4>
                    <p>{{ insufficientVariablesError }}</p>
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
            </div>

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

            <button @click="createGraph(selected.xAxis, selected.yAxis, selected.type)"
                    type="button"
                    class="btn btn-block btn-primary"
            >
                Show
            </button>

            <button @click="download()"
                    :class="loading.svg ? 'disabled' : ''"
                    type="button"
                    class="btn btn-block btn-default"
            >
                <i v-if="loading.svg" class="fa fa-spin fa-spinner"></i>
                Export{{ loading.svg ? 'ing': '' }} as SVG
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

            this.createGraph('t', this.variables[0], this.selected.type);
        },

        data() {
            return {
                selected: {
                    xAxis: '',
                    yAxis: '',
                    type: 'markers'
                },
                loading: {
                    svg: false
                },
                data: {}
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

                this.input = files.result;
                this.variables = variables;

                this.parse();

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

                let graphData = {
                    x: this.data[xName].values,
                    y: this.data[yName].values,
                    mode: mode,
                    line: {
                        color: 'rgb(77, 32, 16)',
                        width: 2
                    },
                    marker: {
                        color: 'rgb(16, 32, 77)',
                        size: 4,
                        opacity: 0.4
                    }
                };

                let graphOptions = {
                    title: `${xName} vs. ${yName}`,
                    xaxis: { title: xName },
                    yaxis: { title: yName },
                    hovermode: 'closest'
                };

                if (redraw) {
                    this.$els.graph.data[0] = graphData;
                    this.$els.graph.layout = Object.assign(this.$els.graph.layout, graphOptions);
                    Plotly.redraw(this.$els.graph);
                } else {
                    Plotly.newPlot(this.$els.graph, [graphData], graphOptions);
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
            },

            /**
             * Hook into Plotly and force download SVG file
             * NOTE: Might only work in Chrome and Firefox
             */
            download() {
                this.loading.svg = true;

                Plotly.toImage(this.$els.graph, { format: 'svg' }).then(data => {
                    let a = document.createElement('a');
                    a.href = 'data:image/svg+xml;utf8,' + data;
                    a.download = 'testing.svg';
                    a.target = '_blank';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);

                    this.loading.svg = false;
                });
            }
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
</style>
