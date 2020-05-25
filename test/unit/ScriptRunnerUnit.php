<?php
namespace Test\Unit;

use Utils\ScriptRunner;

class ScriptRunnerUnit extends \TestTemplate
{
    protected function setUp()
    {
        parent::setUp();
    }
    public function testBasicSafeScript()
    {
        $script = <<<JS
function main(k) {
return k + Math.max(a, b);
}
const c = a * 8 + b;
main(c);
JS;
        $this->assertEquals(264, ScriptRunner::executeJs(['a' => 8, 'b' => 100], $script));
    }
/*
    public function testScriptWithLongExecutionTime()
    {
        $script = <<<JS
let c = a * 8 + b;
let n = 0;
while(n < 10000) {
  console.log(new Date().getTime());
  n++;
}
(c + Math.max(a, b));
JS;
        $this->assertEquals(264, ScriptRunner::executeJs(['a' => 8, 'b' => 100], $script));
    }

    public function testNeverEndingScript()
    {
        $script = <<<JS
let c = a * 8 + b;
while(1) {
  console.log(new Date().getTime());
}
(c + Math.max(a, b));
JS;
        $this->assertEquals('', ScriptRunner::executeJs(['a' => 8, 'b' => 100], $script));
    }

    public function testScriptWithRequire()
    {
        $script = <<<JS
const fs = require('fs');
let c = a * 8 + b;
let n = 0;
while(n < 10000) {
  console.log(new Date().getTime());
  n++;
}
(c + Math.max(a, b));
JS;
        $this->assertEquals('', ScriptRunner::executeJs(['a' => 8, 'b' => 100], $script));
    }

    public function testAccessFileSystem1()
    {
        $script = <<<JS
__dirname
JS;
        $this->assertEquals('', ScriptRunner::executeJs([], $script));
    }

    public function testAccessFileSystem2()
    {
        $script = <<<JS
const fs = module.constructor._load('fs');
console.log(fs.readFileSync('./test.txt'));
JS;
        $this->assertEquals('', ScriptRunner::executeJs([], $script));
    }

    public function testAccessProcess()
    {
        $script = <<<JS
process.exit(22);
JS;
        $this->assertEquals('', ScriptRunner::executeJs([], $script));
    }

    public function testScriptWithImport()
    {
        $script = <<<JS
import { readFileSync } from 'fs'
let c = a * 8 + b;
(c + Math.max(a, b));
JS;
        $this->assertEquals('', ScriptRunner::executeJs(['a' => 8, 'b' => 100], $script));
    }

    public function testDoNotWaitForPromisesToResolve()
    {
        $script = <<<JS
function test(){
  let k = new Promise((resolve, reject) => { resolve(val) });
  let n = 0;
  while (n < 2) {
    k = k.then((val) => new Promise((resolve, reject) => { resolve(val + 1); }));
    n ++;
  }
  return k;
}

async function main() {
  await test();
}
main();
val;
JS;
        $res = ScriptRunner::executeJs(['val' => 100], $script);

        $this->assertEquals(100, $res);
    }

    public function testNeverEndingPromiseChain()
    {
        $script = <<<JS
function test(){
  let k = new Promise((resolve, reject) => { resolve(val) });
  while (1) {
    k = k.then((val) => new Promise((resolve, reject) => { resolve(val + 1); }));
  }
  return k;
}

async function main() {
  await test();
}
main();
val;
JS;
        $res = ScriptRunner::executeJs(['val' => 100], $script);

        $this->assertEquals('', $res);
    }
*/
}
