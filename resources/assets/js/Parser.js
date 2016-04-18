const PARAMETER = 'parameter';
const OPTION = 'option';
const DIFFERENTIAL_EQUATION = 'differentialEquation';
const DIFFERENCE_EQUATION = 'differenceEquation';
const IC = 'initialCondition';

function simpleKeywordRemover(str, keywordsToRemove) {
    let removeRegex = new RegExp('(' + keywordsToRemove.join('|') + ')', 'gi')
    return str.replace(removeRegex, '').trim();
}

function simpleMatcher(str, keywords) {
    keywords.forEach(keyword => {
        if (str.includes(keyword)) {
            return true;
        }
    });
}

function equationMatcher(str, keywords) {
    keywords.forEach(keyword => {
        if (str.includes(keyword) && str.includes('=')) {
            return true;
        }
    });
}

const definition = {
    [PARAMETER]: {
        keywords: ['params', 'par', 'param'],
        matcher: (part) => simpleMatcher(part, definition[PARAMETER].keywords),
        sanitizer: (part) => simpleKeywordRemover(part, definition[PARAMETER].keywords)
    },
    [OPTION]: {
        keywords: ['@'],
        matcher: (part) => simpleMatcher(part, definition[OPTION].keywords),
        sanitizer: (part) => simpleKeywordRemover(part, definition[OPTION].keywords)
    },
    [DIFFERENTIAL_EQUATION]: {
        keywords: ['\'', '/dt'],
        matcher: (part) => equationMatcher(part, definition[DIFFERENTIAL_EQUATION].keywords),
        sanitizer: (part) => part
    },
    [DIFFERENCE_EQUATION]: {
        keywords: ['(t+1)'],
        matcher: (part) => equationMatcher(part, definition[DIFFERENCE_EQUATION].keywords),
        sanitizer: (part) => part
    },
    [IC]: {
        keywords: ['init', '(0)'],
        matcher: (part) => simpleMatcher(part, definition[IC].keywords),
        sanitizer: (part) => simpleKeywordRemover(part, definition[IC].keywords)
    }
};

function tokenExtractor(line, tokenType) {
    if (tokenType === undefined || tokenType === 'other') {
        return {type: 'other', value: [line]};
    }

    console.log(line, tokenType);
    let part = definition[tokenType].sanitizer(line);

    let equationRegex = /(.+?(?=\=))=(.+)/;
    let matches = part.match(equationRegex);
    let token = {type: tokenType};

    // Does not conform to "X=Y" format
    if (matches === null) {
        token.value = [part.trim()];

        return token;
    }

    // We have right and left side of the expression "X=Y"
    if (typeof matches[2] === 'string') {
        const leftSide = matches[1].trim();
        const rightSide = matches[2].trim();

        token.value = [leftSide, rightSide];

        return token;
    }

}

export default class Parser {
    constructor(input) {
        this.input = input;

        this.tokens = [];
    }

    parse() {
        let lines = this.input.split('\n');

        // Line by line
        for (let n = 0; n < lines.length; n++) {
            let line = lines[n];
            let tokenType = 'other';

            // Find the type
            Object.keys(definition).forEach(function (key) {
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

    get params() {
        return this.tokens.filter(token => token.type === PARAMETER);
    }

}
