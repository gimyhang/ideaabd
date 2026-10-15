<?php
require __DIR__ . '/../vendor/autoload.php';

$raw = 'রংপুর বিভাগ';
$clean1 = preg_replace('/\s*(জেলা|বিভাগ|সিটি|সদর|district|division|city|sadar)\b/iu', '', $raw);
echo "Clean with \\b: [" . $clean1 . "]\n";

$clean2 = preg_replace('/(*UCP)\s*(জেলা|বিভাগ|সিটি|সদর|district|division|city|sadar)\b/iu', '', $raw);
echo "Clean with (*UCP): [" . $clean2 . "]\n";

echo "normalizeDivision('রংপুর বিভাগ'): [" . \App\Support\BangladeshGeo::normalizeDivision('রংপুর বিভাগ') . "]\n";
echo "normalizeDivision('রংপুর'): [" . \App\Support\BangladeshGeo::normalizeDivision('রংপুর') . "]\n";
echo "normalizeDistrict('রংপুর'): [" . \App\Support\BangladeshGeo::normalizeDistrict('রংপুর') . "]\n";
echo "normalizeDistrict('রংপুর জেলা'): [" . \App\Support\BangladeshGeo::normalizeDistrict('রংপুর জেলা') . "]\n";
