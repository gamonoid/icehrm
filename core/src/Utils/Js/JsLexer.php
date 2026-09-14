<?php

namespace Utils\Js;

/**
 * Tokenizer for the JavaScript subset the payroll/import sandbox accepts.
 *
 * What is NOT tokenizable here is a deliberate part of the security model — a
 * construct that cannot be lexed can never reach the interpreter:
 *
 *   - No regular-expression literals. '/' is ALWAYS division, which is also what
 *     keeps the lexer context-free (distinguishing `a / b` from `/re/` requires
 *     parser feedback in real JS).
 *   - No template literals: a backtick is an error, so there is no interpolation
 *     syntax and no tagged-template call form.
 *   - No bitwise or shift operators; they have no use in payroll arithmetic.
 *
 * Tokens are arrays rather than objects: they are created in bulk and only ever read
 * positionally, so the object overhead buys nothing.
 *   ['type' => num|str|name|punct|eof, 'value' => mixed, 'line' => int]
 */
class JsLexer
{
    /** Multi-character punctuators, longest first — order is what makes the match greedy. */
    private static $punctuators = array(
        '===', '!==',
        '==', '!=', '<=', '>=', '&&', '||', '++', '--', '+=', '-=', '*=', '/=', '%=',
        '(', ')', '[', ']', '{', '}', ',', ';', ':', '?', '.',
        '+', '-', '*', '/', '%', '<', '>', '=', '!',
    );

    private $src;
    private $len;
    private $pos = 0;
    private $line = 1;

    public function __construct($source)
    {
        $this->src = $source;
        $this->len = strlen($source);
    }

    /**
     * @return array list of tokens, always terminated by one of type 'eof'
     * @throws JsError on an unexpected character or unterminated literal
     */
    public function tokenize()
    {
        $tokens = array();
        while (true) {
            $this->skipTrivia();
            if ($this->pos >= $this->len) {
                $tokens[] = array('type' => 'eof', 'value' => null, 'line' => $this->line);
                return $tokens;
            }

            $ch = $this->src[$this->pos];

            if ($ch === '"' || $ch === "'") {
                $tokens[] = $this->readString($ch);
                continue;
            }
            if (ctype_digit($ch) || ($ch === '.' && $this->isDigit($this->peek(1)))) {
                $tokens[] = $this->readNumber();
                continue;
            }
            if ($this->isIdentStart($ch)) {
                $tokens[] = $this->readName();
                continue;
            }
            if ($ch === '`') {
                throw new JsError('Template literals are not supported', $this->line, JsError::KIND_UNSUPPORTED);
            }

            $punct = $this->readPunctuator();
            if ($punct === null) {
                throw new JsError("Unexpected character '" . $ch . "'", $this->line, JsError::KIND_SYNTAX);
            }
            $tokens[] = $punct;
        }
    }

    /** Whitespace and both comment forms. */
    private function skipTrivia()
    {
        while ($this->pos < $this->len) {
            $ch = $this->src[$this->pos];

            if ($ch === "\n") {
                $this->line++;
                $this->pos++;
                continue;
            }
            if ($ch === ' ' || $ch === "\t" || $ch === "\r" || $ch === "\v" || $ch === "\f") {
                $this->pos++;
                continue;
            }
            if ($ch === '/' && $this->peek(1) === '/') {
                while ($this->pos < $this->len && $this->src[$this->pos] !== "\n") {
                    $this->pos++;
                }
                continue;
            }
            if ($ch === '/' && $this->peek(1) === '*') {
                $end = strpos($this->src, '*/', $this->pos + 2);
                if ($end === false) {
                    throw new JsError('Unterminated comment', $this->line, JsError::KIND_SYNTAX);
                }
                $this->line += substr_count(substr($this->src, $this->pos, $end - $this->pos), "\n");
                $this->pos = $end + 2;
                continue;
            }
            return;
        }
    }

    private function readString($quote)
    {
        $line = $this->line;
        $this->pos++; // opening quote
        $out = '';
        while (true) {
            if ($this->pos >= $this->len) {
                throw new JsError('Unterminated string', $line, JsError::KIND_SYNTAX);
            }
            $ch = $this->src[$this->pos];
            if ($ch === $quote) {
                $this->pos++;
                return array('type' => 'str', 'value' => $out, 'line' => $line);
            }
            if ($ch === "\n") {
                throw new JsError('Unterminated string', $line, JsError::KIND_SYNTAX);
            }
            if ($ch === '\\') {
                $this->pos++;
                $out .= $this->readEscape($line);
                continue;
            }
            $out .= $ch;
            $this->pos++;
        }
    }

    private function readEscape($line)
    {
        if ($this->pos >= $this->len) {
            throw new JsError('Unterminated string', $line, JsError::KIND_SYNTAX);
        }
        $e = $this->src[$this->pos];
        $this->pos++;
        switch ($e) {
            case 'n': return "\n";
            case 't': return "\t";
            case 'r': return "\r";
            case 'b': return "\x08";
            case 'f': return "\f";
            case 'v': return "\v";
            case '0': return "\0";
            case 'x':
                $hex = substr($this->src, $this->pos, 2);
                if (strlen($hex) < 2 || !ctype_xdigit($hex)) {
                    throw new JsError('Invalid \\x escape in string', $line, JsError::KIND_SYNTAX);
                }
                $this->pos += 2;
                return $this->codePointToUtf8(hexdec($hex));
            case 'u':
                $hex = substr($this->src, $this->pos, 4);
                if (strlen($hex) < 4 || !ctype_xdigit($hex)) {
                    throw new JsError('Invalid \\u escape in string', $line, JsError::KIND_SYNTAX);
                }
                $this->pos += 4;
                return $this->codePointToUtf8(hexdec($hex));
            default:
                // \\ \' \" \/ and anything else: the character itself.
                return $e;
        }
    }

    private function codePointToUtf8($cp)
    {
        // No intl/mbstring dependency: the four UTF-8 ranges written out directly.
        if ($cp < 0x80) {
            return chr($cp);
        }
        if ($cp < 0x800) {
            return chr(0xC0 | ($cp >> 6)) . chr(0x80 | ($cp & 0x3F));
        }
        if ($cp < 0x10000) {
            return chr(0xE0 | ($cp >> 12)) . chr(0x80 | (($cp >> 6) & 0x3F)) . chr(0x80 | ($cp & 0x3F));
        }
        return chr(0xF0 | ($cp >> 18)) . chr(0x80 | (($cp >> 12) & 0x3F))
            . chr(0x80 | (($cp >> 6) & 0x3F)) . chr(0x80 | ($cp & 0x3F));
    }

    private function readNumber()
    {
        $line = $this->line;
        $start = $this->pos;

        // 0x / 0b / 0o radix forms.
        if ($this->src[$this->pos] === '0' && $this->pos + 1 < $this->len) {
            $marker = strtolower($this->src[$this->pos + 1]);
            $radix = null;
            if ($marker === 'x') {
                $radix = 16;
            } elseif ($marker === 'b') {
                $radix = 2;
            } elseif ($marker === 'o') {
                $radix = 8;
            }
            if ($radix !== null) {
                $this->pos += 2;
                $digitsStart = $this->pos;
                while ($this->pos < $this->len && ctype_xdigit($this->src[$this->pos])) {
                    $this->pos++;
                }
                $digits = substr($this->src, $digitsStart, $this->pos - $digitsStart);
                if ($digits === '') {
                    throw new JsError('Malformed number', $line, JsError::KIND_SYNTAX);
                }
                return array('type' => 'num', 'value' => (float) intval($digits, $radix), 'line' => $line);
            }
        }

        while ($this->pos < $this->len && ctype_digit($this->src[$this->pos])) {
            $this->pos++;
        }
        if ($this->pos < $this->len && $this->src[$this->pos] === '.') {
            $this->pos++;
            while ($this->pos < $this->len && ctype_digit($this->src[$this->pos])) {
                $this->pos++;
            }
        }
        if ($this->pos < $this->len && ($this->src[$this->pos] === 'e' || $this->src[$this->pos] === 'E')) {
            $save = $this->pos;
            $this->pos++;
            if ($this->pos < $this->len && ($this->src[$this->pos] === '+' || $this->src[$this->pos] === '-')) {
                $this->pos++;
            }
            if ($this->pos < $this->len && ctype_digit($this->src[$this->pos])) {
                while ($this->pos < $this->len && ctype_digit($this->src[$this->pos])) {
                    $this->pos++;
                }
            } else {
                $this->pos = $save; // not an exponent after all
            }
        }

        $text = substr($this->src, $start, $this->pos - $start);
        if (!is_numeric($text)) {
            throw new JsError('Malformed number "' . $text . '"', $line, JsError::KIND_SYNTAX);
        }
        return array('type' => 'num', 'value' => (float) $text, 'line' => $line);
    }

    private function readName()
    {
        $line = $this->line;
        $start = $this->pos;
        while ($this->pos < $this->len && $this->isIdentPart($this->src[$this->pos])) {
            $this->pos++;
        }
        return array(
            'type' => 'name',
            'value' => substr($this->src, $start, $this->pos - $start),
            'line' => $line,
        );
    }

    private function readPunctuator()
    {
        foreach (self::$punctuators as $p) {
            if (substr($this->src, $this->pos, strlen($p)) === $p) {
                $this->pos += strlen($p);
                return array('type' => 'punct', 'value' => $p, 'line' => $this->line);
            }
        }
        return null;
    }

    private function peek($offset)
    {
        $i = $this->pos + $offset;
        return $i < $this->len ? $this->src[$i] : '';
    }

    private function isDigit($ch)
    {
        return $ch !== '' && ctype_digit($ch);
    }

    private function isIdentStart($ch)
    {
        return ctype_alpha($ch) || $ch === '_' || $ch === '$';
    }

    private function isIdentPart($ch)
    {
        return ctype_alnum($ch) || $ch === '_' || $ch === '$';
    }
}
