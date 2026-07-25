let fallbackCounter = 0;

export const stableClientKey = (prefix = 'item') => {
    if (globalThis.crypto?.randomUUID) return `${prefix}-${globalThis.crypto.randomUUID()}`;
    fallbackCounter += 1;
    return `${prefix}-local-${fallbackCounter}`;
};
