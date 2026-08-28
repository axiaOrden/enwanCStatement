import assert from 'node:assert/strict';
import test from 'node:test';

import {
    normalizeBasePath,
    viteBuildBase,
    withBasePath,
} from '../../resources/js/base-path.js';
import viteConfig from '../../vite.config.js';

test('normalizes root and nested application base paths', () => {
    assert.equal(normalizeBasePath(''), '/');
    assert.equal(normalizeBasePath('/'), '/');
    assert.equal(normalizeBasePath('ng/fixporter/'), '/ng/fixporter/');
    assert.equal(normalizeBasePath('/ng/fixporter'), '/ng/fixporter/');
});

test('creates the Vite production asset base', () => {
    assert.equal(viteBuildBase(''), '/build/');
    assert.equal(viteBuildBase('ng/fixporter/'), '/ng/fixporter/build/');
});

test('applies the configured base path only to production builds', () => {
    const previous = process.env.VITE_APP_BASE_PATH;
    try {
        process.env.VITE_APP_BASE_PATH = 'ng/fixporter/';

        assert.equal(
            viteConfig({ command: 'build', mode: 'production' }).base,
            '/ng/fixporter/build/',
        );
        assert.equal(
            viteConfig({ command: 'serve', mode: 'development' }).base,
            undefined,
        );
    } finally {
        if (previous === undefined) {
            delete process.env.VITE_APP_BASE_PATH;
        } else {
            process.env.VITE_APP_BASE_PATH = previous;
        }
    }
});

test('resolves application paths without double-prefixing them', () => {
    assert.equal(withBasePath('statements', ''), '/statements');
    assert.equal(withBasePath('/statements', 'ng/fixporter/'), '/ng/fixporter/statements');
    assert.equal(withBasePath('/ng/fixporter/statements', 'ng/fixporter/'), '/ng/fixporter/statements');
    assert.equal(withBasePath('?page=2', 'ng/fixporter/'), '/ng/fixporter/?page=2');
});

test('leaves external and fragment URLs unchanged', () => {
    assert.equal(withBasePath('https://example.com/file', 'ng/fixporter/'), 'https://example.com/file');
    assert.equal(withBasePath('mailto:finance@example.com', 'ng/fixporter/'), 'mailto:finance@example.com');
    assert.equal(withBasePath('#details', 'ng/fixporter/'), '#details');
});
