<template>
    <!-- Hacky way to enforce Plot.ly's graph width -->
    <div v-el:nullcline-graph id="nullcline-graph" style="width: 96vw; height: 81vh;"></div>
</template>

<script type="text/babel">
    export default {
        props: {
            input: {
                required: true,
                type: String
            },
            variables: {
                type: Array,
                required: true
            }
        },

        ready() {
            this.parse();

            this.createGraph();
        },

        data() {
            return {
                x: [
                    [], []
                ],
                y: [
                    [], []
                ]
            }
        },

        computed: {
            graphData() {
                return [
                    {
                        x: this.x[0],
                        y: this.y[0],
                        name: this.variables[0],
                        type: 'scatter',
                        mode: 'lines'
                    },
                    {
                        x: this.x[1],
                        y: this.y[1],
                        name: this.variables[1],
                        type: 'scatter',
                        mode: 'lines'
                    }
                ]
            }
        },

        events: {
            redraw(variables, files) {
                let hasContent = !!this.input;
                this.x = [[], []];
                this.y = [[], []];

                this.input = files.nullclines;
                this.variables = variables;

                this.parse();

                if (hasContent) {
                    this.$els.nullclineGraph.data = this.graphData;

                    Plotly.redraw(this.$els.nullclineGraph);
                } else {
                    this.createGraph();
                }
            }
        },

        methods: {
            parse() {
                let previousTrace = 0;

                this.input.split('\n').forEach((line) => {
                    // Format: x1 y1
                    let row = line.trim().split(' ');

                    let x = parseFloat(row[0]);
                    let y = parseFloat(row[1]);
                    let trace = parseInt(row[2]) - 1 || previousTrace;
                    previousTrace = trace;

                    this.x[trace].push(x);
                    this.y[trace].push(y);
                });
            },
            createGraph() {
                Plotly.newPlot(this.$els.nullclineGraph, this.graphData, {
                    title: 'Nullclines',
                    hovermode: 'closest'
                });
            }
        }
    }
</script>
