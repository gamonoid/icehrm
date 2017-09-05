<?php

namespace Gettext\Utils;

use Gettext\Extractors\PhpCode;

class PhpFunctionsScanner extends FunctionsScanner
{
    /**
     * PHP tokens of the code to be parsed.
     *
     * @var array
     */
    protected $tokens;

    /**
     * If not false, comments will be extracted.
     *
     * @var string|false
     */
    protected $extractComments = false;

    /**
     * Enable extracting comments that start with a tag (if $tag is empty all the comments will be extracted).
     *
     * @param string $tag
     */
    public function enableCommentsExtraction($tag = '')
    {
        $this->extractComments = (string) $tag;
    }

    /**
     * Disable comments extraction.
     */
    public function disableCommentsExtraction()
    {
        $this->extractComments = false;
    }

    /**
     * Constructor.
     *
     * @param string $code The php code to scan
     */
    public function __construct($code)
    {
        $this->tokens = array_values(
            array_filter(
                token_get_all($code),
                function ($token) {
                    return !is_array($token) || $token[0] !== T_WHITESPACE;
                }
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        $count = count($this->tokens);
        $bufferFunctions = [];
        /* @var ParsedFunction[] $bufferFunctions */
        $functions = [];
        /* @var ParsedFunction[] $functions */

        for ($k = 0; $k < $count; ++$k) {
            $value = $this->tokens[$k];

            if (is_string($value)) {
                if (isset($bufferFunctions[0])) {
                    switch ($value) {
                        case ',':
                            $bufferFunctions[0]->nextArgument();
                            break;
                        case ')':
                            $functions[] = array_shift($bufferFunctions)->close();
                            break;
                        case '.':
                            break;
                        default:
                            $bufferFunctions[0]->stopArgument();
                            break;
                    }
                }
                continue;
            }

            switch ($value[0]) {
                case T_CONSTANT_ENCAPSED_STRING:
                    //add an argument to the current function
                    if (isset($bufferFunctions[0])) {
                        $bufferFunctions[0]->addArgumentChunk(PhpCode::convertString($value[1]));
                    }
                    break;
                case T_STRING:
                    if (isset($bufferFunctions[0])) {
                        $bufferFunctions[0]->stopArgument();
                    }
                    //new function found
                    for ($j = $k + 1; $j < $count; ++$j) {
                        $nextToken = $this->tokens[$j];
                        if (is_array($nextToken) && $nextToken[0] === T_COMMENT) {
                            continue;
                        }
                        if ($nextToken === '(') {
                            $newFunction = new ParsedFunction($value[1], $value[2]);
                            if ($k > 0 && is_array($this->tokens[$k - 1]) && $this->tokens[$k - 1][0] === T_COMMENT) {
                                $comment = $this->parsePhpComment($this->tokens[$k - 1][1]);
                                if ($comment !== null) {
                                    $newFunction->addComment($comment);
                                }
                            }
                            array_unshift($bufferFunctions, $newFunction);
                            $k = $j;
                        }
                        break;
                    }
                    break;
                case T_COMMENT:
                    if (isset($bufferFunctions[0])) {
                        $comment = $this->parsePhpComment($value[1]);
                        if ($comment !== null) {
                            $bufferFunctions[0]->addComment($comment);
                        }
                    }
                    break;
                default:
                    if (isset($bufferFunctions[0])) {
                        $bufferFunctions[0]->stopArgument();
                    }
                    break;
            }
        }

        return $functions;
    }

    protected function parsePhpComment($value)
    {
        $result = null;
        if ($this->extractComments !== false) {
            if ($value[0] === '#') {
                $value = substr($value, 1);
            } elseif ($value[1] === '/') {
                $value = substr($value, 2);
            } else {
                $value = substr($value, 2, -2);
            }
            $value = trim($value);
            if ($value !== '' && ($this->extractComments === '' || strpos($value, $this->extractComments) === 0)) {
                $result = $value;
            }
        }

        return $result;
    }
}
