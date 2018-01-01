const rollup = require('rollup')
const babel = require('rollup-plugin-babel')
const uglify = require('rollup-plugin-uglify')

async function build (filename) {
  const bundle = await rollup.rollup({
    entry: `./assets/js/src/${filename}.js`,
    plugins: [
      babel(),
      uglify()
    ]
  })

  await bundle.write({
    format: 'iife',
    dest: `./assets/js/dist/${filename}.js`,
  })
}

['check', 'market'].forEach(build)
