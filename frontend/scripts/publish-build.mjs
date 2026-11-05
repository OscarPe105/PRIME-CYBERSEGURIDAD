import { copyFile, cp, mkdir } from 'node:fs/promises';

const frontend = new URL('../', import.meta.url);
const backend = new URL('../../backend/public/', import.meta.url);
await mkdir(new URL('assets/', backend), { recursive: true });
await cp(new URL('dist/assets/', frontend), new URL('assets/', backend), { recursive: true });
await copyFile(new URL('public/ejemplo25.csv', frontend), new URL('ejemplo25.csv', backend));
await copyFile(new URL('dist/prime-logo.svg', frontend), new URL('prime-logo.svg', backend));
// Switch the entry point only after all referenced assets are available.
await copyFile(new URL('dist/index.html', frontend), new URL('prime.html', backend));
console.log('Interfaz compilada publicada en backend/public.');
