<?php

namespace App\Support;

/**
 * Bangladesh Administrative Geography Normalizer & Helper.
 * Ensures complete bilingual (Bengali/English) normalization with no duplicates.
 */
class BangladeshGeo
{
    /**
     * Canonical Divisions (Bengali alphabetical order) and their canonical Districts.
     */
    public const DIVISIONS = [
        'খুলনা' => [
            'কুষ্টিয়া', 'খুলনা', 'চুয়াডাঙ্গা', 'ঝিনাইদহ', 'নড়াইল',
            'বাগেরহাট', 'মাগুরা', 'মেহেরপুর', 'যশোর', 'সাতক্ষীরা'
        ],
        'চট্টগ্রাম' => [
            'কক্সবাজার', 'কুমিল্লা', 'খাগড়াছড়ি', 'চট্টগ্রাম', 'চাঁদপুর',
            'নোয়াখালী', 'বান্দরবান', 'ব্রাহ্মণবাড়িয়া', 'লক্ষ্মীপুর', 'রাঙ্গামাটি', 'ফেনী'
        ],
        'ঢাকা' => [
            'কিশোরগঞ্জ', 'গাজীপুর', 'গোপালগঞ্জ', 'টাঙ্গাইল', 'ঢাকা',
            'নরসিংদী', 'নারায়ণগঞ্জ', 'ফরিদপুর', 'মাদারীপুর', 'মানিকগঞ্জ',
            'মুন্সীগঞ্জ', 'রাজবাড়ী', 'শরীয়তপুর'
        ],
        'বরিশাল' => [
            'ঝালকাঠি', 'পটুয়াখালী', 'পিরোজপুর', 'বরগুনা', 'বরিশাল', 'ভোলা'
        ],
        'ময়মনসিংহ' => [
            'জামালপুর', 'নেত্রকোণা', 'ময়মনসিংহ', 'শেরপুর'
        ],
        'রংপুর' => [
            'কুড়িগ্রাম', 'গাইবান্ধা', 'ঠাকুরগাঁও', 'দিনাজপুর',
            'নীলফামারী', 'পঞ্চগড়', 'রংপুর', 'লালমনিরহাট'
        ],
        'রাজশাহী' => [
            'চাঁপাইনবাবগঞ্জ', 'জয়পুরহাট', 'নওগাঁ', 'নাটোর',
            'পাবনা', 'বগুড়া', 'রাজশাহী', 'সিরাজগঞ্জ'
        ],
        'সিলেট' => [
            'মৌলভীবাজার', 'সুনামগঞ্জ', 'সিলেট', 'হবিগঞ্জ'
        ],
    ];

    /**
     * All bilingual aliases mapped to canonical Bengali Division name.
     */
    private const DIVISION_ALIASES = [
        // খুলনা
        'khulna' => 'খুলনা',
        'khulna division' => 'খুলনা',
        'খুলনা' => 'খুলনা',
        'খুলনা বিভাগ' => 'খুলনা',

        // চট্টগ্রাম
        'chattogram' => 'চট্টগ্রাম',
        'chittagong' => 'চট্টগ্রাম',
        'chattogram division' => 'চট্টগ্রাম',
        'chittagong division' => 'চট্টগ্রাম',
        'চট্টগ্রাম' => 'চট্টগ্রাম',
        'চট্টগ্রাম বিভাগ' => 'চট্টগ্রাম',

        // ঢাকা
        'dhaka' => 'ঢাকা',
        'dacca' => 'ঢাকা',
        'dhaka division' => 'ঢাকা',
        'ঢাকা' => 'ঢাকা',
        'ঢাকা বিভাগ' => 'ঢাকা',

        // বরিশাল
        'barishal' => 'বরিশাল',
        'barisal' => 'বরিশাল',
        'barishal division' => 'বরিশাল',
        'barisal division' => 'বরিশাল',
        'বরিশাল' => 'বরিশাল',
        'বরিশাল বিভাগ' => 'বরিশাল',

        // ময়মনসিংহ
        'mymensingh' => 'ময়মনসিংহ',
        'mymensingh division' => 'ময়মনসিংহ',
        'ময়মনসিংহ' => 'ময়মনসিংহ',
        'ময়মনসিংহ বিভাগ' => 'ময়মনসিংহ',

        // রংপুর
        'rangpur' => 'রংপুর',
        'rangpur division' => 'রংপুর',
        'রংপুর' => 'রংপুর',
        'রংপুর বিভাগ' => 'রংপুর',

        // রাজশাহী
        'rajshahi' => 'রাজশাহী',
        'rajshahi division' => 'রাজশাহী',
        'রাজশাহী' => 'রাজশাহী',
        'রাজশাহী বিভাগ' => 'রাজশাহী',

        // সিলেট
        'sylhet' => 'সিলেট',
        'sylhet division' => 'সিলেট',
        'সিলেট' => 'সিলেট',
        'সিলেট বিভাগ' => 'সিলেট',
    ];

    /**
     * All bilingual aliases mapped to canonical Bengali District name.
     */
    private const DISTRICT_ALIASES = [
        // ঢাকা বিভাগ
        'dhaka' => 'ঢাকা', 'dacca' => 'ঢাকা', 'ঢাকা' => 'ঢাকা',
        'faridpur' => 'ফরিদপুর', 'ফরিদপুর' => 'ফরিদপুর',
        'gazipur' => 'গাজীপুর', 'গাজীপুর' => 'গাজীপুর',
        'gopalganj' => 'গোপালগঞ্জ', 'গোপালগঞ্জ' => 'গোপালগঞ্জ',
        'kishoreganj' => 'কিশোরগঞ্জ', 'kishorganj' => 'কিশোরগঞ্জ', 'কিশোরগঞ্জ' => 'কিশোরগঞ্জ',
        'madaripur' => 'মাদারীপুর', 'মাদারীপুর' => 'মাদারীপুর',
        'manikganj' => 'মানিকগঞ্জ', 'মানিকগঞ্জ' => 'মানিকগঞ্জ',
        'munshiganj' => 'মুন্সীগঞ্জ', 'munshigonj' => 'মুন্সীগঞ্জ', 'মুন্সীগঞ্জ' => 'মুন্সীগঞ্জ',
        'narayanganj' => 'নারায়ণগঞ্জ', 'narayonganj' => 'নারায়ণগঞ্জ', 'নারায়ণগঞ্জ' => 'নারায়ণগঞ্জ',
        'narsingdi' => 'নরসিংদী', 'নরসিংদী' => 'নরসিংদী',
        'rajbari' => 'রাজবাড়ী', 'রাজবাড়ী' => 'রাজবাড়ী', 'রাজবাড়ি' => 'রাজবাড়ী',
        'shariatpur' => 'শরীয়তপুর', 'শরীয়তপুর' => 'শরীয়তপুর',
        'tangail' => 'টাঙ্গাইল', 'টাঙ্গাইল' => 'টাঙ্গাইল',

        // চট্টগ্রাম বিভাগ
        'bandarban' => 'বান্দরবান', 'বান্দরবান' => 'বান্দরবান',
        'brahmanbaria' => 'ব্রাহ্মণবাড়িয়া', 'b.baria' => 'ব্রাহ্মণবাড়িয়া', 'b-baria' => 'ব্রাহ্মণবাড়িয়া', 'bbaria' => 'ব্রাহ্মণবাড়িয়া', 'ব্রাহ্মণবাড়িয়া' => 'ব্রাহ্মণবাড়িয়া', 'ব্রাহ্মণবাড়িয়া' => 'ব্রাহ্মণবাড়িয়া',
        'chandpur' => 'চাঁদপুর', 'চাঁদপুর' => 'চাঁদপুর',
        'chattogram' => 'চট্টগ্রাম', 'chittagong' => 'চট্টগ্রাম', 'চট্টগ্রাম' => 'চট্টগ্রাম',
        'cox\'s bazar' => 'কক্সবাজার', 'coxs bazar' => 'কক্সবাজার', 'coxsbazar' => 'কক্সবাজার', 'কক্সবাজার' => 'কক্সবাজার',
        'cumilla' => 'কুমিল্লা', 'comilla' => 'কুমিল্লা', 'কুমিল্লা' => 'কুমিল্লা',
        'feni' => 'ফেনী', 'ফেনী' => 'ফেনী',
        'khagrachhari' => 'খাগড়াছড়ি', 'khagrachari' => 'খাগড়াছড়ি', 'খাগড়াছড়ি' => 'খাগড়াছড়ি', 'খাগড়াছড়ি' => 'খাগড়াছড়ি',
        'lakshmipur' => 'লক্ষ্মীপুর', 'laxmipur' => 'লক্ষ্মীপুর', 'লক্ষ্মীপুর' => 'লক্ষ্মীপুর',
        'noakhali' => 'নোয়াখালী', 'নোয়াখালী' => 'নোয়াখালী', 'নোয়াখালী' => 'নোয়াখালী',
        'rangamati' => 'রাঙ্গামাটি', 'রাঙ্গামাটি' => 'রাঙ্গামাটি',

        // রাজশাহী বিভাগ
        'bogura' => 'বগুড়া', 'bogra' => 'বগুড়া', 'বগুড়া' => 'বগুড়া', 'বগুড়া' => 'বগুড়া',
        'chapainawabganj' => 'চাঁপাইনবাবগঞ্জ', 'chapai nawabganj' => 'চাঁপাইনবাবগঞ্জ', 'nawabganj' => 'চাঁপাইনবাবগঞ্জ', 'চাঁপাইনবাবগঞ্জ' => 'চাঁপাইনবাবগঞ্জ',
        'joypurhat' => 'জয়পুরহাট', 'জয়পুরহাট' => 'জয়পুরহাট',
        'naogaon' => 'নওগাঁ', 'নওগাঁ' => 'নওগাঁ',
        'natore' => 'নাটোর', 'নাটোর' => 'নাটোর',
        'pabna' => 'পাবনা', 'পাবনা' => 'পাবনা',
        'rajshahi' => 'রাজশাহী', 'রাজশাহী' => 'রাজশাহী',
        'sirajganj' => 'সিরাজগঞ্জ', 'সিরাজগঞ্জ' => 'সিরাজগঞ্জ',

        // রংপুর বিভাগ
        'dinajpur' => 'দিনাজপুর', 'দিনাজপুর' => 'দিনাজপুর',
        'gaibandha' => 'গাইবান্ধা', 'গাইবান্ধা' => 'গাইবান্ধা',
        'kurigram' => 'কুড়িগ্রাম', 'কুড়িগ্রাম' => 'কুড়িগ্রাম', 'কুড়িগ্রাম' => 'কুড়িগ্রাম',
        'lalmonirhat' => 'লালমনিরহাট', 'লালমনিরহাট' => 'লালমনিরহাট',
        'nilphamari' => 'নীলফামারী', 'নীলফামারী' => 'নীলফামারী',
        'panchagarh' => 'পঞ্চগড়', 'পঞ্চগড়' => 'পঞ্চগড়', 'পঞ্চগড়' => 'পঞ্চগড়',
        'rangpur' => 'রংপুর', 'রংপুর' => 'রংপুর',
        'thakurgaon' => 'ঠাকুরগাঁও', 'ঠাকুরগাঁও' => 'ঠাকুরগাঁও',

        // খুলনা বিভাগ
        'bagerhat' => 'বাগেরহাট', 'বাগেরহাট' => 'বাগেরহাট',
        'chuadanga' => 'চুয়াডাঙ্গা', 'চুয়াডাঙ্গা' => 'চুয়াডাঙ্গা', 'চুয়াডাঙ্গা' => 'চুয়াডাঙ্গা',
        'jashore' => 'যশোর', 'jessore' => 'যশোর', 'যশোর' => 'যশোর',
        'jhenaidah' => 'ঝিনাইদহ', 'jhenidah' => 'ঝিনাইদহ', 'ঝিনাইদহ' => 'ঝিনাইদহ',
        'khulna' => 'খুলনা', 'খুলনা' => 'খুলনা',
        'kushtia' => 'কুষ্টিয়া', 'kushtea' => 'কুষ্টিয়া', 'কুষ্টিয়া' => 'কুষ্টিয়া', 'কুষ্টিয়া' => 'কুষ্টিয়া',
        'magura' => 'মাগুরা', 'মাগুরা' => 'মাগুরা',
        'meherpur' => 'মেহেরপুর', 'মেহেরপুর' => 'মেহেরপুর',
        'narail' => 'নড়াইল', 'নড়াইল' => 'নড়াইল', 'নড়াইল' => 'নড়াইল',
        'satkhira' => 'সাতক্ষীরা', 'সাতক্ষীরা' => 'সাতক্ষীরা',

        // বরিশাল বিভাগ
        'barguna' => 'বরগুনা', 'বরগুনা' => 'বরগুনা',
        'barishal' => 'বরিশাল', 'barisal' => 'বরিশাল', 'বরিশাল' => 'বরিশাল',
        'bhola' => 'ভোলা', 'ভোলা' => 'ভোলা',
        'jhalokati' => 'ঝালকাঠি', 'jhalakathi' => 'ঝালকাঠি', 'ঝালকাঠি' => 'ঝালকাঠি',
        'patuakhali' => 'পটুয়াখালী', 'পটুয়াখালী' => 'পটুয়াখালী', 'পটুয়াখালী' => 'পটুয়াখালী',
        'pirojpur' => 'পিরোজপুর', 'perojpur' => 'পিরোজপুর', 'পিরোজপুর' => 'পিরোজপুর',

        // সিলেট বিভাগ
        'habiganj' => 'হবিগঞ্জ', 'হবিগঞ্জ' => 'হবিগঞ্জ',
        'moulvibazar' => 'মৌলভীবাজার', 'maulvibazar' => 'মৌলভীবাজার', 'moulavibazar' => 'মৌলভীবাজার', 'মৌলভীবাজার' => 'মৌলভীবাজার',
        'sunamganj' => 'সুনামগঞ্জ', 'সুনামগঞ্জ' => 'সুনামগঞ্জ',
        'sylhet' => 'সিলেট', 'সিলেট' => 'সিলেট',

        // ময়মনসিংহ বিভাগ
        'jamalpur' => 'জামালপুর', 'জামালপুর' => 'জামালপুর',
        'mymensingh' => 'ময়মনসিংহ', 'ময়মনসিংহ' => 'ময়মনসিংহ',
        'netrokona' => 'নেত্রকোণা', 'netrakona' => 'নেত্রকোণা', 'নেত্রকোণা' => 'নেত্রকোণা', 'নেত্রকোনা' => 'নেত্রকোণা',
        'sherpur' => 'শেরপুর', 'শেরপুর' => 'শেরপুর',
    ];

    /**
     * Clean and normalize a string by trimming, lowering case, removing punctuation and generic suffixes.
     */
    private static function cleanName(?string $raw): string
    {
        if (empty($raw)) {
            return '';
        }
        $str = trim(mb_strtolower($raw, 'UTF-8'));
        // Remove common words like 'জেলা', 'জেলা/সিটি', 'বিভাগ', 'district', 'division', 'sadar', 'city'
        $str = preg_replace('/\s*(জেলা|বিভাগ|সিটি|সদর|district|division|city|sadar)\b/iu', '', $str);
        $str = str_replace(['_', '-', '.', ',', "'", '"'], [' ', ' ', ' ', ' ', '', ''], $str);
        return trim($str);
    }

    /**
     * Normalize any Division input (Bengali or English) to canonical Bengali name.
     */
    public static function normalizeDivision(?string $raw): ?string
    {
        if (empty($raw)) {
            return null;
        }

        $clean = self::cleanName($raw);
        if (isset(self::DIVISION_ALIASES[$clean])) {
            return self::DIVISION_ALIASES[$clean];
        }

        // Fuzzy fallback match against aliases
        foreach (self::DIVISION_ALIASES as $alias => $canonical) {
            if ($clean === $alias || mb_strpos($clean, $alias) !== false || mb_strpos($alias, $clean) !== false) {
                return $canonical;
            }
        }

        // If it's already one of the canonical divisions
        if (isset(self::DIVISIONS[$raw])) {
            return $raw;
        }

        return $raw;
    }

    /**
     * Normalize any District input (Bengali or English) to canonical Bengali name.
     */
    public static function normalizeDistrict(?string $raw): ?string
    {
        if (empty($raw)) {
            return null;
        }

        $clean = self::cleanName($raw);
        if (isset(self::DISTRICT_ALIASES[$clean])) {
            return self::DISTRICT_ALIASES[$clean];
        }

        // Fuzzy match against aliases
        foreach (self::DISTRICT_ALIASES as $alias => $canonical) {
            if ($clean === $alias || mb_strpos($clean, $alias) !== false || mb_strpos($alias, $clean) !== false) {
                return $canonical;
            }
        }

        return $raw;
    }

    /**
     * Infer the canonical Bengali Division for any given District (English or Bengali).
     */
    public static function getDivisionForDistrict(?string $districtRaw): ?string
    {
        $canonicalDist = self::normalizeDistrict($districtRaw);
        if (empty($canonicalDist)) {
            return null;
        }

        foreach (self::DIVISIONS as $div => $districts) {
            if (in_array($canonicalDist, $districts, true)) {
                return $div;
            }
        }

        return null;
    }

    /**
     * Get all known bilingual variants for a division or district.
     * Useful for SQL queries: whereIn('district', BangladeshGeo::getVariants('রংপুর'))
     * Matches both 'রংপুর', 'Rangpur', 'rangpur', etc.
     */
    public static function getVariants(string $canonicalName): array
    {
        $variants = [$canonicalName];

        // Check in Division Aliases
        foreach (self::DIVISION_ALIASES as $alias => $can) {
            if ($can === $canonicalName) {
                $variants[] = $alias;
                $variants[] = ucfirst($alias);
                $variants[] = strtoupper($alias);
            }
        }

        // Check in District Aliases
        foreach (self::DISTRICT_ALIASES as $alias => $can) {
            if ($can === $canonicalName) {
                $variants[] = $alias;
                $variants[] = ucfirst($alias);
                $variants[] = strtoupper($alias);
            }
        }

        return array_values(array_unique(array_filter($variants)));
    }

    /**
     * Check if a given string matches a target division or district (bilingual).
     */
    public static function matches(?string $value, string $target): bool
    {
        if (empty($value)) {
            return false;
        }
        $canonVal = self::normalizeDistrict($value) ?: self::normalizeDivision($value);
        $canonTarget = self::normalizeDistrict($target) ?: self::normalizeDivision($target);
        return $canonVal === $canonTarget;
    }
}
