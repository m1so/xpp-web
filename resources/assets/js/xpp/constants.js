export const PARAMETER = 'parameter';
export const OPTION = 'option';
export const DIFFERENTIAL_EQUATION = 'differentialEquation';
export const DIFFERENCE_EQUATION = 'differenceEquation';
export const IC = 'initialCondition';
export const OTHER = 'other';
export const AUX = 'auxiliary';

// SOURCE: http://www.math.pitt.edu/~bard/bardware/xpp_sum.pdf
// VISITED: 1st May 2016
export const OPTIONS_MAP = {
    'maxstor': {
        display: 'MAXSTOR',
        usage: 'MAXSTOR=<code>integer</code>',
        description: `sets the total number of time steps that will be kept in memory. The default is 5000. If you want to perform very long integrations change this to some large number.`
    },

    'total': {
        display: 'TOTAL',
        usage: 'TOTAL=<code>value</code>',
        description: `sets the total amount of time to integrate the equations (default is 20).`
    },

    'dt': {
        display: 'DT',
        usage: 'DT=<code>value</code>',
        description: `sets the time step for the integrator (default is 0.05).`
    },

    't0': {
        display: 'T0',
        usage: 'T0=<code>value</code>',
        description: `sets the starting time (default is 0).`
    },

    'trans': {
        display: 'TRANS',
        usage: 'TRANS=<code>value</code>',
        description: `tells XPP to integrate until <code>T=TRANS</code> and then start plotting solutions (default is 0.)`
    },

    'meth': {
        display: 'METH',
        usage: 'METH=<code>discrete,euler,modeuler,rungekutta,adams,gear,volterra, backeul,qualrk,stiff,cvode,5dp,83dp,2rb,ymp</code>',
        description: `sets the integration method (default is Runge-Kutta.)`
    },

    'bound': {
        display: 'BOUND',
        usage: 'BOUND=<code>value</code>',
        description: `sets the maximum bound any plotted variable can reach in magnitude. If any plottable quantity exceeds this, the integrator will halt with a warning. The program will not stop however (default is 100.)`
    },

    'range': {
        display: 'RANGE',
        usage: 'RANGE=1',
        description: `means that you want to run a range integration (in batch mode).<br>RANGEOVER=name, RANGESTEP=number, RANGELOW=number, RANGEHIGH=number, RANGERESET=Yes,No, RANGEOLDIC=Yes,No all correspond to the entries in the range integration option.<br><span class="text-danger">Note: use <code>RANGEREST=no</code> To ensure corrent parsing and interpretation of results</span><br><span class="text-warning">Please specify the range specific options manually in the ODE editor for better control over the results.</span>`
    }
};
