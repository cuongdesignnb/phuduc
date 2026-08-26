import { readFile } from 'node:fs/promises';
import { resolve } from 'node:path';

const origin = process.argv[2] || process.env.SEO_AUDIT_URL;
const failures = [];

const sourceChecks = [
    ['resources/js/Components/SeoHead.vue', /name=["']keywords["']/i, 'SeoHead must not render meta keywords'],
    ['resources/views/app.blade.php', /name=["']keywords["']/i, 'The server fallback must not render meta keywords'],
    ['app/Services/Admin/Ai/AiContentGenerationService.php', /meta_keywords/i, 'AI must not generate meta keywords'],
];

for (const [file, pattern, message] of sourceChecks) {
    const source = await readFile(resolve(file), 'utf8');
    if (pattern.test(source)) failures.push(`${message}: ${file}`);
}

const inertiaSource = await readFile(resolve('resources/js/app.js'), 'utf8');
if (!/title:\s*\(title\)\s*=>\s*title\s*[,}]/.test(inertiaSource)) {
    failures.push('Inertia must preserve the server SEO title without appending another suffix');
}
if (/\|\|\s*["']Laravel["']/.test(inertiaSource)) {
    failures.push('The client must not fall back to Laravel as a public brand');
}

const localHostPattern = /^(?:localhost|127\.0\.0\.1|::1)$/i;
const requiredIndexableMeta = [
    ['name', 'description'], ['name', 'robots'],
    ['property', 'og:title'], ['property', 'og:description'], ['property', 'og:type'],
    ['property', 'og:url'], ['property', 'og:image'],
    ['name', 'twitter:card'], ['name', 'twitter:title'], ['name', 'twitter:description'], ['name', 'twitter:image'],
];

const tags = (html, name) => html.match(new RegExp(`<${name}\\b[^>]*>`, 'gi')) || [];
const attribute = (tag, name) => tag.match(new RegExp(`\\b${name}=["']([^"']*)["']`, 'i'))?.[1]?.trim() || '';
const tagWithAttribute = (html, key, value) => tags(html, 'meta')
    .find((tag) => attribute(tag, key).toLowerCase() === value.toLowerCase()) || '';
const metaContent = (html, key, value) => attribute(tagWithAttribute(html, key, value), 'content');
const isProductionHttpsUrl = (value) => {
    try {
        const url = new URL(value);
        return url.protocol === 'https:' && !localHostPattern.test(url.hostname);
    } catch {
        return false;
    }
};
const robotsTokens = (value) => value.toLowerCase().split(',').map((token) => token.trim()).filter(Boolean);

function inspectJsonLd(url, html) {
    const scripts = html.matchAll(/<script\b([^>]*)>([\s\S]*?)<\/script\s*>/gi);
    for (const match of scripts) {
        if (attribute(`<script${match[1]}>`, 'type').toLowerCase() !== 'application/ld+json') continue;

        const json = match[2].trim();

        try {
            const value = JSON.parse(json);
            if (!value || typeof value !== 'object') throw new Error('must be an object or array');
            if (JSON.stringify(value).match(/https?:\/\/(?:localhost|127\.0\.0\.1)(?::\d+)?/i)) {
                failures.push(`${url}: JSON-LD contains a localhost URL`);
            }
        } catch (error) {
            failures.push(`${url}: invalid JSON-LD (${error.message})`);
        }
    }
}

async function inspect(url, { indexable = true } = {}) {
    const response = await fetch(url, { redirect: 'follow' });
    const html = await response.text();
    const title = html.match(/<title[^>]*>([\s\S]*?)<\/title>/i)?.[1]?.trim() || '';
    const canonical = attribute(tags(html, 'link').find((tag) => attribute(tag, 'rel').toLowerCase() === 'canonical') || '', 'href');
    const robots = metaContent(html, 'name', 'robots');
    const tokens = robotsTokens(robots);
    const xRobots = robotsTokens(response.headers.get('x-robots-tag') || '');

    if (!response.ok) failures.push(`${url}: expected 2xx, got ${response.status}`);
    if (!title || /laravel/i.test(title) || /(phú đức\s*[|-]\s*){2}|phú đức\s*-\s*phú đức/i.test(title)) {
        failures.push(`${url}: invalid title`);
    }
    if ((tokens.includes('index') && tokens.includes('noindex')) || (tokens.includes('follow') && tokens.includes('nofollow'))) {
        failures.push(`${url}: conflicting robots directives`);
    }
    if (/name=["']keywords["']/i.test(html)) failures.push(`${url}: meta keywords is still rendered`);

    if (indexable) {
        for (const [key, value] of requiredIndexableMeta) {
            if (!metaContent(html, key, value)) failures.push(`${url}: missing ${value} META`);
        }
        if (!canonical) failures.push(`${url}: missing canonical`);
        else if (!isProductionHttpsUrl(canonical)) failures.push(`${url}: invalid or localhost canonical`);
        if (!tokens.includes('index') || !tokens.includes('follow') || tokens.includes('noindex') || tokens.includes('nofollow')) {
            failures.push(`${url}: indexable page has invalid robots META`);
        }
        if (xRobots.includes('noindex') || xRobots.includes('nofollow')) {
            failures.push(`${url}: indexable page has a conflicting X-Robots-Tag`);
        }
    } else if (!xRobots.includes('noindex') || !xRobots.includes('nofollow')) {
        failures.push(`${url}: missing X-Robots-Tag noindex, nofollow`);
    }

    inspectJsonLd(url, html);
}

if (origin) {
    const base = origin.replace(/\/$/, '');
    if (!isProductionHttpsUrl(base)) failures.push('SEO_AUDIT_URL must be an absolute HTTPS, non-localhost URL');

    await inspect(`${base}/`);
    await inspect(`${base}/san-pham`);
    await inspect(`${base}/tin-tuc`);
    await inspect(`${base}/gioi-thieu`);
    await inspect(`${base}/login`, { indexable: false });
    await inspect(`${base}/gio-hang`, { indexable: false });

    const sitemap = await fetch(`${base}/sitemap.xml`);
    const sitemapBody = await sitemap.text();
    const locations = [...sitemapBody.matchAll(/<loc>([^<]+)<\/loc>/g)].map((match) => match[1]);
    if (!sitemap.ok || !/application\/xml/i.test(sitemap.headers.get('content-type') || '') || !/<urlset\b/.test(sitemapBody)) {
        failures.push('sitemap.xml is not valid XML sitemap output');
    }
    if (locations.length === 0 || locations.some((location) => !isProductionHttpsUrl(location))) {
        failures.push('sitemap.xml contains an invalid or localhost URL');
    }
    if (locations.some((location) => /(gio-hang|thanh-toan|tra-cuu|login|register|password|dashboard|profile|admin|\?)/.test(location))) {
        failures.push('sitemap.xml contains a private or query URL');
    }

    const robots = await fetch(`${base}/robots.txt`);
    const robotsBody = await robots.text();
    if (!robots.ok || !robotsBody.includes(`Sitemap: ${base}/sitemap.xml`)) {
        failures.push('robots.txt does not declare the canonical sitemap URL');
    }
}

if (failures.length) {
    console.error('SEO audit failed:\n- ' + failures.join('\n- '));
    process.exit(1);
}

console.log(origin ? `SEO audit passed for ${origin}` : 'Static SEO audit passed (set SEO_AUDIT_URL to crawl an environment).');
