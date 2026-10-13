==============================================================================
MH Scarf STORE — DEMO IMAGE PACKAGE (development / academic use only)
==============================================================================

WHAT THIS PACKAGE ADDS (merge-safe, additive only):
  database/seeders/ProductImageSeeder.php   Laravel seeder (recommended path)
  database/seeders/DemoImageManifest.php    Curated category->photo manifest
  database/seeders/fetch-demo-images.php    Standalone fallback script (optional;
                                            use ONLY if artisan db:seed fails)
  DEMO-IMAGES.md                            Sources + Pexels License notes
  storage/app/public/products/*.jpg         5 verified JPGs (800x1000, 4:5)
  README-INSTALL.txt                        This file

The ZIP mirrors the project root. Extract its contents INTO your existing
project folder so paths merge:
  D:\laragon\www\MH Scarf-store\database\seeders\...
  D:\laragon\www\MH Scarf-store\storage\app\public\products\...
  D:\laragon\www\MH Scarf-store\DEMO-IMAGES.md

NOTHING ELSE in your project is touched. No migrations, no schema changes,
no Blade/CSS/controller/route changes, no cart/wishlist/inventory/checkout/
payment/order/auth/admin logic changes. Existing uploaded images and their
files are NEVER overwritten or deleted by this package's code.

------------------------------------------------------------------------------
STEP 0 — INSPECT YOUR ACTUAL LOCAL DATABASE FIRST (REQUIRED)
------------------------------------------------------------------------------
The seeders match products/categories by SLUG and RELATIONSHIPS only — never
by hardcoded numeric IDs — so they work with any catalog contents. But before
running anything, confirm what exists on YOUR MySQL database:

  cd D:\laragon\www\MH Scarf-store
  D:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan tinker --execute="
    echo 'Products: '.\App\Models\Product::count().PHP_EOL;
    echo 'Images:   '.\App\Models\ProductImage::count().PHP_EOL;
    \App\Models\Product::with('category')->get()->each(fn(\$p) =>
      printf(\"#%d %-45s cat=%-15s imgs=%d%s\", \$p->id, \$p->name,
        \$p->category?->slug ?? '-', \$p->images()->count(), PHP_EOL));
  "

Checklist before proceeding:
  [ ] Category slugs include 'pashmina' and/or 'voal' (the only two categories
      with approved imagery). If YOUR slugs differ (e.g. 'kain-pashmina'),
      edit DemoImageManifest.php CATEGORY_PHOTOS keys to match YOUR slugs.
      Do NOT map a photo to an unrelated category just to fill a card.
  [ ] Products that already show imgs>0 will be SKIPPED automatically.
  [ ] The 2 existing product_images records must remain untouched after the
      run (compare counts at the end).

------------------------------------------------------------------------------
STEP 1 — EXTRACT (merge, do not replace folders wholesale)
------------------------------------------------------------------------------
If prompted to overwrite database/seeders/ProductImageSeeder.php etc., accept
only if you previously copied an older draft of these same files. Never
overwrite CatalogSeeder.php or DatabaseSeeder.php (not included here).

IMPORTANT: if a file named exactly like one of the 5 bundled JPGs already
exists in your storage/app/public/products/, DO NOT overwrite it — rename the
bundled copy or skip it; the seeder itself never overwrites existing files.

------------------------------------------------------------------------------
STEP 2 — RUN THE SEEDER
------------------------------------------------------------------------------
  cd D:\laragon\www\MH Scarf-store
  D:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan db:seed --class=ProductImageSeeder --force

Expected output lines:
  Image <product name> <- Pexels #8063385 (category 'pashmina')
  Image <product name> <- Pexels #16314165 (category 'voal')
  Skip  <product name> (already has images)          <- for your 2 uploads
  PEND  <product name> — category '...' has no approved imagery yet.

Notes:
  * The bundled JPGs mean NO download is needed for pashmina/voal products:
    the seeder finds the file already on disk and only inserts DB rows.
    (It CAN re-download if a file is missing and your network allows.)
  * Re-running is IDEMPOTENT: products with any image row are skipped;
    no duplicate rows, no file overwrites.

------------------------------------------------------------------------------
STEP 3 — STORAGE LINK (only if missing)
------------------------------------------------------------------------------
  D:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan storage:link

Skip if public/storage already points to storage/app/public (do not recreate
a valid symlink). Images then serve at:
  http://127.0.0.1/storage/products/<filename>.jpg

------------------------------------------------------------------------------
STEP 4 — VERIFY
------------------------------------------------------------------------------
  D:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan tinker --execute="
    echo 'Images now: '.\App\Models\ProductImage::count().PHP_EOL;
    foreach (\App\Models\ProductImage::where('image_path','like','products/demo-%')->get() as \$i) {
      \$disk = \Illuminate\Support\Facades\Storage::disk('public');
      printf('prod %d -> %s exists=%s url=%s%s', \$i->product_id, \$i->image_path,
        var_export(\$disk->exists(\$i->image_path), true),
        \Illuminate\Support\Facades\Storage::url(\$i->image_path), PHP_EOL);
    }
  "

Then open the homepage in your browser and check Shop By Category, Best
Sellers, New Arrivals, and Hero. (Browser verification was NOT performed by
the package author — please do it locally.)

ROLLBACK (removes ONLY demo rows/files, never admin uploads):
  php artisan tinker --execute="\App\Models\ProductImage::where('image_path','like','products/demo-%')->delete();"
  del storage\app\public\products\demo-*.jpg

------------------------------------------------------------------------------
STILL MISSING AFTER THIS PACKAGE (documented honestly)
------------------------------------------------------------------------------
  * Categories segi-empat, satin, ceruty, hijab-instant, hijab-premium have
    NO approved imagery — intentionally left empty rather than filled with
    unrelated photos. Add curated entries to DemoImageManifest.php (verified
    hijab/modest-fashion photos from Unsplash/Pexels) or upload via Admin.
  * Hero/editorial/banners: no dedicated campaign assets exist; sections use
    real featured-product imagery where available, else a labeled fallback.
  * All demo photos are illustrative stock/model imagery — NOT verified
    photographs of actual merchandise. Replace before production launch.
  * Unsplash photos could not be fetched from the authoring sandbox (network
    blocked); only the two Pexels sources above were downloaded and verified.

License: Pexels License (https://www.pexels.com/license/). Details and source
page URLs in DEMO-IMAGES.md.
==============================================================================
