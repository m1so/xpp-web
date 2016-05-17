export class XppToPlotly {
    constructor(variables) {
        this.variables = variables;
    }

    prepare2D(file, xName, yName, mode, lastWith = '') {
        return new Promise((resolve, reject) => {
            try {
                let data = this.parse2D(file);
                resolve(this.transform2D(data, xName, yName, mode, lastWith));
            } catch (error) {
                reject(error);
            }
        });
    }

    prepare3D(file, xName, yName, zName) {
        return new Promise((resolve, reject) => {
            try {
                let data = this.parse3D(file);
                resolve(this.transform3D(data, xName, yName, zName));
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

    prepareEquilibria(file) {
        return new Promise((resolve, reject) => {
            try {
                let data = this.parseEquilibria(file);
                resolve(this.transformEquilibria(data));
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

    parse3D(file) {
        // Bootstrap variables + time
        let data = {};
        data['t'] = { name: 't', values: [] };
        this.variables.forEach(variableName => {
            data[variableName] = { name: variableName, values: [] };
        });

        // Grab the data
        file.split('\n').forEach(line => {
            const lineValues = line.trim().split(' ');

            lineValues.forEach((value, index) => {
                // Time values are always in the first column
                if (index === 0) {
                    data['t'].values.push(value);

                    return;
                }

                // The rest is ordered by dif. equations appearance in ODE file
                const name = this.variables[index - 1];
                data[name].values.push(value);
            });
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

    parseEquilibria(file) {
        const lines = file.split('\n');
        const xLine = lines[0].split(' ');
        const yLine = lines[1].split(' ');
        const decimals = 3;

        const x = {
            coordinate: parseFloat(xLine[0]).toFixed(decimals),
            real: parseFloat(xLine[1]).toFixed(decimals),
            imaginary: parseFloat(xLine[2]).toFixed(decimals)
        };

        const y = {
            coordinate: parseFloat(yLine[0]).toFixed(decimals),
            real: parseFloat(yLine[1]).toFixed(decimals),
            imaginary: parseFloat(yLine[2]).toFixed(decimals)
        };

        return {
            x: [x.coordinate],
            y: [y.coordinate],
            text: `Equilibrium [${x.coordinate},${y.coordinate}]<br>` +
                  `Re(x)=${x.real}, Im(x)=${x.imaginary}<br>` +
                  `Re(y)=${y.real}, Im(y)=${y.imaginary}`
        };
    }

    transform2D(data, xName, yName, mode, lastWith = '') {
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
            name: `${xName} vs. ${yName}` + (lastWith ? `<br>[${lastWith}]` : '')
        };
    }

    transform3D(data, xName, yName, zName) {
        return {
            x: data[xName].values,
            y: data[yName].values,
            z: data[zName].values,
            type: 'scatter3d',
            mode: 'lines',
            name: `${xName} vs. ${yName} vs. ${zName}`
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

    transformEquilibria(data) {
        return {
            x: data.x,
            y: data.y,
            name: data.text,
            marker: {
                size: 8,
                symbol: 'circle-open'
            }
        };
    }
}
