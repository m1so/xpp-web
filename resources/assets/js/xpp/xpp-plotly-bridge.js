export class XppToPlotly {
    constructor(variables) {
        this.variables = variables;
    }

    prepare2D(file, xName, yName, mode) {
        return new Promise((resolve, reject) => {
            try {
                let data = this.parse2D(file);
                resolve(this.transform2D(data, xName, yName, mode));
            } catch (error) {
                reject(error);
            }
        });
    }

    prepareNullclines(file) {
        return new Promise((resolve, reject) => {
            try {
                let data = this.parseNullclines(file);
                resolve(this.transformNullclines(data));
            } catch (error) {
                reject(error);
            }
        });
    }

    prepareDirField(file) {
        return new Promise((resolve, reject) => {
            try {
                let data = this.parseDirField(file);
                resolve(this.transformDirField(data));
            } catch (error) {
                reject(error);
            }
        });
    }

    parse2D(file) {
        // Bootstrap variables + time
        let data = {};
        data['t'] = { name: 't', values: [] };
        this.variables.forEach(variableName => {
            data[variableName] = { name: variableName, values: [] };
        });

        // Grab the data
        file.split('\n').forEach(line => {
            const lineValues = line.trim().split(' ');

            for (var index = 0; index < lineValues.length; index++) {
                let value = lineValues[index];

                // Time values are always in the first column
                if (index === 0) {
                    data['t'].values.push(value);
                    continue;
                }

                // The rest is ordered by dif. equations appearance in ODE file
                const name = this.variables[index - 1];
                data[name].values.push(value);
            }
        });

        return data;
    }

    parseNullclines(file) {
        let previousTrace = 0;
        let data = {
            x: [[], []],
            y: [[], []]
        };

        file.split('\n').forEach((line) => {
            // Format: x1 y1
            let row = line.trim().split(' ');

            let x = parseFloat(row[0]);
            let y = parseFloat(row[1]);
            let trace = parseInt(row[2]) - 1 || previousTrace;
            previousTrace = trace;

            data.x[trace].push(x);
            data.y[trace].push(y);
        });

        return data;
    }

    parseDirField(file) {
        let data = {
            x: [],
            y: []
        };

        file.split('\n').forEach((line) => {
            // Format: x1 y1 x2 y2
            let row = line.split(' ');

            // Add x and y coordinates
            data.x.push(row[0], row[2], null);
            data.y.push(row[1], row[3], null);
        });

        return data;
    }

    transform2D(data, xName, yName, mode) {
        return {
            x: data[xName].values,
            y: data[yName].values,
            mode: mode,
            line: {
                width: 2
            },
            marker: {
                size: 4,
                opacity: 0.4
            },
            name: `${xName} vs. ${yName}`
        };
    }

    transformNullclines(data) {
        return [
            {
                x: data.x[0],
                y: data.y[0],
                name: `X nullcline`,
                type: 'scatter',
                mode: 'lines'
            },
            {
                x: data.x[1],
                y: data.y[1],
                name: `Y nullcline`,
                type: 'scatter',
                mode: 'lines'
            }
        ];
    }

    transformDirField(data) {
        return {
            x: data.x,
            y: data.y,
            type: 'scatter',
            mode: 'lines',
            name: 'Direction field'
        };
    }
}
