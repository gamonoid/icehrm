<?php
namespace CreateExtension;
class PhpFile implements CodeEmitter {

    protected $content = '';

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function getCode(CodeFormatter $formatter): string
    {
        $lines = [];
        $lines[] = '<?php';
        $lines[] = $this->content;
        $lines[] = '';

        return join(PHP_EOL, $lines);
    }
}