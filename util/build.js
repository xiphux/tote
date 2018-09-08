({
    baseUrl: '../js',
    paths: {
        jquery: 'empty:',
        qtip: 'empty:',
        cookies: 'empty:',
        d3: 'empty:',
        Vue: 'empty:',
        axios: 'empty:',
        modernizr: 'ext/modernizr.custom',
        'coffee-script': 'ext/coffee-script',
        cs: 'ext/cs',
    },
    stubModules: ['cs'],
    exclude: ['coffee-script'],
    preserveLicenseComments: false,
});
