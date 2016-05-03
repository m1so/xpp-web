export default class Token {
    constructor(type, value) {
        this.type = type;
        this.value = value;
    }

    set lineNumber(number) {
        this.line = number;
    }
}
