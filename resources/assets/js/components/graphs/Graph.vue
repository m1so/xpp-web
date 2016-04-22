<template>
    <div class="row">
        <div class="col-md-10">
            <div v-el:graph id="graph" style="width: 100%; height: 82vh;"></div>
        </div>
        <div class="col-md-2" style="padding-right: 20px;">
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
        </div>
    </div>
</template>

<script>
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
            this.normalizeVariableNames();

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
                data: {}
            }
        },

        methods: {
            createGraph(xName, yName, mode = "markers") {
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

                Plotly.newPlot(this.$els.graph, [graphData], graphOptions);
            },

            parse() {
                // Bootstrap variables + time
                this.data['t'] = { name: 't', values: [] };
                this.variables.forEach(v => {
                    this.data[v] = { name: v, values: [] };
                });

                // Grab the data
                this.input.split('\n').forEach(line => {
                    const row = line.trim().split(' ');
                    let time = row[0];

                    row.forEach((value, index) => {
                        if (index === 0) {
                            this.data['t'].values.push(value);

                            return;
                        }

                        const name = this.variables[index - 1];
                        this.data[name].values.push(value);
                    })
                });
            },

            normalizeVariableNames() {
                this.variables = this.variables.map(variable => {
                    if (variable.includes('/dt')) {
                        // Remove first 'd' and Remove '/dt'
                        return variable.substring(1).replace('/dt', '');
                    }

                    if (variable.includes('\'')) {
                        // Remove "'"
                        return variable.replace('\'', '');
                    }

                    return variable;
                });
            }
        }
    }
</script>

<style>
    .axis-control {
        color: rgb(51, 51, 51);
    }
</style>
