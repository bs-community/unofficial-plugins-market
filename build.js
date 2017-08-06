const rollup = require('rollup')
const rollupBabel = require('rollup-plugin-babel')
const rollupUglify = require('rollup-plugin-uglify')

async function build (filename) {
  const bundle = await rollup.rollup({
    entry: `./assets/js/src/${filename}.js`,
    plugins: [
      rollupBabel(),
      rollupUglify()
    ]
  })

  await bundle.write({
    format: 'iife',
    dest: `./assets/js/dist/${filename}.js`,
  })
}

['check', 'market'].forEach(build)
