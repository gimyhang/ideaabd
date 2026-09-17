<?php

// Script to reassemble the EPUB for 'বৃক্ষ যখন কথা বলে' cleanly.

$buildDir = __DIR__ . '/clean_epub_build';
if (is_dir($buildDir)) {
    // Delete existing
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($buildDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($files as $fileinfo) {
        $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
        $todo($fileinfo->getRealPath());
    }
    rmdir($buildDir);
}

mkdir($buildDir, 0777, true);
mkdir($buildDir . '/META-INF', 0777, true);
mkdir($buildDir . '/OEBPS', 0777, true);
mkdir($buildDir . '/OEBPS/css', 0777, true);
mkdir($buildDir . '/OEBPS/image', 0777, true);
mkdir($buildDir . '/OEBPS/font', 0777, true);

// 1. mimetype (must be uncompressed 'application/epub+zip')
file_put_contents($buildDir . '/mimetype', 'application/epub+zip');

// 2. container.xml
file_put_contents($buildDir . '/META-INF/container.xml', '<?xml version="1.0" encoding="UTF-8"?>
<container version="1.0" xmlns="urn:oasis:names:tc:opendocument:xmlns:container">
  <rootfiles>
    <rootfile full-path="OEBPS/content.opf" media-type="application/oebps-package+xml"/>
  </rootfiles>
</container>');

// Copy assets from live_epub
$srcOebps = __DIR__ . '/live_epub/OEBPS';

// Copy images
foreach (glob($srcOebps . '/image/*.*') as $imgFile) {
    copy($imgFile, $buildDir . '/OEBPS/image/' . basename($imgFile));
}

// Copy fonts
foreach (glob($srcOebps . '/font/*.*') as $fontFile) {
    copy($fontFile, $buildDir . '/OEBPS/font/' . basename($fontFile));
}

// 3. Custom beautiful CSS
$customCss = <<<CSS
@charset "utf-8";

@font-face {
    font-family: 'Nikosh';
    font-style: normal;
    font-weight: normal;
    src: url('../font/Nikosh.ttf');
}

html, body {
    margin: 0;
    padding: 0;
    font-family: 'Nikosh', 'Kalpurush', 'Hind Siliguri', serif, sans-serif;
    color: #1a1a1a;
    background: transparent;
    line-height: 1.8;
}

body {
    padding: 16px 20px;
    box-sizing: border-box;
}

/* Cover Page */
.cover-page {
    text-align: center;
    padding: 0;
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 95vh;
}
.cover-img {
    max-width: 100%;
    max-height: 95vh;
    height: auto;
    object-fit: contain;
    margin: 0 auto;
    border-radius: 4px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

/* Title Page */
.title-page {
    text-align: center;
    padding: 40px 20px 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 85vh;
}
.title-page .inner-art {
    max-width: 220px;
    height: auto;
    margin: 0 auto 25px;
    display: block;
}
.title-page .book-main-title {
    font-size: 2.2rem;
    font-weight: bold;
    color: #1e293b;
    margin: 0 0 12px;
    line-height: 1.3;
}
.title-page .book-author {
    font-size: 1.3rem;
    color: #475569;
    margin: 0 0 40px;
    font-weight: 600;
}
.title-page .publisher-logo-text {
    margin-top: auto;
    font-size: 1.05rem;
    color: #64748b;
    font-weight: 500;
}

/* Imprint / Printers Page */
.imprint-page {
    padding: 24px 20px;
    max-width: 600px;
    margin: 0 auto;
    font-size: 0.95rem;
    line-height: 1.75;
    color: #334155;
}
.imprint-header {
    text-align: center;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e2e8f0;
}
.imprint-header .title {
    font-size: 1.35rem;
    font-weight: bold;
    color: #0f172a;
    margin: 0 0 4px;
}
.imprint-header .subtitle {
    font-size: 0.95rem;
    color: #64748b;
    margin: 0;
}
.imprint-table {
    width: 100%;
    border-collapse: collapse;
    margin: 15px 0 20px;
}
.imprint-table td {
    padding: 5px 8px;
    vertical-align: top;
    font-size: 0.92rem;
}
.imprint-table td.label {
    width: 28%;
    font-weight: 600;
    color: #1e293b;
    white-space: nowrap;
}
.imprint-table td.colon {
    width: 4%;
    text-align: center;
    font-weight: bold;
    color: #64748b;
}
.imprint-table td.value {
    width: 68%;
    color: #334155;
}
.imprint-notice {
    font-size: 0.84rem;
    color: #64748b;
    background: rgba(0,0,0,0.02);
    border: 1px dashed #cbd5e1;
    border-radius: 6px;
    padding: 12px 14px;
    margin-top: 20px;
    line-height: 1.6;
    text-align: justify;
}

/* Dedication Page */
.dedication-page {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    min-height: 80vh;
    text-align: center;
    padding: 40px 20px;
}
.dedication-title {
    font-size: 1.3rem;
    font-weight: bold;
    color: #0f172a;
    margin-bottom: 24px;
    letter-spacing: 1px;
}
.dedication-names {
    font-size: 1.1rem;
    line-height: 2.2;
    color: #334155;
}

/* Story Content */
.story-page {
    padding: 10px 4px;
}
.story-page p {
    text-indent: 1.5em;
    margin: 0 0 0.85em 0;
    text-align: justify;
    line-height: 1.85;
    font-size: 1.05rem;
}
.story-page p.no-indent {
    text-indent: 0;
}
.story-page .section-ornament {
    text-align: center;
    margin: 28px auto 24px;
    display: block;
    max-width: 140px;
    height: auto;
}
CSS;

file_put_contents($buildDir . '/OEBPS/css/style.css', $customCss);

// 4. cover.xhtml
file_put_contents($buildDir . '/OEBPS/cover.xhtml', '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="bn">
<head>
    <meta charset="utf-8"/>
    <title>প্রচ্ছদ — বৃক্ষ যখন কথা বলে</title>
    <link rel="stylesheet" type="text/css" href="css/style.css"/>
</head>
<body class="cover-page">
    <img class="cover-img" src="image/1.png" alt="বৃক্ষ যখন কথা বলে — প্রচ্ছদ"/>
</body>
</html>');

// 5. titlepage.xhtml
file_put_contents($buildDir . '/OEBPS/titlepage.xhtml', '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="bn">
<head>
    <meta charset="utf-8"/>
    <title>বৃক্ষ যখন কথা বলে</title>
    <link rel="stylesheet" type="text/css" href="css/style.css"/>
</head>
<body>
    <div class="title-page">
        <img class="inner-art" src="image/inner.jpg" alt="ইলুস্ট্রেশন"/>
        <h1 class="book-main-title">বৃক্ষ যখন কথা বলে</h1>
        <p class="book-author">আদিল ফকির</p>
        <p class="publisher-logo-text">আইডিয়া প্রকাশন</p>
    </div>
</body>
</html>');

// 6. imprint.xhtml
file_put_contents($buildDir . '/OEBPS/imprint.xhtml', '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="bn">
<head>
    <meta charset="utf-8"/>
    <title>প্রকাশনা ও মুদ্রণ সংক্রান্ত তথ্য</title>
    <link rel="stylesheet" type="text/css" href="css/style.css"/>
</head>
<body>
    <div class="imprint-page">
        <div class="imprint-header">
            <div class="title">বৃক্ষ যখন কথা বলে</div>
            <div class="subtitle">Brikkha Jakhan Katha Bole • Adil Fakir</div>
        </div>

        <table class="imprint-table">
            <tr>
                <td class="label">প্রকাশক</td>
                <td class="colon">:</td>
                <td class="value">
                    <strong>মাসুদ রানা সাকিল</strong><br/>
                    আইডিয়া প্রকাশন<br/>
                    রংপুর, বাংলাদেশ<br/>
                    ফোন: +৮৮০১৭২৬ ৯৭৬৯৮২<br/>
                    ইমেইল: adideabd@gmail.com<br/>
                    ওয়েবসাইট: www.ideaabd.com
                </td>
            </tr>
            <tr>
                <td class="label">স্বত্ব</td>
                <td class="colon">:</td>
                <td class="value">© লেখক (Adil Fakir)</td>
            </tr>
            <tr>
                <td class="label">প্রথম প্রকাশ</td>
                <td class="colon">:</td>
                <td class="value">আগস্ট ২০২৬</td>
            </tr>
            <tr>
                <td class="label">প্রচ্ছদ</td>
                <td class="colon">:</td>
                <td class="value">শিস খন্দকার</td>
            </tr>
            <tr>
                <td class="label">মূল্য</td>
                <td class="colon">:</td>
                <td class="value">৩০০ টাকা ($5)</td>
            </tr>
            <tr>
                <td class="label">মুদ্রণ</td>
                <td class="colon">:</td>
                <td class="value">আইডিয়া প্রেস, রংপুর</td>
            </tr>
            <tr>
                <td class="label">পরিবেশক</td>
                <td class="colon">:</td>
                <td class="value">
                    কাঁচাকঞ্চি, রংপুর, বাংলাদেশ<br/>
                    ফোন: +৮৮০১৫৫৮৭১২৮১০<br/>
                    ইমেইল: kanchakonchi@gmail.com<br/>
                    অনলাইনে: rokomari.com
                </td>
            </tr>
            <tr>
                <td class="label">আইএসবিএন</td>
                <td class="colon">:</td>
                <td class="value">978-</td>
            </tr>
        </table>

        <div class="imprint-notice">
            প্রকাশক ও স্বত্বাধিকারীর লিখিত অনুমতি ছাড়া বাণিজ্যিক উদ্দেশ্যে এই বইয়ের কোনো অংশের প্রতিলিপি তৈরি করা যাবে না। এই শর্ত লঙ্ঘিত হলে আইনানুগ ব্যবস্থা গ্রহণ করা হবে।
        </div>
    </div>
</body>
</html>');

// 7. dedication.xhtml
file_put_contents($buildDir . '/OEBPS/dedication.xhtml', '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="bn">
<head>
    <meta charset="utf-8"/>
    <title>উৎসর্গ</title>
    <link rel="stylesheet" type="text/css" href="css/style.css"/>
</head>
<body>
    <div class="dedication-page">
        <div class="dedication-title">উৎসর্গ</div>
        <div class="dedication-names">
            ফকির মজনু শাহ<br/>
            এনায়েতউল্লাহ ফকির<br/>
            জামানউল্লাহ ফকির<br/>
            জলিল ফকির<br/>
            সোলায়মান ফকির<br/>
            হাছেন ফকির
        </div>
    </div>
</body>
</html>');

// 8. Story Content: Process brikkho-38.xhtml cleanly
$rawStory = file_get_contents($srcOebps . '/brikkho-38.xhtml');

// Extract paragraphs
preg_match_all('/<p[^>]*>(.*?)<\/p>/su', $rawStory, $pMatches);

$cleanStoryParas = [];
foreach ($pMatches[1] as $idx => $pInner) {
    // Check if it's an image
    if (strpos($pInner, 'Untitled-21.png') !== false || strpos($pInner, 'Untitled-2.png') !== false) {
        $cleanStoryParas[] = '<div class="text-center my-4"><img class="section-ornament" src="image/Untitled-21.png" alt="§"/></div>';
        continue;
    }

    $cleanText = strip_tags($pInner);
    $cleanText = html_entity_decode($cleanText, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    // Normalize spaces and non-breaking spaces
    $cleanText = str_replace(["\xc2\xa0", "\t"], [' ', ' '], $cleanText);
    $cleanText = trim(preg_replace('/\s+/', ' ', $cleanText));

    // Skip redundant title/author if it appears at the very beginning
    if ($idx < 3 && ($cleanText === 'আলম ফকির' || $cleanText === 'বৃক্ষ যখন কথা বলে' || empty($cleanText))) {
        continue;
    }

    if (!empty($cleanText)) {
        // Escape special chars for XML
        $escaped = htmlspecialchars($cleanText, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $cleanStoryParas[] = '<p>' . $escaped . '</p>';
    }
}

$storyHtml = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="bn">
<head>
    <meta charset="utf-8"/>
    <title>বৃক্ষ যখন কথা বলে — মূল আখ্যান</title>
    <link rel="stylesheet" type="text/css" href="css/style.css"/>
</head>
<body>
    <div class="story-page">
' . implode("\n", $cleanStoryParas) . '
    </div>
</body>
</html>';

file_put_contents($buildDir . '/OEBPS/chapter1.xhtml', $storyHtml);

// 9. content.opf
$manifestItems = [
    '<item id="style" href="css/style.css" media-type="text/css"/>',
    '<item id="ncx" href="toc.ncx" media-type="application/x-dtbncx+xml"/>',
    '<item id="cover-image" href="image/1.png" media-type="image/png"/>',
    '<item id="inner-image" href="image/inner.jpg" media-type="image/jpeg"/>',
    '<item id="ornament-image" href="image/Untitled-21.png" media-type="image/png"/>',
    '<item id="ornament-2-image" href="image/Untitled-2.png" media-type="image/png"/>',
    '<item id="font-nikosh" href="font/Nikosh.ttf" media-type="application/x-font-ttf"/>',
    '<item id="font-kalpurush" href="font/SutonnyOMJ.ttf" media-type="application/x-font-ttf"/>',
    '<item id="cover-page" href="cover.xhtml" media-type="application/xhtml+xml"/>',
    '<item id="title-page" href="titlepage.xhtml" media-type="application/xhtml+xml"/>',
    '<item id="imprint-page" href="imprint.xhtml" media-type="application/xhtml+xml"/>',
    '<item id="dedication-page" href="dedication.xhtml" media-type="application/xhtml+xml"/>',
    '<item id="chapter1" href="chapter1.xhtml" media-type="application/xhtml+xml"/>',
];

$spineItems = [
    '<itemref idref="cover-page"/>',
    '<itemref idref="title-page"/>',
    '<itemref idref="imprint-page"/>',
    '<itemref idref="dedication-page"/>',
    '<itemref idref="chapter1"/>',
];

$opf = '<?xml version="1.0" encoding="utf-8"?>
<package xmlns="http://www.idpf.org/2007/opf" unique-identifier="BookId" version="2.0">
    <metadata xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:opf="http://www.idpf.org/2007/opf">
        <dc:title>বৃক্ষ যখন কথা বলে</dc:title>
        <dc:creator opf:role="aut">আদিল ফকির</dc:creator>
        <dc:language>bn</dc:language>
        <dc:publisher>আইডিয়া প্রকাশন</dc:publisher>
        <dc:identifier id="BookId">urn:uuid:briksh-zkhn-ktha-ble-v2</dc:identifier>
        <meta name="cover" content="cover-image"/>
    </metadata>
    <manifest>
        ' . implode("\n        ", $manifestItems) . '
    </manifest>
    <spine toc="ncx">
        ' . implode("\n        ", $spineItems) . '
    </spine>
</package>';

file_put_contents($buildDir . '/OEBPS/content.opf', $opf);

// 10. toc.ncx
$ncx = '<?xml version="1.0" encoding="UTF-8"?>
<ncx xmlns="http://www.daisy.org/z3986/2005/ncx/" version="2005-1">
    <head>
        <meta name="dtb:uid" content="urn:uuid:briksh-zkhn-ktha-ble-v2"/>
        <meta name="dtb:depth" content="1"/>
        <meta name="dtb:totalPageCount" content="0"/>
        <meta name="dtb:maxPageNumber" content="0"/>
    </head>
    <docTitle>
        <text>বৃক্ষ যখন কথা বলে</text>
    </docTitle>
    <navMap>
        <navPoint id="navPoint-1" playOrder="1">
            <navLabel><text>প্রচ্ছদ</text></navLabel>
            <content src="cover.xhtml"/>
        </navPoint>
        <navPoint id="navPoint-2" playOrder="2">
            <navLabel><text>গ্রন্থ পরিচিতি ও শিরোনাম</text></navLabel>
            <content src="titlepage.xhtml"/>
        </navPoint>
        <navPoint id="navPoint-3" playOrder="3">
            <navLabel><text>প্রকাশনা ও মুদ্রণ সংক্রান্ত তথ্য</text></navLabel>
            <content src="imprint.xhtml"/>
        </navPoint>
        <navPoint id="navPoint-4" playOrder="4">
            <navLabel><text>উৎসর্গ</text></navLabel>
            <content src="dedication.xhtml"/>
        </navPoint>
        <navPoint id="navPoint-5" playOrder="5">
            <navLabel><text>মূল আখ্যান — বৃক্ষ যখন কথা বলে</text></navLabel>
            <content src="chapter1.xhtml"/>
        </navPoint>
    </navMap>
</ncx>';

file_put_contents($buildDir . '/OEBPS/toc.ncx', $ncx);

echo "All files generated successfully in $buildDir!\n";
