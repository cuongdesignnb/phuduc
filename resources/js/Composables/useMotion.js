const prefersReducedMotion = () => typeof window !== 'undefined'
    && window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

let revealObserver = null;
const getRevealObserver = () => {
    if (revealObserver || typeof IntersectionObserver === 'undefined') return revealObserver;
    revealObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-visible');
            revealObserver.unobserve(entry.target);
        });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.12 });
    return revealObserver;
};

/**
 * v-reveal / v-reveal="{ delay: 120, from: 'left' }"
 * Content stays visible when JS, IntersectionObserver or motion is unavailable.
 */
export const vReveal = {
    mounted(el, binding) {
        const observer = getRevealObserver();
        if (!observer || prefersReducedMotion()) return;
        const { delay = 0, from = 'up' } = binding.value || {};
        el.classList.add('motion-reveal', `motion-reveal-${from}`);
        el.style.setProperty('--motion-delay', `${delay}ms`);
        observer.observe(el);
    },
    unmounted(el) {
        revealObserver?.unobserve(el);
    },
};

const parseStat = (text) => {
    const match = String(text || '').match(/^(\D*)([\d.,]+)(.*)$/);
    if (!match) return null;
    const [, prefix, raw, suffix] = match;
    // Vietnamese thousands use "." (1.200); decimals use "," (4,5).
    const thousands = /^\d{1,3}(\.\d{3})+$/.test(raw);
    const decimals = thousands ? 0 : (raw.split(/[.,]/)[1]?.length || 0);
    const value = Number(thousands ? raw.replaceAll('.', '') : raw.replace(',', '.'));
    if (!Number.isFinite(value)) return null;
    return { prefix, suffix, value, decimals, thousands, separator: raw.includes(',') ? ',' : '.' };
};

const formatStat = (stat, current) => {
    let number = stat.decimals ? current.toFixed(stat.decimals) : String(Math.round(current));
    if (stat.thousands) number = number.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    else if (stat.decimals) number = number.replace('.', stat.separator);
    return `${stat.prefix}${number}${stat.suffix}`;
};

/** v-count-up — animates the numeric part of the element text once it is in view. */
export const vCountUp = {
    mounted(el) {
        const stat = parseStat(el.textContent.trim());
        if (!stat || prefersReducedMotion() || typeof IntersectionObserver === 'undefined') return;
        const finalText = el.textContent;
        const observer = new IntersectionObserver(([entry]) => {
            if (!entry.isIntersecting) return;
            observer.disconnect();
            const start = performance.now();
            const duration = 1400;
            const tick = (now) => {
                const progress = Math.min(1, (now - start) / duration);
                const eased = 1 - (1 - progress) ** 3;
                el.textContent = progress < 1 ? formatStat(stat, stat.value * eased) : finalText;
                if (progress < 1) requestAnimationFrame(tick);
            };
            requestAnimationFrame(tick);
        }, { threshold: 0.4 });
        observer.observe(el);
        el._countUpObserver = observer;
    },
    unmounted(el) {
        el._countUpObserver?.disconnect();
    },
};

/** Scrolls a horizontal track by roughly one card. */
export const scrollTrack = (track, direction) => {
    if (!track) return;
    const card = track.querySelector('[data-track-item]');
    const step = card ? card.getBoundingClientRect().width + 20 : track.clientWidth * 0.8;
    track.scrollBy({ left: direction * step, behavior: prefersReducedMotion() ? 'auto' : 'smooth' });
};
