const runtimeBasePath = typeof import.meta.env === 'object'
    ? import.meta.env.VITE_APP_BASE_PATH
    : '';

export function normalizeBasePath(basePath = '') {
    const normalized = String(basePath ?? '')
        .trim()
        .replace(/^\/+|\/+$/g, '');

    return normalized === '' ? '/' : `/${normalized}/`;
}

export function viteBuildBase(basePath = '') {
    return `${normalizeBasePath(basePath)}build/`;
}

export function withBasePath(path = '', basePath = runtimeBasePath) {
    const value = String(path ?? '').trim();

    if (/^(?:[a-z][a-z\d+.-]*:|\/\/|#)/i.test(value)) {
        return value;
    }

    const base = normalizeBasePath(basePath);

    if (value === '') {
        return base;
    }

    if (value.startsWith('?')) {
        return `${base}${value}`;
    }

    const absolutePath = `/${value.replace(/^\/+/, '')}`;

    if (base !== '/' && (absolutePath === base.slice(0, -1) || absolutePath.startsWith(base))) {
        return absolutePath;
    }

    return base === '/' ? absolutePath : `${base}${absolutePath.slice(1)}`;
}

export const appBasePath = normalizeBasePath(runtimeBasePath);
