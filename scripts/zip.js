const JSZip = require('jszip')
const glob = require('glob')
const fs = require('fs')
const path = require('path')
const { version } = require('../package.json')

const zip = (new JSZip()).folder('unofficial-plugins-market')
const root = zip.folder('unofficial-plugins-market')

function addDir (dir) {
  fs.readdirSync(dir).forEach(f => {
    const filename = dir + '/' + f
    const stat = fs.statSync(filename)
    if (stat.isDirectory()) {
      addDir(filename)
    } else if (stat.isFile()) {
      const location = dir.split('/').reduce((carry, name) => carry.folder(name), root)
      location.file(f, fs.readFileSync(filename))
    }
  })
}

;['bootstrap.php', 'callbacks.php', 'LICENSE', 'package.json'].forEach(file => {
  root.file(file, fs.readFileSync(file))
})

;['src', 'views', 'lang', 'assets/css', 'assets/js/dist'].forEach(addDir)

zip.generateNodeStream({
  streamFiles: true,
  compression: 'DEFLATE',
  compressionOptions: { level: 9 }
}).pipe(fs.createWriteStream(`unofficial-plugins-market_${version}.zip`))
