import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['var(--font-sans)', ...defaultTheme.fontFamily.sans],
                display: ['var(--font-display)', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                'ds-sm': 'var(--ds-shadow-sm)',
                card: 'var(--ds-shadow-card)',
                'card-hover': 'var(--ds-shadow-card-hover)',
                dropdown: 'var(--ds-shadow-dropdown)',
            },
            colors: {
                brand: {
                    DEFAULT: 'rgb(var(--ds-brand-primary) / <alpha-value>)',
                    primary: 'rgb(var(--ds-brand-primary) / <alpha-value>)',
                    hover: 'rgb(var(--ds-brand-hover) / <alpha-value>)',
                    active: 'rgb(var(--ds-brand-active) / <alpha-value>)',
                    soft: 'rgb(var(--ds-brand-soft) / <alpha-value>)',
                    muted: 'rgb(var(--ds-brand-muted) / <alpha-value>)',
                    border: 'rgb(var(--ds-brand-border) / <alpha-value>)',
                    contrast: 'rgb(var(--ds-brand-contrast) / <alpha-value>)',
                    text: 'rgb(var(--ds-brand-text) / <alpha-value>)',
                    'control-border': 'rgb(var(--ds-brand-control-border) / <alpha-value>)',
                    light: 'rgb(var(--ds-brand-soft) / <alpha-value>)',
                },
                content: {
                    primary: 'rgb(var(--ds-content-primary) / <alpha-value>)',
                    secondary: 'rgb(var(--ds-content-secondary) / <alpha-value>)',
                    muted: 'rgb(var(--ds-content-muted) / <alpha-value>)',
                    inverse: 'rgb(var(--ds-content-inverse) / <alpha-value>)',
                },
                line: {
                    DEFAULT: 'rgb(var(--ds-border-default) / <alpha-value>)',
                    strong: 'rgb(var(--ds-border-strong) / <alpha-value>)',
                    subtle: 'rgb(var(--ds-border-subtle) / <alpha-value>)',
                },
                // Deprecated public aliases retained while PR 2B/2C migrate pages.
                ink: {
                    primary: 'rgb(var(--ds-content-primary) / <alpha-value>)',
                    secondary: 'rgb(var(--ds-content-secondary) / <alpha-value>)',
                    light: 'rgb(var(--ds-content-muted) / <alpha-value>)',
                },
                surface: {
                    page: 'rgb(var(--ds-surface-page) / <alpha-value>)',
                    card: 'rgb(var(--ds-surface-card) / <alpha-value>)',
                    muted: 'rgb(var(--ds-surface-muted) / <alpha-value>)',
                    elevated: 'rgb(var(--ds-surface-elevated) / <alpha-value>)',
                    inverse: 'rgb(var(--ds-surface-inverse) / <alpha-value>)',
                    bg: 'rgb(var(--ds-surface-page) / <alpha-value>)',
                    border: 'rgb(var(--ds-border-default) / <alpha-value>)',
                },
                success: {
                    DEFAULT: 'rgb(var(--ds-success) / <alpha-value>)',
                    soft: 'rgb(var(--ds-success-soft) / <alpha-value>)',
                },
                info: {
                    DEFAULT: 'rgb(var(--ds-info) / <alpha-value>)',
                    soft: 'rgb(var(--ds-info-soft) / <alpha-value>)',
                },
                danger: {
                    DEFAULT: 'rgb(var(--ds-danger) / <alpha-value>)',
                    soft: 'rgb(var(--ds-danger-soft) / <alpha-value>)',
                },
                admin: {
                    page: 'rgb(var(--ds-surface-page) / <alpha-value>)',
                    surface: 'rgb(var(--ds-surface-card) / <alpha-value>)',
                    'surface-muted': 'rgb(var(--ds-surface-muted) / <alpha-value>)',
                    border: 'rgb(var(--ds-border-default) / <alpha-value>)',
                    content: 'rgb(var(--ds-content-primary) / <alpha-value>)',
                    'content-muted': 'rgb(var(--ds-content-muted) / <alpha-value>)',
                    accent: 'rgb(var(--ds-brand-primary) / <alpha-value>)',
                    'accent-hover': 'rgb(var(--ds-brand-hover) / <alpha-value>)',
                    danger: 'rgb(var(--ds-danger) / <alpha-value>)',
                    warning: 'rgb(var(--ds-warning) / <alpha-value>)',
                    success: 'rgb(var(--ds-success) / <alpha-value>)',
                    focus: 'rgb(var(--ds-focus-ring) / <alpha-value>)',
                },
                // Admin compatibility; new storefront code must not use this palette.
                industrial: {
                    50: '#eef7ff',
                    100: '#d8edff',
                    200: '#b9dfff',
                    300: '#89ccff',
                    400: '#52aeff',
                    500: '#2a8bfc',
                    600: '#146cf1',
                    700: '#0d55de',
                    800: '#1146b4',
                    900: '#143e8e',
                    950: '#0f2756',
                },
                // Deprecated Admin alias. It now points at canonical theme tokens.
                volt: {
                    50: 'rgb(var(--ds-brand-soft) / <alpha-value>)',
                    100: 'rgb(var(--ds-brand-soft) / <alpha-value>)',
                    200: 'rgb(var(--ds-brand-muted) / <alpha-value>)',
                    300: 'rgb(var(--ds-brand-border) / <alpha-value>)',
                    400: 'rgb(var(--ds-brand-primary) / <alpha-value>)',
                    500: 'rgb(var(--ds-brand-primary) / <alpha-value>)',
                    600: 'rgb(var(--ds-brand-hover) / <alpha-value>)',
                    700: 'rgb(var(--ds-brand-active) / <alpha-value>)',
                    800: 'rgb(var(--ds-brand-active) / <alpha-value>)',
                    900: 'rgb(var(--ds-content-primary) / <alpha-value>)',
                    950: 'rgb(var(--ds-content-primary) / <alpha-value>)',
                },
                // Admin-only legacy palette.
                carbon: {
                    50: '#f6f6f6',
                    100: '#e7e7e7',
                    200: '#d1d1d1',
                    300: '#b0b0b0',
                    400: '#888888',
                    500: '#6d6d6d',
                    600: '#5d5d5d',
                    700: '#4f4f4f',
                    800: '#454545',
                    900: '#1a1a1a',
                    950: '#0d0d0d',
                },
                warning: {
                    DEFAULT: 'rgb(var(--ds-warning) / <alpha-value>)',
                    soft: 'rgb(var(--ds-warning-soft) / <alpha-value>)',
                    400: '#FBBF24',
                    500: '#F59E0B',
                    600: '#D97706',
                }
            },
            animation: {
                'fade-in': 'fadeIn 0.6s ease-out forwards',
                'fade-in-up': 'fadeInUp 0.6s ease-out forwards',
                'fade-in-left': 'fadeInLeft 0.6s ease-out forwards',
                'fade-in-right': 'fadeInRight 0.6s ease-out forwards',
                'slide-up': 'slideUp 0.8s cubic-bezier(0.16,1,0.3,1) forwards',
                'scale-in': 'scaleIn 0.5s ease-out forwards',
                'glow-pulse': 'glowPulse 2s ease-in-out infinite',
                'float': 'float 3s ease-in-out infinite',
                'float-slow': 'float 6s ease-in-out infinite',
                'float-delay': 'floatDelay 4s ease-in-out infinite',
                'marquee': 'marquee 30s linear infinite',
                'counter': 'counter 2s ease-out forwards',
                'draw-line': 'drawLine 1.5s ease-out forwards',
                'spin-slow': 'spin 8s linear infinite',
                'pulse-ring': 'pulseRing 2s ease-out infinite',
                'shimmer': 'shimmer 2.5s ease-in-out infinite',
                'bounce-subtle': 'bounceSubtle 2s ease-in-out infinite',
                'slide-in-right': 'slideInRight 0.5s ease-out forwards',
                'typewriter': 'typewriter 3s steps(30) forwards',
                'blink': 'blink 1s step-end infinite',
                'morph': 'morph 8s ease-in-out infinite',
                'gradient-x': 'gradientX 3s ease infinite',
                'tilt': 'tilt 10s ease-in-out infinite',
            },
            keyframes: {
                fadeIn: {
                    from: { opacity: '0' },
                    to: { opacity: '1' },
                },
                fadeInUp: {
                    from: { opacity: '0', transform: 'translateY(30px)' },
                    to: { opacity: '1', transform: 'translateY(0)' },
                },
                fadeInLeft: {
                    from: { opacity: '0', transform: 'translateX(-30px)' },
                    to: { opacity: '1', transform: 'translateX(0)' },
                },
                fadeInRight: {
                    from: { opacity: '0', transform: 'translateX(30px)' },
                    to: { opacity: '1', transform: 'translateX(0)' },
                },
                slideUp: {
                    from: { opacity: '0', transform: 'translateY(60px)' },
                    to: { opacity: '1', transform: 'translateY(0)' },
                },
                scaleIn: {
                    from: { opacity: '0', transform: 'scale(0.9)' },
                    to: { opacity: '1', transform: 'scale(1)' },
                },
                glowPulse: {
                    '0%, 100%': { boxShadow: '0 0 20px rgba(9,222,82,0.3)' },
                    '50%': { boxShadow: '0 0 40px rgba(9,222,82,0.6)' },
                },
                float: {
                    '0%, 100%': { transform: 'translateY(0px)' },
                    '50%': { transform: 'translateY(-10px)' },
                },
                floatDelay: {
                    '0%, 100%': { transform: 'translateY(0px) rotate(0deg)' },
                    '50%': { transform: 'translateY(-15px) rotate(3deg)' },
                },
                marquee: {
                    from: { transform: 'translateX(0)' },
                    to: { transform: 'translateX(-50%)' },
                },
                drawLine: {
                    from: { width: '0%' },
                    to: { width: '100%' },
                },
                pulseRing: {
                    '0%': { transform: 'scale(1)', opacity: '1' },
                    '100%': { transform: 'scale(2.5)', opacity: '0' },
                },
                shimmer: {
                    '0%': { backgroundPosition: '-200% 0' },
                    '100%': { backgroundPosition: '200% 0' },
                },
                bounceSubtle: {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-5px)' },
                },
                slideInRight: {
                    from: { opacity: '0', transform: 'translateX(40px)' },
                    to: { opacity: '1', transform: 'translateX(0)' },
                },
                typewriter: {
                    from: { width: '0' },
                    to: { width: '100%' },
                },
                blink: {
                    '50%': { borderColor: 'transparent' },
                },
                morph: {
                    '0%, 100%': { borderRadius: '60% 40% 30% 70% / 60% 30% 70% 40%' },
                    '50%': { borderRadius: '30% 60% 70% 40% / 50% 60% 30% 60%' },
                },
                gradientX: {
                    '0%, 100%': { backgroundPosition: '0% 50%' },
                    '50%': { backgroundPosition: '100% 50%' },
                },
                tilt: {
                    '0%, 50%, 100%': { transform: 'rotate(0deg)' },
                    '25%': { transform: 'rotate(1deg)' },
                    '75%': { transform: 'rotate(-1deg)' },
                },
            },
            backgroundImage: {
                'grid-pattern': 'linear-gradient(rgba(255,255,255,.03) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px)',
                'radial-glow': 'radial-gradient(ellipse at center, rgba(9,222,82,0.15) 0%, transparent 70%)',
            },
            backgroundSize: {
                'grid-40': '40px 40px',
            },
        },
    },

    plugins: [forms],
};
