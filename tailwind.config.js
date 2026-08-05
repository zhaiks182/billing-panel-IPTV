import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['"Space Grotesk"', ...defaultTheme.fontFamily.sans],
                mono: ['"JetBrains Mono"', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                brand: {
                    50: '#e9faf5',
                    100: '#c7f1e3',
                    200: '#95e3cb',
                    300: '#63d5b3',
                    400: '#48cbb0',
                    500: '#34c3a6',
                    600: '#2aa890',
                    700: '#228a76',
                    800: '#1c6f5f',
                    900: '#134f44',
                    950: '#0a2a24',
                },
                ink: '#0f1720',
                panel: '#16212d',
                'panel-alt': '#1c2a38',
                steel: '#28394a',
                paper: '#e9eef3',
                dim: '#8da0b3',
                'dim-2': '#5d7085',
                amber: '#f2a93c',
                danger: '#e8615d',
            },
        },
    },

    plugins: [forms],
};
