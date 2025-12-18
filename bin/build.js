import * as esbuild from 'esbuild'

const isDev = process.argv.includes('--dev')

async function compile(options) {
    const context = await esbuild.context(options)

    if (isDev) {
        await context.watch()
    } else {
        await context.rebuild()
        await context.dispose()
    }
}

const defaultOptions = {
    define: {
        'process.env.NODE_ENV': isDev ? `'development'` : `'production'`,
    },
    bundle: true,
    mainFields: ['module', 'main'],
    platform: 'neutral',
    sourcemap: isDev ? 'inline' : false,
    sourcesContent: isDev,
    treeShaking: true,
    target: ['es2020'],
    minify: !isDev,
    loader: {
        '.jpg': 'dataurl',
        '.png': 'dataurl',
        '.svg': 'text',
        '.gif': 'dataurl',
        '.woff': 'file',
        '.woff2': 'file',
        '.data': 'base64',
    },
    plugins: [
        {
            name: 'watchPlugin',
            setup: function (build) {
                build.onStart(() => {
                    console.log(
                        `Build started at ${new Date(
                            Date.now(),
                        ).toLocaleTimeString()}: ${
                            build.initialOptions.outfile
                        }`,
                    )
                })

                build.onEnd((result) => {
                    if (result.errors.length > 0) {
                        console.log(
                            `Build failed at ${new Date(
                                Date.now(),
                            ).toLocaleTimeString()}: ${
                                build.initialOptions.outfile
                            }`,
                            result.errors,
                        )
                    } else {
                        console.log(
                            `Build finished at ${new Date(
                                Date.now(),
                            ).toLocaleTimeString()}: ${
                                build.initialOptions.outfile
                            }`,
                        )
                    }
                })
            },
        },
    ],
}

const formComponents = [
    "filament-google-geocomplete",
    "filament-google-maps",
    "filament-google-maps-widget",
    "filament-google-maps-entry",
]

formComponents.forEach((component) => {
    compile({
        ...defaultOptions,
        entryPoints: [
            `./resources/js/${component}.js`,
        ],
        outfile: `./dist/cheesegrits/filament-google-maps/${component}.js`,
    })
})
