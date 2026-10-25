<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>@yield('title', 'MH SCARF')</title>
    <style>
        body { margin: 0; padding: 0; background: #f5f2ee; color: #292621; font-family: Arial, Helvetica, sans-serif; }
        .wrap { width: 100%; padding: 32px 12px; }
        .card { width: 100%; max-width: 600px; margin: 0 auto; background: #fff; border: 1px solid #e9e3dc; border-radius: 12px; overflow: hidden; }
        .brand { padding: 26px 32px; border-bottom: 1px solid #eee9e3; color: #4b3c32; font-size: 18px; font-weight: 700; letter-spacing: 3px; }
        .content { padding: 30px 32px; font-size: 15px; line-height: 1.65; }
        .heading { margin: 0 0 16px; color: #32271f; font-size: 22px; line-height: 1.3; }
        .details { width: 100%; border-collapse: collapse; margin: 18px 0; }
        .details td, .details th { padding: 10px 0; border-bottom: 1px solid #eee9e3; text-align: left; vertical-align: top; }
        .details th { color: #776b60; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; }
        .button { display: inline-block; margin: 14px 0; padding: 12px 18px; border-radius: 6px; background: #594638; color: #fff !important; font-weight: 700; text-decoration: none; }
        .muted { color: #786f67; font-size: 13px; }
        .footer { padding: 18px 32px 24px; border-top: 1px solid #eee9e3; color: #887f77; font-size: 12px; line-height: 1.6; }
        @media only screen and (max-width: 480px) { .wrap { padding: 12px 6px; } .brand { padding: 22px 20px; } .content { padding: 24px 20px; } .footer { padding: 16px 20px 22px; } }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="brand">MH SCARF</div>
            <div class="content">@yield('content')</div>
            <div class="footer">Email ini dikirim otomatis oleh MH SCARF terkait pesanan Anda. Jika membutuhkan bantuan, silakan hubungi tim layanan pelanggan kami.</div>
        </div>
    </div>
</body>
</html>
