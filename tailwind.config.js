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
                    50: '#f0fdfa',
                    100: '#ccfbf1',
                    500: '#14b8a6',
                    600: '#0d9488',
                    700: '#0f766e',
                },
            },
            boxShadow: {
                glow: '0 24px 80px -32px rgba(13, 148, 136, 0.45)',
            },
        },
    },
    plugins: [],
};
