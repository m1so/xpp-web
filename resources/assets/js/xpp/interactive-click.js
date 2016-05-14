export function clickHandler(onClick) {
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
        // Point that was click in the DOM element's coordinates
        var clickPoint = {
            x: parseInt(e.offsetX) - canvas[0].x.baseVal.value,
            y: parseInt(e.offsetY) - canvas[0].y.baseVal.value
        };

        // Coordinates in the phase plane
        let clickCoordinate = {
            x: null,
            y: null
        };

        if (this.selected.xAxis !== 't') {
            clickCoordinate.x = clickPoint.x * 1.0 / axisValues.x.length * axisValues.x.range + axisValues.x.min;
        }

        if (this.selected.yAxis !== 't') {
            clickCoordinate.y = axisValues.y.max - clickPoint.y * 1.0 / axisValues.y.length * axisValues.y.range;
        }

        onClick(e, clickCoordinate, clickPoint);
    });
}

export function clickUnregister() {
    let canvas = $('#graph > div > div > svg:nth-child(1) > g.subplot.xy > rect');

    canvas.css({
        'pointer-events': 'none',
        'cursor': 'auto'
    });

    canvas.unbind('click.xppweb');
}
