export class PlotStorage {
    constructor() {
        this.store = [];
    }

    get all() {
        return this.store;
    }

    add(trace, name = '') {
        let push = (trace) => {
            trace.name = `(${this.store.length + 1}) ` + trace.name;
            this.store.push(trace);
        };

        // Save multiple traces from array or nested array
        if (Array.isArray(trace)) {
            trace.forEach(tr => {
                if (Array.isArray(tr)) {
                    tr.forEach(t => {
                        push(t);
                    });

                    return;
                }

                push(tr);
            });

            return;
        }

        // Single trace is an Object
        if (trace && typeof trace === 'object') {
            // Overwrite the trace name if one is provided
            if (name) {
                trace.name = name;
            }

            this.store.push(trace);
        }
    }

    removeAll() {
        this.store = [];
    }
}
