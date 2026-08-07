Write-Host "🚀 Ideap Architecture Setup Started..." -ForegroundColor Cyan

# 1. Root & Core Application Folders
$coreDirs = @(
    "app/Console/Commands",
    "app/Exceptions",
    "app/Http/Controllers",
    "app/Http/Middleware",
    "app/Http/Requests",
    "app/Providers",
    "app/Services/Security",
    "app/Traits",
    "app/Helpers",
    "app/Events",
    "app/Listeners",
    "storage/app/secure/ebooks",
    "storage/app/secure/webzines",
    "storage/app/secure/kyc_documents",
    "public/assets/css",
    "public/assets/js",
    "public/assets/fonts",
    "resources/views/components",
    "resources/views/layouts",
    "resources/views/emails",
    "resources/views/frontend/user"
)

foreach ($dir in $coreDirs) {
    New-Item -ItemType Directory -Force -Path $dir | Out-Null
}

# 2. Modules & Standard Subfolders Creation
$modules = @("Vendor", "Inventory", "Sales", "Billing", "Book", "Ebook", "Author", "Payment", "Order", "SEO", "Social", "Marketing", "Webzine", "Blog", "Review", "Notification", "ReaderSpace", "Subscription", "User")

foreach ($m in $modules) {
    $modulePaths = @(
        "Modules/$m/Config",
        "Modules/$m/Contracts",
        "Modules/$m/Database/Migrations",
        "Modules/$m/Database/Seeders",
        "Modules/$m/Http/Controllers/Admin",
        "Modules/$m/Http/Controllers/Vendor",
        "Modules/$m/Http/Controllers/Frontend",
        "Modules/$m/Http/Middleware",
        "Modules/$m/Http/Requests",
        "Modules/$m/Models",
        "Modules/$m/Notifications",
        "Modules/$m/Providers",
        "Modules/$m/Resources/views",
        "Modules/$m/Routes",
        "Modules/$m/Services"
    )
    
    foreach ($path in $modulePaths) {
        New-Item -ItemType Directory -Force -Path $path | Out-Null
    }

    # Default Module Routes Files
    New-Item -ItemType File -Force -Path "Modules/$m/Routes/web.php" | Out-Null
    New-Item -ItemType File -Force -Path "Modules/$m/Routes/api.php" | Out-Null
}

# 3. Essential System Files & Helpers
$files = @(
    "app/Helpers/CurrencyHelper.php",
    "app/Helpers/DeviceHelper.php",
    "app/Events/OrderPlacedEvent.php",
    "app/Events/DeviceLimitReachedEvent.php",
    "app/Listeners/SendOrderNotification.php",
    "app/Listeners/RevokeOldDeviceSession.php",
    "app/Services/Security/WatermarkGeneratorService.php",
    "database/seeders/RolesAndPermissionsSeeder.php",
    "database/seeders/SystemSettingSeeder.php",
    "config/drm.php",
    "config/sms.php",
    "config/courier.php",
    "Modules/Book/Database/Migrations/create_book_author_table.php",
    "Modules/Order/Database/Migrations/create_shippings_table.php",
    "Modules/Ebook/Database/Migrations/create_user_ebook_access_table.php",
    "Modules/Ebook/Resources/views/reader/pdf_viewer.blade.php",
    "Modules/Ebook/Resources/views/reader/epub_viewer.blade.php",
    "Modules/Payment/Services/RefundService.php",
    "resources/views/frontend/checkout.blade.php",
    "resources/views/frontend/user/devices.blade.php"
)

foreach ($f in $files) {
    if (!(Test-Path $f)) {
        New-Item -ItemType File -Path $f -Force | Out-Null
    }
}

# 4. Secure Storage Protection (.htaccess)
Set-Content -Path "storage/app/secure/.htaccess" -Value "# Block Direct Web Access`nOrder allow,deny`nDeny from all"

Write-Host "✅ All Directories, Modules & Necessary Files Successfully Created!" -ForegroundColor Green