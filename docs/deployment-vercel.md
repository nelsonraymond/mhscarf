# Vercel deployment notes

Set `APP_URL` in the Vercel Project Settings for the Production environment to
the actual public site URL after its domain is known. It is intentionally not
hard-coded in `vercel.json`.

`vercel.json` selects Laravel's supported `array` cache store through
`CACHE_STORE`. This avoids relying on the Clever Cloud `cache` table before its
Production migration state is confirmed. Array cache is process-local and is
not shared between separate serverless invocations, so cache locks, rate limits,
and cached shipping/tracking data are not durable or globally coordinated. If
Production needs shared cache, first provision and verify a shared cache store
or confirm the database cache table is available, then configure `CACHE_STORE`
in Vercel Project Settings.
