// Runs the suite in a visible browser with slow motion so a person can watch each step.
// Usage: npm run test:watch [-- <playwright args>]   e.g. npm run test:watch -- tests/uat-04-alur-pesanan.spec.ts
// Slow motion defaults to 400 ms per action; override with E2E_SLOWMO (set it in the shell before running).
import { spawn } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const e2eDir = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');

const child = spawn('npx', ['playwright', 'test', ...process.argv.slice(2)], {
  cwd: e2eDir,
  stdio: 'inherit',
  shell: process.platform === 'win32',
  env: { ...process.env, E2E_HEADED: '1', E2E_SLOWMO: process.env.E2E_SLOWMO ?? '400' },
});
child.on('exit', (code) => process.exit(code ?? 1));
