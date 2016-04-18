<template>
    <div v-el:nullcline-graph id="nullcline-graph" style="width: 100%; height: 500px;"></div>

</template>

<script type="text/babel">
    var Plotly = require('plotly.js');

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

            Plotly.newPlot(this.$els.nullclineGraph, [this.graphData], {
                title: 'Nullclines',
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
                    type: 'scatter'
                }
            }
        },

        methods: {
            parse() {
                this.input.split('\n').forEach((line) => {
                    // Format: x1 y1
                    let row = line.split(' ');

                    // Add x and y coordinates
                    this.x.push(row[0]);
                    this.y.push(row[1]);
                });
            }
        }
    }
</script>
