module.exports = {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                brand: {
                    50: '#f0f7ff',
                    100: '#e0effe',
                    200: '#bae0fd',
                    300: '#7cc7fb',
                    400: '#38a9f8',
                    500: '#0e8ce9',
                    600: '#0270c9',
                    700: '#0358a1',
                    800: '#074c85',
                    900: '#0c406e',
                },
            },
            boxShadow: {
                glow: '0 24px 80px -32px rgba(14, 140, 233, 0.45)',
            },
        },
    },
    plugins: [],
};
