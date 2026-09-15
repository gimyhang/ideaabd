@php
     = app(\Modules\Author\Http\Controllers\Frontend\AuthorController::class);
     =  ?? (function() use () {
         = new \ReflectionClass();
        if (->hasMethod('getAllCountryCodes')) {
             = ->getMethod('getAllCountryCodes');
            ->setAccessible(true);
            return ->invoke();
        }
        return [];
    })();

     =  ?? [
        'কবিতা (Poetry)',
        'উপন্যাস (Novel)',
        'ছোটগল্প (Short Story)',
        'প্রবন্ধ ও গবেষণা (Essay & Research)',
        'শিশুসাহিত্য ও কিশোর উপন্যাস (Children & Teen)',
        'অনুবাদ সাহিত্য (Translation)',
        'বিজ্ঞান কল্পকাহিনী (Sci-Fi)',
        'ইতিহাস ও ঐতিহ্য (History & Heritage)',
        'ইসলামিক সাহিত্য ও দর্শন (Islamic Literature)',
        'নাটক ও চিত্রনাট্য (Drama & Screenplay)',
        'স্মৃতিকথা ও আত্মজীবনী (Memoir & Biography)',
        'ভ্রমণকাহিনী (Travelogue)',
        'রম্য ও ব্যঙ্গ রচনা (Satire & Humor)',
        'স্বনির্ভরতা ও মোটিভেশন (Self-Help)',
    ];
@endphp

@include('author::register', ['countries' => , 'genresList' => ])