# MH Scarf — UI Tahap 1-4 Patch Bundle

Basis patch : commit main `da4c16e` ("fix wishlist button")
HEAD sumber : commit `18a2072` (branch kerja workspace Qwen)
Isi : HANYA 10 file di resources/ (homepage, navbar, footer, pagination,
katalog + bottom-sheet, detail produk, cart, checkout, app.css).
Dikecualikan: .env, .gitignore, node_modules/, public/build, kredensial, backup.

## Cara menerapkan di D:\laragon\www\MH Scarf-store

1. Cadangkan dulu: git switch -c ui-tahap1-4-import
   (idealnya dari commit da4c16e; jika branch Anda sudah maju, gunakan --3way)
2. Cek bersih : git apply --check ui-from-main.patch
3. Terapkan : git apply ui-from-main.patch (atau: git apply --3way ...)
4. Build mandiri : npm install && npm run build (JANGAN salin public/build dari bundle)
5. Review : git diff, lalu commit.

Alternatif: folder resources/ dalam bundle adalah salinan final 1:1 —
boleh ditimpa manual per-file bila patch konflik.
