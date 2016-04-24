<template>
    <div v-el:dir-graph id="dir-graph" style="width: 100%; height: 81vh;"></div>
</template>

<script type="text/babel">
    export default {
        props: {
            input: {
                required: true,
                type: String
            },

            width: {
                required: false,
                type: Number,
                default: 700
            },

            height: {
                required: false,
                type: Number,
                default: 450
            }
        },

        ready() {
            this.parse();

            Plotly.newPlot(this.$els.dirGraph, [this.graphData], {
                title: 'Direction field',
                hovermode: 'closest'
            });
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

        methods: {
            parse() {
                this.input.split('\n').forEach((line) => {
                    // Format: x1 y1 x2 y2
                    let row = line.split(' ');

                    // Add x and y coordinates
                    this.x.push(row[0], row[2], null);
                    this.y.push(row[1], row[3], null);
                });
            }
        }
    }
</script>
