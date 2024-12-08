const path = require('path');

module.exports = {
    mode: 'development', // Или 'production' для сборки
    entry: './assets/js/index.js', // Входная точка
    output: {
        filename: 'bundle.js', // Имя выходного файла
        path: path.resolve(__dirname, 'dist'), // Путь к выходной папке
        clean: true, // Очищать выходную папку
    },
    module: {
        rules: [
            {
                test: /\.js$/, // Применить для всех файлов .js
                exclude: /node_modules/, // Исключить папку node_modules
                use: {
                    loader: 'babel-loader', // Использовать babel для транспиляции
                    options: {
                        presets: ['@babel/preset-env'],
                    },
                },
            },
        ],
    },
    devtool: 'source-map', // Генерация исходных карт
};