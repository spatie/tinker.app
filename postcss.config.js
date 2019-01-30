module.exports = {
    plugins: [
        require('postcss-easy-import')(),
        require('tailwindcss')('./tailwind.js'),
        require('postcss-cssnext')({
            features: {
                rem: false,
                customProperties: {
                    preserve: true,
                    warnings: false,
                },
            },
        }),
    ],
};
