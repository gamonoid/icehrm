<?php


namespace CreateExtension;


interface CodeEmitter
{
    public function getCode(CodeFormatter $formatter): string;
}