import Token from './token';

// Constants for different types of tokens
import {
    PARAMETER,
    OPTION,
    DIFFERENTIAL_EQUATION,
    DIFFERENCE_EQUATION,
    IC
} from './constants';

/**
 * Definitions (map) for different types of tokens
 *
 * @type {Object}
 */
const definition = {
    [PARAMETER]: {
        prefix: 'param ',
        keywords: ['params', 'param', 'pars', 'par'],
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
        matcher: (part) => equationMatcher(part, definition[DIFFERENTIAL_EQUATION].keywords),
        sanitizer: (part) => part
    },
    [DIFFERENCE_EQUATION]: {
        prefix: '',
        keywords: ['(t+1)'],
        matcher: (part) => equationMatcher(part, definition[DIFFERENCE_EQUATION].keywords),
        sanitizer: (part) => part
    },
    [IC]: {
        prefix: 'init ',
        keywords: ['init', '(0)'],
        matcher: (part) => simpleMatcher(part, definition[IC].keywords),
        sanitizer: (part) => simpleKeywordRemover(part, definition[IC].keywords)
    }
};

/**
 * Remove multiples keywords from given string
 */
function simpleKeywordRemover(str, keywordsToRemove) {
    let removeRegex = new RegExp('(' + keywordsToRemove.join('|') + ')', 'gi')
    return str.replace(removeRegex, '').trim();
}

/**
 * Check if given string has contains at least one keyword
 */
function simpleMatcher(str, keywords) {
    for (let i = 0; i < keywords.length; i++) {
        let keyword = keywords[i];

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
        this.input = input;
        this.versions = [{
            input,
            time: new Date()
        }];
        this.output = '';

        this.tokens = [];
    }

    /**
     * Parse - use this only once, for parsing new input use "reparse" method
     */
    parse() {
        let lines = this.input.split('\n');

        // Line by line
        for (let n = 0; n < lines.length; n++) {
            let line = lines[n];
            let tokenType = 'other';

            // Find the type
            Object.keys(definition).forEach(function(key) {
                let entry = definition[key];
                if (entry.matcher(line)) {
                    tokenType = key;
                }
            });

            let parts = line.split(',');

            // Go through all the parts (keyword k1=v1,k2=v2,...) and extract tokens
            for (let i = 0; i < parts.length; i++) {
                let part = parts[i];
                let token = tokenExtractor(part, tokenType);
                token.lineNumber = n;
                this.tokens.push(token);
            }
        }
    }

    /**
     * Reparse new input and save the new version
     */
    reparse(input) {
        this.input = input;
        this.output = '';
        this.tokens = [];

        this.versions.push({
            input,
            time: new Date()
        });

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

}
