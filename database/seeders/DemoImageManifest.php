<?php

namespace Database\Seeders;

/**
 * MH Scarf STORE — curated demo-image manifest (development / academic use only).
 *
 * Deterministic mapping: category slug -> one verified Pexels photograph that
 * genuinely depicts a hijab / modest-fashion subject. An image is NEVER mapped
 * to an unrelated category merely to fill an empty card: if a category has no
 * entry here, the seeders skip it and report it as pending manual sourcing.
 *
 * VERIFICATION STATUS (2026-10-09, from this sandbox):
 *   - images.pexels.com CDN downloads for BOTH photo IDs below were fetched
 *     successfully and validated as genuine JPEG payloads (magic bytes FF D8 FF).
 *   - The pexels.com HTML pages could NOT be opened from this sandbox (blocked),
 *     so on-page titles/photographer credits are UNVERIFIED here. Both IDs come
 *     from the user-supplied source list whose page slugs describe hijab/modest
 *     fashion subjects. Verify visually before any launch.
 *
 * License: Pexels License (https://www.pexels.com/license/) — free for
 * modification and use, no attribution required (attribution appreciated).
 * See DEMO-IMAGES.md for full notes.
 */
class DemoImageManifest
{
    /**
     * Curated per-category photography. Each entry:
     *   [id] => ['pexels_id' => int, 'alt' => string]
     */
    public const CATEGORY_PHOTOS = [
        // "A hijab woman holding a white scarf" — user-approved source page.
        'pashmina' => ['pexels_id' => 8063385, 'alt' => 'Woman in hijab holding a white scarf'],
        // "Beautiful woman in a hijab" — user-approved source page.
        'voal' => ['pexels_id' => 16314165, 'alt' => 'Portrait of a woman wearing a hijab'],
    ];

    /**
     * Categories with NO approved imagery yet. These MUST be filled manually
     * (admin upload or additional curated stock picks) — never auto-filled.
     */
    public const PENDING_CATEGORIES = ['segi-empat', 'satin', 'ceruty', 'hijab-instant', 'hijab-premium'];

    /** Build the CDN download URL for a manifest entry (approx. 4:5 crop). */
    public static function cdnUrl(int $pexelsId, int $w = 800, int $h = 1000): string
    {
        return "https://images.pexels.com/photos/{$pexelsId}/pexels-photo-{$pexelsId}.jpeg"
            ."?auto=compress&cs=tinysrgb&w={$w}&h={$h}&fit=crop";
    }

    /** Source-page URL recorded for documentation/attribution. */
    public static function sourcePageUrl(int $pexelsId): string
    {
        return "https://www.pexels.com/photo/{$pexelsId}/";
    }
}
