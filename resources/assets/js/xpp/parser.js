import Token from './token';

// Constants for different types of tokens
import {
    PARAMETER,
    OPTION,
    DIFFERENTIAL_EQUATION,
    DIFFERENCE_EQUATION,
    IC,
    AUX
} from './constants';

/**
 * Definitions (map) for different types of tokens
 *
 * @type {Object}
 */
const definition = {
    [PARAMETER]: {
        prefix: 'param ',
        keywords: ['params ', 'param ', 'pars ', 'par '],
        matcher: (part) => simpleMatcher(part, definition[PARAMETER].keywords),
        sanitizer: (part) => simpleKeywordRemover(part, definition[PARAMETER].keywords)
    },
    [OPTION]: {
        prefix: '@ ',
        keywords: ['@'],
        matcher: (part) => simpleMatcher(part, definition[OPTION].keywords),
        sanitizer: (part) => simpleKeywordRemover(part, definition[OPTION].keywords)
    },
    [DIFFERENTIAL_EQUATION]: {
        prefix: '',
        keywords: ['\'', '/dt'],
        options: {
            onePerLine: true
        },
        matcher: (part) => equationMatcher(part, definition[DIFFERENTIAL_EQUATION].keywords),
        sanitizer: (part) => part
    },
    [DIFFERENCE_EQUATION]: {
        prefix: '',
        keywords: ['(t+1)'],
        options: {
            onePerLine: true
        },
        matcher: (part) => equationMatcher(part, definition[DIFFERENCE_EQUATION].keywords),
        sanitizer: (part) => part
    },
    [AUX]: {
        prefix: 'aux ',
        keywords: ['aux '],
        options: {
            onePerLine: true
        },
        matcher: (part) => equationMatcher(part, definition[AUX].keywords),
        sanitizer: (part) => simpleKeywordRemover(part, definition[AUX].keywords)
    },
    [IC]: {
        prefix: 'init ',
        keywords: ['init', '(0)'],
        matcher: (part) => simpleMatcher(part, definition[IC].keywords),
        sanitizer: (part) => simpleKeywordRemover(part, definition[IC].keywords)
    }
};

function splitEquation(str) {
    let equationRegex = /(.+?(?=\=))=(.+)/;
    let matches = str.match(equationRegex);

    if (matches && typeof matches[2] === 'string') {
        return {
            lhs: matches[1].trim(),
            rhs: matches[2].trim()
        };
    }

    return null;
}

/**
 * Remove multiples keywords from given string
 */
function simpleKeywordRemover(str, keywordsToRemove) {
    let removeRegex = new RegExp('(' + keywordsToRemove.join('|') + ')', 'gi');
    let eqn = splitEquation(str);

    if (eqn) {
        return eqn.lhs.replace(removeRegex, '') + '=' + eqn.rhs;
    }

    return str.replace(removeRegex, '').trim();
}

/**
 * Check if given string has contains at least one keyword
 */
function simpleMatcher(str, keywords) {
    let eqn = splitEquation(str);

    for (let i = 0; i < keywords.length; i++) {
        let keyword = keywords[i];

        if (eqn && eqn.lhs.includes(keyword)) {
            return true;
        }

        if (str.includes(keyword)) {
            return true;
        }
    }
}

/**
 * Check if given string has contains at least one keyword, as well as contains equals character
 */
function equationMatcher(str, keywords) {
    for (let i = 0; i < keywords.length; i++) {
        let keyword = keywords[i];

        if (str.includes(keyword) && str.includes('=')) {
            return true;
        }
    }
}

/**
 * Extract token from given string based on it's token type
 *
 * @method tokenExtractor
 * @param  String  string
 * @param  String  tokenType
 *
 * @return Token
 */
function tokenExtractor(string, tokenType) {
    if (tokenType === undefined || tokenType === 'other') {
        return new Token('other', [string]);
    }

    let part = definition[tokenType].sanitizer(string);

    let equationRegex = /(.+?(?=\=))=(.+)/;
    let matches = part.match(equationRegex);

    // Does not conform to "X=Y" format
    if (matches === null) {
        return new Token(tokenType, [part.trim()]);
    }

    // We have right and left side of the expression "X=Y"
    if (typeof matches[2] === 'string') {
        const leftSide = matches[1].trim();
        const rightSide = matches[2].trim();

        return new Token(tokenType, [leftSide, rightSide]);
    }

    throw `Could not extract Token (of type "${tokenType}") from string: "${string}"`;
}

/**
 * Parser class
 */
export default class Parser {
    /**
     * Constructor
     */
    constructor(input) {
        // Input file
        this.input = input;

        // Version parser runs
        // this.versions = [{
        //     input,
        //     time: new Date()
        // }];

        // Generated output after parsing
        this.output = '';

        // Token storage
        this.tokens = [];
    }

    /**
     * Parse - use this only once, for parsing new input use "reparse" method
     */
    parse() {
        let lines = this.input.split('\n');

        // Line by line
        for (let n = 0; n < lines.length; n++) {
            let line = lines[n].trim();
            let tokenType = 'other';

            // Comment found, add and continue to next line
            if (line.charAt(0) === '#') {
                this.addToken(new Token(tokenType, [line]), n);
                continue;
            }

            // Find the type
            Object.keys(definition).some(function(key) {
                let entry = definition[key];
                if (entry.matcher(line)) {
                    tokenType = key;
                    return true; // Token type found, stop iteration
                }
            });

            // No relevant types matched, no need to split now, continue to next line
            if (tokenType === 'other') {
                this.addToken(new Token(tokenType, [line]), n);
                continue;
            }

            // Eqns have to be on seperate lines
            if (definition[tokenType].options && definition[tokenType].options.onePerLine) {
                this.addToken(tokenExtractor(line, tokenType), n);
                continue;
            }

            // Now extract types that can have multiple key-values for one line
            let parts = line.split(',');

            // Go through all the parts (keyword k1=v1,k2=v2,...) and extract tokens
            for (let i = 0; i < parts.length; i++) {
                this.addToken(tokenExtractor(parts[i], tokenType), n);
            }
        }
    }

    /**
     * Add token
     */
    addToken(token, lineNumber) {
        token.lineNumber = lineNumber;
        this.tokens.push(token);
    }

    /**
     * Reparse new input and save the new version
     */
    reparse(input = null) {
        if (input) {
            this.input = input;
        }

        this.output = '';
        this.tokens = [];

        // this.versions.push({
        //     input,
        //     time: new Date()
        // });

        this.parse();
    }

    /**
     * Generate ODE file from currently stored tokens
     */
    generate() {
        this.output = '';

        const numLines = this.tokens
            .reduce((prev, current) => (prev.line > current.line) ? prev : current)
            .line;

        for (let i = 0; i <= numLines; i++) {
            let tokens = this.getTokensForLine(i);

            if (tokens.length === 0) {
                continue;
            }

            const tokensType = tokens[0].type;

            // Generat prefix according to type
            if (tokensType !== 'other') {
                this.output += definition[tokensType].prefix;
            }

            // Concatenate tokens that are on the same line with comma
            this.output += tokens.map(token => {
                if (token.type !== 'other') {
                    return token.value[0] + '=' + token.value[1];
                } else {
                    return token.value[0];
                }
            }).join(',') + '\n';
        }

        this.output = this.output.trim();

        return this.output;
    }

    add(key, value, tokenType, newLine = false) {
        // Find last index of same type
        let tokens = this.tokens.filter(token => token.type === tokenType);
        const lastIndex = tokens.length > 0 ? tokens.reduce((prev, current) => (prev.line > current.line) ? prev : current).line : 0;

        if (lastIndex === 0 || newLine) {
            // Move all tokens one line down to make space, if idx=0 make sure to move all lines one down
            this.tokens = this.tokens.map(token => {
                if (token.line > (lastIndex - (lastIndex === 0 ? 1 : 0))) {
                    token.line++;
                }

                return token;
            });
        }

        let token = new Token(tokenType, [key, value]);
        token.line = lastIndex + (newLine ? 1 : 0) - (lastIndex === 0 ? 1 : 0); // if idx=0 make sure to move all the way to the top

        this.tokens.push(token);
    }

    replace(key, withValue, tokenType) {
        let tokens = this.tokens.filter(token => token.type === tokenType && token.value[0].toLowerCase() === key.toLowerCase());

        // If no tokens match this criteria, add new one
        if (tokens.length === 0) {
            this.add(key, withValue, tokenType, true);
            return;
        }

        // In case of multiple tokens, modify the last one
        let token = tokens[tokens.length - 1];
        const index = this.tokens.indexOf(token);
        this.tokens[index].value[token.value.length - 1] = withValue;
    }

    remove(token) {
        const index = this.tokens.indexOf(token);

        if (index > -1) {
            this.tokens.splice(index, 1);
        }

    }

    getTokensForLine(lineNumber) {
        return this.tokens.filter(token => token.line === lineNumber);
    }

    get params() {
        return this.tokens.filter(token => token.type === PARAMETER);
    }

    get options() {
        return this.tokens.filter(token => token.type === OPTION);
    }

    get ics() {
        return this.tokens.filter(token => token.type === IC);
    }

    get des() {
        return this.tokens.filter(token => token.type === DIFFERENTIAL_EQUATION || token.type === DIFFERENCE_EQUATION);
    }

    get variables() {
        let isVariableType = (type) => (type === DIFFERENTIAL_EQUATION || type === DIFFERENCE_EQUATION || type === AUX);
        let variables = this.tokens
                            .filter(token => isVariableType(token.type))
                            .map(token => token.value[0])
                            .map(variable => {
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
        return variables;
    }

}
