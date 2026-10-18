# DESIGN.md — MH ScarfFRONT DESIGN SYSTEM v2

> **Purpose:** Visual source of truth for the MH Scarf hijab e-commerce storefront.
>
> **Brand:** MH Scarf
>
> **Direction:** Soft Luxury Feminine + Editorial Fashion Commerce
>
> **Stack:** Laravel 13 + Blade + Tailwind CSS v4
>
> **Implementation rule:** Read this file before creating or modifying customer-facing UI.

---

# 1. DESIGN NORTH STAR

MH Scarf should feel like a **real Indonesian hijab fashion brand**, not a Laravel template and not a generic marketplace.

The previous design direction remains correct in its brand fundamentals:

- Pink
- Cream
- Feminine
- Floral
- Elegant
- Premium

However, the UI must become more **editorial, confident, visual, and commerce-focused**.

## Core visual statement

> **Soft femininity with a modern editorial fashion-store experience.**

Think:

```text
Premium modest-fashion editorial
        +
Modern Indonesian e-commerce
        +
Subtle botanical identity
```

Do NOT make the site feel like:

```text
❌ Generic CRUD dashboard
❌ Marketplace clone
❌ Pink children's website
❌ Bubble-heavy SaaS UI
❌ Every section inside a rounded white card
❌ Floral decoration everywhere
```

The brand personality should be:

```text
GRACEFUL
CONFIDENT
SOFT
MODERN
FASHIONABLE
WARM
TRUSTWORTHY
```

---

# 2. DESIGN BENCHMARK / REFERENCE DIRECTION

The following websites are references for **information architecture, merchandising, editorial composition, and shopping experience only**.

Do not copy their branding, logo, typography, images, copy, or exact layouts.

## Lozy

Reference:
https://lozy.id/

Useful lessons:

- Strong product/category merchandising.
- Large product taxonomy makes it easy to browse by material/series.
- Best Seller and product-group navigation are prominent.
- The store feels like a fashion catalog rather than a generic shop grid.

Source observed from the current site: extensive navigation around product families such as Paris, Voal, Rayon, Viscose, Tencel, Silk, Jersey, and Ceruty, plus Best Seller groupings.

## Elzatta

Reference:
https://elzatta.com/

Useful lessons:

- Strong editorial product storytelling.
- Product photography receives large visual space.
- Product pages explain comfort, coverage, material, finishing, and color choice.
- Shopping is supported by useful decision aids such as color/undertone guidance.
- Hero/product storytelling is more editorial than a basic product grid.

Source observed from the current site: hero product storytelling, feature blocks for Comfort/Coverage/Material/Finishing, color discovery, and an undertone guide.

## Kenan Hijab

Reference:
https://kenanhijab.co.id/home/

Useful lessons:

- Promotional merchandising is visually prominent.
- Product cards communicate sale pricing clearly.
- New Arrival and Popular product groups create shopping momentum.
- Editorial/news and brand activity are mixed with commerce content.
- The homepage combines products, promotions, social proof/brand activity, and offline-store information.

Source observed from the current site: promotional product sections, New Arrival/Popular groupings, offline-store links, and editorial/news content.

## MH Scarf decision

Borrow the **principles**, not the identity:

```text
Lozy:
Category depth + merchandising

Elzatta:
Editorial storytelling + product education

Kenan:
Promotion + new arrivals + brand activity

MH Scarf:
Soft luxury + floral identity + clean editorial commerce
```

---

# 3. VISUAL HIERARCHY

The old system was too card-heavy.

New rule:

> **Not every section needs a card.**

Use a mixture of:

- full-bleed image sections
- editorial split layouts
- product grids
- soft-background sections
- whitespace-led sections
- thin dividers
- floating badges
- occasional cards

## Visual priority

```text
1. Fashion/product photography
2. Brand message
3. Product information
4. CTA
5. Supporting UI
6. Decoration
```

Floral decoration is always last in priority.

---

# 4. COLOR SYSTEM — KEEP THE BRAND

The brand colors remain unchanged.

## Primary

```text
--primary-pink: #EFA7C1
--deep-pink: #D98FAF
--soft-pink: #F8C8DC
```

## Neutrals

```text
--cream: #FFF9F5
--white: #FFFFFF
--surface: #FCF7F8
--text-primary: #3A3033
--text-secondary: #75686D
--border: #EBDDE2
```

## Accent

```text
--gold: #C9A227
--mauve: #B97897
```

## Color ratios

Approximate visual balance:

```text
50–60% white / cream
20–25% photography / natural product colors
10–15% soft pink
5–10% deep pink / mauve
<5% gold
```

## Important change

Do NOT tint every element pink.

Pink should identify:

- CTA
- active state
- selected state
- editorial highlights
- small brand accents

Cream and white must dominate the canvas.

---

# 5. ART DIRECTION

This is the biggest visual upgrade.

## Photography direction

Prefer:

- editorial hijab photography
- soft daylight
- natural skin tones
- textured fabric close-ups
- clean studio product shots
- warm neutral environments
- sophisticated female styling
- movement in fabric
- close-up details

## Avoid

- generic stock photography
- overly posed corporate images
- oversaturated pink photos
- heavy artificial glow
- unrealistic AI-looking model imagery
- busy backgrounds

## Product imagery

Product photos should feel consistent:

```text
4:5 ratio
soft light
neutral background
accurate color
consistent crop
```

Where multiple images exist:

1. hero/product image
2. lifestyle image
3. close-up material image
4. color/variant image

---

# 6. TYPOGRAPHY

## Display / Editorial

Primary:

```text
Playfair Display
```

Alternative:

```text
Cormorant Garamond
```

Use for:

- hero headline
- editorial statements
- section headlines
- campaign headlines
- product storytelling headlines

## UI / Commerce

Primary:

```text
Poppins
```

Alternative:

```text
Inter
```

Use for:

- navigation
- prices
- buttons
- filters
- forms
- product metadata
- badges

## Typography hierarchy

Hero:

```text
clamp(2.5rem, 7vw, 5.5rem)
```

Section heading:

```text
clamp(1.75rem, 4vw, 3.25rem)
```

Product title:

```text
0.95rem – 1.05rem
```

Product price:

```text
0.95rem – 1.1rem
```

Micro-label:

```text
0.65rem – 0.75rem
```

Use letter-spacing carefully.

Do NOT make every heading bold.

---

# 7. LAYOUT PHILOSOPHY

## Container

Desktop:

```text
max-width: 1280px
```

Wide editorial sections may use:

```text
max-width: 1440px
```

Mobile:

```text
padding-inline: 16px
```

Tablet:

```text
padding-inline: 24px
```

Desktop:

```text
padding-inline: 32px
```

## Grid

Prefer asymmetric layouts where useful.

Examples:

```text
2/5 + 3/5
3/5 + 2/5
1/3 + 2/3
```

Do not make every section a 50/50 split.

---

# 8. SPACING

Base rhythm:

```text
4
8
12
16
20
24
32
40
48
64
80
96
120
```

Homepage section spacing:

Desktop:

```text
80–120px
```

Mobile:

```text
56–72px
```

Product grids:

```text
16–24px
```

Editorial sections:

```text
64–96px
```

Whitespace should feel intentional and luxurious.

---

# 9. BORDER RADIUS

The previous UI overused rounded containers.

New rule:

> **Use radius strategically, not everywhere.**

Recommended:

```text
Image/media: 16–20px
Product cards: 12–16px
Buttons: 10–12px
Input: 10–12px
Pills: 9999px
Editorial section: often NO container radius
```

Large hero sections may use square/soft corners instead of a giant rounded rectangle.

---

# 10. SHADOWS

Prefer depth through:

- whitespace
- contrast
- image composition
- subtle borders

Default:

```text
0 8px 30px rgba(58, 48, 51, 0.05)
```

Hover:

```text
0 14px 40px rgba(58, 48, 51, 0.09)
```

Do not use shadows on every section.

---

# 11. FLORAL SYSTEM — MORE REFINED

Floral decoration stays, but becomes more sophisticated.

## Preferred

- botanical line-art
- fine rose outlines
- thin stems
- petal silhouettes
- hand-drawn botanical sketches
- pressed-flower inspired elements
- small floral marks

## Placement

Best:

- hero edge
- campaign section edge
- footer corner
- editorial image overlay at very low opacity
- empty states
- divider accents

Avoid:

- repeating flowers on every card
- flowers next to every heading
- flowers behind product prices
- flowers over buttons

## Opacity

```text
8% – 25%
```

Occasionally:

```text
30% max
```

Floral decoration should be discovered, not shouted.

---

# 12. BRAND MARK

Logo direction:

```text
❀ MH Scarf
```

or

```text
MH Scarf
small floral mark
```

Keep it simple.

Do not turn the flower into a complicated logo illustration.

---

# 13. NAVIGATION — PREMIUM FASHION STORE

## Desktop

Recommended:

```text
          MH Scarf

SHOP   COLLECTIONS   ABOUT   JOURNAL        SEARCH  ♡  BAG
```

If the content width allows, account can appear inside a utility area.

## Header behavior

- transparent/overlay over hero where appropriate
- transitions to white/cream when scrolling
- thin bottom border
- restrained shadow
- sticky

This creates a more editorial experience than a permanently boxed navbar.

## Mobile

Use:

```text
☰     MH Scarf     ♡   BAG
```

or:

```text
☰     MH Scarf     BAG
```

Search can expand into a full-width search panel.

Mobile navigation must feel intentional, not like a desktop menu collapsed into a hamburger.

---

# 14. HOMEPAGE — NEW STRUCTURE

Homepage should feel like a fashion campaign.

Recommended order:

```text
1. Announcement bar
2. Hero campaign
3. Category / Shop by style
4. Best sellers
5. Editorial brand story
6. New arrivals
7. Product education / material spotlight
8. Campaign / promotion
9. Social proof / reviews
10. Social gallery
11. Newsletter
12. Footer
```

The homepage must NOT be:

```text
Hero
rounded card
rounded card
rounded card
rounded card
rounded card
```

Instead alternate visual rhythms.

---

# 15. ANNOUNCEMENT BAR

Minimal.

Example:

```text
Free shipping for selected orders ✦
```

or:

```text
New Collection — Discover Your Everyday Favorite
```

Height:

```text
32–38px
```

Do not overcrowd.

---

# 16. HERO — EDITORIAL FIRST

The hero should be the biggest visual upgrade.

## Desktop

Use a large image-led composition.

Preferred structure:

```text
┌──────────────────────────────────────────────┐
│                                              │
│  small eyebrow                              │
│  ELEGANCE IN EVERY WRAP                     │
│                                              │
│  Discover                                    │
│  your everyday                               │
│  signature.                    [MODEL IMAGE] │
│                                              │
│  short copy                     floral edge  │
│  [SHOP COLLECTION]                           │
│                                              │
└──────────────────────────────────────────────┘
```

Do not place the entire hero inside a tiny rounded card.

## Mobile

Stack:

```text
eyebrow
headline
copy
CTA
hero image
```

Image should remain visually dominant.

---

# 17. SHOP BY STYLE / CATEGORY

Instead of generic equal cards, use an editorial arrangement.

Example:

```text
        Shop by Style

[ PASHMINA large ]
[ VOAL ] [ SEGI EMPAT ]
[ SATIN ] [ CERUTY ]
```

Use different card sizes where useful.

Category labels should remain simple.

Use real category data.

---

# 18. BEST SELLER SECTION

Section heading:

```text
Most Loved
```

Supporting line:

```text
Pilihan yang paling sering dipilih untuk menemani hari-harimu.
```

Use a horizontal scroll on mobile when appropriate.

Desktop:
3–4 products

Mobile:
1.2–2 products visible for a premium carousel feel

Product card should show:

- image
- favorite
- badge
- title
- material
- rating
- price
- compare price

Do not put every piece of metadata on the card.

---

# 19. PRODUCT CARD — NEW STANDARD

Product cards should be visually cleaner.

Structure:

```text
┌─────────────────────┐
│                     │
│    PRODUCT IMAGE    │
│                 ♡   │
│                     │
│  BEST SELLER        │
└─────────────────────┘

Product Name
Voal Premium
★★★★★

Rp99.000
Rp129.000
```

### Interaction

On desktop:

- subtle image zoom
- optional image swap on hover
- wishlist animates
- CTA appears subtly

On mobile:

- no hover dependency
- actions visible/tappable

Do not put giant Add to Cart buttons under every product card.

---

# 20. NEW ARRIVALS

Use a more energetic section.

Heading:

```text
Just In
```

Add:

```text
View All →
```

Use actual latest products.

Use small product labels:

```text
NEW
```

not oversized badges.

---

# 21. EDITORIAL BRAND STORY

This is the major missing piece from the old design.

Use an editorial split:

```text
[ Large editorial image ]

                     About MH Scarf
                     A quiet kind of
                     everyday elegance.

                     short brand story

                     [Discover Our Story →]
```

Background:

- cream
- white
- very subtle botanical line-art

Purpose:
Make MH Scarf feel like a **brand**, not only a store.

---

# 22. MATERIAL / PRODUCT EDUCATION

Inspired by the product-education approach visible on premium hijab stores.

Possible blocks:

```text
Why Voal?
Light. Cool. Easy to style.

Why Ceruty?
Flowy texture with an effortless drape.

Why Silk?
Soft sheen for elevated moments.
```

Do NOT invent claims that are not supported by actual product data.

When possible, use product material data from the database.

This section can link directly to filtered products.

---

# 23. PROMOTIONAL CAMPAIGN

Use one strong campaign block instead of many banners.

Example:

```text
THE EVERYDAY EDIT

20% OFF SELECTED STYLES

[SHOP NOW]
```

Visual:

- large image
- pink/cream text panel
- subtle floral decoration

Avoid discount-site aesthetics.

---

# 24. REVIEWS / SOCIAL PROOF

Use actual reviews where possible.

Layout:

```text
“quote”

★★★★★

Customer Name
Verified Purchase
```

If there are not enough reviews:

- use a graceful empty state
- do not fabricate customer claims

Optional:

- customer photo grid
- review photo masonry

---

# 25. SOCIAL GALLERY

A clean visual grid.

Prefer:

```text
2 columns mobile
4 columns desktop
```

Mix:

- product
- lifestyle
- packaging
- detail

Avoid oversized Instagram logo decoration.

---

# 26. NEWSLETTER / COMMUNITY

Instead of a basic input box, create a stronger brand statement.

Example:

```text
A little beauty, delivered.

Join the MH Scarf list for new collections,
special offers, and styling inspiration.

[ Email Address              ][ Join ]
```

Background:
cream/pink

Optional botanical edge.

---

# 27. FOOTER

Footer should feel editorial and premium.

Columns:

```text
MH Scarf
Quietly elegant hijabs for everyday moments.

SHOP
New Arrivals
Best Sellers
All Hijabs
Categories

HELP
Shipping
Returns
Order Tracking
FAQ
Contact

FOLLOW
Instagram
TikTok
WhatsApp
```

Add newsletter/social CTA.

---

# 28. SHOP PAGE

The shop page should feel like a fashion catalog.

Top:

```text
Shop Hijab

short editorial intro
```

Then:

```text
Search
Category
Material
Color
Price
Sort
```

## Desktop

Filter sidebar optional.

## Mobile

Use:

```text
[ Filter ] [ Sort ]
```

as sticky/top controls.

Use a slide-over/bottom sheet instead of a permanently visible sidebar.

---

# 29. PRODUCT DETAIL

Product detail should feel premium and educational.

## Desktop

```text
IMAGE GALLERY      PRODUCT INFO

                    Product Name
                    Material
                    Rating
                    Price
                    Color
                    Variant
                    Quantity
                    Add to Cart
                    Buy Now
```

## Mobile

```text
image
gallery dots
name
rating
price
color
variant
stock
CTA
description
material
reviews
related products
```

CTA can become sticky at the bottom on mobile.

---

# 30. PRODUCT COLOR SELECTION

Use real color swatches.

Swatch:

```text
●
```

Selected state:

- 1–2px dark outline
- subtle ring
- accessible text label

Never rely only on color.

Always include a text label such as:

```text
Dusty Pink
```

---

# 31. CART

Cart should be calm and editorial.

Desktop:

```text
Your Bag

Products                 Summary
                         Subtotal
                         Shipping
                         Total

                         [CHECKOUT]
```

Mobile:

- product first
- summary below
- sticky CTA where appropriate

Do not add decorative flowers between every item.

---

# 32. CHECKOUT

Checkout should maximize trust.

Use:

```text
Contact
Shipping Address
Shipping Method
Payment
Order Summary
```

Keep one clear primary CTA.

No unnecessary navigation distractions.

---

# 33. ORDER DETAIL / TRACKING

Order timeline should be a proper visual component.

Desktop:
horizontal timeline where space allows.

Mobile:
vertical timeline.

Never allow labels to overlap or clip.

Use concise labels:

```text
Dibuat
Dikonfirmasi
Diproses
Dikemas
Dikirim
Tiba
Selesai
```

Descriptions can appear below the active step only.

---

# 34. AUTHENTICATION

Login/register pages should feel branded but not over-designed.

Use:

- cream canvas
- centered form
- simple floral line-art
- serif heading
- pink CTA
- clear validation

Example:

```text
Welcome back.

Masuk untuk melanjutkan
ke koleksi MH Scarf.

[ Email ]
[ Password ]

[ MASUK ]

Belum punya akun?
Daftar →
```

---

# 35. ADMIN UI

Admin remains functional and separate from the fashion storefront.

Style:

```text
white
cream
gray
pink accent
dense information
clear tables
```

Do not add large editorial photography or decorative floral walls to the admin.

---

# 36. COMPONENT SYSTEM

Reusable components should include:

```text
StorefrontLayout
AnnouncementBar
Navbar
MobileNav
SearchOverlay
SectionHeading
EditorialSplit
HeroSection
CategoryMosaic
CategoryCard
ProductCard
ProductGrid
ProductCarousel
PriceDisplay
RatingStars
WishlistButton
ColorSwatch
Badge
PromoCampaign
MaterialSpotlight
ReviewCard
ReviewPhotoGrid
Newsletter
Footer
Toast
EmptyState
FilterDrawer
SortControl
Pagination
OrderTimeline
```

Prefer composition over one giant component.

---

# 37. COMPONENT RULE — AVOID UI FATIGUE

Do not use the same visual box repeatedly.

Example bad:

```text
Card
Card
Card
Card
Card
Card
```

Better:

```text
Editorial image
Product grid
Whitespace
Soft section
Product carousel
Editorial image
Campaign banner
Review strip
```

Visual rhythm matters.

---

# 38. MOBILE-FIRST

This is mandatory.

Build from:

```text
320px+
```

Then enhance for:

```text
640px
768px
1024px
1280px
1536px
```

## Mobile rules

- no horizontal scroll
- minimum 44px touch targets
- buttons full-width when helpful
- 2-column product grid where appropriate
- 1-column editorial sections
- sticky CTA only when useful
- navigation simplified
- filters become drawer/sheet
- text must wrap naturally
- floral decoration must not overlap content

---

# 39. RESPONSIVE PRODUCT GRID

Recommended:

```text
Mobile:
2 columns

Tablet:
2–3 columns

Desktop:
4 columns

Wide:
4–5 columns where content supports it
```

Do not make products too small.

---

# 40. RESPONSIVE HERO

Desktop:

- large visual composition
- asymmetrical layout

Mobile:

- stacked
- image remains large
- text remains readable

Never preserve desktop side-by-side layout on narrow screens.

---

# 41. INTERACTIONS

Preferred:

- 200–350ms
- ease-out
- subtle transform
- image scale 1.02–1.04
- soft opacity transitions

Avoid:

- bouncing
- spinning
- flashing
- excessive parallax
- large motion

---

# 42. ACCESSIBILITY

Required:

- semantic HTML
- alt text
- keyboard focus
- visible focus states
- sufficient color contrast
- labels for inputs
- aria labels for icon-only buttons
- do not rely on hover for essential information
- do not rely on color alone

---

# 43. PERFORMANCE

Prefer:

- Blade + Tailwind
- minimal JavaScript
- native CSS transitions
- lazy-loaded images
- responsive image sizing
- reusable components

Avoid unnecessary frontend frameworks.

Do not add a dependency just to animate a simple element.

---

# 44. SEO / SHARING

Storefront pages should support:

- meaningful `<title>`
- meta description
- Open Graph
- product image alt text
- canonical URLs where appropriate

Product URL:

```text
/product/{product:slug}
```

Category URL:

```text
/shop/{category:slug}
```

---

# 45. BRAND VOICE

Tone:

```text
Warm
Elegant
Confident
Modern
Human
```

Prefer:

```text
Find your everyday favorite.
Elegance in every wrap.
Made for your everyday moments.
Discover your next signature shade.
```

Avoid:

```text
BUY NOW!!!
SUPER CHEAP!!!
BEST DEAL!!!
```

Use Indonesian for most commerce UI.

English may be used selectively for editorial campaign lines.

---

# 46. DO / DON'T

## DO

- Keep pink identity
- Use cream/white as the canvas
- Use editorial photography
- Use subtle floral marks
- Use asymmetric layouts
- Use strong visual merchandising
- Highlight product imagery
- Use clean product cards
- Use real product data
- Make mobile experience excellent
- Alternate between grids and editorial layouts

## DON'T

- Don't make every section a rounded card
- Don't make everything pink
- Don't put flowers everywhere
- Don't use giant shadows
- Don't make every CTA a huge pill
- Don't overcrowd product cards
- Don't make the homepage look like a CRUD app
- Don't use fake reviews
- Don't hardcode product data
- Don't copy reference websites directly
- Don't depend on hover for mobile interactions

---

# 47. PAGE-BY-PAGE MOOD

## Homepage

```text
Editorial
Warm
Aspirational
Fashion-led
```

## Shop

```text
Clean
Curated
Product-first
Easy to filter
```

## Product detail

```text
Premium
Educational
Trustworthy
Conversion-focused
```

## Cart

```text
Minimal
Calm
Clear
```

## Checkout

```text
Trustworthy
Focused
Low distraction
```

## Order tracking

```text
Clear
Reassuring
Structured
```

## Auth

```text
Warm
Simple
Branded
```

## Admin

```text
Functional
Professional
Efficient
```

---

# 48. VIBE CODING RULES

When an AI coding agent works on the project:

1. Read DESIGN.md first.
2. Preserve the existing brand palette.
3. Do not invent a new design language.
4. Prefer editorial layouts over repetitive card stacks.
5. Reuse components.
6. Use real Laravel data.
7. Preserve backend behavior.
8. Build mobile-first.
9. Test narrow viewport layouts.
10. Fix horizontal overflow.
11. Keep floral details subtle.
12. Do not copy reference websites.
13. When a section feels visually flat, improve hierarchy through image scale, typography, spacing, and composition before adding more decoration.
14. Prefer one strong visual idea per section.
15. Do not add unnecessary JavaScript dependencies.

---

# 49. ACCEPTANCE CRITERIA — STOREFRONT

A page is visually complete when:

- It follows this design system.
- Brand colors are consistent.
- Typography is consistent.
- Product photography is dominant.
- Floral decoration is subtle.
- Layout does not rely on repetitive cards.
- Responsive behavior is intentionally designed.
- No horizontal overflow exists.
- Mobile controls are touch-friendly.
- Product data comes from Laravel.
- No fake business data is hardcoded.
- Loading / empty / error states exist where needed.
- Focus states are accessible.
- Page hierarchy is immediately understandable.

---

# 50. FINAL DESIGN GOAL

The final MH Scarf website should create this reaction:

> **"Ini terasa seperti brand hijab premium yang benar-benar punya identitas."**

The customer should be able to:

```text
Discover
   ↓
Feel inspired
   ↓
Understand the product
   ↓
Find a preferred color/material
   ↓
Add to bag
   ↓
Checkout
   ↓
Track the order
```

The interface must be **fashion editorial first, e-commerce second, floral third**.

That balance is what makes MH Scarf feel premium instead of decorative.
