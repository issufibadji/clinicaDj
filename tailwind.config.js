import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
        './app/View/Components/**/*.php',
    ],

    safelist: [
        // Badge colors gerados dinamicamente
        'bg-emerald-100', 'text-emerald-700', 'dark:bg-emerald-900/30', 'dark:text-emerald-400',
        'bg-red-100',     'text-red-700',     'dark:bg-red-900/30',     'dark:text-red-400',
        'bg-amber-100',   'text-amber-700',   'dark:bg-amber-900/30',   'dark:text-amber-400',
        'bg-blue-100',    'text-blue-700',    'dark:bg-blue-900/30',    'dark:text-blue-400',
        'bg-gray-100',    'text-gray-700',    'dark:bg-gray-900/30',    'dark:text-gray-400',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Kode Mono', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                primary: {
                    50:  '#ecfdf5',
                    100: '#d1fae5',
                    200: '#a7f3d0',
                    300: '#6ee7b7',
                    400: '#34d399',
                    500: '#10b981',
                    600: '#059669',
                    700: '#047857',
                    800: '#065f46',
                    900: '#064e3b',
                    950: '#022c22',
                    DEFAULT: '#10b981',
                },
                secondary: {
                    DEFAULT: '#F59E0B',
                    400: '#fbbf24',
                    500: '#f59e0b',
                    600: '#d97706',
                },
                sidebar: '#1E293B',
            },
        },
    },

    plugins: [forms, typography],
};
