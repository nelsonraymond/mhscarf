<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $inventoryService = app(InventoryService::class);

        // 1. Categories
        $categoriesData = [
            [
                'name' => 'Pashmina',
                'slug' => 'pashmina',
                'description' => 'Koleksi pashmina anggun berbahan lembut, jatuh, dan mudah dibentuk untuk berbagai gaya hijab.',
                'is_active' => true,
            ],
            [
                'name' => 'Segi Empat',
                'slug' => 'segi-empat',
                'description' => 'Hijab segi empat klasik nan praktis dengan ketegakan sempurna di dahi.',
                'is_active' => true,
            ],
            [
                'name' => 'Voal',
                'slug' => 'voal',
                'description' => 'Hijab voal premium dengan sirkulasi udara optimal, sejuk, dan tidak mudah kusut.',
                'is_active' => true,
            ],
            [
                'name' => 'Satin',
                'slug' => 'satin',
                'description' => 'Kilau mewah satin silk untuk acara formal, pesta, dan momen istimewa.',
                'is_active' => true,
            ],
            [
                'name' => 'Ceruty',
                'slug' => 'ceruty',
                'description' => 'Bahan ceruty babydoll bertekstur pasir lembut, flowy, dan berkarakter elegan.',
                'is_active' => true,
            ],
            [
                'name' => 'Hijab Instant',
                'slug' => 'hijab-instant',
                'description' => 'Pilihan praktis langsung pakai tanpa jarum pentul, nyaman untuk aktivitas harian.',
                'is_active' => true,
            ],
            [
                'name' => 'Hijab Premium',
                'slug' => 'hijab-premium',
                'description' => 'Koleksi edisi terbatas dengan material sutra dan finishing laser cut eksklusif.',
                'is_active' => true,
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $catData) {
            $categories[$catData['slug']] = Category::firstOrCreate(
                ['slug' => $catData['slug']],
                $catData
            );
        }

        // Color palettes consistent with DESIGN.md
        $brandColors = [
            ['name' => 'Dusty Pink', 'hex' => '#D98FAF'],
            ['name' => 'Rose Blush', 'hex' => '#EFA7C1'],
            ['name' => 'Soft Cream', 'hex' => '#FFF9F5'],
            ['name' => 'Mauve Blossom', 'hex' => '#B97897'],
            ['name' => 'Nude Almond', 'hex' => '#E8D3C4'],
            ['name' => 'Charcoal Noir', 'hex' => '#3A3033'],
            ['name' => 'Sage Leaf', 'hex' => '#B3BEA9'],
            ['name' => 'Misty Grey', 'hex' => '#C4C2C3'],
        ];

        // 2. 15 Realistic Products
        $productsData = [
            // Pashmina
            [
                'category_slug' => 'pashmina',
                'name' => 'Pashmina Silk Premium',
                'sku' => 'PAS-SLK-001',
                'material' => 'Silk Satin Grade A',
                'base_price' => 89000,
                'compare_at_price' => 109000,
                'is_featured' => true,
                'is_best_seller' => true,
                'description' => 'Pashmina silk premium dengan kilau mewah yang lembut dan tidak licin. Serat sutra halus memberikan efek drapery yang jatuh menawan.',
                'variant_count' => 4,
            ],
            [
                'category_slug' => 'pashmina',
                'name' => 'Pashmina Ceruty Babydoll Armani',
                'sku' => 'PAS-CRT-002',
                'material' => 'Ceruty Babydoll Armani',
                'base_price' => 59000,
                'compare_at_price' => 75000,
                'is_featured' => false,
                'is_best_seller' => true,
                'description' => 'Tekstur pasir halus dan flowy yang anggun. Sangat ringan dan sejuk digunakan seharian.',
                'variant_count' => 4,
            ],
            [
                'category_slug' => 'pashmina',
                'name' => 'Pashmina Plisket Full Ceruty',
                'sku' => 'PAS-PLK-003',
                'material' => 'Ceruty Plisket Lidi',
                'base_price' => 65000,
                'compare_at_price' => null,
                'is_featured' => false,
                'is_best_seller' => false,
                'description' => 'Plisket lidi rapi tanpa garis tengah lipatan. Memberikan volume cantik dan praktis tanpa perlu disetrika.',
                'variant_count' => 3,
            ],

            // Segi Empat
            [
                'category_slug' => 'segi-empat',
                'name' => 'Segi Empat Paris Japan Premium',
                'sku' => 'SGE-PRS-001',
                'material' => 'Katun Paris Japan',
                'base_price' => 45000,
                'compare_at_price' => 55000,
                'is_featured' => false,
                'is_best_seller' => true,
                'description' => 'Katun paris import Jepang original dengan jahit tepi rapi. Tegak paripurna di dahi dan tidak mudah bergeser.',
                'variant_count' => 4,
            ],
            [
                'category_slug' => 'segi-empat',
                'name' => 'Segi Empat Bella Square Polycotton',
                'sku' => 'SGE-BLA-002',
                'material' => 'Double Hycon Polycotton',
                'base_price' => 35000,
                'compare_at_price' => null,
                'is_featured' => false,
                'is_best_seller' => false,
                'description' => 'Hijab sejuta umat andalan harian dengan bahan adem, lembut, dan warna pastel kalem yang manis.',
                'variant_count' => 5,
            ],

            // Voal
            [
                'category_slug' => 'voal',
                'name' => 'Voal Ultrafine Laser Cut',
                'sku' => 'VOL-ULT-001',
                'material' => 'Voal Ultrafine Original',
                'base_price' => 79000,
                'compare_at_price' => 95000,
                'is_featured' => true,
                'is_best_seller' => true,
                'description' => 'Material voal ultrafine kelas atas dengan finishing tepian laser cut bermotif ombak halus.',
                'variant_count' => 4,
            ],
            [
                'category_slug' => 'voal',
                'name' => 'Voal Watersplash Anti Air',
                'sku' => 'VOL-WTR-002',
                'material' => 'Voal Nano Water-Repellent',
                'base_price' => 85000,
                'compare_at_price' => 105000,
                'is_featured' => true,
                'is_best_seller' => false,
                'description' => 'Teknologi tahan percikan air sehingga tidak mudah basah dan kotor saat wudhu maupun minum.',
                'variant_count' => 3,
            ],

            // Satin
            [
                'category_slug' => 'satin',
                'name' => 'Satin Silk Jacquard Floral',
                'sku' => 'STN-JCQ-001',
                'material' => 'Jacquard Silk Textured',
                'base_price' => 99000,
                'compare_at_price' => 125000,
                'is_featured' => true,
                'is_best_seller' => false,
                'description' => 'Embos motif bunga botanical subtle yang tercetak halus di atas kain sutra mewah.',
                'variant_count' => 3,
            ],
            [
                'category_slug' => 'satin',
                'name' => 'Satin Velvet Square Mewah',
                'sku' => 'STN-VLV-002',
                'material' => 'Velvet Silk Sheen',
                'base_price' => 69000,
                'compare_at_price' => 85000,
                'is_featured' => false,
                'is_best_seller' => false,
                'description' => 'Kilau matte yang sopan dan tidak mencolok berlebihan. Cocok dipadukan dengan busana pesta kebaya atau gamis.',
                'variant_count' => 3,
            ],

            // Ceruty
            [
                'category_slug' => 'ceruty',
                'name' => 'Khimar Ceruty 2 Layer Soft Pad',
                'sku' => 'CRT-KHM-001',
                'material' => 'Ceruty Babydoll Double Layer',
                'base_price' => 115000,
                'compare_at_price' => 139000,
                'is_featured' => false,
                'is_best_seller' => true,
                'description' => 'Khimar syari dengan 2 layer kain ceruty tidak menerawang. Dilengkapi soft pad antem yang membingkai wajah.',
                'variant_count' => 3,
            ],
            [
                'category_slug' => 'ceruty',
                'name' => 'Segi Empat Ceruty Syari 130x130',
                'sku' => 'CRT-SYR-002',
                'material' => 'Ceruty Babydoll Jumbo',
                'base_price' => 75000,
                'compare_at_price' => null,
                'is_featured' => false,
                'is_best_seller' => false,
                'description' => 'Ukuran jumbo 130x130 cm untuk styling syari menutup dada dengan sempurna.',
                'variant_count' => 3,
            ],

            // Hijab Instant
            [
                'category_slug' => 'hijab-instant',
                'name' => 'Bergo Jersey Premium Daily',
                'sku' => 'INS-BRG-001',
                'material' => 'Jersey Spandek High Grade',
                'base_price' => 49000,
                'compare_at_price' => 60000,
                'is_featured' => true,
                'is_best_seller' => true,
                'description' => 'Bergo harian anti-ribet berbahan jersey elastis, lembut, dan dingin saat menyentuh kulit.',
                'variant_count' => 4,
            ],
            [
                'category_slug' => 'hijab-instant',
                'name' => 'Pashmina Instant Inner Ciput',
                'sku' => 'INS-PSH-002',
                'material' => 'Ceruty Babydoll + Inner Rayon',
                'base_price' => 79000,
                'compare_at_price' => 99000,
                'is_featured' => false,
                'is_best_seller' => true,
                'description' => 'Pashmina yang sudah menyatu dengan inner ninja rayon, tidak perlu repot memakai jarum atau ciput terpisah.',
                'variant_count' => 4,
            ],

            // Hijab Premium
            [
                'category_slug' => 'hijab-premium',
                'name' => 'MH Scarf Signature Rose Mulberry Silk',
                'sku' => 'PRM-SGN-001',
                'material' => '100% Mulberry Silk',
                'base_price' => 249000,
                'compare_at_price' => 299000,
                'is_featured' => true,
                'is_best_seller' => true,
                'description' => 'Karya istimewa MH Scarf menggunakan 100% serat sutra murbei alami berhias signature botanical rose charm.',
                'variant_count' => 3,
            ],
            [
                'category_slug' => 'hijab-premium',
                'name' => 'Voal Royale Gold Charm Edition',
                'sku' => 'PRM-GLD-002',
                'material' => 'Voal Arabella Royale',
                'base_price' => 149000,
                'compare_at_price' => 180000,
                'is_featured' => true,
                'is_best_seller' => false,
                'description' => 'Ditenun dari benang kualitas istimewa dengan pin logo plat gold anti karat di sudut hijab.',
                'variant_count' => 3,
            ],
        ];

        foreach ($productsData as $pData) {
            $cat = $categories[$pData['category_slug']];

            $product = Product::firstOrCreate(
                ['sku' => $pData['sku']],
                [
                    'category_id' => $cat->id,
                    'name' => $pData['name'],
                    'slug' => Str::slug($pData['name']),
                    'sku' => $pData['sku'],
                    'material' => $pData['material'],
                    'care_instructions' => 'Cuci tangan lembut dengan sabun cair, jangan diperas kencang, setrika suhu rendah.',
                    'base_price' => $pData['base_price'],
                    'compare_at_price' => $pData['compare_at_price'],
                    'is_featured' => $pData['is_featured'],
                    'is_best_seller' => $pData['is_best_seller'],
                    'is_active' => true,
                    'description' => $pData['description'],
                ]
            );

            // Create 2–5 variants per product
            $variantCount = $pData['variant_count'];
            for ($i = 0; $i < $variantCount; $i++) {
                $color = $brandColors[$i % count($brandColors)];
                $varSku = $product->sku.'-V'.($i + 1);

                $existingVariant = ProductVariant::where('sku', $varSku)->first();
                if (! $existingVariant) {
                    $stock = 15 + ($i * 10); // 15, 25, 35, 45 pcs
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'name' => $color['name'],
                        'sku' => $varSku,
                        'color_name' => $color['name'],
                        'color_hex' => $color['hex'],
                        'size' => 'All Size',
                        'additional_price' => 0,
                        'stock_qty' => 0,
                        'low_stock_threshold' => 5,
                        'weight_gram' => 140,
                        'is_active' => true,
                    ]);

                    // Record initial inventory movement
                    $inventoryService->recordMovement(
                        variant: $variant,
                        quantity: $stock,
                        type: 'in',
                        note: 'Initial catalog stock seeding'
                    );
                }
            }
        }
    }
}
