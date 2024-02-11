const esbuild = require("esbuild");
const shouldWatch = process.argv.includes("--watch");

esbuild
  .build({
    sourcemap: "external",
    define: {
      "process.env.NODE_ENV": shouldWatch ? `'production'` : `'development'`,
    },
    entryPoints: [`resources/js/index.js`],
    outfile: `dist/index.js`,
    bundle: true,
    platform: "browser",
    mainFields: ["module", "main"],
    watch: shouldWatch,
    minifySyntax: true,
    minifyWhitespace: true,
  })
  .catch(() => process.exit(1));

const formComponents = [
  "filament-google-geocomplete",
  "filament-google-maps",
  "filament-google-maps-widget",
  "filament-google-maps-entry",
];

formComponents.forEach((component) => {
  esbuild
    .build({
      define: {
        "process.env.NODE_ENV": shouldWatch ? `'production'` : `'development'`,
      },
      entryPoints: [`resources/js/${component}.js`],
      outfile: `dist/cheesegrits/filament-google-maps/${component}.js`,
      bundle: true,
      platform: "neutral",
      mainFields: ["module", "main"],
      watch: shouldWatch,
      minifySyntax: true,
      minifyWhitespace: true,
    })
    .catch(() => process.exit(1));
});
