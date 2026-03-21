import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        // "./node_modules/flowbite/**/*.js",
        "./src/**/*.{vue,js,ts,jsx,tsx}",
        "./src/**/*.{html,js}"
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: [ "Public Sans, sans-serif", ...defaultTheme.fontFamily.sans ],
                almarai: [ "Almarai, sans-serif", ...defaultTheme.fontFamily.sans ],
            },
            colors: {
                mainColor: '#12afe5',
                lightMainColor: 'rgb(154 229 255)',
                primary: {
                    "50": "#eff6ff",
                    "100": "#dbeafe",
                    "200": "#bfdbfe",
                    "300": "#93c5fd",
                    "400": "#60a5fa",
                    "500": "#3b82f6",
                    "600": "#2563eb",
                    "700": "#1d4ed8",
                    "800": "#1e40af",
                    "900": "#1e3a8a",
                    "950": "#172554"
                },
                'hb-blue': {
                    "50": '#F1F8FE',
                    "100": '#E1EFFD',
                    "200": "#F0F9FF",
                    "400": '#41A7EF',
                    "500": '#188BDF',
                    "600": '#0B6EBE',
                    "700": '#0A579A',
                    "900": '#113F69',
                    "950": '#0B2846'
                },
                'hb-gray': {
                    "50": '#F6F8F9',
                    "100": '#E9ECEE',
                    "300": '#B4C0C5',
                    "500": '#6D818A',
                    "700": '#47555D',
                    "800": '#3D494F',
                    "900": '#363F44',
                    "950": '#24292D'
                },
                'hb-green': {
                    "300": '#ECFFEE',
                    "400": '#8AFF9D',
                    "900": '#022307',
                },
                'hb-yellow': {
                    '300': '#FFF8EA',
                    '400': '#FFCB52',
                    '900': '#302000',
                },
                'hb-purple': {
                    '300': '#F2EDFF',
                    '400': '#DDD3FB',
                    '900': '#150542',
                },
                'hb-red': {
                    '500': '#FE2F18',
                },
                hbPrimary: '#0093FF',
                hbSecondary: '#60EF5A',
                'hb-primary': '#0C5EA1',
                'hb-dark': '#0B2846',
                'hb-gray-primary': '#0C5EA114',
                "hb-gray-1": '#707070',
                "hb-gray-2": '#EBEBEB',
                "hb-gray-3": '#f7f8f9',
                "hb-stroke": '#e5e7eb',
                "hb-primary-bg": '#F1F8FE',
                "hb-bg-neutral": '#F4F6F8',
                "hb-secondary": '#637381',
                "hb-text-secondary": '#576972',
                "hb-text-primary": '#212B36',
                "hb-text-disabled": '#9DA9B5',
                "hb-disabled-bg": '#B1BECB',
                "blue-color": '#0093ff',
                "tree-color": '#60ef5a',
                'wrong-color': '#fdf7e7',
                'action-hover': '#919EAB14',
                success: {
                    bg: '#22C55E29',
                    text: '#118D57',
                },
                neutral: {
                    bg: '#919EAB29',
                    text: '#637381',
                },
                warning: {
                    bg: '#FFAB0029',
                    text: '#B76E00',
                },
                info: {
                    bg: '#00B8D929',
                    text: '#006C9C',
                },
                danger: {
                    bg: '#FF563029',
                    text: '#B71D18',
                },
                purple: {
                    bg: '#8E33FF29',
                    text: '#5119B7',
                },
            },
            screens: {
                'xs': '320px',
                'sm': '481px',
                'md': '768px',
                'lg': '992px',
                'xl': '1200px',
                '2xl': '1536px',
                '3xl': '1920px',
            },
            borderRadius: {
                '9px': '9px',
            },
            spacing: {
                '7.5': '1.95rem'
            },
            boxShadow: {
                'glow': '0 4px 6px 0 rgba(0, 0, 0, 0.2), 0 6px 10px 0 rgba(0, 0, 0, 0.19)',
                'table': '0px 0px 2px 0px rgba(145,158,171,0.20), 0px 12px 24px -4px rgba(145,158,171,0.12)'
            }
        },
    },
    plugins: [forms, typography],
};
