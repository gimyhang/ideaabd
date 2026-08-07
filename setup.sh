#!/bin/bash

echo "🚀 Ideap Architecture Setup Started..."

# Root App Folders
mkdir -p app/Console/Commands
mkdir -p app/Exceptions
mkdir -p app/Http/Controllers
mkdir -p app/Http/Middleware
mkdir -p app/Http/Requests
mkdir -p app/Providers
mkdir -p app/Services/Security
mkdir -p app/Traits
mkdir -p app/Helpers
mkdir -p app/Events
mkdir -p app/Listeners

# Standard Laravel Core Files/Helpers
touch app/Helpers/CurrencyHelper.php
touch app/Helpers/DeviceHelper.php
touch app/Events/OrderPlacedEvent.php
touch app/Events/DeviceLimitReachedEvent.php
touch app/Listeners/SendOrderNotification.php
touch app/Listeners/RevokeOldDeviceSession.php
touch app/Services/Security/WatermarkGeneratorService.php

# Modules List
modules=(
    "Vendor"
    "Inventory"
    "Sales"
    "Billing"
    "Book"
    "Ebook"
    "Author"
    "Payment"
    "Order"
    "SEO"
    "Social"
    "Marketing"
    "Webzine"
    "Blog"
    "Review"
    "Notification"
    "ReaderSpace"
    "Subscription"
    "User"
)

# Standard Subfolders inside each Module
for module in "${modules[@]}"; do
    echo "📂 Creating Module: $module"
    mkdir -p Modules/$module/Config
    mkdir -p Modules/$module/Contracts
    mkdir -p Modules/$module/Database/Migrations
    mkdir -p Modules/$module/Database/Seeders
    mkdir -p Modules/$module/Http/Controllers/Admin
    mkdir -p Modules/$module/Http/Controllers/Vendor
    mkdir -p Modules/$module/Http/Controllers/Frontend
    mkdir -p Modules/$module/Http/Middleware
    mkdir -p Modules/$module/Http/Requests
    mkdir -p Modules/$module/Models
    mkdir -p Modules/$module/Notifications
    mkdir -p Modules/$module/Providers
    mkdir -p Modules/$module/Resources/views
    mkdir -p Modules/$module/Routes
    mkdir -p Modules/$module/Services

    # Default Route Files for Each Module
    touch Modules/$module/Routes/web.php
    touch Modules/$module/Routes/api.php
done

# Specific Module-Level Files Setup
touch Modules/Book/Database/Migrations/create_book_author_table.php
touch Modules/Order/Database/Migrations/create_shippings_table.php
touch Modules/Ebook/Database/Migrations/create_user_ebook_access_table.php

mkdir -p Modules/Ebook/Resources/views/reader
touch Modules/Ebook/Resources/views/reader/pdf_viewer.blade.php
touch Modules/Ebook/Resources/views/reader/epub_viewer.blade.php

mkdir -p Modules/Payment/Services
touch Modules/Payment/Services/RefundService.php

# Global Config Files
touch config/drm.php
touch config/sms.php
touch config/courier.php

# Seeders
touch database/seeders/RolesAndPermissionsSeeder.php
touch database/seeders/SystemSettingSeeder.php

# Secure Storage Directory
mkdir -p storage/app/secure/ebooks
mkdir -p storage/app/secure/webzines
mkdir -p storage/app/secure/kyc_documents

# Secure Directory Protection (.htaccess)
cat <<EOT > storage/app/secure/.htaccess
# Block Direct Web Access to Protected Files
Order allow,deny
Deny from all
EOT

# Public Assets
mkdir -p public/assets/css
mkdir -p public/assets/js
mkdir -p public/assets/fonts

# Additional Email, Layout & Frontend Views
mkdir -p resources/views/components
mkdir -p resources/views/layouts
mkdir -p resources/views/emails
mkdir -p resources/views/frontend/user

touch resources/views/frontend/checkout.blade.php
touch resources/views/frontend/user/devices.blade.php

echo "✅ All System Directories & Files Successfully Created and Secured!"