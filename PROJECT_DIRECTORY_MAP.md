# 📂 আইডিয়া প্রকাশন — সম্পূর্ণ প্রজেক্ট ডিরেক্টরি, ফাইল ও রাউট ইনডেক্স (Project File & Route Map)

> এই নথিতে প্রজেক্টের প্রতিটি ফাইল, ফোল্ডার, কন্ট্রোলার, মডেল, ভিউ এবং সমস্ত রেজিস্টার্ড রাউটের সঠিক লোকেশন ও বাস্তব কাজের শিরোনামসহ বিস্তারিত বিবরণ তালিকাভুক্ত করা হয়েছে। কোনো ফাইল বাদ নেই।

---

## 🧭 ১. প্রধান ডিরেক্টরি পরিচিতি (Root Folder Structure)

| ডিরেক্টরি | ক্যাটাগরি | বিবরণ |
| :--- | :--- | :--- |
| `app/` | Core Application | কন্ট্রোলার, মডেল, সার্ভিস, মিডলওয়্যার, ট্রেইট ও ইভেন্ট |
| `app/Http/Controllers/` | Controllers | ফ্রন্টএন্ড, ব্যাকএন্ড এডমিন ও সেলার বিলিং কন্ট্রোলারসমূহ |
| `app/Http/Controllers/Admin/` | Admin Controllers | ড্যাশবোর্ড, পিওএস, সাবস্ক্রিপশন, ডিআরএম, মাল্টি-কারেন্সি, ব্যাকআপ ও সিকিউরিটি |
| `app/Http/Controllers/SubAdmin/` | Sub-Admin & Seller | সেলার বিলিং, ইনভয়েস ও অ্যাকাউন্টস লেজার |
| `app/Models/` | Eloquent Models | ডেটাবেজ টেবিল রিলেশনশিপ ও অবজেক্ট মডেলসমূহ |
| `app/Services/` | Business Services | কারেন্সি এফএক্স, ডিআরএম ওয়াটারমার্কিং, ড্যাশবোর্ড ও সিকিউরিটি সার্ভিস |
| `bootstrap/` | Bootstrap Engine | ফ্রেমওয়ার্ক বুটস্ট্র্যাপ ও ক্যাশ ডিরেক্টরি |
| `config/` | Configurations | ব্র্যান্ড, ডেটাবেজ, মেইল, ক্যাশ ও অ্যাপ সেটিংস |
| `database/migrations/` | Migrations | ডেটাবেজ স্কিমা ও টেবিল তৈরি স্ক্রিপ্ট |
| `database/seeders/` | Seeders | ডিফল্ট রোল, ইউজার ও স্যাম্পল ডেটা সিডিং |
| `Modules/` | Modular Architecture | মডিউলার প্লাগইন ডিরেক্টরি (Book Module ইত্যাদি) |
| `public/` | Web Root Assets | সিএসএস (admin.css), জাভাস্ক্রিপ্ট, ফন্টস, লোগো ও আপলোড ব্যানার |
| `resources/views/` | Blade Views | সকল ইউজার ইন্টারফেস ও ব্লেড টেমপ্লেটসমূহ |
| `resources/views/admin/` | Admin Views | এডমিন ড্যাশবোর্ড, পিওএস, ব্যাকআপ, ট্রান্সলেশন ও হেল্পডেস্ক ভিউ |
| `resources/views/frontend/` | Frontend Views | পাবলিক ই-কমার্স স্টোরফ্রন্ট, ই-বুক রিডার ও অথর প্রোফাইল |
| `resources/views/subadmin/` | Subadmin Views | সেলার পিওএস বিলিং ও ক্যাশ লেজার ভিউ |
| `routes/` | Route Endpoints | web.php, api.php, console.php ও চ্যানেল রাউটস |
| `storage/` | Local Storage | সংরক্ষিত ব্যাকআপ ফাইল, ই-বুক পিডিএফে ও আপলোড কভার ইমেজ |

---

## 📄 ২. সকল ফাইল ও ফোল্ডারের পূর্ণাঙ্গ তালিকা (Complete File List with Descriptions)

### 📁 App Controllers (Admin Panel) (25 files)

| ফাইল পাথ | কী কী কাজ করছে (কাজের সুনির্দিষ্ট শিরোনাম ও দায়িত্ব) |
| :--- | :--- |
| `app/Http/Controllers/Admin/AdminAccessController.php` | অ্যাডমিন রোল, পারমিশন ম্যাট্রিক্স, সিস্টেম সেটিংস ও অডিট লগ কন্ট্রোলার |
| `app/Http/Controllers/Admin/AdminBackupController.php` | ডাটাবেজ ব্যাকআপ তৈরি, ডাউনলোড, আপলোড ও রিস্টোর (Disaster Recovery) কন্ট্রোলার |
| `app/Http/Controllers/Admin/AdminCacheController.php` | ভিউ ক্যাশ, ডাটা ক্যাশ, কনফিগ ও অপটিমাইজেশন কন্ট্রোলার |
| `app/Http/Controllers/Admin/AdminCurrencyController.php` | মাল্টি-কারেন্সি (BDT, USD, EUR, GBP) ও লাইভ এফএক্স এক্সচেঞ্জ রেট কন্ট্রোলার |
| `app/Http/Controllers/Admin/AdminMediaController.php` | বইয়ের কভার, ব্যানার, কিউআর কোড ও মিডিয়া লাইব্রেরি ম্যানেজার |
| `app/Http/Controllers/Admin/AdminProfileController.php` | সিস্টেম ফাইল: AdminProfileController.php |
| `app/Http/Controllers/Admin/AffiliateAdminController.php` | আন্তর্জাতিক অ্যাফিলিয়েট পার্টনার, রেফারেল কোড ও কমিশন পে-আউট কন্ট্রোলার |
| `app/Http/Controllers/Admin/AuthorHonorariumAdminController.php` | আইডিয়াপত্র ও ব্লগের লেখকদের সম্মানী ও পেমেন্ট বিতরণ কন্ট্রোলার |
| `app/Http/Controllers/Admin/AuthorPayoutAdminController.php` | ই-বুক ও ফিজিক্যাল বইয়ের রয়্যালটি পে-আউট রিকোয়েস্ট অনুমোদন কন্ট্রোলার |
| `app/Http/Controllers/Admin/AuthorRoyaltyAdminController.php` | লেখকভিত্তিক বই বিক্রির রয়্যালটি ক্যালকুলেশন ও লেজার কন্ট্রোলার |
| `app/Http/Controllers/Admin/BundleAdminController.php` | স্পেশাল কম্বো বান্ডেল অফার ও প্রি-অর্ডার ক্যাম্পেইন কন্ট্রোলার |
| `app/Http/Controllers/Admin/CommunicationAdminController.php` | Amazon SES, SendGrid, WhatsApp Cloud API ও অ্যাব্যান্ডন্ড কার্ট রিকভারি হাব |
| `app/Http/Controllers/Admin/ContentController.php` | ওয়েবজিন, গবেষণা প্রবন্ধ ও ওয়েবসাইট কন্টেন্ট ম্যানেজার |
| `app/Http/Controllers/Admin/GatewayReportController.php` | বিকাশ, নগদ, রকেট, ব্যাংক ও গেটওয়ে ট্রানজেকশন অডিট রিপোর্ট কন্ট্রোলার |
| `app/Http/Controllers/Admin/IdeaAccountingController.php` | আয়-ব্যয়, ভাউচার, চালান, কাস্টমার লেজার ও পেরোল স্যালারি অ্যাকাউন্টিং কন্ট্রোলার |
| `app/Http/Controllers/Admin/PaymentAdminController.php` | অনলাইন পেমেন্ট গেটওয়ে কনফিগারেশন ও ম্যানুয়াল পেমেন্ট ভেরিফিকেশন |
| `app/Http/Controllers/Admin/PosAdminController.php` | অমর একুশে বইমেলা ও অফলাইন স্টল বারকোড পিওএস ও থার্মাল রিসিট কন্ট্রোলার |
| `app/Http/Controllers/Admin/PublisherPurchaseController.php` | অন্যান্য প্রকাশনীর বই ক্রয়, স্টক ইনভেন্টরি ও ভেন্ডর লেজার কন্ট্রোলার |
| `app/Http/Controllers/Admin/QuickResourceController.php` | নতুন লেখক, ক্যাটাগরি ও পাবলিশার দ্রুত যুক্ত করার মডাল API কন্ট্রোলার |
| `app/Http/Controllers/Admin/RegistrationApprovalController.php` | নতুন লেখক, প্রকাশক ও সেলার রেজিস্ট্রেশন আবেদন যাচাই ও অনুমোদন |
| `app/Http/Controllers/Admin/SubAdminController.php` | সাব-এডমিন ও স্টাফ একাউন্ট তৈরি, ওটিপি ও অ্যাক্টিভেশন কন্ট্রোলার |
| `app/Http/Controllers/Admin/SubscriptionAdminController.php` | আইডিয়া আনলিমিটেড মেম্বারশিপ প্ল্যান ও পেজ-রিড রয়্যালটি ট্র্যাকার কন্ট্রোলার |
| `app/Http/Controllers/Admin/SupportTicketAdminController.php` | কাস্টমার ও অথর ৩৬০° হেল্পডেস্ক সাপোর্ট টিকিট মেসেজিং কন্ট্রোলার |
| `app/Http/Controllers/Admin/TranslationAdminController.php` | বহুভাষিক লোকালাইজেশন (i18n), বাংলা-ইংরেজি-আরবি ট্রান্সলেশন ও AI অনুবাদক |
| `app/Http/Controllers/Admin/UserSecurityAdminController.php` | লগইন সিকিউরিটি, ওটিপি জেনারেশন, সন্দেহজনক আইপি ব্লক ও ব্রুট-ফোর্স লকআউট |

### 📁 App Controllers (Storefront, Auth & Seller) (27 files)

| ফাইল পাথ | কী কী কাজ করছে (কাজের সুনির্দিষ্ট শিরোনাম ও দায়িত্ব) |
| :--- | :--- |
| `app/Http/Controllers/AdminController.php` | এডমিন ড্যাশবোর্ড মাস্টার কন্ট্রোলার ও ট্র্যাফিক অ্যানালিটিক্স প্রসেসর |
| `app/Http/Controllers/Auth/LoginController.php` | ইউজার ও এডমিন লগইন এবং সেশন অথেন্টিকেশন কন্ট্রোলার |
| `app/Http/Controllers/Auth/PasswordResetController.php` | পাসওয়ার্ড রিসেট লিংক ও ইমেইল ভেরিফিকেশন কন্ট্রোলার |
| `app/Http/Controllers/Auth/PasswordResetOtpController.php` | মোবাইল এসএমএস ও ওটিপি (OTP) দিয়ে তাৎক্ষণিক পাসওয়ার্ড রিসেট |
| `app/Http/Controllers/Auth/RegistrationController.php` | সাধারণ পাঠক, লেখক ও প্রকাশকদের সাইন-আপ কন্ট্রোলার |
| `app/Http/Controllers/Author/AuthorDashboardController.php` | লেখকদের নিজস্ব ড্যাশবোর্ড, রয়্যালটি আয় ও বইয়ের পরিসংখ্যান |
| `app/Http/Controllers/Author/AuthorEbookController.php` | লেখকদের নতুন ই-বুক পাণ্ডুলিপি আপলোড ও রিভিউ কন্ট্রোলার |
| `app/Http/Controllers/Author/AuthorPayoutController.php` | লেখকদের ব্যাংক/বিকাশে রয়্যালটি উত্তোলনের আবেদন কন্ট্রোলার |
| `app/Http/Controllers/AuthorBlogController.php` | লেখকদের আইডিয়াপত্র সাহিত্য ব্লগ ও প্রবন্ধ পোস্ট কন্ট্রোলার |
| `app/Http/Controllers/AuthorController.php` | পাবলিক লেখক তালিকা ও লেখক বিস্তারিত প্রোফাইল কন্ট্রোলার |
| `app/Http/Controllers/AuthorHonorariumController.php` | লেখক সম্মানী ভিউ ও স্ট্যাটাস ট্র্যাকিং কন্ট্রোলার |
| `app/Http/Controllers/BookRequestController.php` | পাঠকদের অপ্রাপ্ত বইয়ের অনুরোধ (Book Request) গ্রহণ কন্ট্রোলার |
| `app/Http/Controllers/CartController.php` | শপিং কার্ট, কুপন ডিসকাউন্ট ও চেকআউট প্রসেসর |
| `app/Http/Controllers/Controller.php` | লারাবেল বেস কন্ট্রোলার ক্লাস |
| `app/Http/Controllers/EbookReaderController.php` | সুরক্ষিত ই-বুক রিডার ও ডিআরএম ওয়াটারমার্ক ইনজেকশন কন্ট্রোলার |
| `app/Http/Controllers/GoogleMerchantController.php` | Google Shopping ও Merchant Center XML প্রোডাক্ট ফিড জেনারেটর |
| `app/Http/Controllers/HomeController.php` | আইডিয়া প্রকাশন প্রধান হোমপেজ, বুক শোকেস ও ফিচারড সেকশন |
| `app/Http/Controllers/OrderController.php` | কাস্টমার অর্ডার তৈরি, চালান প্রিন্ট ও লাইভ কুরিয়ার ট্র্যাকিং |
| `app/Http/Controllers/PaymentController.php` | বিকাশ, নগদ, রকেট ও অনলাইন পেমেন্ট গেটওয়ে প্রসেসিং কন্ট্রোলার |
| `app/Http/Controllers/Publisher/PublisherPortalController.php` | প্রকাশকদের নিজস্ব পোর্টাল, প্রকাশিত বই ও বিক্রয় রিপোর্ট |
| `app/Http/Controllers/PublisherController.php` | পাবলিক প্রকাশনী তালিকা ও প্রকাশকের বই ডিসপ্লে কন্ট্রোলার |
| `app/Http/Controllers/ResearchController.php` | একাডেমিক গবেষণা প্রবন্ধ ও জার্নাল আর্কাইভ কন্ট্রোলার |
| `app/Http/Controllers/ReviewController.php` | বইয়ের পাঠক রিভিউ, স্টার রেটিং ও মন্তব্য কন্ট্রোলার |
| `app/Http/Controllers/SitemapController.php` | এসইও (SEO) ডাইনামিক XML সাইটম্যাপ জেনারেটর |
| `app/Http/Controllers/SocialProofController.php` | লাইভ সেলস নোটিফিকেশন ও সোশাল প্রুফ পপআপ API |
| `app/Http/Controllers/SubAdmin/BillingController.php` | সেলার অফলাইন বিলিং, ইনভয়েস তৈরি ও ক্যাশ লেজার কন্ট্রোলার |
| `app/Http/Controllers/UserController.php` | কাস্টমার প্রোফাইল, অর্ডার হিস্ট্রি ও ই-বুক লাইব্রেরি কন্ট্রোলার |

### 📁 App Eloquent Models (Database Tables) (40 files)

| ফাইল পাথ | কী কী কাজ করছে (কাজের সুনির্দিষ্ট শিরোনাম ও দায়িত্ব) |
| :--- | :--- |
| `app/Models/AdminActivityLog.php` | এডমিনদের কার্যক্রম, লগইন ও পরিবর্তনসমূহের সিকিউরিটি অডিট ট্রেইল মডেল |
| `app/Models/AdminDashboardSetting.php` | ড্যাশবোর্ড কনফিগারেশন, নোটিশ ও গ্লোবাল সিস্টেম সেটিংস মডেল |
| `app/Models/AdminPermission.php` | এডমিন ও সাব-এডমিন মডিউল পারমিশন পার্সিং মডেল |
| `app/Models/Affiliate.php` | আন্তর্জাতিক অ্যাফিলিয়েট পার্টনার প্রোফাইল ও ব্যালেন্স মডেল |
| `app/Models/AffiliateReferral.php` | অ্যাফিলিয়েট রেফারেল লিংক থেকে আসা বুক অর্ডার ট্র্যাকিং মডেল |
| `app/Models/AuthorHonorarium.php` | ব্লগ ও আইডিয়াপত্র লেখকের সম্মানী প্রদান রেকর্ড মডেল |
| `app/Models/AuthorPayoutRequest.php` | লেখকদের রয়্যালটি উইথড্রয়াল পে-আউট রিকোয়েস্ট মডেল |
| `app/Models/AuthorRoyalty.php` | বই বিক্রয় থেকে অর্জিত লেখক রয়্যালটি ব্যালেন্স মডেল |
| `app/Models/Bill.php` | সেলার অফলাইন পিওএস বিল ও ক্যাশ মেমো মডেল |
| `app/Models/BookBundle.php` | একাধিক বই নিয়ে তৈরি ডিসকাউন্টেড স্পেশাল কম্বো বান্ডেল মডেল |
| `app/Models/BookRequest.php` | পাঠকদের অপ্রাপ্ত বা কাঙ্ক্ষিত বইয়ের অনুরোধ মডেল |
| `app/Models/BundleItem.php` | কম্বো বান্ডেলের ভেতরে থাকা নির্দিষ্ট বই ও কোয়ান্টিটি মডেল |
| `app/Models/CommunicationLog.php` | ইমেইল ও হোয়াটসঅ্যাপ ডিসপ্যাচ ট্র্যাকিং ও ডেলিভারি লগ মডেল |
| `app/Models/CommunicationTemplate.php` | স্বয়ংক্রিয় অর্ডার কনফার্মেশন ও অ্যাব্যান্ডন্ড কার্ট ইমেইল টেমপ্লেট |
| `app/Models/Concerns/Moderatable.php` | কন্টেন্ট অ্যাপ্রুভাল ও মডারেশন রিলেশনশিপ ট্রেইট |
| `app/Models/CurrencyRate.php` | আন্তর্জাতিক কারেন্সি (USD, EUR, GBP ইত্যাদি) ও ব্যাংক রেট মডেল |
| `app/Models/EbookReadingLog.php` | আনলিমিটেড রিডিং সাবস্ক্রিপশনের পেজ-রিড ট্র্যাকিং লগ মডেল |
| `app/Models/IdeaAccountingEntry.php` | দৈনিক ক্যাশ ও ব্যাংক ডেবিট/ক্রেডিট হিসাব এন্ট্রি মডেল |
| `app/Models/IdeaEmployee.php` | স্টাফ ও কর্মচারীদের প্রোফাইল, পদবী ও বেতন স্কেল মডেল |
| `app/Models/IdeaEmployeeWorkLog.php` | কর্মচারীদের দৈনিক কাজের লগ ও ডিউটি রেকর্ড মডেল |
| `app/Models/IdeaInvoice.php` | অফিসিয়াল জিএসটি/ভ্যাট চালানের ইনভয়েস রেকর্ড মডেল |
| `app/Models/IdeaInvoicePayment.php` | চালানের আংশিক বা পূর্ণাঙ্গ কিস্তি পেমেন্ট হিস্ট্রি মডেল |
| `app/Models/IdeaSalaryPayment.php` | কর্মচারীদের বেতন পরিশোধ ও ব্যাংক ভাউচার রেকর্ড মডেল |
| `app/Models/LoginSecurityLog.php` | ব্রুট-ফোর্স রোধে ভুল পাসওয়ার্ড ও সন্দেহজনক আইপি ট্র্যাকিং মডেল |
| `app/Models/Order.php` | ই-কমার্স বুক অর্ডার, গ্রাহকের ঠিকানা ও কুরিয়ার ট্র্যাকিং মডেল |
| `app/Models/PasswordResetRequest.php` | ম্যানুয়াল ও ওটিপি পাসওয়ার্ড পরিবর্তনের আবেদন মডেল |
| `app/Models/PosRegister.php` | বইমেলা স্টল ক্যাশ রেজিস্টার ও ড্রয়ার সেশন মডেল |
| `app/Models/PosSale.php` | বইমেলা স্টলের দ্রুত বারকোড বিক্রয় ও রিসিট রেকর্ড মডেল |
| `app/Models/PreOrder.php` | নতুন অপ্রকাশিত বইয়ের অগ্রিম বুকিং প্রি-অর্ডার মডেল |
| `app/Models/PublisherPayment.php` | ভেন্ডর প্রকাশকদের পাওনা পরিশোধের পেমেন্ট ভাউচার মডেল |
| `app/Models/PublisherPurchase.php` | ভেন্ডর প্রকাশকদের কাছ থেকে বই ক্রয়ের চালান মডেল |
| `app/Models/PublisherPurchaseItem.php` | ক্রয়কৃত চালানের নির্দিষ্ট বই ও ক্রয়মূল্য রেকর্ড মডেল |
| `app/Models/SiteTranslation.php` | বহুভাষিক অনুবাদ (বাংলা, ইংরেজি, আরবি) স্ট্রিং মডেল |
| `app/Models/SubscriptionPlan.php` | আইডিয়া আনলিমিটেড মাসিক/বাৎসরিক মেম্বারশিপ প্ল্যান মডেল |
| `app/Models/SupportTicket.php` | হেল্পডেস্ক সাপোর্ট টিকিট ও অভিযোগ ক্যাটাগরি মডেল |
| `app/Models/TicketMessage.php` | সাপোর্ট টিকিটের মধ্যকার বার্তা আদান-প্রদান থ্রেড মডেল |
| `app/Models/User.php` | ইউজার, এডমিন, লেখক, প্রকাশক ও সেলার মাস্টার একাউন্ট মডেল |
| `app/Models/UserEbookLibrary.php` | কাস্টমারের কেনা ই-বুকের পার্সোনাল ডিজিটাল লাইব্রেরি মডেল |
| `app/Models/UserSubscription.php` | গ্রাহকের সক্রিয় আনলিমিটেড মেম্বারশিপ লাইসেন্স মডেল |
| `app/Models/VisitorLog.php` | লাইভ ওয়েবসাইট ভিজিটর, ডিভাইস, আইপি ও দেশভিত্তিক ট্র্যাফিক মডেল |

### 📁 App Services, Security & Helpers (12 files)

| ফাইল পাথ | কী কী কাজ করছে (কাজের সুনির্দিষ্ট শিরোনাম ও দায়িত্ব) |
| :--- | :--- |
| `app/Services/AdminAccessService.php` | পারমিশন চেকিং ও অডিট অ্যাক্টিভিটি লগিং হেল্পার সার্ভিস |
| `app/Services/AdminDashboardService.php` | ড্যাশবোর্ড সেলস, রেভিনিউ, ভিজিটর ও স্টক অ্যানালিটিক্স ক্যালকুলেটর |
| `app/Services/CurrencyService.php` | টাকা (BDT) থেকে ডলার ($) ও ইউরোতে কনভার্সন ও লাইভ FX এক্সচেঞ্জ ইঞ্জিন |
| `app/Services/DrmProtectionService.php` | ই-বুক রিডারে ভাসমান ওয়াটারমার্ক ও পাইরেসি সুরক্ষা জেনারেটর |
| `app/Services/RoyaltyService.php` | বইয়ের বিক্রি থেকে লেখকের রয়্যালটি স্বয়ংক্রিয় হিসাব করার ইঞ্জিন |
| `app/Services/Security/BkashService.php` | বিকাশ পেমেন্ট গেটওয়ে API ইন্টিগ্রেশন ও পেমেন্ট ভেরিফিকেশন |
| `app/Services/Security/WatermarkGeneratorService.php` | পিডিএফ ও ইমেজে স্বয়ংক্রিয় ওয়াটারমার্ক স্ট্যাম্পিং সার্ভিস |
| `app/Support/Bn.php` | ইংরেজি সংখ্যা ও তারিখকে বাংলায় রূপান্তরের হেল্পার ক্লাস |
| `app/Support/ContentTypes.php` | বই, ই-বুক, ব্লগ ও ওয়েবজিনের কন্টেন্ট টাইপ কনস্ট্যান্ট |
| `app/Support/SiteSetting.php` | সাইটের গ্লোবাল কনফিগারেশন, লোগো ও থিম কালার লোডার হেল্পার |
| `app/Traits/HasDeviceLimit.php` | ই-বুক পড়ার জন্য সর্বোচ্চ ডিভাইস লিমিট নিয়ন্ত্রণ ট্রেইট |
| `app/Traits/HasVendorRelation.php` | ভেন্ডর ও প্রকাশকদের সম্পর্কযুক্ত করার ট্রেইট |

### 📁 Database Migrations (Table Schemas) (70 files)

| ফাইল পাথ | কী কী কাজ করছে (কাজের সুনির্দিষ্ট শিরোনাম ও দায়িত্ব) |
| :--- | :--- |
| `database/migrations/0001_01_01_000000_create_users_table.php` | ডেটাবেজ স্কিমা: Users টেবিল তৈরি মাইগ্রেশন |
| `database/migrations/0001_01_01_000001_create_cache_table.php` | ডেটাবেজ স্কিমা: Cache টেবিল তৈরি মাইগ্রেশন |
| `database/migrations/0001_01_01_000002_create_jobs_table.php` | ডেটাবেজ স্কিমা: Jobs টেবিল তৈরি মাইগ্রেশন |
| `database/migrations/2024_08_09_000000_create_blog_categories_table.php` | ডেটাবেজ স্কিমা: Blog Categories টেবিল তৈরি মাইগ্রেশন |
| `database/migrations/2024_08_09_000001_create_blog_posts_table.php` | ডেটাবেজ স্কিমা: Blog Posts টেবিল তৈরি মাইগ্রেশন |
| `database/migrations/2024_08_09_000003_create_blog_tags_table.php` | ডেটাবেজ স্কিমা: Blog Tags টেবিল তৈরি মাইগ্রেশন |
| `database/migrations/2024_08_09_000004_create_authors_table.php.disabled` | ডেটাবেজ স্কিমা: Authors.php টেবিল তৈরি মাইগ্রেশন |
| `database/migrations/2024_08_09_000005_create_catalog_tables.php` | ডেটাবেজ স্কিমা: Catalogs টেবিল তৈরি মাইগ্রেশন |
| `database/migrations/2024_08_09_000006_create_webzines_table.php` | ডেটাবেজ স্কিমা: Webzines টেবিল তৈরি মাইগ্রেশন |
| `database/migrations/2024_08_09_000007_create_research_papers_table.php` | ডেটাবেজ স্কিমা: Research Papers টেবিল তৈরি মাইগ্রেশন |
| `database/migrations/2026_08_10_000001_create_tags_table.php` | ডেটাবেজ স্কিমা: Tags টেবিল তৈরি মাইগ্রেশন |
| `database/migrations/2026_08_10_000002_create_kids_zones_table.php` | ডেটাবেজ স্কিমা: Kids Zones টেবিল তৈরি মাইগ্রেশন |
| `database/migrations/2026_08_10_000003_create_bulk_orders_table.php` | ডেটাবেজ স্কিমা: Bulk Orders টেবিল তৈরি মাইগ্রেশন |
| `database/migrations/2026_08_10_000004_create_wishlists_table.php` | ডেটাবেজ স্কিমা: Wishlists টেবিল তৈরি মাইগ্রেশন |
| `database/migrations/2026_08_10_000005_create_promotions_table.php` | ডেটাবেজ স্কিমা: Promotions টেবিল তৈরি মাইগ্রেশন |
| `database/migrations/2026_08_10_000006_add_roles_and_bills.php` | ডেটাবেজ স্কিমা: Roles And Bills টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_11_000002_create_reviews_and_pivots.php` | ডেটাবেজ স্কিমা: Reviews And Pivots টেবিল তৈরি মাইগ্রেশন |
| `database/migrations/2026_08_12_000001_add_moderation_to_content_tables.php` | ডেটাবেজ স্কিমা: Moderation To Contents টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_12_100001_add_subtitle_and_author_role_to_books_ebooks.php` | ডেটাবেজ স্কিমা: Subtitle And Author Role To Books Ebooks টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_12_100002_add_author_link_id_to_books_ebooks.php` | ডেটাবেজ স্কিমা: Author Link Id To Books Ebooks টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_13_000001_create_admin_access_and_dashboard_tables.php` | ডেটাবেজ স্কিমা: Admin Access And Dashboards টেবিল তৈরি মাইগ্রেশন |
| `database/migrations/2026_08_13_000002_add_performance_indexes_for_dashboard.php` | ডেটাবেজ স্কিমা: Performance Indexes For Dashboard টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_14_154811_create_book_requests_table.php` | ডেটাবেজ স্কিমা: Book Requests টেবিল তৈরি মাইগ্রেশন |
| `database/migrations/2026_08_14_155421_create_orders_table.php` | ডেটাবেজ স্কিমা: Orders টেবিল তৈরি মাইগ্রেশন |
| `database/migrations/2026_08_14_172025_add_phase2_fields_to_users_and_orders_tables.php` | ডেটাবেজ স্কিমা: Phase2 Fields To Users And Orderss টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_14_220500_add_advanced_fields_to_ebooks_table.php` | ডেটাবেজ স্কিমা: Advanced Fields To Ebooks টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_15_163000_add_ecommerce_order_extended_fields.php` | ডেটাবেজ স্কিমা: Ecommerce Order Extended Fields টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_15_172000_add_payment_trx_to_orders_table.php` | ডেটাবেজ স্কিমা: Payment Trx To Orders টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_15_174500_create_visitor_logs_table.php` | ডেটাবেজ স্কিমা: Visitor Logs টেবিল তৈরি মাইগ্রেশন |
| `database/migrations/2026_08_16_180000_add_detailed_fields_to_books_table.php` | ডেটাবেজ স্কিমা: Detailed Fields To Books টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_17_000001_create_publisher_purchases_and_payments_tables.php` | ডেটাবেজ স্কিমা: Publisher Purchases And Paymentss টেবিল তৈরি মাইগ্রেশন |
| `database/migrations/2026_08_17_000002_create_idea_accounting_and_invoices_tables.php` | ডেটাবেজ স্কিমা: Idea Accounting And Invoicess টেবিল তৈরি মাইগ্রেশন |
| `database/migrations/2026_08_17_000003_add_memo_and_discounts_to_publisher_purchases.php` | ডেটাবেজ স্কিমা: Memo And Discounts To Publisher Purchases টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_17_230000_add_blog_post_id_to_reviews_table.php` | ডেটাবেজ স্কিমা: Blog Post Id To Reviews টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_17_233000_add_image_to_blog_categories_table.php` | ডেটাবেজ স্কিমা: Image To Blog Categories টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_17_234500_add_subtitle_to_blog_posts_table.php` | ডেটাবেজ স্কিমা: Subtitle To Blog Posts টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_19_120000_add_modern_bookstore_fields_to_books_table.php` | ডেটাবেজ স্কিমা: Modern Bookstore Fields To Books টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_19_180000_add_dimensions_and_preorder_to_books_table.php` | ডেটাবেজ স্কিমা: Dimensions And Preorder To Books টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_19_190000_add_quotation_and_tender_fields_to_idea_invoices_table.php` | ডেটাবেজ স্কিমা: Quotation And Tender Fields To Idea Invoices টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_19_200000_add_customer_org_to_idea_invoices_table.php` | ডেটাবেজ স্কিমা: Customer Org To Idea Invoices টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_19_220000_add_customer_email_and_token_to_idea_invoices_table.php` | ডেটাবেজ স্কিমা: Customer Email And Token To Idea Invoices টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_20_170000_remove_unique_title_from_blog_posts_table.php` | ডেটাবেজ স্কিমা: Unique Title From Blog Posts টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_21_180000_add_country_and_product_type_to_books_table.php` | ডেটাবেজ স্কিমা: Country And Product Type To Books টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_21_181306_add_dimensions_cm_to_books_table.php` | ডেটাবেজ স্কিমা: Dimensions Cm To Books টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_21_190000_add_rokomari_detailed_fields_to_books_table.php` | ডেটাবেজ স্কিমা: Rokomari Detailed Fields To Books টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_22_140000_create_author_kdp_and_royalty_tables.php` | ডেটাবেজ স্কিমা: Author Kdp And Royaltys টেবিল তৈরি মাইগ্রেশন |
| `database/migrations/2026_08_22_143000_add_gateway_fields_to_author_payout_requests.php` | ডেটাবেজ স্কিমা: Gateway Fields To Author Payout Requests টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_23_000001_add_contributors_to_ebooks_table.php` | ডেটাবেজ স্কিমা: Contributors To Ebooks টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_25_120000_create_author_honorariums_table.php` | ডেটাবেজ স্কিমা: Author Honorariums টেবিল তৈরি মাইগ্রেশন |
| `database/migrations/2026_08_26_000001_create_idea_employees_and_salaries_tables.php` | ডেটাবেজ স্কিমা: Idea Employees And Salariess টেবিল তৈরি মাইগ্রেশন |
| `database/migrations/2026_08_26_000002_add_purchase_category_to_publisher_purchases_table.php` | ডেটাবেজ স্কিমা: Purchase Category To Publisher Purchases টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_26_000003_make_publisher_id_nullable_in_purchases_table.php` | ডেটাবেজ স্কিমা: Publisher Id Nullable In Purchases টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_26_181206_add_raw_materials_fields_to_publisher_purchase_items_table.php` | ডেটাবেজ স্কিমা: Raw Materials Fields To Publisher Purchase Items টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_26_183507_add_sales_category_to_idea_invoices_table.php` | ডেটাবেজ স্কিমা: Sales Category To Idea Invoices টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_27_225500_add_contract_and_piece_rate_fields_to_idea_employees_and_salaries_table.php` | ডেটাবেজ স্কিমা: Contract And Piece Rate Fields To Idea Employees And Salaries টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_27_230215_create_idea_employee_work_logs_table.php` | ডেটাবেজ স্কিমা: Idea Employee Work Logs টেবিল তৈরি মাইগ্রেশন |
| `database/migrations/2026_08_27_231654_add_production_tracking_to_idea_employee_work_logs_table.php` | ডেটাবেজ স্কিমা: Production Tracking To Idea Employee Work Logs টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_28_000500_add_reams_quantity_to_publisher_purchase_items_table.php` | ডেটাবেজ স্কিমা: Reams Quantity To Publisher Purchase Items টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_28_002306_add_installment_fields_to_publisher_purchases_table.php` | ডেটাবেজ স্কিমা: Installment Fields To Publisher Purchases টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_28_003900_make_publisher_id_nullable_in_publisher_payments_table.php` | ডেটাবেজ স্কিমা: Publisher Id Nullable In Publisher Payments টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_29_000001_add_vendor_name_to_publisher_payments_table.php` | ডেটাবেজ স্কিমা: Vendor Name To Publisher Payments টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_29_000002_add_vendor_contact_to_publisher_purchases_table.php` | ডেটাবেজ স্কিমা: Vendor Contact To Publisher Purchases টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_30_000001_add_geo_and_source_to_visitor_logs_table.php` | ডেটাবেজ স্কিমা: Geo And Source To Visitor Logs টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_30_000002_add_device_model_to_visitor_logs_table.php` | ডেটাবেজ স্কিমা: Device Model To Visitor Logs টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_30_180000_create_idea_invoice_payments_table.php` | ডেটাবেজ স্কিমা: Idea Invoice Payments টেবিল তৈরি মাইগ্রেশন |
| `database/migrations/2026_08_31_120000_make_quantity_decimal_in_publisher_purchase_items_table.php` | ডেটাবেজ স্কিমা: Quantity Decimal In Publisher Purchase Items টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_08_31_130000_create_login_security_and_password_requests_tables.php` | ডেটাবেজ স্কিমা: Login Security And Password Requestss টেবিল তৈরি মাইগ্রেশন |
| `database/migrations/2026_08_31_140000_add_security_issue_fields_to_login_security_logs_table.php` | ডেটাবেজ স্কিমা: Security Issue Fields To Login Security Logs টেবিল ফিল্ড আপডেট মাইগ্রেশন |
| `database/migrations/2026_09_01_120000_create_worldwide_and_enterprise_features_tables.php` | ডেটাবেজ স্কিমা: Worldwide And Enterprise Featuress টেবিল তৈরি মাইগ্রেশন |
| `database/migrations/2026_09_01_130000_create_translations_and_communications_tables.php` | ডেটাবেজ স্কিমা: Translations And Communicationss টেবিল তৈরি মাইগ্রেশন |

### 📁 Database Seeders (Initial Data) (8 files)

| ফাইল পাথ | কী কী কাজ করছে (কাজের সুনির্দিষ্ট শিরোনাম ও দায়িত্ব) |
| :--- | :--- |
| `database/seeders/AdminUserSeeder.php` | ডিফল্ট ডেটাবেজ সিডার স্ক্রিপ্ট: AdminUserSeeder |
| `database/seeders/DatabaseSeeder.php` | ডিফল্ট ডেটাবেজ সিডার স্ক্রিপ্ট: DatabaseSeeder |
| `database/seeders/DemoContentSeeder.php` | ডিফল্ট ডেটাবেজ সিডার স্ক্রিপ্ট: DemoContentSeeder |
| `database/seeders/DemoDataSeeder.php` | ডিফল্ট ডেটাবেজ সিডার স্ক্রিপ্ট: DemoDataSeeder |
| `database/seeders/IdeapatraContentSeeder.php` | ডিফল্ট ডেটাবেজ সিডার স্ক্রিপ্ট: IdeapatraContentSeeder |
| `database/seeders/PlatformSeeder.php` | ডিফল্ট ডেটাবেজ সিডার স্ক্রিপ্ট: PlatformSeeder |
| `database/seeders/RolesAndPermissionsSeeder.php` | ডিফল্ট ডেটাবেজ সিডার স্ক্রিপ্ট: RolesAndPermissionsSeeder |
| `database/seeders/SystemSettingSeeder.php` | ডিফল্ট ডেটাবেজ সিডার স্ক্রিপ্ট: SystemSettingSeeder |

### 📁 Blade Views (Admin Panel & Management) (80 files)

| ফাইল পাথ | কী কী কাজ করছে (কাজের সুনির্দিষ্ট শিরোনাম ও দায়িত্ব) |
| :--- | :--- |
| `resources/views/admin/accounting/customer_ledger/index.blade.php` | আয়-ব্যয়, ভাউচার, চালান ও পেরোল অ্যাকাউন্টিং ভিউ (index.blade.php) |
| `resources/views/admin/accounting/employees/index.blade.php` | আয়-ব্যয়, ভাউচার, চালান ও পেরোল অ্যাকাউন্টিং ভিউ (index.blade.php) |
| `resources/views/admin/accounting/employees/ledger.blade.php` | আয়-ব্যয়, ভাউচার, চালান ও পেরোল অ্যাকাউন্টিং ভিউ (ledger.blade.php) |
| `resources/views/admin/accounting/index.blade.php` | আয়-ব্যয়, ভাউচার, চালান ও পেরোল অ্যাকাউন্টিং ভিউ (index.blade.php) |
| `resources/views/admin/accounting/invoices/create.blade.php` | আয়-ব্যয়, ভাউচার, চালান ও পেরোল অ্যাকাউন্টিং ভিউ (create.blade.php) |
| `resources/views/admin/accounting/invoices/edit.blade.php` | আয়-ব্যয়, ভাউচার, চালান ও পেরোল অ্যাকাউন্টিং ভিউ (edit.blade.php) |
| `resources/views/admin/accounting/invoices/index.blade.php` | আয়-ব্যয়, ভাউচার, চালান ও পেরোল অ্যাকাউন্টিং ভিউ (index.blade.php) |
| `resources/views/admin/accounting/invoices/receipt.blade.php` | আয়-ব্যয়, ভাউচার, চালান ও পেরোল অ্যাকাউন্টিং ভিউ (receipt.blade.php) |
| `resources/views/admin/accounting/invoices/show.blade.php` | আয়-ব্যয়, ভাউচার, চালান ও পেরোল অ্যাকাউন্টিং ভিউ (show.blade.php) |
| `resources/views/admin/accounting/reports/index.blade.php` | আয়-ব্যয়, ভাউচার, চালান ও পেরোল অ্যাকাউন্টিং ভিউ (index.blade.php) |
| `resources/views/admin/accounting/salary/index.blade.php` | আয়-ব্যয়, ভাউচার, চালান ও পেরোল অ্যাকাউন্টিং ভিউ (index.blade.php) |
| `resources/views/admin/accounting/salary/slip.blade.php` | আয়-ব্যয়, ভাউচার, চালান ও পেরোল অ্যাকাউন্টিং ভিউ (slip.blade.php) |
| `resources/views/admin/activity-logs.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (activity-logs.blade) |
| `resources/views/admin/affiliates/index.blade.php` | অ্যাফিলিয়েট পার্টনার ও কমিশন লেজার ভিউ (index.blade.php) |
| `resources/views/admin/analytics.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (analytics.blade) |
| `resources/views/admin/authors.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (authors.blade) |
| `resources/views/admin/backup.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (backup.blade) |
| `resources/views/admin/blog-categories.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (blog-categories.blade) |
| `resources/views/admin/blog.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (blog.blade) |
| `resources/views/admin/book-requests.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (book-requests.blade) |
| `resources/views/admin/books.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (books.blade) |
| `resources/views/admin/bundles/index.blade.php` | স্পেশাল কম্বো বান্ডেল ও প্রি-অর্ডার ক্যাম্পেইন ভিউ (index.blade.php) |
| `resources/views/admin/cache-manage.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (cache-manage.blade) |
| `resources/views/admin/categories.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (categories.blade) |
| `resources/views/admin/communication/index.blade.php` | আন্তর্জাতিক ইমেইল, হোয়াটসঅ্যাপ ও অ্যাব্যান্ডন্ড কার্ট অটোমেশন ভিউ (index.blade.php) |
| `resources/views/admin/content/books_form.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (books_form.blade) |
| `resources/views/admin/content/ebooks_form.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (ebooks_form.blade) |
| `resources/views/admin/content/form.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (form.blade) |
| `resources/views/admin/currencies/index.blade.php` | মাল্টি-কারেন্সি ও রিয়েল-টাইম এফএক্স এক্সচেঞ্জ রেট ভিউ (index.blade.php) |
| `resources/views/admin/customers/index.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (index.blade) |
| `resources/views/admin/dashboard.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (dashboard.blade) |
| `resources/views/admin/ebooks.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (ebooks.blade) |
| `resources/views/admin/ecommerce-orders.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (ecommerce-orders.blade) |
| `resources/views/admin/gateways/index.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (index.blade) |
| `resources/views/admin/gateways/royalty_payout_logs.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (royalty_payout_logs.blade) |
| `resources/views/admin/honorariums/index.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (index.blade) |
| `resources/views/admin/media.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (media.blade) |
| `resources/views/admin/orders/ecommerce-invoice.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (ecommerce-invoice.blade) |
| `resources/views/admin/orders/ecommerce-slip.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (ecommerce-slip.blade) |
| `resources/views/admin/orders.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (orders.blade) |
| `resources/views/admin/partials/branding-modal.blade.php` | এডমিন প্যানেল হেডার, টপবার ও স্মার্ট অ্যাকর্ডিয়ন সাইডবার কম্পোনেন্ট (branding-modal.blade.php) |
| `resources/views/admin/partials/data-table.blade.php` | এডমিন প্যানেল হেডার, টপবার ও স্মার্ট অ্যাকর্ডিয়ন সাইডবার কম্পোনেন্ট (data-table.blade.php) |
| `resources/views/admin/partials/filters.blade.php` | এডমিন প্যানেল হেডার, টপবার ও স্মার্ট অ্যাকর্ডিয়ন সাইডবার কম্পোনেন্ট (filters.blade.php) |
| `resources/views/admin/partials/sidebar.blade.php` | এডমিন প্যানেল হেডার, টপবার ও স্মার্ট অ্যাকর্ডিয়ন সাইডবার কম্পোনেন্ট (sidebar.blade.php) |
| `resources/views/admin/partials/topbar.blade.php` | এডমিন প্যানেল হেডার, টপবার ও স্মার্ট অ্যাকর্ডিয়ন সাইডবার কম্পোনেন্ট (topbar.blade.php) |
| `resources/views/admin/payments/index.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (index.blade) |
| `resources/views/admin/payouts/index.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (index.blade) |
| `resources/views/admin/payouts/receipt.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (receipt.blade) |
| `resources/views/admin/pos/index.blade.php` | বইমেলা ও অফলাইন স্টল পিওএস বারকোড স্ক্যানার ও রিসিট টেমপ্লেট (index.blade.php) |
| `resources/views/admin/pos/receipt.blade.php` | বইমেলা ও অফলাইন স্টল পিওএস বারকোড স্ক্যানার ও রিসিট টেমপ্লেট (receipt.blade.php) |
| `resources/views/admin/profile/index.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (index.blade) |
| `resources/views/admin/publishers/show.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (show.blade) |
| `resources/views/admin/publishers.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (publishers.blade) |
| `resources/views/admin/purchases/create.blade.php` | অন্যান্য প্রকাশনীর বই ক্রয় ও ভেন্ডর লেজার ভিউ (create.blade.php) |
| `resources/views/admin/purchases/edit.blade.php` | অন্যান্য প্রকাশনীর বই ক্রয় ও ভেন্ডর লেজার ভিউ (edit.blade.php) |
| `resources/views/admin/purchases/index.blade.php` | অন্যান্য প্রকাশনীর বই ক্রয় ও ভেন্ডর লেজার ভিউ (index.blade.php) |
| `resources/views/admin/purchases/ledger.blade.php` | অন্যান্য প্রকাশনীর বই ক্রয় ও ভেন্ডর লেজার ভিউ (ledger.blade.php) |
| `resources/views/admin/purchases/monthly-report.blade.php` | অন্যান্য প্রকাশনীর বই ক্রয় ও ভেন্ডর লেজার ভিউ (monthly-report.blade.php) |
| `resources/views/admin/purchases/partials/branding-modal.blade.php` | অন্যান্য প্রকাশনীর বই ক্রয় ও ভেন্ডর লেজার ভিউ (branding-modal.blade.php) |
| `resources/views/admin/purchases/payments.blade.php` | অন্যান্য প্রকাশনীর বই ক্রয় ও ভেন্ডর লেজার ভিউ (payments.blade.php) |
| `resources/views/admin/purchases/show.blade.php` | অন্যান্য প্রকাশনীর বই ক্রয় ও ভেন্ডর লেজার ভিউ (show.blade.php) |
| `resources/views/admin/purchases/voucher.blade.php` | অন্যান্য প্রকাশনীর বই ক্রয় ও ভেন্ডর লেজার ভিউ (voucher.blade.php) |
| `resources/views/admin/registrations/edit.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (edit.blade) |
| `resources/views/admin/registrations/index.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (index.blade) |
| `resources/views/admin/registrations/show.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (show.blade) |
| `resources/views/admin/reports/print.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (print.blade) |
| `resources/views/admin/roles-permissions.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (roles-permissions.blade) |
| `resources/views/admin/royalties/index.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (index.blade) |
| `resources/views/admin/royalties/sales_report.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (sales_report.blade) |
| `resources/views/admin/sub-admins/create.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (create.blade) |
| `resources/views/admin/sub-admins/index.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (index.blade) |
| `resources/views/admin/sub-admins/show.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (show.blade) |
| `resources/views/admin/subscriptions/index.blade.php` | আইডিয়া আনলিমিটেড সাবস্ক্রিপশন ও মেম্বারশিপ ভিউ (index.blade.php) |
| `resources/views/admin/system-settings.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (system-settings.blade) |
| `resources/views/admin/tickets/index.blade.php` | কাস্টমার ৩৬০° হেল্পডেস্ক সাপোর্ট টিকিট ভিউ (index.blade.php) |
| `resources/views/admin/tickets/show.blade.php` | কাস্টমার ৩৬০° হেল্পডেস্ক সাপোর্ট টিকিট ভিউ (show.blade.php) |
| `resources/views/admin/translations/index.blade.php` | বহুভাষিক অনুবাদ ম্যানেজার ও AI ট্রান্সলেটর ভিউ (index.blade.php) |
| `resources/views/admin/users-security.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (users-security.blade) |
| `resources/views/admin/users.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (users.blade) |
| `resources/views/admin/webzines.blade.php` | এডমিন প্যানেল ম্যানেজমেন্ট ইন্টারফেস ভিউ (webzines.blade) |

### 📁 Blade Views (Seller & Sub-Admin Panel) (7 files)

| ফাইল পাথ | কী কী কাজ করছে (কাজের সুনির্দিষ্ট শিরোনাম ও দায়িত্ব) |
| :--- | :--- |
| `resources/views/subadmin/billing/accounts.blade.php` | সেলার ও সাব-এডমিন পিওএস বিলিং ও লেজার ভিউ (accounts.blade) |
| `resources/views/subadmin/billing/create.blade.php` | সেলার ও সাব-এডমিন পিওএস বিলিং ও লেজার ভিউ (create.blade) |
| `resources/views/subadmin/billing/edit.blade.php` | সেলার ও সাব-এডমিন পিওএস বিলিং ও লেজার ভিউ (edit.blade) |
| `resources/views/subadmin/billing/index.blade.php` | সেলার ও সাব-এডমিন পিওএস বিলিং ও লেজার ভিউ (index.blade) |
| `resources/views/subadmin/billing/receipt.blade.php` | সেলার ও সাব-এডমিন পিওএস বিলিং ও লেজার ভিউ (receipt.blade) |
| `resources/views/subadmin/billing/show.blade.php` | সেলার ও সাব-এডমিন পিওএস বিলিং ও লেজার ভিউ (show.blade) |
| `resources/views/subadmin/seller-accounts.blade.php` | সেলার ও সাব-এডমিন পিওএস বিলিং ও লেজার ভিউ (seller-accounts.blade) |

### 📁 Blade Views (Storefront, E-Books & Public) (76 files)

| ফাইল পাথ | কী কী কাজ করছে (কাজের সুনির্দিষ্ট শিরোনাম ও দায়িত্ব) |
| :--- | :--- |
| `resources/views/about.blade.php` | সিস্টেম ফাইল: about.blade.php |
| `resources/views/auth/forgot-password.blade.php` | সিস্টেম ফাইল: forgot-password.blade.php |
| `resources/views/auth/login.blade.php` | সিস্টেম ফাইল: login.blade.php |
| `resources/views/auth/partials/base-fields.blade.php` | সিস্টেম ফাইল: base-fields.blade.php |
| `resources/views/auth/pending-approval.blade.php` | সিস্টেম ফাইল: pending-approval.blade.php |
| `resources/views/auth/register-author.blade.php` | সিস্টেম ফাইল: register-author.blade.php |
| `resources/views/auth/register-buyer.blade.php` | সিস্টেম ফাইল: register-buyer.blade.php |
| `resources/views/auth/register-choose.blade.php` | সিস্টেম ফাইল: register-choose.blade.php |
| `resources/views/auth/register-publisher.blade.php` | সিস্টেম ফাইল: register-publisher.blade.php |
| `resources/views/auth/register-seller.blade.php` | সিস্টেম ফাইল: register-seller.blade.php |
| `resources/views/auth/reset-password-otp.blade.php` | সিস্টেম ফাইল: reset-password-otp.blade.php |
| `resources/views/auth/reset-password.blade.php` | সিস্টেম ফাইল: reset-password.blade.php |
| `resources/views/author/dashboard.blade.php` | সিস্টেম ফাইল: dashboard.blade.php |
| `resources/views/author/ebooks/create.blade.php` | সিস্টেম ফাইল: create.blade.php |
| `resources/views/author/ebooks/edit.blade.php` | সিস্টেম ফাইল: edit.blade.php |
| `resources/views/author/ebooks/index.blade.php` | সিস্টেম ফাইল: index.blade.php |
| `resources/views/author/honorariums.blade.php` | সিস্টেম ফাইল: honorariums.blade.php |
| `resources/views/author/layout.blade.php` | সিস্টেম ফাইল: layout.blade.php |
| `resources/views/author/payouts.blade.php` | সিস্টেম ফাইল: payouts.blade.php |
| `resources/views/author/posts/create.blade.php` | সিস্টেম ফাইল: create.blade.php |
| `resources/views/author/posts/edit.blade.php` | সিস্টেম ফাইল: edit.blade.php |
| `resources/views/author/posts/index.blade.php` | সিস্টেম ফাইল: index.blade.php |
| `resources/views/author/royalties.blade.php` | সিস্টেম ফাইল: royalties.blade.php |
| `resources/views/authors/index.blade.php` | সিস্টেম ফাইল: index.blade.php |
| `resources/views/authors/show.blade.php` | সিস্টেম ফাইল: show.blade.php |
| `resources/views/components/author-card.blade.php` | সিস্টেম ফাইল: author-card.blade.php |
| `resources/views/components/book-card.blade.php` | সিস্টেম ফাইল: book-card.blade.php |
| `resources/views/components/brand-logo.blade.php` | সিস্টেম ফাইল: brand-logo.blade.php |
| `resources/views/components/cart-drawer.blade.php` | সিস্টেম ফাইল: cart-drawer.blade.php |
| `resources/views/components/cart-summary.blade.php` | সিস্টেম ফাইল: cart-summary.blade.php |
| `resources/views/components/category-list.blade.php` | সিস্টেম ফাইল: category-list.blade.php |
| `resources/views/components/footer.blade.php` | সিস্টেম ফাইল: footer.blade.php |
| `resources/views/components/header.blade.php` | সিস্টেম ফাইল: header.blade.php |
| `resources/views/components/hero.blade.php` | সিস্টেম ফাইল: hero.blade.php |
| `resources/views/components/product.blade.php` | সিস্টেম ফাইল: product.blade.php |
| `resources/views/components/webzine-article-card.blade.php` | সিস্টেম ফাইল: webzine-article-card.blade.php |
| `resources/views/components/webzine-hero.blade.php` | সিস্টেম ফাইল: webzine-hero.blade.php |
| `resources/views/contact.blade.php` | সিস্টেম ফাইল: contact.blade.php |
| `resources/views/emails/blog-approved.blade.php` | সিস্টেম ফাইল: blog-approved.blade.php |
| `resources/views/emails/customer-invoice.blade.php` | সিস্টেম ফাইল: customer-invoice.blade.php |
| `resources/views/emails/order-confirmation.blade.php` | সিস্টেম ফাইল: order-confirmation.blade.php |
| `resources/views/emails/password-reset-link.blade.php` | সিস্টেম ফাইল: password-reset-link.blade.php |
| `resources/views/emails/publisher-purchase-order.blade.php` | সিস্টেম ফাইল: publisher-purchase-order.blade.php |
| `resources/views/emails/user-approved.blade.php` | সিস্টেম ফাইল: user-approved.blade.php |
| `resources/views/errors/404.blade.php` | সিস্টেম ফাইল: 404.blade.php |
| `resources/views/errors/500.blade.php` | সিস্টেম ফাইল: 500.blade.php |
| `resources/views/frontend/checkout.blade.php` | পাবলিক স্টোরফ্রন্ট, ই-কমার্স ও রিডার ইন্টারফেস ভিউ (checkout.blade) |
| `resources/views/frontend/ebooks/preview.blade.php` | পাবলিক স্টোরফ্রন্ট, ই-কমার্স ও রিডার ইন্টারফেস ভিউ (preview.blade) |
| `resources/views/frontend/ebooks/reader.blade.php` | ডিআরএম ওয়াটারমার্ক সুরক্ষিত আন্তর্জাতিক মানের ই-বুক রিডার ভিউ |
| `resources/views/frontend/home.blade.php` | পাবলিক স্টোরফ্রন্ট, ই-কমার্স ও রিডার ইন্টারফেস ভিউ (home.blade) |
| `resources/views/frontend/index.blade.php` | পাবলিক স্টোরফ্রন্ট, ই-কমার্স ও রিডার ইন্টারফেস ভিউ (index.blade) |
| `resources/views/frontend/pages/about.blade.php` | পাবলিক স্টোরফ্রন্ট, ই-কমার্স ও রিডার ইন্টারফেস ভিউ (about.blade) |
| `resources/views/frontend/pages/authors.blade.php` | পাবলিক স্টোরফ্রন্ট, ই-কমার্স ও রিডার ইন্টারফেস ভিউ (authors.blade) |
| `resources/views/frontend/pages/cart.blade.php` | পাবলিক স্টোরফ্রন্ট, ই-কমার্স ও রিডার ইন্টারফেস ভিউ (cart.blade) |
| `resources/views/frontend/pages/contact.blade.php` | পাবলিক স্টোরফ্রন্ট, ই-কমার্স ও রিডার ইন্টারফেস ভিউ (contact.blade) |
| `resources/views/frontend/pages/hub.blade.php` | পাবলিক স্টোরফ্রন্ট, ই-কমার্স ও রিডার ইন্টারফেস ভিউ (hub.blade) |
| `resources/views/frontend/pages/my-account.blade.php` | পাবলিক স্টোরফ্রন্ট, ই-কমার্স ও রিডার ইন্টারফেস ভিউ (my-account.blade) |
| `resources/views/frontend/pages/publisher-detail.blade.php` | পাবলিক স্টোরফ্রন্ট, ই-কমার্স ও রিডার ইন্টারফেস ভিউ (publisher-detail.blade) |
| `resources/views/frontend/pages/publishers.blade.php` | পাবলিক স্টোরফ্রন্ট, ই-কমার্স ও রিডার ইন্টারফেস ভিউ (publishers.blade) |
| `resources/views/frontend/pages/research-detail.blade.php` | পাবলিক স্টোরফ্রন্ট, ই-কমার্স ও রিডার ইন্টারফেস ভিউ (research-detail.blade) |
| `resources/views/frontend/pages/research.blade.php` | পাবলিক স্টোরফ্রন্ট, ই-কমার্স ও রিডার ইন্টারফেস ভিউ (research.blade) |
| `resources/views/frontend/user/devices.blade.php` | পাবলিক স্টোরফ্রন্ট, ই-কমার্স ও রিডার ইন্টারফেস ভিউ (devices.blade) |
| `resources/views/invoices/public-show.blade.php` | সিস্টেম ফাইল: public-show.blade.php |
| `resources/views/layouts/admin.blade.php` | মাস্টার লেআউট ও বেস থিম স্ট্রাকচার টেমপ্লেট (admin.blade) |
| `resources/views/layouts/app.blade.php` | মাস্টার লেআউট ও বেস থিম স্ট্রাকচার টেমপ্লেট (app.blade) |
| `resources/views/layouts/footer.blade.php` | মাস্টার লেআউট ও বেস থিম স্ট্রাকচার টেমপ্লেট (footer.blade) |
| `resources/views/layouts/header.blade.php` | মাস্টার লেআউট ও বেস থিম স্ট্রাকচার টেমপ্লেট (header.blade) |
| `resources/views/partials/cart-drawer.blade.php` | সিস্টেম ফাইল: cart-drawer.blade.php |
| `resources/views/partials/google-ad.blade.php` | সিস্টেম ফাইল: google-ad.blade.php |
| `resources/views/payment/fail.blade.php` | সিস্টেম ফাইল: fail.blade.php |
| `resources/views/payment/success.blade.php` | সিস্টেম ফাইল: success.blade.php |
| `resources/views/publisher/challan.blade.php` | সিস্টেম ফাইল: challan.blade.php |
| `resources/views/publisher/dashboard.blade.php` | সিস্টেম ফাইল: dashboard.blade.php |
| `resources/views/webzine/index.blade.php` | সিস্টেম ফাইল: index.blade.php |
| `resources/views/webzine/show.blade.php` | সিস্টেম ফাইল: show.blade.php |
| `resources/views/welcome.blade.php` | সিস্টেম ফাইল: welcome.blade.php |

### 📁 Configuration Files (14 files)

| ফাইল পাথ | কী কী কাজ করছে (কাজের সুনির্দিষ্ট শিরোনাম ও দায়িত্ব) |
| :--- | :--- |
| `config/app.php` | অ্যাপ্লিকেশন নাম, টাইমজোন (Asia/Dhaka), ভাষা ও সার্ভিস প্রোভাইডার কনফিগারেশন |
| `config/auth.php` | ইউজার অথেন্টিকেশন গার্ডস, রোল ও পাসওয়ার্ড হ্যাশিং সেটিংস |
| `config/brand.php` | আইডিয়া প্রকাশনের ব্র্যান্ড নাম, হেল্পলাইন, ঠিকানা ও মেটা ট্যাগ কনফিগারেশন |
| `config/cache.php` | রেডিস, ফাইল ও ওপি-ক্যাশ ড্রাইভ কনফিগারেশন |
| `config/courier.php` | সিস্টেম ফাইল: courier.php |
| `config/database.php` | MySQL ও SQLite ডাটাবেজ কানেকশন, পোর্ট ও ক্রেডেনশিয়াল সেটিংস |
| `config/drm.php` | সিস্টেম ফাইল: drm.php |
| `config/filesystems.php` | লোকাল স্টোরেজ, পাবলিক ডিস্ক ও এসথ্রি ক্লাউড ফাইল ড্রাইভার সেটিংস |
| `config/logging.php` | সিস্টেম এরর ও অডিট লগ চ্যানেল কনফিগারেশন |
| `config/mail.php` | Amazon SES, SMTP ও SendGrid ইমেইল গেটওয়ে সেটিংস |
| `config/queue.php` | ব্যাকগ্রাউন্ড জব ও কিউ ওয়ার্কার কনফিগারেশন |
| `config/services.php` | বিকাশ, নগদ, স্ট্রাইপ, পেপ্যাল ও থার্ড-পার্টি সার্ভিস ক্রেডেনশিয়াল |
| `config/session.php` | কুকি ও সেশন লাইফটাইম সিকিউরিটি কনফিগারেশন |
| `config/sms.php` | সিস্টেম ফাইল: sms.php |

### 📁 Route Definition Files (2 files)

| ফাইল পাথ | কী কী কাজ করছে (কাজের সুনির্দিষ্ট শিরোনাম ও দায়িত্ব) |
| :--- | :--- |
| `routes/console.php` | ক্রন জব ও শিডিউলড আর্টিসান কমান্ড রাউটস |
| `routes/web.php` | প্রধান ওয়েবসাইট, এডমিন ড্যাশবোর্ড ও পাবলিক পেজের সমস্ত ওয়েব রাউটস |

### 📁 Public Assets, CSS & JavaScript (34 files)

| ফাইল পাথ | কী কী কাজ করছে (কাজের সুনির্দিষ্ট শিরোনাম ও দায়িত্ব) |
| :--- | :--- |
| `public/.htaccess` | সিস্টেম ফাইল: .htaccess |
| `public/ads.txt` | সিস্টেম ফাইল: ads.txt |
| `public/build/assets/app-BvHm885Z.js` | সিস্টেম ফাইল: app-BvHm885Z.js |
| `public/build/assets/app-CTF8FkcO.css` | সিস্টেম ফাইল: app-CTF8FkcO.css |
| `public/build/manifest.json` | সিস্টেম ফাইল: manifest.json |
| `public/css/admin.css` | এডমিন প্যানেলের সম্পূর্ণ কাস্টম সিএসএস স্টাইলশিট (Dark Mode, Responsive, Dynamic Sidebar) |
| `public/css/site.css` | পাবলিক ওয়েবসাইটের সিএসএস স্টাইলশিট (site.css) |
| `public/favicon.ico` | সিস্টেম ফাইল: favicon.ico |
| `public/fonts/kalpurush/kalpurush.ttf` | সিস্টেম ফাইল: kalpurush.ttf |
| `public/fonts/kalpurush/kalpurush.woff2` | সিস্টেম ফাইল: kalpurush.woff2 |
| `public/google-merchant-feed.xml` | সিস্টেম ফাইল: google-merchant-feed.xml |
| `public/hot` | সিস্টেম ফাইল: hot |
| `public/images/authors/author-1.jpg` | ওয়েবসাইট লোগো, ব্যানার ও গ্রাফিক্যাল অ্যাসেট (author-1.jpg) |
| `public/images/authors/author-2.jpg` | ওয়েবসাইট লোগো, ব্যানার ও গ্রাফিক্যাল অ্যাসেট (author-2.jpg) |
| `public/images/blog/hero.jpg` | ওয়েবসাইট লোগো, ব্যানার ও গ্রাফিক্যাল অ্যাসেট (hero.jpg) |
| `public/images/blog/ideapatra-og.jpg` | ওয়েবসাইট লোগো, ব্যানার ও গ্রাফিক্যাল অ্যাসেট (ideapatra-og.jpg) |
| `public/images/books/book-1.jpg` | ওয়েবসাইট লোগো, ব্যানার ও গ্রাফিক্যাল অ্যাসেট (book-1.jpg) |
| `public/images/books/book-2.jpg` | ওয়েবসাইট লোগো, ব্যানার ও গ্রাফিক্যাল অ্যাসেট (book-2.jpg) |
| `public/images/books/book-3.jpg` | ওয়েবসাইট লোগো, ব্যানার ও গ্রাফিক্যাল অ্যাসেট (book-3.jpg) |
| `public/images/logo-mark.svg` | ওয়েবসাইট লোগো, ব্যানার ও গ্রাফিক্যাল অ্যাসেট (logo-mark.svg) |
| `public/images/logo.png` | ওয়েবসাইট লোগো, ব্যানার ও গ্রাফিক্যাল অ্যাসেট (logo.png) |
| `public/images/logo.svg` | ওয়েবসাইট লোগো, ব্যানার ও গ্রাফিক্যাল অ্যাসেট (logo.svg) |
| `public/images/og-banner.jpg` | ওয়েবসাইট লোগো, ব্যানার ও গ্রাফিক্যাল অ্যাসেট (og-banner.jpg) |
| `public/images/og-banner.png` | ওয়েবসাইট লোগো, ব্যানার ও গ্রাফিক্যাল অ্যাসেট (og-banner.png) |
| `public/images/payments/bkash.svg` | ওয়েবসাইট লোগো, ব্যানার ও গ্রাফিক্যাল অ্যাসেট (bkash.svg) |
| `public/images/payments/mastercard.svg` | ওয়েবসাইট লোগো, ব্যানার ও গ্রাফিক্যাল অ্যাসেট (mastercard.svg) |
| `public/images/payments/visa.svg` | ওয়েবসাইট লোগো, ব্যানার ও গ্রাফিক্যাল অ্যাসেট (visa.svg) |
| `public/images/settings/invoice_logo_1787144586.png` | ওয়েবসাইট লোগো, ব্যানার ও গ্রাফিক্যাল অ্যাসেট (invoice_logo_1787144586.png) |
| `public/images/settings/invoice_logo_1787814418.png` | ওয়েবসাইট লোগো, ব্যানার ও গ্রাফিক্যাল অ্যাসেট (invoice_logo_1787814418.png) |
| `public/index.php` | সিস্টেম ফাইল: index.php |
| `public/js/spellchecker.js` | ইন্টারঅ্যাক্টিভ জাভাস্ক্রিপ্ট ফ্রন্টএন্ড স্ক্রিপ্ট (spellchecker.js) |
| `public/robots.txt` | সিস্টেম ফাইল: robots.txt |
| `public/sitemap.xml` | সিস্টেম ফাইল: sitemap.xml |
| `public/storage` | সিস্টেম ফাইল: storage |

### 📁 Core Root Configuration & Environment (278 files)

| ফাইল পাথ | কী কী কাজ করছে (কাজের সুনির্দিষ্ট শিরোনাম ও দায়িত্ব) |
| :--- | :--- |
| `.claude/settings.local.json` | সিস্টেম ফাইল: settings.local.json |
| `.cpanel.yml` | সিস্টেম ফাইল: .cpanel.yml |
| `.editorconfig` | সিস্টেম ফাইল: .editorconfig |
| `.env` | লাইভ সার্ভার ডাটাবেজ, মেইল ও সিকিউরিটি এনভায়রনমেন্ট কনফিগারেশন |
| `.env.example` | ডাটাবেজ ও এনভায়রনমেন্ট ভেরিয়েবল টেমপ্লেট ফাইল |
| `.env.local` | সিস্টেম ফাইল: .env.local |
| `.htaccess` | সিস্টেম ফাইল: .htaccess |
| `.phpunit.result.cache` | সিস্টেম ফাইল: .phpunit.result.cache |
| `.stylelintrc.json` | সিস্টেম ফাইল: .stylelintrc.json |
| `.vscode/settings.json` | সিস্টেম ফাইল: settings.json |
| `.vscode/sftp.json` | সিস্টেম ফাইল: sftp.json |
| `app/Console/Commands/SetupDemoData.php` | সিস্টেম ফাইল: SetupDemoData.php |
| `app/Events/DeviceLimitReachedEvent.php` | সিস্টেম ফাইল: DeviceLimitReachedEvent.php |
| `app/Events/OrderPlacedEvent.php` | সিস্টেম ফাইল: OrderPlacedEvent.php |
| `app/Exceptions/Handler.php` | সিস্টেম ফাইল: Handler.php |
| `app/Helpers/CurrencyHelper.php` | সিস্টেম ফাইল: CurrencyHelper.php |
| `app/Helpers/DeviceHelper.php` | সিস্টেম ফাইল: DeviceHelper.php |
| `app/Http/Admin/UserController.php` | কাস্টমার প্রোফাইল, অর্ডার হিস্ট্রি ও ই-বুক লাইব্রেরি কন্ট্রোলার |
| `app/Http/Middleware/AffiliateTracking.php` | সিস্টেম ফাইল: AffiliateTracking.php |
| `app/Http/Middleware/MinifyHtmlResponse.php` | সিস্টেম ফাইল: MinifyHtmlResponse.php |
| `app/Http/Middleware/RoleMiddleware.php` | সিস্টেম ফাইল: RoleMiddleware.php |
| `app/Http/Middleware/SecurityHeaders.php` | সিস্টেম ফাইল: SecurityHeaders.php |
| `app/Http/Middleware/SetLocale.php` | সিস্টেম ফাইল: SetLocale.php |
| `app/Http/Middleware/TrackVisitor.php` | সিস্টেম ফাইল: TrackVisitor.php |
| `app/Http/Middleware/VerifyCsrfToken.php` | সিস্টেম ফাইল: VerifyCsrfToken.php |
| `app/Listeners/RevokeOldDeviceSession.php` | সিস্টেম ফাইল: RevokeOldDeviceSession.php |
| `app/Listeners/SendOrderNotification.php` | সিস্টেম ফাইল: SendOrderNotification.php |
| `app/Mail/BlogPostApprovedMail.php` | সিস্টেম ফাইল: BlogPostApprovedMail.php |
| `app/Mail/CustomerInvoiceMail.php` | সিস্টেম ফাইল: CustomerInvoiceMail.php |
| `app/Mail/PasswordResetLinkMail.php` | সিস্টেম ফাইল: PasswordResetLinkMail.php |
| `app/Mail/PublisherPurchaseOrderMail.php` | সিস্টেম ফাইল: PublisherPurchaseOrderMail.php |
| `app/Mail/UserApprovedMail.php` | সিস্টেম ফাইল: UserApprovedMail.php |
| `app/Providers/AppServiceProvider.php` | সিস্টেম ফাইল: AppServiceProvider.php |
| `app/Providers/ModularServiceProvider.php` | সিস্টেম ফাইল: ModularServiceProvider.php |
| `artisan` | লারাবেল কমান্ড লাইন ইন্টারফেস (CLI) এক্সিকিউটেবল স্ক্রিপ্ট |
| `bootstrap/app.php` | অ্যাপ্লিকেশন নাম, টাইমজোন (Asia/Dhaka), ভাষা ও সার্ভিস প্রোভাইডার কনফিগারেশন |
| `bootstrap/providers.php` | সিস্টেম ফাইল: providers.php |
| `CODE_OF_CONDUCT.md` | সিস্টেম ফাইল: CODE_OF_CONDUCT.md |
| `COMPLETE_SETUP.bat` | সিস্টেম ফাইল: COMPLETE_SETUP.bat |
| `composer.json` | পিএইচপি প্যাকেজ ও লাইব্রেরি ডিপেনডেন্সি কনফিগারেশন ফাইল |
| `composer.lock` | ইনস্টলকৃত পিএইচপি প্যাকেজের সুনির্দিষ্ট ভার্সন লক ফাইল |
| `CUSTOMIZATION_COPYRIGHT.md` | সিস্টেম ফাইল: CUSTOMIZATION_COPYRIGHT.md |
| `database/.gitignore` | গিট রিপোজিটরির অপ্রয়োজনীয় ফাইল ইগনোর রুলস |
| `database/database.sqlite` | সিস্টেম ফাইল: database.sqlite |
| `database/factories/UserFactory.php` | সিস্টেম ফাইল: UserFactory.php |
| `fileroot` | সিস্টেম ফাইল: fileroot |
| `INSTALLATION.md` | সার্ভারে প্রজেক্ট সেটআপ ও ডিপ্লয়মেন্ট নির্দেশিকা |
| `lang/bn/auth.php` | ইউজার অথেন্টিকেশন গার্ডস, রোল ও পাসওয়ার্ড হ্যাশিং সেটিংস |
| `lang/bn/pagination.php` | সিস্টেম ফাইল: pagination.php |
| `lang/bn/site.php` | সিস্টেম ফাইল: site.php |
| `lang/bn/validation.php` | সিস্টেম ফাইল: validation.php |
| `LICENSE` | সিস্টেম ফাইল: LICENSE |
| `Modules/Author/Http/Controllers/Frontend/AuthorController.php` | পাবলিক লেখক তালিকা ও লেখক বিস্তারিত প্রোফাইল কন্ট্রোলার |
| `Modules/Author/Models/Author.php` | লেখক প্রোফাইল, ছবি, বায়ো ও সামাজিক যোগাযোগ লিংক মডেল |
| `Modules/Author/Models/AuthorSubmission.php` | সিস্টেম ফাইল: AuthorSubmission.php |
| `Modules/Author/Providers/AuthorServiceProvider.php` | সিস্টেম ফাইল: AuthorServiceProvider.php |
| `Modules/Author/Resources/views/index.blade.php` | সিস্টেম ফাইল: index.blade.php |
| `Modules/Author/Resources/views/register.blade.php` | সিস্টেম ফাইল: register.blade.php |
| `Modules/Author/Resources/views/show.blade.php` | সিস্টেম ফাইল: show.blade.php |
| `Modules/Author/Routes/api.php` | মোবাইল অ্যাপ ও বাহ্যিক সফটওয়্যারের জন্য RESTful API রাউটস |
| `Modules/Author/Routes/web.php` | প্রধান ওয়েবসাইট, এডমিন ড্যাশবোর্ড ও পাবলিক পেজের সমস্ত ওয়েব রাউটস |
| `Modules/Billing/Routes/api.php` | মোবাইল অ্যাপ ও বাহ্যিক সফটওয়্যারের জন্য RESTful API রাউটস |
| `Modules/Billing/Routes/web.php` | প্রধান ওয়েবসাইট, এডমিন ড্যাশবোর্ড ও পাবলিক পেজের সমস্ত ওয়েব রাউটস |
| `Modules/Blog/Http/Controllers/Admin/BlogController.php` | সিস্টেম ফাইল: BlogController.php |
| `Modules/Blog/Http/Controllers/Frontend/BlogController.php` | সিস্টেম ফাইল: BlogController.php |
| `Modules/Blog/Models/BlogCategory.php` | সিস্টেম ফাইল: BlogCategory.php |
| `Modules/Blog/Models/BlogPost.php` | সিস্টেম ফাইল: BlogPost.php |
| `Modules/Blog/Models/BlogTag.php` | সিস্টেম ফাইল: BlogTag.php |
| `Modules/Blog/Providers/BlogServiceProvider.php` | সিস্টেম ফাইল: BlogServiceProvider.php |
| `Modules/Blog/Resources/views/category.blade.php` | সিস্টেম ফাইল: category.blade.php |
| `Modules/Blog/Resources/views/index.blade.php` | সিস্টেম ফাইল: index.blade.php |
| `Modules/Blog/Resources/views/show.blade.php` | সিস্টেম ফাইল: show.blade.php |
| `Modules/Blog/Resources/views/tag.blade.php` | সিস্টেম ফাইল: tag.blade.php |
| `Modules/Blog/Routes/admin.php` | সিস্টেম ফাইল: admin.php |
| `Modules/Blog/Routes/api.php` | মোবাইল অ্যাপ ও বাহ্যিক সফটওয়্যারের জন্য RESTful API রাউটস |
| `Modules/Blog/Routes/web.php` | প্রধান ওয়েবসাইট, এডমিন ড্যাশবোর্ড ও পাবলিক পেজের সমস্ত ওয়েব রাউটস |
| `Modules/Book/Http/Controllers/Frontend/BookController.php` | সিস্টেম ফাইল: BookController.php |
| `Modules/Book/Models/Book.php` | ফিজিক্যাল বই, স্টক, মূল্য, ছাড়, আইএসবিএন ও বিবরণ মডেল |
| `Modules/Book/Models/Category.php` | সিস্টেম ফাইল: Category.php |
| `Modules/Book/Models/Wishlist.php` | সিস্টেম ফাইল: Wishlist.php |
| `Modules/Book/Resources/views/frontend/index.blade.php` | সিস্টেম ফাইল: index.blade.php |
| `Modules/Book/Resources/views/frontend/partials/book-card.blade.php` | সিস্টেম ফাইল: book-card.blade.php |
| `Modules/Book/Resources/views/frontend/show.blade.php` | সিস্টেম ফাইল: show.blade.php |
| `Modules/Book/Resources/views/pdf/invoice.blade.php` | সিস্টেম ফাইল: invoice.blade.php |
| `Modules/Book/Routes/api.php` | মোবাইল অ্যাপ ও বাহ্যিক সফটওয়্যারের জন্য RESTful API রাউটস |
| `Modules/Book/Routes/web.php` | প্রধান ওয়েবসাইট, এডমিন ড্যাশবোর্ড ও পাবলিক পেজের সমস্ত ওয়েব রাউটস |
| `Modules/Book/Services/BookFilterService.php` | সিস্টেম ফাইল: BookFilterService.php |
| `Modules/Book/Services/RecommendationEngine.php` | সিস্টেম ফাইল: RecommendationEngine.php |
| `Modules/Book/Services/WishlistService.php` | সিস্টেম ফাইল: WishlistService.php |
| `Modules/BulkOrder/Http/Controllers/Frontend/BulkOrderController.php` | সিস্টেম ফাইল: BulkOrderController.php |
| `Modules/BulkOrder/Models/BulkOrder.php` | সিস্টেম ফাইল: BulkOrder.php |
| `Modules/BulkOrder/Models/BulkOrderItem.php` | সিস্টেম ফাইল: BulkOrderItem.php |
| `Modules/BulkOrder/Providers/BulkOrderServiceProvider.php` | সিস্টেম ফাইল: BulkOrderServiceProvider.php |
| `Modules/BulkOrder/Routes/api.php` | মোবাইল অ্যাপ ও বাহ্যিক সফটওয়্যারের জন্য RESTful API রাউটস |
| `Modules/BulkOrder/Services/BulkOrderService.php` | সিস্টেম ফাইল: BulkOrderService.php |
| `Modules/Ebook/Http/Controllers/Frontend/EbookController.php` | সিস্টেম ফাইল: EbookController.php |
| `Modules/Ebook/Models/Ebook.php` | ডিজিটাল ই-বুক, ফাইল পাথ, পেজ সংখ্যা ও লাইসেন্স মডেল |
| `Modules/Ebook/Resources/views/frontend/index.blade.php` | সিস্টেম ফাইল: index.blade.php |
| `Modules/Ebook/Resources/views/frontend/read.blade.php` | সিস্টেম ফাইল: read.blade.php |
| `Modules/Ebook/Resources/views/frontend/show.blade.php` | সিস্টেম ফাইল: show.blade.php |
| `Modules/Ebook/Resources/views/index.blade.php` | সিস্টেম ফাইল: index.blade.php |
| `Modules/Ebook/Resources/views/read.blade.php` | সিস্টেম ফাইল: read.blade.php |
| `Modules/Ebook/Resources/views/reader/epub_viewer.blade.php` | সিস্টেম ফাইল: epub_viewer.blade.php |
| `Modules/Ebook/Resources/views/reader/pdf_viewer.blade.php` | সিস্টেম ফাইল: pdf_viewer.blade.php |
| `Modules/Ebook/Resources/views/show.blade.php` | সিস্টেম ফাইল: show.blade.php |
| `Modules/Ebook/Routes/api.php` | মোবাইল অ্যাপ ও বাহ্যিক সফটওয়্যারের জন্য RESTful API রাউটস |
| `Modules/Ebook/Routes/web.php` | প্রধান ওয়েবসাইট, এডমিন ড্যাশবোর্ড ও পাবলিক পেজের সমস্ত ওয়েব রাউটস |
| `Modules/Ebook/Services/EbookFilterService.php` | সিস্টেম ফাইল: EbookFilterService.php |
| `Modules/Inventory/Routes/api.php` | মোবাইল অ্যাপ ও বাহ্যিক সফটওয়্যারের জন্য RESTful API রাউটস |
| `Modules/Inventory/Routes/web.php` | প্রধান ওয়েবসাইট, এডমিন ড্যাশবোর্ড ও পাবলিক পেজের সমস্ত ওয়েব রাউটস |
| `Modules/KidsZone/Http/Controllers/Frontend/KidsZoneController.php` | সিস্টেম ফাইল: KidsZoneController.php |
| `Modules/KidsZone/Models/KidsZone.php` | সিস্টেম ফাইল: KidsZone.php |
| `Modules/KidsZone/Providers/KidsZoneServiceProvider.php` | সিস্টেম ফাইল: KidsZoneServiceProvider.php |
| `Modules/KidsZone/Routes/api.php` | মোবাইল অ্যাপ ও বাহ্যিক সফটওয়্যারের জন্য RESTful API রাউটস |
| `Modules/KidsZone/Services/KidsZoneService.php` | সিস্টেম ফাইল: KidsZoneService.php |
| `Modules/Marketing/Models/HotDeal.php` | সিস্টেম ফাইল: HotDeal.php |
| `Modules/Marketing/Models/Promotion.php` | সিস্টেম ফাইল: Promotion.php |
| `Modules/Marketing/Routes/api.php` | মোবাইল অ্যাপ ও বাহ্যিক সফটওয়্যারের জন্য RESTful API রাউটস |
| `Modules/Marketing/Routes/web.php` | প্রধান ওয়েবসাইট, এডমিন ড্যাশবোর্ড ও পাবলিক পেজের সমস্ত ওয়েব রাউটস |
| `Modules/Marketing/Services/MarketingService.php` | সিস্টেম ফাইল: MarketingService.php |
| `Modules/Notification/Routes/api.php` | মোবাইল অ্যাপ ও বাহ্যিক সফটওয়্যারের জন্য RESTful API রাউটস |
| `Modules/Notification/Routes/web.php` | প্রধান ওয়েবসাইট, এডমিন ড্যাশবোর্ড ও পাবলিক পেজের সমস্ত ওয়েব রাউটস |
| `Modules/Order/Routes/api.php` | মোবাইল অ্যাপ ও বাহ্যিক সফটওয়্যারের জন্য RESTful API রাউটস |
| `Modules/Order/Routes/web.php` | প্রধান ওয়েবসাইট, এডমিন ড্যাশবোর্ড ও পাবলিক পেজের সমস্ত ওয়েব রাউটস |
| `Modules/Payment/Routes/api.php` | মোবাইল অ্যাপ ও বাহ্যিক সফটওয়্যারের জন্য RESTful API রাউটস |
| `Modules/Payment/Routes/web.php` | প্রধান ওয়েবসাইট, এডমিন ড্যাশবোর্ড ও পাবলিক পেজের সমস্ত ওয়েব রাউটস |
| `Modules/Payment/Services/RefundService.php` | সিস্টেম ফাইল: RefundService.php |
| `Modules/Publisher/Http/Controllers/Frontend/PublisherController.php` | পাবলিক প্রকাশনী তালিকা ও প্রকাশকের বই ডিসপ্লে কন্ট্রোলার |
| `Modules/Publisher/Models/Publisher.php` | প্রকাশনী প্রোফাইল, ঠিকানা, লোগো ও যোগাযোগ মডেল |
| `Modules/Publisher/Providers/PublisherServiceProvider.php` | সিস্টেম ফাইল: PublisherServiceProvider.php |
| `Modules/Publisher/Resources/views/index.blade.php` | সিস্টেম ফাইল: index.blade.php |
| `Modules/Publisher/Resources/views/show.blade.php` | সিস্টেম ফাইল: show.blade.php |
| `Modules/Publisher/Routes/web.php` | প্রধান ওয়েবসাইট, এডমিন ড্যাশবোর্ড ও পাবলিক পেজের সমস্ত ওয়েব রাউটস |
| `Modules/ReaderSpace/Routes/api.php` | মোবাইল অ্যাপ ও বাহ্যিক সফটওয়্যারের জন্য RESTful API রাউটস |
| `Modules/ReaderSpace/Routes/web.php` | প্রধান ওয়েবসাইট, এডমিন ড্যাশবোর্ড ও পাবলিক পেজের সমস্ত ওয়েব রাউটস |
| `Modules/Research/Http/Controllers/Frontend/ResearchController.php` | একাডেমিক গবেষণা প্রবন্ধ ও জার্নাল আর্কাইভ কন্ট্রোলার |
| `Modules/Research/Models/ResearchPaper.php` | সিস্টেম ফাইল: ResearchPaper.php |
| `Modules/Research/Providers/ResearchServiceProvider.php` | সিস্টেম ফাইল: ResearchServiceProvider.php |
| `Modules/Research/Resources/views/index.blade.php` | সিস্টেম ফাইল: index.blade.php |
| `Modules/Research/Resources/views/show.blade.php` | সিস্টেম ফাইল: show.blade.php |
| `Modules/Research/Routes/web.php` | প্রধান ওয়েবসাইট, এডমিন ড্যাশবোর্ড ও পাবলিক পেজের সমস্ত ওয়েব রাউটস |
| `Modules/Review/Models/Review.php` | সিস্টেম ফাইল: Review.php |
| `Modules/Review/Routes/api.php` | মোবাইল অ্যাপ ও বাহ্যিক সফটওয়্যারের জন্য RESTful API রাউটস |
| `Modules/Review/Routes/web.php` | প্রধান ওয়েবসাইট, এডমিন ড্যাশবোর্ড ও পাবলিক পেজের সমস্ত ওয়েব রাউটস |
| `Modules/Sales/Routes/api.php` | মোবাইল অ্যাপ ও বাহ্যিক সফটওয়্যারের জন্য RESTful API রাউটস |
| `Modules/Sales/Routes/web.php` | প্রধান ওয়েবসাইট, এডমিন ড্যাশবোর্ড ও পাবলিক পেজের সমস্ত ওয়েব রাউটস |
| `Modules/SEO/Routes/api.php` | মোবাইল অ্যাপ ও বাহ্যিক সফটওয়্যারের জন্য RESTful API রাউটস |
| `Modules/SEO/Routes/web.php` | প্রধান ওয়েবসাইট, এডমিন ড্যাশবোর্ড ও পাবলিক পেজের সমস্ত ওয়েব রাউটস |
| `Modules/Social/Routes/api.php` | মোবাইল অ্যাপ ও বাহ্যিক সফটওয়্যারের জন্য RESTful API রাউটস |
| `Modules/Social/Routes/web.php` | প্রধান ওয়েবসাইট, এডমিন ড্যাশবোর্ড ও পাবলিক পেজের সমস্ত ওয়েব রাউটস |
| `Modules/Subscription/Routes/api.php` | মোবাইল অ্যাপ ও বাহ্যিক সফটওয়্যারের জন্য RESTful API রাউটস |
| `Modules/Subscription/Routes/web.php` | প্রধান ওয়েবসাইট, এডমিন ড্যাশবোর্ড ও পাবলিক পেজের সমস্ত ওয়েব রাউটস |
| `Modules/Tag/Http/Controllers/Admin/TagController.php` | সিস্টেম ফাইল: TagController.php |
| `Modules/Tag/Http/Controllers/Frontend/TagController.php` | সিস্টেম ফাইল: TagController.php |
| `Modules/Tag/Models/Tag.php` | সিস্টেম ফাইল: Tag.php |
| `Modules/Tag/Providers/TagServiceProvider.php` | সিস্টেম ফাইল: TagServiceProvider.php |
| `Modules/Tag/Routes/api.php` | মোবাইল অ্যাপ ও বাহ্যিক সফটওয়্যারের জন্য RESTful API রাউটস |
| `Modules/Tag/Services/TagService.php` | সিস্টেম ফাইল: TagService.php |
| `Modules/User/Routes/api.php` | মোবাইল অ্যাপ ও বাহ্যিক সফটওয়্যারের জন্য RESTful API রাউটস |
| `Modules/User/Routes/web.php` | প্রধান ওয়েবসাইট, এডমিন ড্যাশবোর্ড ও পাবলিক পেজের সমস্ত ওয়েব রাউটস |
| `Modules/Vendor/Routes/api.php` | মোবাইল অ্যাপ ও বাহ্যিক সফটওয়্যারের জন্য RESTful API রাউটস |
| `Modules/Vendor/Routes/web.php` | প্রধান ওয়েবসাইট, এডমিন ড্যাশবোর্ড ও পাবলিক পেজের সমস্ত ওয়েব রাউটস |
| `Modules/Webzine/Http/Controllers/Frontend/WebzineController.php` | সিস্টেম ফাইল: WebzineController.php |
| `Modules/Webzine/Models/Webzine.php` | ডিজিটাল ওয়েবজিন ও সাহিত্য সাময়িকী কন্টেন্ট মডেল |
| `Modules/Webzine/Models/WebzineArticle.php` | সিস্টেম ফাইল: WebzineArticle.php |
| `Modules/Webzine/Providers/WebzineServiceProvider.php` | সিস্টেম ফাইল: WebzineServiceProvider.php |
| `Modules/Webzine/Resources/views/frontend/index.blade.php` | সিস্টেম ফাইল: index.blade.php |
| `Modules/Webzine/Resources/views/frontend/read.blade.php` | সিস্টেম ফাইল: read.blade.php |
| `Modules/Webzine/Resources/views/frontend/show.blade.php` | সিস্টেম ফাইল: show.blade.php |
| `Modules/Webzine/Resources/views/index.blade.php` | সিস্টেম ফাইল: index.blade.php |
| `Modules/Webzine/Resources/views/read.blade.php` | সিস্টেম ফাইল: read.blade.php |
| `Modules/Webzine/Resources/views/show.blade.php` | সিস্টেম ফাইল: show.blade.php |
| `Modules/Webzine/Routes/api.php` | মোবাইল অ্যাপ ও বাহ্যিক সফটওয়্যারের জন্য RESTful API রাউটস |
| `Modules/Webzine/Routes/web.php` | প্রধান ওয়েবসাইট, এডমিন ড্যাশবোর্ড ও পাবলিক পেজের সমস্ত ওয়েব রাউটস |
| `package-lock.json` | এনপিএম নোড প্যাকেজের ভার্সন লক ফাইল |
| `package.json` | জাভাস্ক্রিপ্ট ও ফ্রন্টএন্ড এনপিএম ডিপেনডেন্সি কনফিগারেশন |
| `page.html` | সিস্টেম ফাইল: page.html |
| `phpunit.xml` | অটোমেটেড ইউনিট টেস্টিং কনফিগারেশন ফাইল |
| `PLATFORM_DOCS.md` | আইডিয়া প্রকাশন প্ল্যাটফর্ম আর্কিটেকচার ও পূর্ণাঙ্গ ডক্যুমেন্টেশন |
| `postcss.config.cjs` | সিস্টেম ফাইল: postcss.config.cjs |
| `PROJECT_DIRECTORY_MAP.md` | সম্পূর্ণ প্রজেক্টের প্রতিটি ফাইল, ডিরেক্টরি ও রাউটের কাজের বিবরণী ইনডেক্স |
| `QUICK_START.md` | সিস্টেম ফাইল: QUICK_START.md |
| `README.md` | প্রজেক্ট পরিচিতি ও আর্কিটেকচার গাইড |
| `resources/css/app.css` | সিস্টেম ফাইল: app.css |
| `resources/js/app.js` | সিস্টেম ফাইল: app.js |
| `resources/js/bootstrap.js` | সিস্টেম ফাইল: bootstrap.js |
| `scratch/analyze_books_categories.php` | সিস্টেম ফাইল: analyze_books_categories.php |
| `scratch/check_books_data.php` | সিস্টেম ফাইল: check_books_data.php |
| `scratch/check_tables.php` | সিস্টেম ফাইল: check_tables.php |
| `scratch/fix_null_categories.php` | সিস্টেম ফাইল: fix_null_categories.php |
| `scratch/list_all_books.php` | সিস্টেম ফাইল: list_all_books.php |
| `scratch/search_all_db.php` | সিস্টেম ফাইল: search_all_db.php |
| `scratch/test_books_category.php` | সিস্টেম ফাইল: test_books_category.php |
| `scratch/test_books_frontend.php` | সিস্টেম ফাইল: test_books_frontend.php |
| `scratch/test_create_category.php` | সিস্টেম ফাইল: test_create_category.php |
| `scratch/test_page_3.php` | সিস্টেম ফাইল: test_page_3.php |
| `scratch/verify_final_books.php` | সিস্টেম ফাইল: verify_final_books.php |
| `setup.ps1` | সিস্টেম ফাইল: setup.ps1 |
| `setup.sh` | সিস্টেম ফাইল: setup.sh |
| `SETUP_DEMO_DATA.md` | সিস্টেম ফাইল: SETUP_DEMO_DATA.md |
| `storage/app/.gitignore` | গিট রিপোজিটরির অপ্রয়োজনীয় ফাইল ইগনোর রুলস |
| `storage/app/ebooks/sample-demo.opf` | সিস্টেম ফাইল: sample-demo.opf |
| `storage/app/private/.gitignore` | গিট রিপোজিটরির অপ্রয়োজনীয় ফাইল ইগনোর রুলস |
| `storage/app/public/.gitignore` | গিট রিপোজিটরির অপ্রয়োজনীয় ফাইল ইগনোর রুলস |
| `storage/app/public/authors/5dZR9ZBQYQRNClXmzrXuQjeK1MlGCqgY0f6v72Bn.jpg` | স্টোরেজে সংরক্ষিত ইউজার আপলোড মিডিয়া ও ফাইল |
| `storage/app/public/authors/CRQ0KP0eI7L9dZNcCUMJMD2nMBzY62cf2cJMX3kJ.jpg` | স্টোরেজে সংরক্ষিত ইউজার আপলোড মিডিয়া ও ফাইল |
| `storage/app/public/blog/photocard_1787042106_8R0KcNmS.jpeg` | স্টোরেজে সংরক্ষিত ইউজার আপলোড মিডিয়া ও ফাইল |
| `storage/app/public/blog/photocard_1787055124_jHrlMZYk.jpeg` | স্টোরেজে সংরক্ষিত ইউজার আপলোড মিডিয়া ও ফাইল |
| `storage/app/public/blog/photocard_1787460412_KAtQZ8jq.svg` | স্টোরেজে সংরক্ষিত ইউজার আপলোড মিডিয়া ও ফাইল |
| `storage/app/public/blog/photocard_1787460432_XvXVdcv1.svg` | স্টোরেজে সংরক্ষিত ইউজার আপলোড মিডিয়া ও ফাইল |
| `storage/app/public/blog/photocard_1787460441_K8DzvBTU.svg` | স্টোরেজে সংরক্ষিত ইউজার আপলোড মিডিয়া ও ফাইল |
| `storage/app/public/blog/photocard_1787483522_cNpX8c6H.svg` | স্টোরেজে সংরক্ষিত ইউজার আপলোড মিডিয়া ও ফাইল |
| `storage/app/public/books/cover_biplob_bari_firbe.svg` | স্টোরেজে সংরক্ষিত ইউজার আপলোড মিডিয়া ও ফাইল |
| `storage/app/public/books/cover_nadir_opar_akash.svg` | স্টোরেজে সংরক্ষিত ইউজার আপলোড মিডিয়া ও ফাইল |
| `storage/app/public/books/eRVcqYAKcy75hsTY6EmsB9X7cbYiBrP4mdeWqPPS.png` | স্টোরেজে সংরক্ষিত ইউজার আপলোড মিডিয়া ও ফাইল |
| `storage/app/public/books/hhWWKWtzXpeBz1F1bH5MplSa8LTg4Why8U10VnCn.jpg` | স্টোরেজে সংরক্ষিত ইউজার আপলোড মিডিয়া ও ফাইল |
| `storage/app/public/books/qlo6O809BpyrDcCTf3F2MqSWUa8lZhRHPhhNkmQ3.jpg` | স্টোরেজে সংরক্ষিত ইউজার আপলোড মিডিয়া ও ফাইল |
| `storage/app/public/books/samples/2jWnhi4HPgaMbsMHOiMuWi9TZ6KUmsjtPuEX9Yea.pdf` | স্টোরেজে সংরক্ষিত ইউজার আপলোড মিডিয়া ও ফাইল |
| `storage/app/public/books/samples/53rGR8iyobqaunZALewQ3lMvjzmbY4DSvN5zRfBs.pdf` | স্টোরেজে সংরক্ষিত ইউজার আপলোড মিডিয়া ও ফাইল |
| `storage/app/public/ebooks/covers/cover_auto_1787463231_av2NOOVs.svg` | ই-বুকের ভেক্টর কভার ইমেজ (cover_auto_1787463231_av2NOOVs.svg) |
| `storage/app/public/ebooks/covers/cover_auto_1787463242_uIqahH6l.svg` | ই-বুকের ভেক্টর কভার ইমেজ (cover_auto_1787463242_uIqahH6l.svg) |
| `storage/app/public/ebooks/covers/cover_auto_1787463254_jimt4J02.svg` | ই-বুকের ভেক্টর কভার ইমেজ (cover_auto_1787463254_jimt4J02.svg) |
| `storage/app/public/ebooks/covers/cover_auto_1787464175_BuX06zpX.svg` | ই-বুকের ভেক্টর কভার ইমেজ (cover_auto_1787464175_BuX06zpX.svg) |
| `storage/app/public/ebooks/covers/cover_auto_1787467232_ZH3xDNnl.svg` | ই-বুকের ভেক্টর কভার ইমেজ (cover_auto_1787467232_ZH3xDNnl.svg) |
| `storage/app/public/ebooks/covers/cover_auto_1787467247_hFf9Gv4J.svg` | ই-বুকের ভেক্টর কভার ইমেজ (cover_auto_1787467247_hFf9Gv4J.svg) |
| `storage/app/public/ebooks/covers/cover_auto_1787467314_9io7LtpE.svg` | ই-বুকের ভেক্টর কভার ইমেজ (cover_auto_1787467314_9io7LtpE.svg) |
| `storage/app/public/ebooks/covers/cover_auto_1787467328_8Al8qcy7.svg` | ই-বুকের ভেক্টর কভার ইমেজ (cover_auto_1787467328_8Al8qcy7.svg) |
| `storage/app/public/ebooks/covers/cover_auto_1787470140_0fZjDr8y.svg` | ই-বুকের ভেক্টর কভার ইমেজ (cover_auto_1787470140_0fZjDr8y.svg) |
| `storage/app/public/ebooks/covers/cover_auto_1787483475_h8xRp7Dy.svg` | ই-বুকের ভেক্টর কভার ইমেজ (cover_auto_1787483475_h8xRp7Dy.svg) |
| `storage/app/public/ebooks/covers/cover_auto_1787483491_4xUdAzD8.svg` | ই-বুকের ভেক্টর কভার ইমেজ (cover_auto_1787483491_4xUdAzD8.svg) |
| `storage/app/public/ebooks/covers/cover_auto_1787483502_cl4LsuIl.svg` | ই-বুকের ভেক্টর কভার ইমেজ (cover_auto_1787483502_cl4LsuIl.svg) |
| `storage/app/public/ebooks/covers/cover_auto_1787483523_otYezvBZ.svg` | ই-বুকের ভেক্টর কভার ইমেজ (cover_auto_1787483523_otYezvBZ.svg) |
| `storage/app/public/ebooks/files/15oRs8C2J6epDvJAT5B18gnwc0lk0uwjQmMxHlcb.pdf` | ডিজিটাল লাইব্রেরিতে সংরক্ষিত ই-বুক মূল পিডিএফ ফাইল (15oRs8C2J6epDvJAT5B18gnwc0lk0uwjQmMxHlcb.pdf) |
| `storage/app/public/ebooks/files/3Th5slAgqOx03HH47LF7Qlp4gE497Al5NkzNmp24.pdf` | ডিজিটাল লাইব্রেরিতে সংরক্ষিত ই-বুক মূল পিডিএফ ফাইল (3Th5slAgqOx03HH47LF7Qlp4gE497Al5NkzNmp24.pdf) |
| `storage/app/public/ebooks/files/9YeuVmK8Pk1GjOZJ4Yb0wbkdLI6oPpum4yd4k8Ms.pdf` | ডিজিটাল লাইব্রেরিতে সংরক্ষিত ই-বুক মূল পিডিএফ ফাইল (9YeuVmK8Pk1GjOZJ4Yb0wbkdLI6oPpum4yd4k8Ms.pdf) |
| `storage/app/public/ebooks/files/ao1Ytx8qEjUtSxCXXeG0ZuYRR83bnRaguOINHP0P.pdf` | ডিজিটাল লাইব্রেরিতে সংরক্ষিত ই-বুক মূল পিডিএফ ফাইল (ao1Ytx8qEjUtSxCXXeG0ZuYRR83bnRaguOINHP0P.pdf) |
| `storage/app/public/ebooks/files/DdSEzRQwmyxjlx8c9CS74GNn0q5Pe0aFG5Z9hFZe.pdf` | ডিজিটাল লাইব্রেরিতে সংরক্ষিত ই-বুক মূল পিডিএফ ফাইল (DdSEzRQwmyxjlx8c9CS74GNn0q5Pe0aFG5Z9hFZe.pdf) |
| `storage/app/public/ebooks/files/GhlhIuomXath4yvaPpkcTqvjYQBVRdD2rmgi9KeR.pdf` | ডিজিটাল লাইব্রেরিতে সংরক্ষিত ই-বুক মূল পিডিএফ ফাইল (GhlhIuomXath4yvaPpkcTqvjYQBVRdD2rmgi9KeR.pdf) |
| `storage/app/public/ebooks/files/gZ8R6dWEpQetqTUGfuD2ToBH50JYsDYDGvquYJyI.pdf` | ডিজিটাল লাইব্রেরিতে সংরক্ষিত ই-বুক মূল পিডিএফ ফাইল (gZ8R6dWEpQetqTUGfuD2ToBH50JYsDYDGvquYJyI.pdf) |
| `storage/app/public/ebooks/files/KWRfZowGs7hdj4CqaHTqM31RhKb6JMwVzR8GzqJM.pdf` | ডিজিটাল লাইব্রেরিতে সংরক্ষিত ই-বুক মূল পিডিএফ ফাইল (KWRfZowGs7hdj4CqaHTqM31RhKb6JMwVzR8GzqJM.pdf) |
| `storage/app/public/ebooks/files/O6tZoVCdEcLizfQlZAfMO57fC3W6RUjKpjeusdZ2.pdf` | ডিজিটাল লাইব্রেরিতে সংরক্ষিত ই-বুক মূল পিডিএফ ফাইল (O6tZoVCdEcLizfQlZAfMO57fC3W6RUjKpjeusdZ2.pdf) |
| `storage/app/public/ebooks/files/wdf4ATG7XvCdpY4dH5wOdMPLGF0d1zHdczY9nT0H.pdf` | ডিজিটাল লাইব্রেরিতে সংরক্ষিত ই-বুক মূল পিডিএফ ফাইল (wdf4ATG7XvCdpY4dH5wOdMPLGF0d1zHdczY9nT0H.pdf) |
| `storage/app/public/ebooks/files/wzRpnNvTSQZ8ZWTa8GX4TpfYaNz6uCuDzZGKHIm1.pdf` | ডিজিটাল লাইব্রেরিতে সংরক্ষিত ই-বুক মূল পিডিএফ ফাইল (wzRpnNvTSQZ8ZWTa8GX4TpfYaNz6uCuDzZGKHIm1.pdf) |
| `storage/app/public/ebooks/ideaabd-sample.epub` | স্টোরেজে সংরক্ষিত ইউজার আপলোড মিডিয়া ও ফাইল |
| `storage/app/public/ebooks/vWA2SqGTk9WOciQJw5n45cs8QkqcJ9mAnDiSnM9n.jpg` | স্টোরেজে সংরক্ষিত ইউজার আপলোড মিডিয়া ও ফাইল |
| `storage/app/public/images/banners/crop_6a7f47dc34cf28.30074865.png` | ওয়েবসাইট প্রমোশনাল অফার ও স্লাইডার ব্যানার |
| `storage/app/public/images/banners/crop_6a7f47dc3cef95.52512487.png` | ওয়েবসাইট প্রমোশনাল অফার ও স্লাইডার ব্যানার |
| `storage/app/public/images/banners/edm8jQ6fjhtPRPD91cN4bLq7UCoXAa6mQ1GnmfwM.jpg` | ওয়েবসাইট প্রমোশনাল অফার ও স্লাইডার ব্যানার |
| `storage/app/public/images/brand/crop_6a7f47db3365a6.91626056.png` | আইডিয়া প্রকাশন ব্র্যান্ডিং ও লোগো ইমেজ ফাইল |
| `storage/app/public/images/brand/crop_6a7f47dc30ef64.08106695.png` | আইডিয়া প্রকাশন ব্র্যান্ডিং ও লোগো ইমেজ ফাইল |
| `storage/app/public/images/brand/crop_6a7f4902c674c3.57059816.png` | আইডিয়া প্রকাশন ব্র্যান্ডিং ও লোগো ইমেজ ফাইল |
| `storage/app/public/images/brand/crop_6a7f4902d412c9.42697460.png` | আইডিয়া প্রকাশন ব্র্যান্ডিং ও লোগো ইমেজ ফাইল |
| `storage/app/public/images/brand/crop_6a820067930a37.72249706.png` | আইডিয়া প্রকাশন ব্র্যান্ডিং ও লোগো ইমেজ ফাইল |
| `storage/app/public/images/brand/crop_6a820067aec657.78989619.png` | আইডিয়া প্রকাশন ব্র্যান্ডিং ও লোগো ইমেজ ফাইল |
| `storage/app/public/images/brand/crop_6a82007eafe137.34837445.png` | আইডিয়া প্রকাশন ব্র্যান্ডিং ও লোগো ইমেজ ফাইল |
| `storage/app/public/images/brand/crop_6a82007ebd0122.77156194.png` | আইডিয়া প্রকাশন ব্র্যান্ডিং ও লোগো ইমেজ ফাইল |
| `storage/app/public/images/brand/crop_6a82016bb80ec6.24412253.png` | আইডিয়া প্রকাশন ব্র্যান্ডিং ও লোগো ইমেজ ফাইল |
| `storage/app/public/images/brand/crop_6a82016e420946.62339226.png` | আইডিয়া প্রকাশন ব্র্যান্ডিং ও লোগো ইমেজ ফাইল |
| `storage/app/public/images/brand/crop_6a8b1d193bc853.91776971.png` | আইডিয়া প্রকাশন ব্র্যান্ডিং ও লোগো ইমেজ ফাইল |
| `storage/app/public/images/brand/dLGVOxw8oJ9MJnkaZckzkHDMedmfAtdC3fTXrHoL.png` | আইডিয়া প্রকাশন ব্র্যান্ডিং ও লোগো ইমেজ ফাইল |
| `storage/app/public/images/brand/EWuQKmHyw1wb02mGl0kA88RK65bfMgJmstjXKpa1.png` | আইডিয়া প্রকাশন ব্র্যান্ডিং ও লোগো ইমেজ ফাইল |
| `storage/app/public/publishers/8qKiDj1yCmBnu84CkwaK5JCKKcbsUeKYhJVnVmkp.jpg` | ভেন্ডর প্রকাশকদের লোগো ও ব্র্যান্ড ইমেজ |
| `storage/app/public/settings/invoice_logo_1787144586.png` | ইনভয়েস ও মেমো প্রিন্টের জন্য সংরক্ষিত অফিশিয়াল লোগো |
| `storage/app/public/settings/invoice_logo_1787814418.png` | ইনভয়েস ও মেমো প্রিন্টের জন্য সংরক্ষিত অফিশিয়াল লোগো |
| `storage/app/secure/.htaccess` | সিস্টেম ফাইল: .htaccess |
| `stup.sh` | লিনাক্স সার্ভার সেটআপ ও পারমিশন ব্যাশ স্ক্রিপ্ট |
| `tailwind.config.js` | টেইলউইন্ড সিএসএস কালার প্যালেট ও ইউটিলিটি ক্লাস কনফিগারেশন |
| `temp.html` | অস্থায়ী এইচটিএমএল ভিউ প্রিভিউ স্ক্র্যাচ ফাইল |
| `tests/Feature/BookshopPageTest.php` | বুকশপ পেজ ও ক্যাটালগ ইন্টিগ্রেশন অটোমেটেড টেস্ট |
| `tests/Feature/ExampleTest.php` | লারাবেল বেসিক ফিচার ও ইউনিট টেস্ট স্ক্রিপ্ট |
| `tests/TestCase.php` | লারাবেল টেস্ট কেস বেস ক্লাস |
| `tests/Unit/ExampleTest.php` | লারাবেল বেসিক ফিচার ও ইউনিট টেস্ট স্ক্রিপ্ট |
| `test_class.php` | পিএইচপি ক্লাস ও অটোলোড টেস্ট স্ক্রিপ্ট |
| `test_db.php` | ডাটাবেজ কানেক্টিভিটি ও টেবিল কোয়েরি টেস্ট স্ক্রিপ্ট |
| `test_invoice.php` | ইনভয়েস পিডিএফ ও ক্যালকুলেশন ভেরিফিকেশন টেস্ট স্ক্রিপ্ট |
| `test_rokomari_improvements.php` | রকমারি ক্যাটালগ ফিল্ডস ও এসইও টেস্টিং স্ক্রিপ্ট |
| `vite.config.js` | Vite ফ্রন্টএন্ড অ্যাসেট বান্ডলার কনফিগারেশন |
| `zip_project.php` | সম্পূর্ণ প্রজেক্টের সোর্স কোড এক ক্লিকে ব্যাকআপ জিপ ফাইল তৈরি স্ক্রিপ্ট |

---

## 🌐 ৩. সম্পূর্ণ সিস্টেম রাউট ও অ্যাকশন ম্যাপিং (Registered System Routes — 407 Routes)

| মেথড | URL পাথ | রাউট নাম (Route Name) | কন্ট্রোলার / হ্যান্ডলার অ্যাকশন |
| :--- | :--- | :--- | :--- |
| `GET` | `//` | `home` | `App\Http\Controllers\HomeController@index` |
| `GET` | `/about` | `about` | `\Illuminate\Routing\ViewController` |
| `GET` | `/admin` | `admin.index` | `App\Http\Controllers\AdminController@index` |
| `GET` | `/admin/accounting` | `admin.accounting.index` | `App\Http\Controllers\Admin\IdeaAccountingController@index` |
| `GET` | `/admin/accounting/customer-ledger` | `admin.accounting.customer-ledger.index` | `App\Http\Controllers\Admin\IdeaAccountingController@customerLedger` |
| `POST` | `/admin/accounting/customer-ledger/payments` | `admin.accounting.customer-ledger.payments.store` | `App\Http\Controllers\Admin\IdeaAccountingController@storeCustomerLedgerPayment` |
| `GET` | `/admin/accounting/employees` | `admin.accounting.employees.index` | `App\Http\Controllers\Admin\IdeaAccountingController@employees` |
| `POST` | `/admin/accounting/employees` | `admin.accounting.employees.store` | `App\Http\Controllers\Admin\IdeaAccountingController@storeEmployee` |
| `DELETE` | `/admin/accounting/employees/work-logs/{workLog}` | `admin.accounting.employees.work-logs.destroy` | `App\Http\Controllers\Admin\IdeaAccountingController@destroyWorkLog` |
| `PUT` | `/admin/accounting/employees/{employee}` | `admin.accounting.employees.update` | `App\Http\Controllers\Admin\IdeaAccountingController@updateEmployee` |
| `DELETE` | `/admin/accounting/employees/{employee}` | `admin.accounting.employees.destroy` | `App\Http\Controllers\Admin\IdeaAccountingController@destroyEmployee` |
| `GET` | `/admin/accounting/employees/{employee}/ledger` | `admin.accounting.employees.ledger` | `App\Http\Controllers\Admin\IdeaAccountingController@employeeLedger` |
| `GET` | `/admin/accounting/employees/{employee}/work-logs` | `admin.accounting.employees.work-logs.index` | `App\Http\Controllers\Admin\IdeaAccountingController@employeeLedger` |
| `POST` | `/admin/accounting/employees/{employee}/work-logs` | `admin.accounting.employees.work-logs.store` | `App\Http\Controllers\Admin\IdeaAccountingController@storeWorkLog` |
| `POST` | `/admin/accounting/entries` | `admin.accounting.entries.store` | `App\Http\Controllers\Admin\IdeaAccountingController@storeEntry` |
| `DELETE` | `/admin/accounting/entries/{entry}` | `admin.accounting.entries.destroy` | `App\Http\Controllers\Admin\IdeaAccountingController@destroyEntry` |
| `GET` | `/admin/accounting/invoices` | `admin.accounting.invoices.index` | `App\Http\Controllers\Admin\IdeaAccountingController@invoices` |
| `POST` | `/admin/accounting/invoices` | `admin.accounting.invoices.store` | `App\Http\Controllers\Admin\IdeaAccountingController@storeInvoice` |
| `GET` | `/admin/accounting/invoices/create` | `admin.accounting.invoices.create` | `App\Http\Controllers\Admin\IdeaAccountingController@createInvoice` |
| `DELETE` | `/admin/accounting/invoices/payments/{payment}` | `admin.accounting.invoices.payments.destroy` | `App\Http\Controllers\Admin\IdeaAccountingController@destroyInvoicePayment` |
| `GET` | `/admin/accounting/invoices/payments/{payment}/receipt` | `admin.accounting.invoices.payments.receipt` | `App\Http\Controllers\Admin\IdeaAccountingController@invoicePaymentReceipt` |
| `POST` | `/admin/accounting/invoices/quick-store-book` | `admin.accounting.invoices.quick-store-book` | `App\Http\Controllers\Admin\IdeaAccountingController@quickStoreBook` |
| `GET` | `/admin/accounting/invoices/search-books` | `admin.accounting.invoices.search-books` | `App\Http\Controllers\Admin\IdeaAccountingController@searchBooks` |
| `GET` | `/admin/accounting/invoices/{invoice}` | `admin.accounting.invoices.show` | `App\Http\Controllers\Admin\IdeaAccountingController@showInvoice` |
| `PUT` | `/admin/accounting/invoices/{invoice}` | `admin.accounting.invoices.update` | `App\Http\Controllers\Admin\IdeaAccountingController@updateInvoice` |
| `DELETE` | `/admin/accounting/invoices/{invoice}` | `admin.accounting.invoices.destroy` | `App\Http\Controllers\Admin\IdeaAccountingController@destroyInvoice` |
| `POST` | `/admin/accounting/invoices/{invoice}/convert` | `admin.accounting.invoices.convert` | `App\Http\Controllers\Admin\IdeaAccountingController@convertInvoiceType` |
| `GET` | `/admin/accounting/invoices/{invoice}/edit` | `admin.accounting.invoices.edit` | `App\Http\Controllers\Admin\IdeaAccountingController@editInvoice` |
| `POST` | `/admin/accounting/invoices/{invoice}/payments` | `admin.accounting.invoices.payments.store` | `App\Http\Controllers\Admin\IdeaAccountingController@storeInvoicePayment` |
| `POST` | `/admin/accounting/invoices/{invoice}/send-email` | `admin.accounting.invoices.send-email` | `App\Http\Controllers\Admin\IdeaAccountingController@sendInvoiceEmail` |
| `GET` | `/admin/accounting/reports` | `admin.accounting.reports.index` | `App\Http\Controllers\Admin\IdeaAccountingController@reports` |
| `GET` | `/admin/accounting/salary` | `admin.accounting.salary.index` | `App\Http\Controllers\Admin\IdeaAccountingController@salaryDisbursements` |
| `POST` | `/admin/accounting/salary` | `admin.accounting.salary.store` | `App\Http\Controllers\Admin\IdeaAccountingController@storeSalaryPayment` |
| `GET` | `/admin/accounting/salary/{salary}/slip` | `admin.accounting.salary.slip` | `App\Http\Controllers\Admin\IdeaAccountingController@salarySlip` |
| `POST` | `/admin/accounting/settings` | `admin.accounting.settings.update` | `App\Http\Controllers\Admin\IdeaAccountingController@updateSettings` |
| `GET` | `/admin/activity-logs` | `admin.activity-logs` | `App\Http\Controllers\Admin\AdminAccessController@activityLogs` |
| `GET` | `/admin/affiliates` | `admin.affiliates.index` | `App\Http\Controllers\Admin\AffiliateAdminController@index` |
| `POST` | `/admin/affiliates` | `admin.affiliates.store` | `App\Http\Controllers\Admin\AffiliateAdminController@store` |
| `POST` | `/admin/affiliates/{affiliate}/payout` | `admin.affiliates.payout` | `App\Http\Controllers\Admin\AffiliateAdminController@recordPayout` |
| `GET` | `/admin/audit-logs` | `admin.audit-logs.index` | `App\Http\Controllers\Admin\AdminAccessController@activityLogs` |
| `GET` | `/admin/author-honorariums` | `admin.author-honorariums.index` | `App\Http\Controllers\Admin\AuthorHonorariumAdminController@index` |
| `DELETE` | `/admin/author-honorariums/{id}` | `admin.author-honorariums.destroy` | `App\Http\Controllers\Admin\AuthorHonorariumAdminController@destroy` |
| `PATCH` | `/admin/author-honorariums/{id}/status` | `admin.author-honorariums.status` | `App\Http\Controllers\Admin\AuthorHonorariumAdminController@updateStatus` |
| `GET` | `/admin/author-payouts` | `admin.author-payouts.index` | `App\Http\Controllers\Admin\AuthorPayoutAdminController@index` |
| `GET` | `/admin/author-payouts/{id}/receipt` | `admin.author-payouts.receipt` | `App\Http\Controllers\Admin\AuthorRoyaltyAdminController@payoutReceipt` |
| `POST` | `/admin/author-payouts/{payout}/process` | `admin.author-payouts.process` | `App\Http\Controllers\Admin\AuthorPayoutAdminController@process` |
| `GET` | `/admin/author-royalties` | `admin.author-royalties.index` | `App\Http\Controllers\Admin\AuthorRoyaltyAdminController@index` |
| `POST` | `/admin/author-royalties/adjustment` | `admin.author-royalties.adjustment` | `App\Http\Controllers\Admin\AuthorRoyaltyAdminController@storeAdjustment` |
| `GET` | `/admin/authors` | `admin.authors` | `App\Http\Controllers\AdminController@authors` |
| `POST` | `/admin/authors/quick-store` | `admin.authors.quick-store` | `App\Http\Controllers\AdminController@quickStoreAuthor` |
| `GET` | `/admin/authors/{id}/details` | `admin.authors.details` | `App\Http\Controllers\AdminController@authorDetails` |
| `POST` | `/admin/authors/{id}/quick-update` | `admin.authors.quick-update` | `App\Http\Controllers\AdminController@quickUpdateAuthor` |
| `POST` | `/admin/authors/{id}/reset-password` | `admin.authors.reset-password` | `App\Http\Controllers\AdminController@resetAuthorPassword` |
| `POST` | `/admin/authors/{id}/toggle-status` | `admin.authors.toggle-status` | `App\Http\Controllers\AdminController@toggleAuthorStatus` |
| `POST` | `/admin/authors/{id}/toggle-verified` | `admin.authors.toggle-verified` | `App\Http\Controllers\AdminController@toggleAuthorVerified` |
| `GET` | `/admin/backup` | `admin.backup.index` | `App\Http\Controllers\Admin\AdminBackupController@index` |
| `POST` | `/admin/backup/create` | `admin.backup.create` | `App\Http\Controllers\Admin\AdminBackupController@create` |
| `GET` | `/admin/backup/download/{filename}` | `admin.backup.download` | `App\Http\Controllers\Admin\AdminBackupController@download` |
| `POST` | `/admin/backup/restore/{filename}` | `admin.backup.restore` | `App\Http\Controllers\Admin\AdminBackupController@restore` |
| `POST` | `/admin/backup/upload` | `admin.backup.upload` | `App\Http\Controllers\Admin\AdminBackupController@upload` |
| `DELETE` | `/admin/backup/{filename}` | `admin.backup.destroy` | `App\Http\Controllers\Admin\AdminBackupController@destroy` |
| `GET` | `/admin/blog` | `admin.blog` | `App\Http\Controllers\AdminController@blog` |
| `GET` | `/admin/blog-categories` | `admin.blog-categories` | `App\Http\Controllers\AdminController@blogCategories` |
| `POST` | `/admin/blog/bulk-action` | `admin.blog.bulk-action` | `App\Http\Controllers\AdminController@bulkBlogAction` |
| `POST` | `/admin/blog/bulk-normalize-typography` | `admin.blog.bulk-normalize-typography` | `App\Http\Controllers\AdminController@bulkNormalizeBlogTypography` |
| `POST` | `/admin/blog/settings` | `admin.blog.settings.update` | `App\Http\Controllers\AdminController@updateBlogSettings` |
| `DELETE` | `/admin/blog/{id}` | `admin.blog.destroy` | `App\Http\Controllers\AdminController@destroyPost` |
| `POST` | `/admin/blog/{id}/toggle-featured` | `admin.blog.toggle-featured` | `App\Http\Controllers\AdminController@togglePostFeatured` |
| `POST` | `/admin/blog/{id}/toggle-status` | `admin.blog.toggle-status` | `App\Http\Controllers\AdminController@togglePostStatus` |
| `GET` | `/admin/book-requests` | `admin.book-requests.index` | `App\Http\Controllers\BookRequestController@index` |
| `POST` | `/admin/book-requests/admin-store` | `admin.book-requests.admin-store` | `App\Http\Controllers\BookRequestController@storeAdmin` |
| `POST` | `/admin/book-requests/bulk-action` | `admin.book-requests.bulk-action` | `App\Http\Controllers\BookRequestController@bulkAction` |
| `PATCH` | `/admin/book-requests/{id}` | `admin.book-requests.update` | `App\Http\Controllers\BookRequestController@updateStatus` |
| `DELETE` | `/admin/book-requests/{id}` | `admin.book-requests.destroy` | `App\Http\Controllers\BookRequestController@destroy` |
| `POST` | `/admin/book-requests/{id}/notes` | `admin.book-requests.notes` | `App\Http\Controllers\BookRequestController@updateNotes` |
| `GET` | `/admin/books` | `admin.books` | `App\Http\Controllers\AdminController@books` |
| `POST` | `/admin/books/quick-stock` | `admin.books.quick-stock` | `App\Http\Controllers\AdminController@quickUpdateStock` |
| `POST` | `/admin/books/quick-update` | `admin.books.quick-update` | `App\Http\Controllers\AdminController@quickUpdateBook` |
| `POST` | `/admin/books/{id}/approve` | `admin.books.approve` | `App\Http\Controllers\AdminController@approveBook` |
| `POST` | `/admin/books/{id}/reject` | `admin.books.reject` | `App\Http\Controllers\AdminController@rejectBook` |
| `POST` | `/admin/books/{id}/toggle-status` | `admin.books.toggle-status` | `App\Http\Controllers\AdminController@toggleBookStatus` |
| `GET` | `/admin/bundles` | `admin.bundles.index` | `App\Http\Controllers\Admin\BundleAdminController@index` |
| `POST` | `/admin/bundles` | `admin.bundles.store` | `App\Http\Controllers\Admin\BundleAdminController@storeBundle` |
| `PATCH` | `/admin/bundles/pre-orders/{preOrder}/status` | `admin.bundles.pre-orders.status` | `App\Http\Controllers\Admin\BundleAdminController@updatePreOrderStatus` |
| `POST` | `/admin/cache/clear-all` | `admin.cache.clear-all` | `App\Http\Controllers\Admin\AdminCacheController@clearAll` |
| `POST` | `/admin/cache/clear-app` | `admin.cache.clear-app` | `App\Http\Controllers\Admin\AdminCacheController@clearApp` |
| `POST` | `/admin/cache/clear-config` | `admin.cache.clear-config` | `App\Http\Controllers\Admin\AdminCacheController@clearConfig` |
| `POST` | `/admin/cache/clear-routes` | `admin.cache.clear-routes` | `App\Http\Controllers\Admin\AdminCacheController@clearRoutes` |
| `POST` | `/admin/cache/clear-views` | `admin.cache.clear-views` | `App\Http\Controllers\Admin\AdminCacheController@clearViews` |
| `GET` | `/admin/cache/manage` | `admin.cache.manage` | `App\Http\Controllers\Admin\AdminCacheController@index` |
| `POST` | `/admin/cache/optimize` | `admin.cache.optimize` | `App\Http\Controllers\Admin\AdminCacheController@optimize` |
| `GET` | `/admin/categories` | `admin.categories` | `App\Http\Controllers\AdminController@categories` |
| `GET` | `/admin/communication` | `admin.communication.index` | `App\Http\Controllers\Admin\CommunicationAdminController@index` |
| `PUT` | `/admin/communication/templates/{template}` | `admin.communication.templates.update` | `App\Http\Controllers\Admin\CommunicationAdminController@updateTemplate` |
| `POST` | `/admin/communication/test-send` | `admin.communication.test-send` | `App\Http\Controllers\Admin\CommunicationAdminController@sendTest` |
| `POST` | `/admin/content/{type}` | `admin.content.store` | `App\Http\Controllers\Admin\ContentController@store` |
| `GET` | `/admin/content/{type}/create` | `admin.content.create` | `App\Http\Controllers\Admin\ContentController@create` |
| `GET` | `/admin/content/{type}/{id}` | `admin.content.show` | `App\Http\Controllers\Admin\ContentController@show` |
| `PUT` | `/admin/content/{type}/{id}` | `admin.content.update` | `App\Http\Controllers\Admin\ContentController@update` |
| `DELETE` | `/admin/content/{type}/{id}` | `admin.content.destroy` | `App\Http\Controllers\Admin\ContentController@destroy` |
| `PATCH` | `/admin/content/{type}/{id}/approve` | `admin.content.approve` | `App\Http\Controllers\Admin\ContentController@approve` |
| `GET` | `/admin/content/{type}/{id}/edit` | `admin.content.edit` | `App\Http\Controllers\Admin\ContentController@edit` |
| `PATCH` | `/admin/content/{type}/{id}/reject` | `admin.content.reject` | `App\Http\Controllers\Admin\ContentController@reject` |
| `PATCH` | `/admin/content/{type}/{id}/restore` | `admin.content.restore` | `App\Http\Controllers\Admin\ContentController@restore` |
| `GET` | `/admin/currencies` | `admin.currencies.index` | `App\Http\Controllers\Admin\AdminCurrencyController@index` |
| `POST` | `/admin/currencies` | `admin.currencies.store` | `App\Http\Controllers\Admin\AdminCurrencyController@store` |
| `POST` | `/admin/currencies/sync` | `admin.currencies.sync` | `App\Http\Controllers\Admin\AdminCurrencyController@syncRates` |
| `PUT` | `/admin/currencies/{currency}` | `admin.currencies.update` | `App\Http\Controllers\Admin\AdminCurrencyController@update` |
| `GET` | `/admin/customers` | `admin.customers` | `App\Http\Controllers\AdminController@customers` |
| `POST` | `/admin/customers/broadcast-message` | `admin.customers.broadcast` | `App\Http\Controllers\AdminController@broadcastMessage` |
| `GET` | `/admin/dashboard` | `admin.dashboard` | `App\Http\Controllers\AdminController@dashboard` |
| `GET` | `/admin/ebook-sales-report` | `admin.ebook-sales-report` | `App\Http\Controllers\Admin\AuthorRoyaltyAdminController@salesReport` |
| `GET` | `/admin/ebooks` | `admin.ebooks` | `App\Http\Controllers\AdminController@ebooks` |
| `POST` | `/admin/ebooks/settings` | `admin.ebooks.settings` | `App\Http\Controllers\AdminController@updateEbookSettings` |
| `POST` | `/admin/ebooks/{id}/approve` | `admin.ebooks.approve` | `App\Http\Controllers\AdminController@approveEbook` |
| `POST` | `/admin/ebooks/{id}/reject` | `admin.ebooks.reject` | `App\Http\Controllers\AdminController@rejectEbook` |
| `POST` | `/admin/ebooks/{id}/toggle-status` | `admin.ebooks.toggle-status` | `App\Http\Controllers\AdminController@toggleEbookStatus` |
| `GET` | `/admin/ecommerce-orders` | `admin.ecommerce-orders` | `App\Http\Controllers\AdminController@ecommerceOrders` |
| `GET` | `/admin/ecommerce-orders/{order}` | `admin.ecommerce-orders.show` | `App\Http\Controllers\AdminController@showEcommerceOrder` |
| `PUT` | `/admin/ecommerce-orders/{order}` | `admin.ecommerce-orders.update` | `App\Http\Controllers\AdminController@updateEcommerceOrder` |
| `DELETE` | `/admin/ecommerce-orders/{order}` | `admin.ecommerce-orders.destroy` | `App\Http\Controllers\AdminController@destroyEcommerceOrder` |
| `GET` | `/admin/ecommerce-orders/{order}/invoice` | `admin.ecommerce-orders.invoice` | `App\Http\Controllers\AdminController@ecommerceOrderInvoice` |
| `GET` | `/admin/ecommerce-orders/{order}/slip` | `admin.ecommerce-orders.slip` | `App\Http\Controllers\AdminController@ecommerceOrderSlip` |
| `PATCH` | `/admin/ecommerce-orders/{order}/status` | `admin.ecommerce-orders.status` | `App\Http\Controllers\AdminController@updateEcommerceOrderStatus` |
| `GET` | `/admin/gateway-reports` | `admin.gateway-reports` | `App\Http\Controllers\Admin\GatewayReportController@index` |
| `GET` | `/admin/media` | `admin.media.index` | `App\Http\Controllers\Admin\AdminMediaController@index` |
| `DELETE` | `/admin/media` | `admin.media.destroy` | `App\Http\Controllers\Admin\AdminMediaController@destroy` |
| `POST` | `/admin/media/upload` | `admin.media.upload` | `App\Http\Controllers\Admin\AdminMediaController@upload` |
| `GET` | `/admin/moderation` | `admin.moderation` | `App\Http\Controllers\Admin\ContentController@queue` |
| `GET` | `/admin/orders` | `admin.orders` | `App\Http\Controllers\AdminController@orders` |
| `GET` | `/admin/payments` | `admin.payments.index` | `App\Http\Controllers\Admin\PaymentAdminController@index` |
| `POST|PUT|PATCH` | `/admin/payments` | `admin.payments.update` | `App\Http\Controllers\Admin\PaymentAdminController@updateGateways` |
| `PATCH` | `/admin/payments/{order}/status` | `admin.payments.status` | `App\Http\Controllers\Admin\PaymentAdminController@updateStatus` |
| `GET` | `/admin/pos` | `admin.pos.index` | `App\Http\Controllers\Admin\PosAdminController@index` |
| `POST` | `/admin/pos/checkout` | `admin.pos.checkout` | `App\Http\Controllers\Admin\PosAdminController@checkout` |
| `GET` | `/admin/pos/receipt/{id}` | `admin.pos.receipt` | `App\Http\Controllers\Admin\PosAdminController@receipt` |
| `GET` | `/admin/pos/search` | `admin.pos.search` | `App\Http\Controllers\Admin\PosAdminController@searchBooks` |
| `GET` | `/admin/profile` | `admin.profile` | `App\Http\Controllers\Admin\AdminProfileController@index` |
| `DELETE` | `/admin/profile/avatar` | `admin.profile.avatar.remove` | `App\Http\Controllers\Admin\AdminProfileController@removeAvatar` |
| `POST` | `/admin/profile/logout-others` | `admin.profile.logout-others` | `App\Http\Controllers\Admin\AdminProfileController@logoutOtherDevices` |
| `POST` | `/admin/profile/password` | `admin.profile.password` | `App\Http\Controllers\Admin\AdminProfileController@updatePassword` |
| `POST` | `/admin/profile/preferences` | `admin.profile.preferences` | `App\Http\Controllers\Admin\AdminProfileController@updatePreferences` |
| `POST` | `/admin/profile/update` | `admin.profile.update` | `App\Http\Controllers\Admin\AdminProfileController@updateProfile` |
| `GET` | `/admin/publishers` | `admin.publishers` | `App\Http\Controllers\AdminController@publishers` |
| `POST` | `/admin/publishers/quick-store` | `admin.publishers.quick-store` | `App\Http\Controllers\AdminController@quickStorePublisher` |
| `GET` | `/admin/publishers/{id}` | `admin.publishers.show` | `App\Http\Controllers\AdminController@publisherShow` |
| `POST` | `/admin/publishers/{id}/quick-payment` | `admin.publishers.quick-payment` | `App\Http\Controllers\AdminController@quickPublisherPayment` |
| `POST` | `/admin/publishers/{id}/quick-update` | `admin.publishers.quick-update` | `App\Http\Controllers\AdminController@quickUpdatePublisher` |
| `POST` | `/admin/publishers/{id}/send-purchase-order` | `admin.publishers.send-po` | `App\Http\Controllers\AdminController@sendPublisherPurchaseOrderEmail` |
| `POST` | `/admin/publishers/{id}/toggle-status` | `admin.publishers.toggle-status` | `App\Http\Controllers\AdminController@togglePublisherStatus` |
| `GET` | `/admin/purchases` | `admin.purchases.index` | `App\Http\Controllers\Admin\PublisherPurchaseController@index` |
| `POST` | `/admin/purchases` | `admin.purchases.store` | `App\Http\Controllers\Admin\PublisherPurchaseController@store` |
| `GET` | `/admin/purchases/create` | `admin.purchases.create` | `App\Http\Controllers\Admin\PublisherPurchaseController@create` |
| `GET` | `/admin/purchases/ledger` | `admin.purchases.ledger` | `App\Http\Controllers\Admin\PublisherPurchaseController@ledger` |
| `GET` | `/admin/purchases/monthly-report` | `admin.purchases.monthly-report` | `App\Http\Controllers\Admin\PublisherPurchaseController@monthlyReport` |
| `GET` | `/admin/purchases/payments` | `admin.purchases.payments` | `App\Http\Controllers\Admin\PublisherPurchaseController@payments` |
| `POST` | `/admin/purchases/payments` | `admin.purchases.payments.store` | `App\Http\Controllers\Admin\PublisherPurchaseController@storePayment` |
| `DELETE` | `/admin/purchases/payments/{payment}` | `admin.purchases.payments.destroy` | `App\Http\Controllers\Admin\PublisherPurchaseController@destroyPayment` |
| `GET` | `/admin/purchases/payments/{payment}/voucher` | `admin.purchases.payments.voucher` | `App\Http\Controllers\Admin\PublisherPurchaseController@paymentVoucher` |
| `GET` | `/admin/purchases/search-books` | `admin.purchases.search-books` | `App\Http\Controllers\Admin\PublisherPurchaseController@searchBooks` |
| `GET` | `/admin/purchases/{purchase}` | `admin.purchases.show` | `App\Http\Controllers\Admin\PublisherPurchaseController@show` |
| `PUT` | `/admin/purchases/{purchase}` | `admin.purchases.update` | `App\Http\Controllers\Admin\PublisherPurchaseController@update` |
| `DELETE` | `/admin/purchases/{purchase}` | `admin.purchases.destroy` | `App\Http\Controllers\Admin\PublisherPurchaseController@destroy` |
| `GET` | `/admin/purchases/{purchase}/edit` | `admin.purchases.edit` | `App\Http\Controllers\Admin\PublisherPurchaseController@edit` |
| `POST` | `/admin/quick/author` | `admin.quick.author` | `App\Http\Controllers\Admin\QuickResourceController@quickStoreAuthor` |
| `POST` | `/admin/quick/blog-category` | `admin.quick.blog-category` | `App\Http\Controllers\Admin\QuickResourceController@quickStoreBlogCategory` |
| `POST` | `/admin/quick/category` | `admin.quick.category` | `App\Http\Controllers\Admin\QuickResourceController@quickStoreCategory` |
| `POST` | `/admin/quick/publisher` | `admin.quick.publisher` | `App\Http\Controllers\Admin\QuickResourceController@quickStorePublisher` |
| `GET` | `/admin/registrations` | `admin.registrations.index` | `App\Http\Controllers\Admin\RegistrationApprovalController@index` |
| `GET` | `/admin/registrations/{user}` | `admin.registrations.show` | `App\Http\Controllers\Admin\RegistrationApprovalController@show` |
| `PUT` | `/admin/registrations/{user}` | `admin.registrations.update` | `App\Http\Controllers\Admin\RegistrationApprovalController@update` |
| `DELETE` | `/admin/registrations/{user}` | `admin.registrations.cancel` | `App\Http\Controllers\Admin\RegistrationApprovalController@cancel` |
| `PATCH` | `/admin/registrations/{user}/approve` | `admin.registrations.approve` | `App\Http\Controllers\Admin\RegistrationApprovalController@approve` |
| `GET` | `/admin/registrations/{user}/details` | `admin.registrations.details` | `App\Http\Controllers\Admin\RegistrationApprovalController@details` |
| `GET` | `/admin/registrations/{user}/edit` | `admin.registrations.edit` | `App\Http\Controllers\Admin\RegistrationApprovalController@edit` |
| `POST` | `/admin/registrations/{user}/quick-update` | `admin.registrations.quick-update` | `App\Http\Controllers\Admin\RegistrationApprovalController@quickUpdate` |
| `PATCH` | `/admin/registrations/{user}/reject` | `admin.registrations.reject` | `App\Http\Controllers\Admin\RegistrationApprovalController@reject` |
| `PATCH` | `/admin/registrations/{user}/toggle-status` | `admin.registrations.toggle-status` | `App\Http\Controllers\Admin\RegistrationApprovalController@toggleStatus` |
| `GET` | `/admin/reports/print` | `admin.reports.print` | `App\Http\Controllers\AdminController@printReport` |
| `GET` | `/admin/roles-permissions` | `admin.roles.index` | `App\Http\Controllers\Admin\AdminAccessController@rolesPermissions` |
| `POST` | `/admin/roles-permissions` | `admin.roles.update` | `App\Http\Controllers\Admin\AdminAccessController@updatePermissions` |
| `GET` | `/admin/royalty-payout-logs` | `admin.royalty-payout-logs` | `App\Http\Controllers\Admin\GatewayReportController@royaltyPayoutLogs` |
| `GET` | `/admin/sub-admins` | `admin.sub-admins.index` | `App\Http\Controllers\Admin\SubAdminController@index` |
| `POST` | `/admin/sub-admins` | `admin.sub-admins.store` | `App\Http\Controllers\Admin\SubAdminController@store` |
| `GET` | `/admin/sub-admins/create` | `admin.sub-admins.create` | `App\Http\Controllers\Admin\SubAdminController@create` |
| `GET` | `/admin/sub-admins/{user}` | `admin.sub-admins.show` | `App\Http\Controllers\Admin\SubAdminController@show` |
| `DELETE` | `/admin/sub-admins/{user}` | `admin.sub-admins.destroy` | `App\Http\Controllers\Admin\SubAdminController@destroy` |
| `PATCH` | `/admin/sub-admins/{user}/toggle` | `admin.sub-admins.toggle` | `App\Http\Controllers\Admin\SubAdminController@toggle` |
| `GET` | `/admin/subscriptions` | `admin.subscriptions.index` | `App\Http\Controllers\Admin\SubscriptionAdminController@index` |
| `POST` | `/admin/subscriptions/grant` | `admin.subscriptions.grant` | `App\Http\Controllers\Admin\SubscriptionAdminController@grantSubscription` |
| `POST` | `/admin/subscriptions/plans` | `admin.subscriptions.plans.store` | `App\Http\Controllers\Admin\SubscriptionAdminController@storePlan` |
| `GET` | `/admin/system-settings` | `admin.system-settings` | `App\Http\Controllers\Admin\AdminAccessController@systemSettings` |
| `POST` | `/admin/system-settings` | `admin.system-settings.update` | `App\Http\Controllers\Admin\AdminAccessController@updateSystemSettings` |
| `POST` | `/admin/system-settings/clear-cache` | `admin.system-settings.clear-cache` | `App\Http\Controllers\Admin\AdminAccessController@clearCache` |
| `GET` | `/admin/tickets` | `admin.tickets.index` | `App\Http\Controllers\Admin\SupportTicketAdminController@index` |
| `GET` | `/admin/tickets/{ticket}` | `admin.tickets.show` | `App\Http\Controllers\Admin\SupportTicketAdminController@show` |
| `POST` | `/admin/tickets/{ticket}/reply` | `admin.tickets.reply` | `App\Http\Controllers\Admin\SupportTicketAdminController@reply` |
| `PATCH` | `/admin/tickets/{ticket}/status` | `admin.tickets.status` | `App\Http\Controllers\Admin\SupportTicketAdminController@updateStatus` |
| `GET` | `/admin/translations` | `admin.translations.index` | `App\Http\Controllers\Admin\TranslationAdminController@index` |
| `POST` | `/admin/translations` | `admin.translations.store` | `App\Http\Controllers\Admin\TranslationAdminController@store` |
| `POST` | `/admin/translations/auto-translate` | `admin.translations.auto-translate` | `App\Http\Controllers\Admin\TranslationAdminController@autoTranslate` |
| `PUT` | `/admin/translations/{translation}` | `admin.translations.update` | `App\Http\Controllers\Admin\TranslationAdminController@update` |
| `GET` | `/admin/users` | `admin.users` | `App\Http\Controllers\AdminController@users` |
| `GET` | `/admin/users/security` | `admin.users.security.index` | `App\Http\Controllers\Admin\UserSecurityAdminController@index` |
| `POST` | `/admin/users/security/auto-generate-password` | `admin.users.security.auto-generate-password` | `App\Http\Controllers\Admin\UserSecurityAdminController@autoGeneratePassword` |
| `POST` | `/admin/users/security/block-ip` | `admin.users.security.block-ip` | `App\Http\Controllers\Admin\UserSecurityAdminController@blockIp` |
| `POST` | `/admin/users/security/clean-expired` | `admin.users.security.clean-expired` | `App\Http\Controllers\Admin\UserSecurityAdminController@cleanExpired` |
| `POST` | `/admin/users/security/generate-otp` | `admin.users.security.generate-otp` | `App\Http\Controllers\Admin\UserSecurityAdminController@generateOtp` |
| `POST` | `/admin/users/security/unblock-ip` | `admin.users.security.unblock-ip` | `App\Http\Controllers\Admin\UserSecurityAdminController@unblockIp` |
| `GET` | `/admin/visitor-reports` | `admin.visitor-reports` | `App\Http\Controllers\AdminController@visitorReports` |
| `GET` | `/admin/webzines` | `admin.webzines` | `App\Http\Controllers\AdminController@webzines` |
| `GET` | `/ads.txt` | `ads.txt` | `Closure` |
| `POST` | `/api/api/bulk-order` | `—` | `Modules\BulkOrder\Http\Controllers\Frontend\BulkOrderController@store` |
| `GET` | `/api/api/bulk-order/create` | `—` | `Modules\BulkOrder\Http\Controllers\Frontend\BulkOrderController@create` |
| `GET` | `/api/api/bulk-order/my-orders` | `—` | `Modules\BulkOrder\Http\Controllers\Frontend\BulkOrderController@myOrders` |
| `GET` | `/api/api/bulk-order/{order}` | `—` | `Modules\BulkOrder\Http\Controllers\Frontend\BulkOrderController@show` |
| `GET` | `/api/api/kids-zone` | `—` | `Modules\KidsZone\Http\Controllers\Frontend\KidsZoneController@index` |
| `GET` | `/api/api/kids-zone/{zone}` | `—` | `Modules\KidsZone\Http\Controllers\Frontend\KidsZoneController@show` |
| `GET` | `/api/api/kids-zone/{zone}/api` | `—` | `Modules\KidsZone\Http\Controllers\Frontend\KidsZoneController@api` |
| `GET` | `/api/api/tags` | `—` | `Modules\Tag\Http\Controllers\Frontend\TagController@index` |
| `GET` | `/api/api/tags/cloud` | `—` | `Modules\Tag\Http\Controllers\Frontend\TagController@cloud` |
| `GET` | `/api/api/tags/popular` | `—` | `Modules\Tag\Http\Controllers\Frontend\TagController@popular` |
| `GET` | `/api/api/tags/search` | `—` | `Modules\Tag\Http\Controllers\Frontend\TagController@search` |
| `GET` | `/api/api/tags/{tag}` | `—` | `Modules\Tag\Http\Controllers\Frontend\TagController@show` |
| `GET|POST` | `/api/payment/bkash/callback` | `api.payment.bkash.callback` | `App\Http\Controllers\PaymentController@bkashCallback` |
| `POST` | `/api/payment/bkash/create` | `api.payment.bkash.create` | `App\Http\Controllers\PaymentController@createBkashPayment` |
| `GET` | `/api/payment/cancel` | `api.payment.cancel` | `App\Http\Controllers\PaymentController@cancel` |
| `GET` | `/api/payment/fail` | `api.payment.fail` | `App\Http\Controllers\PaymentController@fail` |
| `GET|POST` | `/api/payment/nagad/callback` | `api.payment.nagad.callback` | `App\Http\Controllers\PaymentController@nagadCallback` |
| `POST` | `/api/payment/nagad/create` | `api.payment.nagad.create` | `App\Http\Controllers\PaymentController@createNagadPayment` |
| `GET` | `/api/payment/success` | `api.payment.success` | `App\Http\Controllers\PaymentController@success` |
| `GET` | `/api/recent-orders` | `—` | `App\Http\Controllers\SocialProofController@getRecentOrders` |
| `POST` | `/author-honorarium/send` | `author.honorarium.send` | `App\Http\Controllers\AuthorHonorariumController@store` |
| `GET` | `/author/blog` | `author.blog.index` | `App\Http\Controllers\AuthorBlogController@index` |
| `POST` | `/author/blog` | `author.blog.store` | `App\Http\Controllers\AuthorBlogController@store` |
| `GET` | `/author/blog/create` | `author.blog.create` | `App\Http\Controllers\AuthorBlogController@createPost` |
| `PUT` | `/author/blog/{id}` | `author.blog.update` | `App\Http\Controllers\AuthorBlogController@update` |
| `DELETE` | `/author/blog/{id}` | `author.blog.destroy` | `App\Http\Controllers\AuthorBlogController@destroy` |
| `POST` | `/author/categories/quick-store` | `author.categories.quick-store` | `App\Http\Controllers\Author\AuthorEbookController@quickStoreCategory` |
| `GET` | `/author/dashboard` | `author.dashboard` | `App\Http\Controllers\Author\AuthorDashboardController@dashboard` |
| `GET` | `/author/ebooks` | `author.ebooks.index` | `App\Http\Controllers\Author\AuthorEbookController@index` |
| `POST` | `/author/ebooks` | `author.ebooks.store` | `App\Http\Controllers\Author\AuthorEbookController@store` |
| `GET` | `/author/ebooks/create` | `author.ebooks.create` | `App\Http\Controllers\Author\AuthorEbookController@create` |
| `GET` | `/author/ebooks/{ebook}` | `author.ebooks.show` | `App\Http\Controllers\Author\AuthorEbookController@show` |
| `PUT|PATCH` | `/author/ebooks/{ebook}` | `author.ebooks.update` | `App\Http\Controllers\Author\AuthorEbookController@update` |
| `DELETE` | `/author/ebooks/{ebook}` | `author.ebooks.destroy` | `App\Http\Controllers\Author\AuthorEbookController@destroy` |
| `GET` | `/author/ebooks/{ebook}/edit` | `author.ebooks.edit` | `App\Http\Controllers\Author\AuthorEbookController@edit` |
| `GET` | `/author/honorariums` | `author.honorariums` | `App\Http\Controllers\Author\AuthorDashboardController@honorariums` |
| `GET` | `/author/payouts` | `author.payouts.index` | `App\Http\Controllers\Author\AuthorPayoutController@index` |
| `POST` | `/author/payouts` | `author.payouts.store` | `App\Http\Controllers\Author\AuthorPayoutController@storeRequest` |
| `GET` | `/author/posts` | `author.posts.index` | `App\Http\Controllers\AuthorBlogController@index` |
| `POST` | `/author/posts` | `author.posts.store` | `App\Http\Controllers\AuthorBlogController@store` |
| `GET` | `/author/posts/create` | `author.posts.create` | `App\Http\Controllers\AuthorBlogController@createPost` |
| `PUT` | `/author/posts/{id}` | `author.posts.update` | `App\Http\Controllers\AuthorBlogController@update` |
| `DELETE` | `/author/posts/{id}` | `author.posts.destroy` | `App\Http\Controllers\AuthorBlogController@destroy` |
| `GET` | `/author/posts/{id}/edit` | `author.posts.edit` | `App\Http\Controllers\AuthorBlogController@editPost` |
| `GET` | `/author/royalties` | `author.royalties` | `App\Http\Controllers\Author\AuthorDashboardController@royalties` |
| `GET` | `/authors` | `authors.index` | `App\Http\Controllers\AuthorController@index` |
| `GET` | `/authors/register` | `author.register` | `Modules\Author\Http\Controllers\Frontend\AuthorController@register` |
| `POST` | `/authors/register` | `author.store-registration` | `Modules\Author\Http\Controllers\Frontend\AuthorController@storeRegistration` |
| `GET` | `/authors/{author}` | `authors.show` | `App\Http\Controllers\AuthorController@show` |
| `GET` | `/authors/{slug}` | `author.show` | `Modules\Author\Http\Controllers\Frontend\AuthorController@show` |
| `GET` | `/blog` | `blog.index` | `Modules\Blog\Http\Controllers\Frontend\BlogController@index` |
| `GET` | `/blog/category/{slug}` | `blog.category` | `Modules\Blog\Http\Controllers\Frontend\BlogController@category` |
| `POST` | `/blog/honorarium/send` | `blog.honorarium.send` | `App\Http\Controllers\AuthorHonorariumController@store` |
| `GET` | `/blog/tag/{slug}` | `blog.tag` | `Modules\Blog\Http\Controllers\Frontend\BlogController@tag` |
| `GET` | `/blog/write` | `blog.write` | `App\Http\Controllers\AuthorBlogController@writeGateway` |
| `POST` | `/blog/{id}/review` | `blog.review.store` | `Modules\Blog\Http\Controllers\Frontend\BlogController@storeReview` |
| `GET` | `/blog/{slug}` | `blog.show` | `Modules\Blog\Http\Controllers\Frontend\BlogController@show` |
| `POST` | `/book-requests` | `book-requests.store` | `App\Http\Controllers\BookRequestController@store` |
| `GET` | `/books` | `book.index` | `Modules\Book\Http\Controllers\Frontend\BookController@index` |
| `GET` | `/books/{id}/quick-view` | `book.quick-view` | `Modules\Book\Http\Controllers\Frontend\BookController@quickView` |
| `GET` | `/books/{slug}` | `book.show` | `Modules\Book\Http\Controllers\Frontend\BookController@show` |
| `GET` | `/books/{slug}/preview` | `book.preview` | `Modules\Book\Http\Controllers\Frontend\BookController@preview` |
| `GET` | `/cart` | `cart` | `App\Http\Controllers\CartController@index` |
| `POST` | `/cart/add` | `cart.add` | `Closure` |
| `GET|POST` | `/cart/checkout` | `cart.checkout` | `Closure` |
| `POST` | `/cart/validate-coupon` | `cart.validate-coupon` | `App\Http\Controllers\CartController@validateCoupon` |
| `GET` | `/checkout` | `checkout` | `App\Http\Controllers\CartController@index` |
| `GET` | `/company-panel` | `company-panel` | `Closure` |
| `GET` | `/company-panel/add-book` | `company-panel.add-book` | `Closure` |
| `GET` | `/company-panel/book-list` | `company-panel.books` | `Closure` |
| `GET` | `/company-panel/product-entry` | `company-panel.product-entry` | `Closure` |
| `GET` | `/company-panel/today-purchase-list` | `company-panel.today-purchases` | `Closure` |
| `GET` | `/contact` | `contact` | `\Illuminate\Routing\ViewController` |
| `GET` | `/ebook` | `—` | `Closure` |
| `GET` | `/ebooks` | `ebook.index` | `Modules\Ebook\Http\Controllers\Frontend\EbookController@index` |
| `POST` | `/ebooks/{id}/progress` | `ebook.progress` | `Modules\Ebook\Http\Controllers\Frontend\EbookController@saveProgress` |
| `GET` | `/ebooks/{id}/stream` | `ebook.stream` | `Modules\Ebook\Http\Controllers\Frontend\EbookController@stream` |
| `GET` | `/ebooks/{slug}` | `ebook.show` | `Modules\Ebook\Http\Controllers\Frontend\EbookController@show` |
| `POST` | `/ebooks/{slug}/claim` | `ebook.claim` | `Modules\Ebook\Http\Controllers\Frontend\EbookController@claim` |
| `GET` | `/ebooks/{slug}/download` | `ebook.download` | `Modules\Ebook\Http\Controllers\Frontend\EbookController@download` |
| `GET` | `/ebooks/{slug}/preview` | `ebook.preview` | `Modules\Ebook\Http\Controllers\Frontend\EbookController@preview` |
| `GET` | `/ebooks/{slug}/read` | `ebook.read` | `Modules\Ebook\Http\Controllers\Frontend\EbookController@read` |
| `GET` | `/feed` | `feed` | `App\Http\Controllers\SitemapController@feed` |
| `GET` | `/feeds/google-merchant.xml` | `google.merchant.feed` | `App\Http\Controllers\GoogleMerchantController@feedXml` |
| `GET` | `/forgot-password` | `password.request` | `App\Http\Controllers\Auth\PasswordResetController@showRequestForm` |
| `POST` | `/forgot-password` | `password.email` | `App\Http\Controllers\Auth\PasswordResetController@sendResetLink` |
| `POST` | `/forgot-password/help-request` | `password.help-request` | `App\Http\Controllers\Auth\PasswordResetController@submitHelpRequest` |
| `POST` | `/forgot-password/send` | `password.send-otp` | `App\Http\Controllers\Auth\PasswordResetController@sendResetLink` |
| `GET` | `/google-merchant-feed.xml` | `google.merchant.xml` | `App\Http\Controllers\GoogleMerchantController@feedXml` |
| `GET` | `/google-merchant-products.tsv` | `google.merchant.tsv` | `App\Http\Controllers\GoogleMerchantController@feedTsv` |
| `GET` | `/hub` | `hub` | `\Illuminate\Routing\ViewController` |
| `GET` | `/ideapatra` | `ideapatra.index` | `Modules\Blog\Http\Controllers\Frontend\BlogController@index` |
| `GET` | `/ideapatra/category/{slug}` | `ideapatra.category` | `Modules\Blog\Http\Controllers\Frontend\BlogController@category` |
| `POST` | `/ideapatra/honorarium/send` | `ideapatra.honorarium.send` | `App\Http\Controllers\AuthorHonorariumController@store` |
| `GET` | `/ideapatra/tag/{slug}` | `ideapatra.tag` | `Modules\Blog\Http\Controllers\Frontend\BlogController@tag` |
| `GET` | `/ideapatra/write` | `ideapatra.write` | `App\Http\Controllers\AuthorBlogController@writeGateway` |
| `GET` | `/ideapatra/{slug}` | `ideapatra.show` | `Modules\Blog\Http\Controllers\Frontend\BlogController@show` |
| `GET` | `/invoices/view/{token}` | `invoices.public.show` | `App\Http\Controllers\Admin\IdeaAccountingController@publicShow` |
| `GET` | `/lang/{locale}` | `lang.switch` | `Closure` |
| `GET` | `/login` | `login` | `App\Http\Controllers\Auth\LoginController@showLoginForm` |
| `POST` | `/login` | `—` | `App\Http\Controllers\Auth\LoginController@login` |
| `GET` | `/login/refresh-bot-challenge` | `login.refresh-bot` | `App\Http\Controllers\Auth\LoginController@refreshBotChallenge` |
| `POST` | `/login/verify-visual-challenge` | `login.verify-visual-challenge` | `App\Http\Controllers\Auth\LoginController@verifyVisualChallenge` |
| `GET` | `/login/visual-challenge` | `login.visual-challenge` | `App\Http\Controllers\Auth\LoginController@getVisualChallenge` |
| `GET|POST` | `/logout` | `logout` | `App\Http\Controllers\Auth\LoginController@logout` |
| `GET` | `/magazines` | `magazine.index` | `Closure` |
| `GET` | `/magazines/{slug}` | `magazine.show` | `Closure` |
| `GET` | `/magazines/{slug}/read` | `magazine.read` | `Closure` |
| `GET` | `/merchant-products.xml` | `google.merchant.alt` | `App\Http\Controllers\GoogleMerchantController@feedXml` |
| `GET` | `/my-account` | `my-account` | `App\Http\Controllers\UserController@dashboard` |
| `POST` | `/my-account/address` | `my-account.address.update` | `App\Http\Controllers\UserController@updateAddress` |
| `GET` | `/my-account/orders/{id}` | `my-account.orders.details` | `App\Http\Controllers\UserController@orderDetails` |
| `POST` | `/my-account/password` | `my-account.password.update` | `App\Http\Controllers\UserController@updatePassword` |
| `POST` | `/my-account/profile` | `my-account.profile.update` | `App\Http\Controllers\UserController@updateProfile` |
| `POST` | `/my-account/wishlist/remove/{id}` | `my-account.wishlist.remove` | `App\Http\Controllers\UserController@removeFromWishlist` |
| `POST` | `/newsletter/subscribe` | `newsletter.subscribe` | `Closure` |
| `POST` | `/orders` | `orders.store` | `App\Http\Controllers\OrderController@store` |
| `GET|POST` | `/payment/bkash/callback` | `bkash.callback` | `App\Http\Controllers\PaymentController@bkashCallback` |
| `POST` | `/payment/bkash/create` | `bkash.create` | `App\Http\Controllers\PaymentController@createBkashPayment` |
| `GET` | `/payment/fail` | `payment.fail` | `App\Http\Controllers\PaymentController@fail` |
| `GET|POST` | `/payment/nagad/callback` | `nagad.callback` | `App\Http\Controllers\PaymentController@nagadCallback` |
| `POST` | `/payment/nagad/create` | `nagad.create` | `App\Http\Controllers\PaymentController@createNagadPayment` |
| `POST` | `/payment/sslcommerz/cancel` | `sslcommerz.cancel` | `App\Http\Controllers\PaymentController@sslcommerzCancel` |
| `POST` | `/payment/sslcommerz/create` | `sslcommerz.create` | `App\Http\Controllers\PaymentController@createSslcommerzPayment` |
| `POST` | `/payment/sslcommerz/fail` | `sslcommerz.fail` | `App\Http\Controllers\PaymentController@sslcommerzFail` |
| `POST` | `/payment/sslcommerz/ipn` | `sslcommerz.ipn` | `App\Http\Controllers\PaymentController@sslcommerzIpn` |
| `POST` | `/payment/sslcommerz/success` | `sslcommerz.success` | `App\Http\Controllers\PaymentController@sslcommerzSuccess` |
| `GET` | `/payment/success` | `payment.success` | `App\Http\Controllers\PaymentController@success` |
| `GET` | `/pending-approval` | `pending.approval` | `App\Http\Controllers\Auth\RegistrationController@pendingApproval` |
| `POST` | `/publisher/books` | `publisher.books.store` | `App\Http\Controllers\Publisher\PublisherPortalController@storeBook` |
| `PUT` | `/publisher/books/{id}` | `publisher.books.update` | `App\Http\Controllers\Publisher\PublisherPortalController@updateBook` |
| `DELETE` | `/publisher/books/{id}` | `publisher.books.destroy` | `App\Http\Controllers\Publisher\PublisherPortalController@destroyBook` |
| `GET` | `/publisher/books/{id}/edit` | `publisher.books.edit` | `App\Http\Controllers\Publisher\PublisherPortalController@editBook` |
| `POST` | `/publisher/books/{id}/quick-update` | `publisher.books.quick-update` | `App\Http\Controllers\Publisher\PublisherPortalController@quickUpdateBook` |
| `GET` | `/publisher/dashboard` | `publisher.dashboard` | `App\Http\Controllers\Publisher\PublisherPortalController@dashboard` |
| `POST` | `/publisher/profile` | `publisher.profile.update` | `App\Http\Controllers\Publisher\PublisherPortalController@updateProfile` |
| `GET` | `/publisher/purchases/{id}/challan` | `publisher.purchases.challan` | `App\Http\Controllers\Publisher\PublisherPortalController@printChallan` |
| `GET` | `/publishers` | `publishers.index` | `App\Http\Controllers\PublisherController@index` |
| `GET` | `/publishers/{publisher}` | `publishers.show` | `App\Http\Controllers\PublisherController@show` |
| `GET` | `/publishers/{slug}` | `publisher.show` | `Modules\Publisher\Http\Controllers\Frontend\PublisherController@show` |
| `GET` | `/register` | `register.choose` | `App\Http\Controllers\Auth\RegistrationController@choose` |
| `GET` | `/register/{type}` | `register.form` | `App\Http\Controllers\Auth\RegistrationController@showForm` |
| `POST` | `/register/{type}` | `register.submit` | `App\Http\Controllers\Auth\RegistrationController@register` |
| `GET` | `/research` | `research.index` | `App\Http\Controllers\ResearchController@index` |
| `GET` | `/research/{slug}` | `research.show` | `App\Http\Controllers\ResearchController@show` |
| `GET` | `/research/{slug}/download` | `research.download` | `Modules\Research\Http\Controllers\Frontend\ResearchController@download` |
| `POST` | `/reset-password` | `password.update` | `App\Http\Controllers\Auth\PasswordResetController@resetPassword` |
| `GET` | `/reset-password-otp` | `password.reset-otp` | `App\Http\Controllers\Auth\PasswordResetController@showOtpResetForm` |
| `POST` | `/reset-password-otp` | `password.update-otp` | `App\Http\Controllers\Auth\PasswordResetController@resetPasswordWithOtp` |
| `GET` | `/reset-password/{token}` | `password.reset` | `App\Http\Controllers\Auth\PasswordResetController@showResetForm` |
| `POST` | `/reviews` | `reviews.store` | `App\Http\Controllers\ReviewController@store` |
| `GET` | `/reviews` | `reviews.list` | `App\Http\Controllers\ReviewController@list` |
| `GET` | `/rss.xml` | `rss` | `App\Http\Controllers\SitemapController@feed` |
| `GET` | `/search` | `search` | `Modules\Book\Http\Controllers\Frontend\BookController@index` |
| `GET` | `/seller/accounts` | `subadmin.accounts` | `App\Http\Controllers\SubAdmin\BillingController@sellerAccounts` |
| `GET` | `/seller/api/books/search` | `subadmin.books.search` | `App\Http\Controllers\SubAdmin\BillingController@searchBooks` |
| `GET` | `/seller/bills` | `subadmin.bills.index` | `App\Http\Controllers\SubAdmin\BillingController@index` |
| `POST` | `/seller/bills` | `subadmin.bills.store` | `App\Http\Controllers\SubAdmin\BillingController@store` |
| `POST` | `/seller/bills/bulk-action` | `subadmin.bills.bulk-action` | `App\Http\Controllers\SubAdmin\BillingController@bulkAction` |
| `GET` | `/seller/bills/create` | `subadmin.bills.create` | `App\Http\Controllers\SubAdmin\BillingController@create` |
| `GET` | `/seller/bills/export` | `subadmin.bills.export` | `App\Http\Controllers\SubAdmin\BillingController@exportCsv` |
| `GET` | `/seller/bills/{bill}` | `subadmin.bills.show` | `App\Http\Controllers\SubAdmin\BillingController@show` |
| `PUT` | `/seller/bills/{bill}` | `subadmin.bills.update` | `App\Http\Controllers\SubAdmin\BillingController@update` |
| `DELETE` | `/seller/bills/{bill}` | `subadmin.bills.destroy` | `App\Http\Controllers\SubAdmin\BillingController@destroy` |
| `GET` | `/seller/bills/{bill}/edit` | `subadmin.bills.edit` | `App\Http\Controllers\SubAdmin\BillingController@edit` |
| `POST` | `/seller/bills/{bill}/quick-pay` | `subadmin.bills.quick-pay` | `App\Http\Controllers\SubAdmin\BillingController@quickPay` |
| `GET` | `/seller/bills/{bill}/receipt` | `subadmin.bills.receipt` | `App\Http\Controllers\SubAdmin\BillingController@receipt` |
| `GET` | `/shop` | `shop.index` | `Closure` |
| `GET` | `/shop/{id}/quick-view` | `shop.quick-view` | `Modules\Book\Http\Controllers\Frontend\BookController@quickView` |
| `GET` | `/shop/{slug}` | `shop.show` | `Closure` |
| `GET` | `/shop/{slug}/preview` | `shop.preview` | `Closure` |
| `GET` | `/sitemap.xml` | `sitemap` | `App\Http\Controllers\SitemapController@index` |
| `GET` | `/sitemap/ping` | `sitemap.ping` | `App\Http\Controllers\SitemapController@pingSearchEngines` |
| `GET` | `/sitemaps/authors.xml` | `sitemap.authors` | `App\Http\Controllers\SitemapController@authorsSitemap` |
| `GET` | `/sitemaps/blog.xml` | `sitemap.blog` | `App\Http\Controllers\SitemapController@postsSitemap` |
| `GET` | `/sitemaps/books.xml` | `sitemap.books` | `App\Http\Controllers\SitemapController@booksSitemap` |
| `GET` | `/sitemaps/categories.xml` | `sitemap.categories` | `App\Http\Controllers\SitemapController@categoriesSitemap` |
| `GET` | `/sitemaps/ebooks.xml` | `sitemap.ebooks` | `App\Http\Controllers\SitemapController@ebooksSitemap` |
| `GET` | `/sitemaps/ideapatra.xml` | `sitemap.ideapatra` | `App\Http\Controllers\SitemapController@postsSitemap` |
| `GET` | `/sitemaps/magazines.xml` | `sitemap.magazines` | `App\Http\Controllers\SitemapController@magazinesSitemap` |
| `GET` | `/sitemaps/pages.xml` | `sitemap.pages` | `App\Http\Controllers\SitemapController@pagesSitemap` |
| `GET` | `/sitemaps/posts.xml` | `sitemap.posts` | `App\Http\Controllers\SitemapController@postsSitemap` |
| `GET` | `/sitemaps/publishers.xml` | `sitemap.publishers` | `App\Http\Controllers\SitemapController@publishersSitemap` |
| `GET` | `/sitemaps/research.xml` | `sitemap.research` | `App\Http\Controllers\SitemapController@researchSitemap` |
| `GET` | `/sitemaps/sitemap-index.xml` | `sitemap.index` | `App\Http\Controllers\SitemapController@sitemapIndex` |
| `GET` | `/sitemaps/webzines.xml` | `sitemap.webzines` | `App\Http\Controllers\SitemapController@magazinesSitemap` |
| `GET` | `/sitemaps/{slug}.xml` | `sitemap.dynamic` | `App\Http\Controllers\SitemapController@dynamicPageSitemap` |
| `GET` | `/storage/{path}` | `storage.file` | `Closure` |
| `GET` | `/up` | `—` | `Closure` |
| `GET` | `/user` | `user.portal` | `Closure` |
| `GET` | `/webzines` | `webzine.index` | `Modules\Webzine\Http\Controllers\Frontend\WebzineController@index` |
| `GET` | `/webzines/archive` | `webzine.archive` | `Closure` |
| `GET` | `/webzines/{slug}` | `webzine.show` | `Modules\Webzine\Http\Controllers\Frontend\WebzineController@show` |
| `GET` | `/webzines/{slug}/read` | `webzine.read` | `Modules\Webzine\Http\Controllers\Frontend\WebzineController@read` |
| `GET` | `/wishlist` | `wishlist` | `Closure` |
