import flowbite from 'flowbite/plugin';

export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './node_modules/flowbite/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                brand: {
                    50: '#eff8ff',
                    100: '#dbefff',
                    200: '#bfe1ff',
                    300: '#93caff',
                    400: '#60a8ff',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8',
                    800: '#1e40af',
                    900: '#1e3a8a',
                },
            },
        },
    },
    plugins: [flowbite],
};