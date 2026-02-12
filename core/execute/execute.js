const { VM } = require('vm2');

const sandbox = JSON.parse(Buffer.from(process.argv[2], 'base64').toString('utf8'));

const script = Buffer.from(process.argv[3], 'base64').toString('utf8');

const vm = new VM({
  timeout: 2000,
  console: 'none',
  sandbox,
  wrapper: 'none',
  eval: false,
  wasm: false,
});

process.stdout.write(`${vm.run(script, 'vm.js')}`);
