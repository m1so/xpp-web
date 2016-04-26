<template>
    <!-- Hacky way to enforce Plot.ly's graph width -->
    <div v-el:dir-graph id="dir-graph" style="width: 96vw; height: 81vh;"></div>
</template>

<script type="text/babel">
    export default {
        props: {
            input: {
                required: true,
                type: String
            }
        },

        ready() {
            this.parse();

            this.createGraph();
        },

        data() {
            return {
                x: [],
                y: []
            }
        },

        computed: {
            graphData() {
                return {
                    x: this.x,
                    y: this.y,
                    type: 'scatter',
                    mode: 'lines'
                }
            }
        },

        events: {
            redraw(variables, files) {
                let hasContent = !!this.input;
                this.x = [];
                this.y = [];

                this.input = files.directionField;

                this.parse();

                if (hasContent) {
                    this.$els.dirGraph.data[0] = this.graphData;

                    Plotly.redraw(this.$els.dirGraph);
                } else {
                    this.createGraph();
                }
            }
        },

        methods: {
            parse() {
                this.input.split('\n').forEach((line) => {
                    // Format: x1 y1 x2 y2
                    let row = line.split(' ');

                    // Add x and y coordinates
                    this.x.push(row[0], row[2], null);
                    this.y.push(row[1], row[3], null);
                });
            },
            createGraph() {
                Plotly.newPlot(this.$els.dirGraph, [this.graphData], {
                    title: 'Direction field',
                    hovermode: 'closest'
                });
            }
        }
    }
</script>
