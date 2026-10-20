import { cpSync, mkdirSync } from 'node:fs';

const staticDirectory = new URL('../public/vercel-static/', import.meta.url);

mkdirSync(staticDirectory, { recursive: true });

for (const asset of ['build', 'images', 'favicon.ico', 'robots.txt']) {
    cpSync(new URL(`../public/${asset}`, import.meta.url), new URL(asset, staticDirectory), {
        recursive: true,
    });
}
