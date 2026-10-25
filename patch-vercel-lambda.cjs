const fs = require('fs');
const path = require('path');

const dir = '.vercel/output/functions/api/index.func';
const configPath = path.join(dir, '.vc-config.json');
const config = JSON.parse(fs.readFileSync(configPath, 'utf8'));

NODEole.log('Mapping launcher-orig.js:', map['launcher-orig.js']);er.js')));;
root@160f321028c8:/workspace# 
node patch-vercel-lambda.cjs

echo
echo "=== VERIFIKASI FILE ==="
ls -la .vercel/output/functions/api/index.func/launcher.js

echo
echo "=== VERIFIKASI KONFIGURASI ==="
node -e "const c=require('./.vercel/output/functions/api/index.func/.vc-config.json'); console.log('Handler:',c.handler); console.log('Launcher mapping:',c.filePathMap['launcher.js']); console.log('Original mapping:',c.filePathMap['launcher-orig.js'])"
Bridge dibuat: true
Handler: launcher.launcher
Mapping launcher.js: launcher.js
Mapping launcher-orig.js: .vercel/builders/node_modules/vercel-php/dist/launchers/builtin.js

=== VERIFIKASI FILE ===
-rw-r--r-- 1 root root 559 Oct 20 18:32 .vercel/output/functions/api/index.func/launcher.js

=== VERIFIKASI KONFIGURASI ===
Handler: launcher.launcher
Launcher mapping: launcher.js
Original mapping: .vercel/builders/node_modules/vercel-php/dist/launchers/builtin.js
root@160f321028c8:/workspace# 
Q
EXIT





exit
q
qq


node <<'NODE'
const fs = require('fs');
const path = require('path');

const dir = '.vercel/output/functions/api/index.func';
const config = JSON.parse(
  fs.readFileSync(path.join(dir, '.vc-config.json'), 'utf8')
);

for (const key of ['launcher.js', 'launcher-orig.js', 'helpers.js']) {
  const target = config.filePathMap?.[key];
  console.log(`\n${key} => ${target}`);

  if (target) {
    const candidates = [
      path.join(dir, target),
      path.resolve(target),
      path.join('.vercel/output/functions/api/index.func', target)
    ];

    for (const candidate of [...new Set(candidates)]) {
      console.log(
        `  ${candidate}: ${fs.existsSync(candidate) ? 'ADA' : 'tidak ditemukan'}`
      );
    }
  }
}

console.log('\n=== METADATA HANDLER ===');
console.log(JSON.stringify({
  handler: config.handler,
  launcherType: config.launcherType,
  awsLambdaHandler: config.awsLambdaHandler
}, null, 2));
