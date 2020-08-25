module.exports = {
    plugins: [
        require('postcss-inline-svg')({
            paths: [
                './src/svg',
                '../../shared/client/src/svg'
            ]
        }),
        require('autoprefixer')
    ]
};
