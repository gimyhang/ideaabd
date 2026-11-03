/*
 * Printing & Production Cost Calculator
 * Supports Books, Magazines, Diaries, Brochures, Cards, Leaflets, Packaging, Stationery & Custom items.
 *
 * Capabilities:
 * - Dynamic job types and intelligent sheet layout (Ups) calculation.
 * - Formas calculation with configurable forma size (16, 8, 4, 32 pages).
 * - Real-time component costing (Design, Paper, CTP, Press, Lamination, Cutting, Binding, Packing, Transport, Other).
 * - Live quotation specification preview box.
 * - Dual quotation insertion modes: Single Consolidated Item OR Itemized Component Breakdown.
 * - Raw material rate sheet synchronization with server database.
 * - Quantity tier price comparison table.
 */
(function () {
    'use strict';
    var doc = document;

    var GROUPS = ['Design', 'Paper', 'CTP', 'Press', 'Lamination', 'Cutting', 'Binding', 'Packing', 'Transport', 'Other'];
    
    var GROUP_TITLES_BN = {
        Design: 'Design',
        Paper: 'Paper',
        CTP: 'Plates',
        Press: 'Press',
        Lamination: 'Lamination',
        Cutting: 'Cutting',
        Binding: 'Binding',
        Packing: 'Packing',
        Transport: 'Transport',
        Other: 'Other'
    };

    var GROUP_ICONS = {
        Design: 'fa-pen-nib text-indigo',
        Paper: 'fa-sheet-plastic text-warning',
        CTP: 'fa-barcode text-primary',
        Press: 'fa-print text-danger',
        Lamination: 'fa-wand-magic-sparkles text-info',
        Cutting: 'fa-scissors text-secondary',
        Binding: 'fa-book text-success',
        Packing: 'fa-boxes-packing text-warning',
        Transport: 'fa-truck text-dark',
        Other: 'fa-cube text-muted'
    };

    var BASIS = {
        Design: 'Qty', Paper: 'Reams', CTP: 'Plates', Press: 'Thousands',
        Lamination: 'Copies', Cutting: 'Copies', Binding: 'Copies',
        Packing: 'Qty', Transport: 'Qty', Other: 'Qty'
    };

    var BASIS_BN = {
        Design: 'Qty', Paper: 'Reams', CTP: 'Plates', Press: 'Thousands',
        Lamination: 'Copies', Cutting: 'Copies', Binding: 'Copies',
        Packing: 'Qty', Transport: 'Qty', Other: 'Qty'
    };

    var NAMES = {
        Design: ['Layout Design', 'Cover Design', 'Proof Reading', 'Ebook Conversion', 'ISBN Processing', 'Dummy Copy'],
        Paper: ['Inner Paper', 'Cover Board', 'Hard Board / Grey Board', 'Endpaper', 'Jacket Paper'],
        CTP: ['Inner Plates', 'Cover Plates', 'Spot UV Plate', 'Foil Die Plate', 'Emboss Die Plate'],
        Press: ['Inner Press (Offset)', 'Cover Press (Offset)', 'Spot Color Press', 'Digital Press'],
        Lamination: ['Cover Lamination', 'Jacket Lamination', 'Spot UV Coating', 'Foil Stamping', 'Embossing'],
        Cutting: ['Final 3-Knife Trim', 'Die Cutting', 'Creasing & Scoring', 'Perforation', 'Corner Rounding'],
        Binding: ['Perfect Glue Binding', 'Sewn + Perfect Binding', 'Hardcover Binding', 'Saddle Stitch (Pin)', 'Spiral Binding', 'Wire-O Binding', 'Pad Pasting'],
        Packing: ['Poly Bag Wrapping', 'Bundle & Paper Band', 'Heavy Master Carton', 'Shrink Packaging'],
        Transport: ['Press to Office Delivery', 'Office to Client Door', 'Courier & Parcel Transport', 'Loading & Labor'],
        Other: ['Ribbon Bookmark', 'Bookmark Card', 'Slipcase Box', 'Inspection & QC', 'Miscellaneous']
    };

    // Paper GSM lists
    var GSM_INNER = [45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 100, 110, 120, 150];
    var GSM_COVER = [150, 170, 200, 230, 250, 300, 350, 400];
    var gsmOpts = function (list) { return list.map(function (g) { return [g, g + ' GSM']; }); };
    var plain = function (list) { return list.map(function (v) { return [v, v]; }); };

    var VARIANTS = {
        PaperInner: { 
            a: ['GSM', gsmOpts(GSM_INNER)], 
            b: ['Kind', plain(['Offset White', 'Offwhite', 'Cream Munken', 'Art Paper', 'Matt Art Paper', 'Newsprint', 'Kraft', 'Bond Paper', 'NCR Top', 'NCR Middle', 'NCR Bottom', 'Sticker Gum'])] 
        },
        PaperCover: { 
            a: ['GSM', gsmOpts(GSM_COVER)], 
            b: ['Kind', plain(['Art Card', 'Swedish Board', 'Matt Card', 'Duplex Board', 'Grey Board 1.5mm', 'Grey Board 2.0mm', 'Grey Board 2.5mm', 'Kraft Board', 'Ivory Card', 'Glossy Card'])] 
        },
        Design: { 
            a: ['Work', plain(['Layout Design', 'Cover Design', 'Proof Reading', 'Ebook Conversion', 'ISBN Processing', 'Dummy Copy', 'Logo & Branding', 'Illustration'])], 
            b: ['Per', plain(['Job', 'Page'])] 
        },
        CTP: { 
            a: ['Plate', plain(['CTP Thermal', 'CTP Violet', 'CTCP', 'Film Negative', 'Zinc Block', 'Copper Die'])], 
            b: ['Side', plain(['Inner', 'Cover', 'Spot UV', 'Foil Stamping', 'Emboss'])] 
        },
        Press: { 
            a: ['Machine', plain(['Offset 4-Color', 'Offset 2-Color', 'Offset 1-Color', 'Digital Laser', 'Screen Print', 'Flex Banner', 'Letterpress'])], 
            b: ['Side', plain(['Inner', 'Cover', 'Spot Color'])] 
        },
        Lamination: { 
            a: ['Finish', plain(['Matt Lamination', 'Glossy Lamination', 'Velvet Soft-Touch', 'Thermal Matt', 'Thermal Glossy', 'Spot UV', 'Gold Foil', 'Silver Foil', 'Embossing', 'Scratch-off'])], 
            b: ['Sides', plain(['Front (Single)', 'Both Sides'])] 
        },
        Cutting: { 
            a: ['Method', plain(['3-Knife Trim', 'Die Cut', 'Creasing', 'Perforation', 'Punching', 'Folding', 'Numbering', 'Corner Rounding'])], 
            b: null 
        },
        Binding: { 
            a: ['Method', plain(['Perfect Binding', 'Thread Sewn + Perfect', 'Hardcover / Case', 'Saddle Stitch (Pin)', 'Spiral Ring', 'Wire-O Double Ring', 'Pad Pasting', 'Jacket Pasting'])], 
            b: null 
        },
        Packing: { 
            a: ['Material', plain(['Poly Bag Wrap', 'Paper Band', 'Master Carton', 'Shrink Film', 'Bubble Wrap', 'Wooden Pallet'])], 
            b: ['Per', plain(['Copy', 'Dozen', 'Hundred', 'Carton', 'Lot'])] 
        },
        Transport: { 
            a: ['Work', plain(['Press to Office', 'Office to Client', 'Courier Transport', 'Loading Labor', 'Doorstep Delivery'])], 
            b: ['Per', plain(['Lot', 'Copy', 'Hundred', 'Thousand', 'Carton'])] 
        },
        Other: { a: null, b: null }
    };

    var PAPER_DEFAULT = { 
        '80|offset white': 3200, '70|offset white': 2800, '70|offwhite': 3100, '80|offwhite': 3500,
        '70|cream munken': 3800, '80|cream munken': 4200, '100|art paper': 4500, '120|matt art paper': 5200, 
        '65|offset white': 2500, '55|newsprint': 2100, '80|kraft': 3000, '300|art card': 5800, '350|swedish board': 6500 
    };

    var SIZES = { 
        Demy: [5.5, 8.5], 
        Crown: [7.25, 9.5], 
        Royal: [6.5, 9.5], 
        A4: [8.3, 11.7], 
        A5: [5.8, 8.3], 
        A3: [11.7, 16.5], 
        B5: [6.9, 9.8], 
        Card: [3.5, 2.0], 
        Poster: [18.0, 23.0], 
        Envelope: [10.0, 4.5],
        Full: [23.0, 36.0] 
    };

    var SIZES_BN = {
        Demy: 'Demy (5.5"×8.5")',
        Crown: 'Crown (7.25"×9.5")',
        Royal: 'Royal (6.5"×9.5")',
        A4: 'A4 (8.3"×11.7")',
        A5: 'A5 (5.8"×8.3")',
        A3: 'A3 (11.7"×16.5")',
        B5: 'B5 (6.9"×9.8")',
        Card: 'Card (3.5"×2.0")',
        Poster: 'Poster (18"×23")',
        Envelope: 'Envelope (10"×4.5")',
        Full: 'Sheet (23"×36")'
    };

    var SHEETS = { 
        '23x36': [23, 36], 
        '25x36': [25, 36], 
        '20x30': [20, 30], 
        '18x23': [18, 23],
        '27x38': [27, 38],
        '31x43': [31, 43]
    };

    var P = function (g, n, o) { o = o || {}; o.g = g; o.n = n; return o; };

    var TYPES = {
        book: { label: 'Book', unit: 'Copy', size: 'Demy', pages: true, defaultTitle: 'Book Printing', rows: [
            P('Design', 'Layout Design', { auto: 'per', a: 'Layout Design', b: 'Page' }), 
            P('Design', 'Cover Design', { auto: 'per', a: 'Cover Design', b: 'Job' }),
            P('Paper', 'Inner Paper', { auto: 'paperBook', a: 80, b: 'Offwhite', waste: 5 }),
            P('Paper', 'Cover Board', { auto: 'sheet', ups: 8, waste: 5, flat: true, noSize: true, a: 300, b: 'Art Card' }),
            P('CTP', 'Inner Plates', { auto: 'platesBook', a: 'CTP Thermal', b: 'Inner' }), 
            P('CTP', 'Cover Plates', { auto: 'platesCover', a: 'CTP Thermal', b: 'Cover' }),
            P('Press', 'Inner Press (Offset)', { auto: 'pressBook', a: 'Offset 1-Color', b: 'Inner' }), 
            P('Press', 'Cover Press (Offset)', { auto: 'pressCover', a: 'Offset 4-Color', b: 'Cover' }),
            P('Lamination', 'Cover Lamination', { auto: 'lam', a: 'Matt Lamination', b: 'Front (Single)' }), 
            P('Cutting', 'Final 3-Knife Trim', { auto: 'copies', a: '3-Knife Trim' }),
            P('Binding', 'Perfect Glue Binding', { auto: 'copies', a: 'Perfect Binding' }),
            P('Packing', 'Poly Bag Wrapping', { auto: 'per', a: 'Poly Bag Wrap', b: 'Copy', off: true }), 
            P('Transport', 'Press to Office Delivery', { auto: 'per', a: 'Press to Office', b: 'Lot', off: true })
        ] },
        magazine: { label: 'Magazine', unit: 'Copy', size: 'A4', pages: true, defaultTitle: 'Magazine Printing', rows: [
            P('Design', 'Layout Design', { auto: 'per', a: 'Layout Design', b: 'Page' }),
            P('Design', 'Cover Design', { auto: 'per', a: 'Cover Design', b: 'Job' }),
            P('Paper', 'Inner Paper', { auto: 'paperBook', a: 100, b: 'Art Paper', waste: 5 }),
            P('Paper', 'Cover Board', { auto: 'sheet', ups: 4, waste: 5, flat: true, noSize: true, a: 250, b: 'Art Card' }),
            P('CTP', 'Inner Plates', { auto: 'platesBook', a: 'CTP Thermal', b: 'Inner' }),
            P('CTP', 'Cover Plates', { auto: 'platesCover', a: 'CTP Thermal', b: 'Cover' }),
            P('Press', 'Inner Press (Offset)', { auto: 'pressBook', a: 'Offset 4-Color', b: 'Inner' }),
            P('Press', 'Cover Press (Offset)', { auto: 'pressCover', a: 'Offset 4-Color', b: 'Cover' }),
            P('Lamination', 'Cover Lamination', { auto: 'lam', a: 'Glossy Lamination', b: 'Front (Single)' }),
            P('Cutting', 'Final 3-Knife Trim', { auto: 'copies', a: '3-Knife Trim' }),
            P('Binding', 'Saddle Stitch (Pin)', { auto: 'copies', a: 'Saddle Stitch (Pin)' }),
            P('Packing', 'Master Carton', { auto: 'per', a: 'Master Carton', b: 'Hundred', off: true })
        ] },
        notebook: { label: 'Notebook', unit: 'Book', size: 'Demy', pages: true, defaultTitle: 'Executive Notebook & Diary', rows: [
            P('Design', 'Layout Design', { auto: 'per', a: 'Layout Design', b: 'Job' }),
            P('Paper', 'Inner Paper', { auto: 'paperBook', a: 80, b: 'Cream Munken', waste: 5 }),
            P('Paper', 'Cover Board', { auto: 'sheet', ups: 8, waste: 5, flat: true, noSize: true, a: 350, b: 'Grey Board 2.0mm' }),
            P('CTP', 'Inner Plates', { auto: 'platesBook', a: 'CTP Thermal', b: 'Inner' }),
            P('Press', 'Inner Press (Offset)', { auto: 'pressBook', a: 'Offset 2-Color', b: 'Inner' }),
            P('Lamination', 'Cover Lamination', { auto: 'lam', a: 'Velvet Soft-Touch', b: 'Front (Single)' }),
            P('Binding', 'Hardcover Binding', { auto: 'copies', a: 'Hardcover / Case' }),
            P('Other', 'Ribbon Bookmark', { auto: 'copies', a: 'Ribbon Bookmark', b: 'Copy' })
        ] },
        brochure: { label: 'Brochure', unit: 'Pcs', size: 'A4', defaultTitle: 'Multicolor Tri-Fold Brochure', rows: [
            P('Design', 'Layout Design', { auto: 'per', a: 'Layout Design', b: 'Job' }),
            P('Paper', 'Inner Paper', { auto: 'sheet', waste: 5, flat: true, a: 170, b: 'Matt Art Paper' }),
            P('CTP', 'Inner Plates', { auto: 'platesJob', a: 'CTP Thermal', b: 'Inner' }),
            P('Press', 'Inner Press (Offset)', { auto: 'pressJob', a: 'Offset 4-Color', b: 'Inner' }),
            P('Lamination', 'Cover Lamination', { auto: 'lam', a: 'Matt Lamination', b: 'Both Sides' }),
            P('Cutting', 'Creasing & Scoring', { auto: 'copies', a: 'Creasing' }),
            P('Packing', 'Master Carton', { auto: 'per', a: 'Master Carton', b: 'Hundred', off: true })
        ] },
        card: { label: 'Card', unit: 'Pcs', size: 'Card', defaultTitle: 'Premium Visiting Cards', rows: [
            P('Design', 'Layout Design', { auto: 'per', a: 'Layout Design', b: 'Job' }),
            P('Paper', 'Cover Board', { auto: 'sheet', waste: 5, flat: true, a: 300, b: 'Art Card' }),
            P('CTP', 'Inner Plates', { auto: 'platesJob', a: 'CTP Thermal', b: 'Inner' }),
            P('Press', 'Inner Press (Offset)', { auto: 'pressJob', a: 'Offset 4-Color', b: 'Inner' }),
            P('Lamination', 'Cover Lamination', { auto: 'lam', a: 'Matt Lamination', b: 'Both Sides' }),
            P('Lamination', 'Spot UV Coating', { auto: 'copies', a: 'Spot UV', b: 'Front (Single)', off: true }),
            P('Cutting', 'Die Cutting', { auto: 'copies', a: 'Die Cut' }),
            P('Packing', 'Master Carton', { auto: 'per', a: 'Master Carton', b: 'Hundred', off: true })
        ] },
        leaflet: { label: 'Leaflet', unit: 'Pcs', size: 'A4', defaultTitle: 'Color Flyer / Leaflet', rows: [
            P('Design', 'Layout Design', { auto: 'per', a: 'Layout Design', b: 'Job' }),
            P('Paper', 'Inner Paper', { auto: 'sheet', waste: 5, flat: true, a: 120, b: 'Matt Art Paper' }),
            P('CTP', 'Inner Plates', { auto: 'platesJob', a: 'CTP Thermal', b: 'Inner' }),
            P('Press', 'Inner Press (Offset)', { auto: 'pressJob', a: 'Offset 4-Color', b: 'Inner' }),
            P('Cutting', 'Final 3-Knife Trim', { auto: 'copies', a: '3-Knife Trim' }),
            P('Packing', 'Paper Band', { auto: 'per', a: 'Paper Band', b: 'Hundred', off: true })
        ] },
        poster: { label: 'Poster', unit: 'Pcs', size: 'Poster', defaultTitle: 'Offset Color Poster', rows: [
            P('Design', 'Layout Design', { auto: 'per', a: 'Layout Design', b: 'Job' }),
            P('Paper', 'Inner Paper', { auto: 'sheet', waste: 5, flat: true, a: 100, b: 'Art Paper' }),
            P('CTP', 'Inner Plates', { auto: 'platesJob', a: 'CTP Thermal', b: 'Inner' }),
            P('Press', 'Inner Press (Offset)', { auto: 'pressJob', a: 'Offset 4-Color', b: 'Inner' }),
            P('Lamination', 'Cover Lamination', { auto: 'lam', a: 'Glossy Lamination', b: 'Front (Single)', off: true }),
            P('Cutting', 'Final 3-Knife Trim', { auto: 'copies', a: '3-Knife Trim' })
        ] },
        calendar: { label: 'Calendar', unit: 'Pcs', size: 'A3', pages: true, defaultTitle: 'Wall / Desk Calendar', rows: [
            P('Design', 'Layout Design', { auto: 'per', a: 'Layout Design', b: 'Job' }),
            P('Paper', 'Inner Paper', { auto: 'sheet', waste: 5, a: 150, b: 'Matt Art Paper' }),
            P('CTP', 'Inner Plates', { auto: 'platesJob', a: 'CTP Thermal', b: 'Inner' }),
            P('Press', 'Inner Press (Offset)', { auto: 'pressJob', a: 'Offset 4-Color', b: 'Inner' }),
            P('Cutting', 'Final 3-Knife Trim', { auto: 'copies', a: '3-Knife Trim' }),
            P('Binding', 'Spiral Ring', { auto: 'copies', a: 'Spiral Ring' }),
            P('Packing', 'Poly Bag Wrapping', { auto: 'per', a: 'Poly Bag Wrap', b: 'Copy', off: true })
        ] },
        box: { label: 'Box', unit: 'Pcs', size: 'A4', defaultTitle: 'Product Box / Carton', rows: [
            P('Design', 'Layout Design', { auto: 'per', a: 'Layout Design', b: 'Job' }),
            P('Paper', 'Cover Board', { auto: 'sheet', waste: 8, flat: true, a: 350, b: 'Duplex Board' }),
            P('CTP', 'Inner Plates', { auto: 'platesJob', a: 'CTP Thermal', b: 'Inner' }),
            P('Press', 'Inner Press (Offset)', { auto: 'pressJob', a: 'Offset 4-Color', b: 'Inner' }),
            P('Lamination', 'Cover Lamination', { auto: 'lam', a: 'Matt Lamination', b: 'Front (Single)' }),
            P('Cutting', 'Die Cutting', { auto: 'copies', a: 'Die Cut' }),
            P('Binding', 'Pad Pasting', { auto: 'copies', a: 'Pad Pasting' }),
            P('Packing', 'Master Carton', { auto: 'per', a: 'Master Carton', b: 'Hundred', off: true })
        ] },
        memo: { label: 'Memo', unit: 'Book', size: 'A5', pages: true, defaultTitle: 'Cash Memo / Bill Pad (3-Part)', rows: [
            P('Design', 'Layout Design', { auto: 'per', a: 'Layout Design', b: 'Job' }),
            P('Paper', 'Inner Paper', { auto: 'sheet', waste: 5, a: 55, b: 'NCR Top' }),
            P('Paper', 'Inner Paper', { auto: 'sheet', waste: 5, a: 55, b: 'NCR Middle' }),
            P('Paper', 'Cover Board', { auto: 'sheet', waste: 5, flat: true, a: 200, b: 'Duplex Board' }),
            P('CTP', 'Inner Plates', { auto: 'platesJob', a: 'CTP Thermal', b: 'Inner' }),
            P('Press', 'Inner Press (Offset)', { auto: 'pressJob', a: 'Offset 1-Color', b: 'Inner' }),
            P('Cutting', 'Perforation', { auto: 'copies', a: 'Perforation' }),
            P('Cutting', 'Numbering', { auto: 'copies', a: 'Numbering' }),
            P('Binding', 'Pad Pasting', { auto: 'copies', a: 'Pad Pasting' })
        ] },
        envelope: { label: 'Envelope', unit: 'Pcs', size: 'Envelope', defaultTitle: 'Printed Office Envelopes', rows: [
            P('Design', 'Layout Design', { auto: 'per', a: 'Layout Design', b: 'Job' }),
            P('Paper', 'Inner Paper', { auto: 'sheet', waste: 5, flat: true, a: 100, b: 'Offset White' }),
            P('CTP', 'Inner Plates', { auto: 'platesJob', a: 'CTP Thermal', b: 'Inner' }),
            P('Press', 'Inner Press (Offset)', { auto: 'pressJob', a: 'Offset 4-Color', b: 'Inner' }),
            P('Cutting', 'Die Cutting', { auto: 'copies', a: 'Die Cut' }),
            P('Binding', 'Pad Pasting', { auto: 'copies', a: 'Pad Pasting' }),
            P('Packing', 'Master Carton', { auto: 'per', a: 'Master Carton', b: 'Hundred', off: true })
        ] },
        stationery: { label: 'Stationery', unit: 'Pcs', size: '', defaultTitle: 'Corporate Stationery Items', rows: [
            P('Design', 'Layout Design', { auto: 'per', a: 'Layout Design', b: 'Job' }),
            P('Other', 'Miscellaneous', { auto: 'copies', a: 'Corporate Item', b: 'Copy' }),
            P('Packing', 'Master Carton', { auto: 'per', a: 'Master Carton', b: 'Hundred' }),
            P('Transport', 'Office to Client Door', { auto: 'per', a: 'Office to Client', b: 'Lot' })
        ] },
        custom: { label: 'Custom', unit: 'Pcs', size: 'Demy', defaultTitle: 'Custom Production Item', rows: [] }
    };

    var TIERS = [250, 500, 1000, 2000, 3000, 5000, 10000];

    var GROUP_AUTO = { 
        Design: 'per', Paper: 'sheet', CTP: 'platesJob', Press: 'pressJob', 
        Lamination: 'lam', Cutting: 'copies', Binding: 'copies', 
        Packing: 'per', Transport: 'per', Other: 'one' 
    };

    var GROUP_DEFAULT = {
        Design: { a: 'Layout Design', b: 'Job' }, 
        Paper: { a: 80, b: 'Offset White' }, 
        CTP: { a: 'CTP Thermal', b: 'Inner' }, 
        Press: { a: 'Offset 4-Color', b: 'Inner' },
        Lamination: { a: 'Matt Lamination', b: 'Front (Single)' }, 
        Cutting: { a: '3-Knife Trim' }, 
        Binding: { a: 'Perfect Binding' }, 
        Packing: { a: 'Poly Bag Wrap', b: 'Copy' }, 
        Transport: { a: 'Press to Office', b: 'Lot' }, 
        Other: {}
    };

    var state = { type: 'book', rows: [], seq: 0 };
    var el = {};

    function num(v) { var n = parseFloat(v); return isFinite(n) ? n : 0; }
    function money(v, d) { return '৳' + Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: d == null ? 2 : d, maximumFractionDigits: d == null ? 2 : d }); }
    function round(v, d) { var p = Math.pow(10, d == null ? 2 : d); return Math.round((v + Number.EPSILON) * p) / p; }
    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
    function $(id) { return doc.getElementById(id); }

    function variantKey(r) {
        if (r.g !== 'Paper') return r.g;
        return /cover|board|jacket|card|wrapper|dust/i.test(r.n) ? 'PaperCover' : 'PaperInner';
    }
    function variants(r) { return VARIANTS[variantKey(r)] || { a: null, b: null }; }

    function customKinds() { var k = readStore('pc-kinds'); return Array.isArray(k) ? k : (k.list || []); }
    function addKind(name) {
        var list = customKinds();
        if (list.indexOf(name) === -1) { list.push(name); try { localStorage.setItem('pc-kinds', JSON.stringify({ list: list })); } catch (e) {} }
    }
    function kindsFor(r) {
        var base = variants(r).b ? variants(r).b[1].map(function (o) { return o[0]; }) : [];
        customKinds().forEach(function (k) { if (base.indexOf(k) === -1) base.push(k); });
        return base;
    }
    function customGsms() { var k = readStore('pc-gsms'); return Array.isArray(k) ? k : (k.list || []); }
    function addGsm(n) {
        var list = customGsms();
        if (list.indexOf(n) === -1) { list.push(n); try { localStorage.setItem('pc-gsms', JSON.stringify({ list: list })); } catch (e) {} }
    }
    function gsmsFor(r) {
        var base = variants(r).a ? variants(r).a[1].map(function (o) { return o[0]; }) : [];
        customGsms().concat(r.a ? [r.a] : []).forEach(function (g) { if (base.indexOf(g) === -1) base.push(g); });
        return base.sort(function (x, y) { return x - y; });
    }
    function knownRate(g, a, b) {
        var key = keyOf(g, a, b);
        return SHEET[key] != null || readStore('pc-rates')[key] != null || (!sheetLoaded && g === 'Paper' && !!PAPER_DEFAULT[[a, b].join('|').toLowerCase()]);
    }
    function stockText(r) { return (r.a ? r.a + ' GSM' : '') + (r.b ? (r.a ? ' ' : '') + r.b : ''); }
    function titleCase(w) { return w.toLowerCase().replace(/(^|[\s-])(\p{L})/gu, function (m, p, c) { return p + c.toUpperCase(); }); }

    function parseStock(text, r) {
        var t = String(text || '').trim();
        if (!t) return null;
        var m = t.match(/\d+(\.\d+)?/);
        var a = m ? (parseFloat(m[0]) % 1 === 0 ? parseInt(m[0], 10) : parseFloat(m[0])) : r.a;
        if (m && a > 0 && (variants(r).a[1].every(function (o) { return o[0] !== a; }))) addGsm(a);
        var word = t.replace(/\d+(\.\d+)?/g, ' ').replace(/gsm/ig, ' ').replace(/[^\p{L}\s-]/gu, ' ').replace(/\s+/g, ' ').trim();
        var b = r.b;
        if (word) {
            var all = kindsFor(r);
            var hit = all.filter(function (k) { return k.toLowerCase() === word.toLowerCase(); })[0]
                || all.filter(function (k) { return k.toLowerCase().indexOf(word.toLowerCase()) === 0; })[0];
            if (hit) b = hit;
            else { b = titleCase(word); addKind(b); buildPaperLists(); }
        }
        return { a: a, b: b };
    }

    function pickRow(items, current, cls, label, suffix, dotFor) {
        var opts = items.map(function (v) {
            return '<option value="' + esc(v) + '"' + (String(v) === String(current) ? ' selected' : '') + '>' + (dotFor(v) ? '● ' : '') + esc(v) + (suffix || '') + '</option>';
        }).join('');
        return '<div class="pc-paper-field pc-paper-field-select"><span class="pc-sub-label">' + label + '</span><select class="form-select pc-var pc-paper-select ' + cls + '">' + opts + '</select></div>';
    }

    function paperBlock(r) {
        var list = variantKey(r) === 'PaperCover' ? 'Cover' : 'Inner';
        return '<div class="pc-paper mt-1">'
            + '<div class="pc-paper-grid">'
            + '<div class="pc-paper-field pc-paper-field-stock">'
            + '<span class="pc-sub-label">Stock &amp; Quality</span>'
            + '<input class="form-control form-control-sm pc-stock font-monospace" list="pcPaper' + list + '" value="' + esc(stockText(r)) + '" aria-label="Paper" title="GSM & Paper Kind" placeholder="e.g. 80 GSM Offwhite">'
            + '</div>'
            + (r.auto === 'sheet' ? '<div class="pc-paper-field pc-paper-field-num"><span class="pc-sub-label">Ups</span><input type="number" class="form-control form-control-sm pc-ups text-end font-monospace" min="1" step="1" value="' + esc(r.ups) + '"></div>' : '')
            + '<div class="pc-paper-field pc-paper-field-num"><span class="pc-sub-label">Waste %</span><input type="number" class="form-control form-control-sm pc-waste text-end font-monospace" min="0" step="1" value="' + esc(r.waste) + '"></div>'
            + pickRow(gsmsFor(r), r.a, 'pc-a', 'GSM', '', function (g) { return knownRate('Paper', g, r.b); })
            + pickRow(kindsFor(r), r.b, 'pc-b', 'Type', '', function (k) { return knownRate('Paper', r.a, k); })
            + '</div>'
            + '</div>';
    }

    function buildPaperLists() {
        var host = $('pcLists');
        if (!host) return;
        ['Inner', 'Cover'].forEach(function (which) {
            var key = which === 'Cover' ? 'PaperCover' : 'PaperInner';
            var kinds = (VARIANTS[key].b[1].map(function (o) { return o[0]; })).concat(customKinds().filter(function (k) { return VARIANTS[key].b[1].every(function (o) { return o[0] !== k; }); }));
            var id = 'pcPaper' + which;
            var dl = $(id);
            if (!dl) { dl = doc.createElement('datalist'); dl.id = id; host.appendChild(dl); }
            var html = '';
            kinds.forEach(function (k) { VARIANTS[key].a[1].forEach(function (g) { html += '<option value="' + g[0] + ' GSM ' + esc(k) + '">'; }); });
            dl.innerHTML = html;
        });
    }

    // ── Local Storage Cache ────────────────────────────────────────────────
    function store(key, fn) {
        try { var all = JSON.parse(localStorage.getItem(key) || '{}'); fn(all); localStorage.setItem(key, JSON.stringify(all)); } catch (e) {}
    }
    function readStore(key) { try { return JSON.parse(localStorage.getItem(key) || '{}'); } catch (e) { return {}; } }
    
    var KEY_B = { Paper: 1, Packing: 1, Transport: 1, Design: 1 };
    var SHEET = {}, SHEET_ROWS = [], sheetLoaded = false;
    function norm(v) { return String(v == null ? '' : v).trim().toLowerCase(); }
    function keyOf(g, a, b) { return [norm(g), norm(a), KEY_B[g] ? norm(b) : ''].join('|'); }
    function plainLine(r) { var v = variants(r); return !v.a && !v.b; }
    function rateKey(r) { return plainLine(r) ? keyOf(r.g, r.n, '') : keyOf(r.g, r.a, r.b); }
    function memKey(r) { return rateKey(r); }
    function sheetPayload(r) {
        var plain = plainLine(r);
        return { group: r.g, item: String(plain ? r.n : (r.a == null ? '' : r.a)), kind: (!plain && KEY_B[r.g]) ? String(r.b == null ? '' : r.b) : '', rate: num(r.rate) };
    }
    function indexSheet(rows) {
        SHEET_ROWS = rows;
        SHEET = {};
        rows.forEach(function (x) { SHEET[keyOf(x.group, x.item, x.kind)] = x; });
    }
    function defaultRate(r) {
        if (!sheetLoaded && r.g === 'Paper') return PAPER_DEFAULT[[r.a, r.b].join('|').toLowerCase()] || 0;
        return 0;
    }
    function rateFor(r) {
        var s = SHEET[rateKey(r)];
        if (s) return s.rate;
        var m = readStore('pc-rates')[memKey(r)];
        return m != null ? m : defaultRate(r);
    }
    function saveRate(r) { store('pc-rates', function (m) { m[memKey(r)] = r.rate; }); }
    function saveLast() {
        store('pc-last', function (m) {
            m[state.type] = { 
                title: $('pcTitleIn').value,
                qty: $('pcQty').value, 
                pages: $('pcPages').value, 
                formas: $('pcFormasIn') ? $('pcFormasIn').value : '',
                formaSize: $('pcFormaSize') ? $('pcFormaSize').value : '16',
                colors: $('pcColors').value, 
                sides: $('pcSides').value, 
                plateMethod: $('pcPlateMethod') ? $('pcPlateMethod').value : 'work_and_turn',
                size: $('pcSize').value, 
                sheet: $('pcSheet').value, 
                overhead: $('pcOverhead').value, 
                profit: $('pcProfit').value, 
                round: $('pcRound').value 
            };
        });
    }
    function loadLast(type) {
        var v = readStore('pc-last')[type];
        var d = TYPES[type];
        $('pcTitleIn').value = v && v.title !== undefined ? v.title : (d.defaultTitle || '');
        $('pcQty').value = v ? v.qty : '1000';
        $('pcPages').value = v && d.pages ? v.pages : (d.pages ? '128' : '');
        if ($('pcFormasIn')) $('pcFormasIn').value = v && v.formas !== undefined ? v.formas : (d.pages ? '8' : '');
        if ($('pcFormaSize')) $('pcFormaSize').value = v && v.formaSize ? v.formaSize : '16';
        $('pcColors').value = v ? v.colors : '4';
        $('pcSides').value = v ? v.sides : '1';
        if ($('pcPlateMethod')) $('pcPlateMethod').value = v && v.plateMethod ? v.plateMethod : 'work_and_turn';
        $('pcSize').value = v && v.size !== undefined ? v.size : d.size;
        $('pcSheet').value = v ? v.sheet : '23x36';
        $('pcOverhead').value = v && v.overhead !== undefined ? v.overhead : '0';
        $('pcProfit').value = v && v.profit !== undefined ? v.profit : '20';
        $('pcRound').value = v ? v.round : '0';
    }

    // ── Calculations Engine ────────────────────────────────────────────────
    function job(qOverride) {
        var q = qOverride != null ? qOverride : num($('pcQty').value);
        var formaSize = $('pcFormaSize') ? (num($('pcFormaSize').value) || 16) : 16;
        var formasIn = $('pcFormasIn');
        var pages = num($('pcPages').value);
        var formas = 0;

        if (formasIn && formasIn.value !== '') {
            formas = num(formasIn.value);
            if (!pages && formas > 0) {
                pages = Math.round(formas * formaSize);
            }
        } else if (pages > 0) {
            formas = Math.ceil(pages / formaSize);
        }

        var plateMethod = $('pcPlateMethod') ? $('pcPlateMethod').value : 'work_and_turn';

        return {
            q: q,
            pages: pages,
            formaSize: formaSize,
            formas: formas,
            plateMethod: plateMethod,
            colors: num($('pcColors').value) || 4,
            sides: num($('pcSides').value) || 1,
            size: $('pcSize').value,
            sheet: $('pcSheet').value
        };
    }

    function upsFor(size, sheet) {
        var p = SIZES[size], s = SHEETS[sheet];
        if (!p || !s) return 0;
        var a = Math.floor(s[0] / p[0]) * Math.floor(s[1] / p[1]);
        var b = Math.floor(s[0] / p[1]) * Math.floor(s[1] / p[0]);
        return Math.max(a, b);
    }

    function perQty(r, j) {
        switch (r.b) {
            case 'Copy': return j.q;
            case 'Dozen': return Math.ceil(j.q / 12);
            case 'Hundred': return Math.ceil(j.q / 100);
            case 'Thousand': return Math.ceil(j.q / 1000);
            case 'Carton': return Math.ceil(j.q / 100);
            case 'Page': return j.pages || 1;
            default: return 1; // Job, Lot
        }
    }

    function auto(r, j) {
        var q = j.q;
        // Bangladesh Offset Press Standard: 
        // 1 to 1,000 copies = flat 1,000 impression minimum charge per pass/forma.
        // Above 1,000 copies, charged proportionally per thousand (e.g. 1.5 for 1,500, 2.5 for 2,500).
        var impressionMultiplier = Math.max(1, q / 1000);

        switch (r.auto) {
            case 'one': return 1;
            case 'copies': return q;
            case 'per': return perQty(r, j);
            case 'lam': return q * (r.b === 'Both Sides' ? 2 : 1);
            case 'paperBook': {
                // Book Inner Paper:
                // Formas = ceil(pages / formaSize)
                // Total full press sheets = (formas * q) * (1 + waste% / 100)
                // Total reams = sheets / 500
                if (!j.formas || q <= 0) return 0;
                var wastePct = num(r.waste) != null ? num(r.waste) : 5;
                var sheets = (j.formas * q) * (1 + wastePct / 100);
                return round(sheets / 500, 2);
            }
            case 'sheet': {
                // Cover board or flat single sheet items:
                var mult = (!r.flat && j.pages > 0 && TYPES[state.type].pages) ? j.pages : 1;
                var ups = Math.max(1, num(r.ups));
                var wastePct = num(r.waste) != null ? num(r.waste) : 5;
                var sheets = (q * mult / ups) * (1 + wastePct / 100);
                return round(sheets / 500, 2);
            }
            case 'platesBook': {
                // Plates based on Formas, Colors, and Plate Method:
                // Work & Turn = 1 plate per color per forma
                // Sheetwise = 2 plates per color per forma
                var methodMultiplier = (j.plateMethod === 'sheetwise') ? 2 : 1;
                return j.formas * j.colors * methodMultiplier;
            }
            case 'platesCover': {
                // Cover Plate: Standard 4 CTP plates for 4-Color (CMYK)
                var coverSides = (r.b === 'Both Sides' || j.sides === 2) ? 2 : 1;
                var coverColors = j.colors >= 4 ? 4 : (j.colors || 4);
                return coverColors * coverSides;
            }
            case 'platesJob': {
                var methodMultiplier = (j.plateMethod === 'sheetwise') ? 2 : 1;
                return j.colors * (j.sides === 2 ? methodMultiplier : 1);
            }
            case 'pressBook': {
                // Offset Printing Impression for Books:
                // Formas * Minimum 1,000 impression multiplier * sides
                // For 1-1,000 copies, exactly 1 thousand per forma!
                var sides = (j.plateMethod === 'sheetwise' && j.sides === 2) ? 2 : 1;
                return round(j.formas * impressionMultiplier * sides, 1);
            }
            case 'pressCover': {
                // Cover press impression:
                var coverSides = (r.b === 'Both Sides' || j.sides === 2) ? 2 : 1;
                return round(impressionMultiplier * coverSides, 1);
            }
            case 'pressJob': {
                // General Job Impression:
                var sides = (j.plateMethod === 'sheetwise' && j.sides === 2) ? 2 : 1;
                return round(impressionMultiplier * sides, 1);
            }
        }
        return 0;
    }

    function newRow(spec) {
        var d = GROUP_DEFAULT[spec.g] || {};
        var r = {
            id: ++state.seq, g: spec.g, n: spec.n, on: !spec.off, auto: spec.auto || GROUP_AUTO[spec.g], manual: false,
            qty: 0, rate: 0, ups: spec.ups || 8, upsManual: !!spec.ups, waste: spec.waste == null ? 5 : spec.waste,
            flat: !!spec.flat, noSize: !!spec.noSize,
            a: spec.a !== undefined ? spec.a : d.a, b: spec.b !== undefined ? spec.b : d.b
        };
        r.rate = rateFor(r); 
        r.touched = false;
        return r;
    }

    function applyType(type, keepJob) {
        state.type = type;
        var t = TYPES[type];
        state.rows = t.rows.map(newRow);
        $('pcUnit').value = t.unit;
        $('pcPagesWrap').hidden = !t.pages;
        if ($('pcFormasField')) $('pcFormasField').hidden = !t.pages;
        if (!keepJob) loadLast(type);
        
        var badgeEl = $('pcActiveTypeBadge');
        if (badgeEl) badgeEl.textContent = t.label;

        if ($('pcTypeSelect')) $('pcTypeSelect').value = type;

        if (el.types) {
            el.types.querySelectorAll('.pc-type').forEach(function (b) { 
                b.classList.toggle('is-active', b.dataset.type === type); 
            });
        }
        render();
    }

    function lineTotal(r, qty) { return r.on ? round((qty == null ? num(r.qty) : qty) * num(r.rate), 2) : 0; }

    function recalcAuto() {
        var j = job();
        var layout = upsFor(j.size, j.sheet);
        state.rows.forEach(function (r) {
            if (r.auto === 'sheet' && !r.upsManual && !r.noSize && layout > 0) r.ups = layout;
            if (!r.manual) r.qty = auto(r, j);
        });
    }

    function priceFor(q) {
        var j = job(q);
        var layout = upsFor(j.size, j.sheet);
        var direct = 0;
        state.rows.forEach(function (r) {
            if (!r.on) return;
            var probe = { auto: r.auto, b: r.b, waste: r.waste, flat: r.flat, ups: (r.auto === 'sheet' && !r.upsManual && !r.noSize && layout > 0) ? layout : r.ups };
            var qty = r.manual ? num(r.qty) : auto(probe, j);
            direct += round(qty * num(r.rate), 2);
        });
        var overheadPct = num($('pcOverhead').value);
        var overhead = round(direct * overheadPct / 100, 2);
        var cost = round(direct + overhead, 2);
        var profitPct = num($('pcProfit').value);
        var profit = round(cost * profitPct / 100, 2);
        var total = round(cost + profit, 2);
        var unit = q > 0 ? total / q : 0;
        var step = num($('pcRound').value);
        var rounded = false;
        if (step > 0 && unit > 0) { 
            var up = Math.ceil(unit / step - 1e-9) * step; 
            rounded = up !== unit; 
            unit = up; 
            total = round(unit * q, 2); 
        } else {
            unit = round(unit, 2);
        }
        return { direct: direct, overhead: overhead, cost: cost, profit: profit, total: total, unit: round(unit, 2), rounded: rounded };
    }

    // ── Table Rendering ─────────────────────────────────────────────────────
    function render() {
        recalcAuto();
        var html = state.rows.map(rowHtml).join('');
        el.groups.innerHTML = html || '<tr><td colspan="8" class="text-center text-muted py-4">No items added. Click "+ Add Item" above.</td></tr>';
        if ($('pcRowCountBadge')) $('pcRowCountBadge').textContent = state.rows.length;
        paint();
    }

    function select(cls, label, spec, current) {
        if (!spec) return '';
        var opts = spec[1];
        var has = opts.some(function (o) { return String(o[0]) === String(current); });
        return '<select class="form-select pc-var ' + cls + '" aria-label="' + esc(label) + '">'
            + (has ? '' : '<option value="" selected>' + esc(label) + '</option>')
            + opts.map(function (o) { return '<option value="' + esc(o[0]) + '"' + (String(o[0]) === String(current) ? ' selected' : '') + '>' + esc(o[1]) + '</option>'; }).join('') + '</select>';
    }

    function specHtml(r) {
        var v = variants(r);
        if (r.g === 'Paper') {
            var list = variantKey(r) === 'PaperCover' ? 'Cover' : 'Inner';
            var gsmOptsHtml = gsmsFor(r).map(function (g) {
                return '<option value="' + esc(g) + '"' + (String(g) === String(r.a) ? ' selected' : '') + '>' + (knownRate('Paper', g, r.b) ? '● ' : '') + esc(g) + ' GSM</option>';
            }).join('');
            var typeOptsHtml = kindsFor(r).map(function (k) {
                return '<option value="' + esc(k) + '"' + (String(k) === String(r.b) ? ' selected' : '') + '>' + (knownRate('Paper', r.a, k) ? '● ' : '') + esc(k) + '</option>';
            }).join('');

            return '<div class="d-flex align-items-center gap-1.5 flex-nowrap w-100">'
                + '<input class="form-control form-control-sm pc-stock font-monospace" list="pcPaper' + list + '" value="' + esc(stockText(r)) + '" placeholder="e.g. 80 GSM Offwhite" title="GSM & Paper Type" style="flex: 1 1 130px; min-width: 110px;">'
                + (r.auto === 'sheet' ? '<div class="input-group input-group-sm pc-spec-mini" style="width: 76px;" title="Ups per sheet"><span class="input-group-text px-1 text-muted" style="font-size: 10px;">Ups</span><input type="number" class="form-control form-control-sm pc-ups text-end font-monospace px-1" min="1" step="1" value="' + esc(r.ups) + '"></div>' : '')
                + '<div class="input-group input-group-sm pc-spec-mini" style="width: 82px;" title="Waste Percentage"><span class="input-group-text px-1 text-muted" style="font-size: 10px;">Waste</span><input type="number" class="form-control form-control-sm pc-waste text-end font-monospace px-1" min="0" step="1" value="' + esc(r.waste) + '"></div>'
                + '<select class="form-select form-select-sm pc-var pc-paper-select pc-a font-monospace" style="width: 90px;" aria-label="GSM" title="GSM">' + gsmOptsHtml + '</select>'
                + '<select class="form-select form-select-sm pc-var pc-paper-select pc-b" style="width: 115px;" aria-label="Type" title="Type">' + typeOptsHtml + '</select>'
                + '</div>';
        }

        if (r.g === 'Design') {
            return '<div class="d-flex align-items-center gap-1.5 flex-nowrap w-100">'
                + '<div style="flex: 1; min-width: 120px;">' + select('pc-a', v.a ? v.a[0] : 'Work', v.a, r.a) + '</div>'
                + '<div style="width: 100px;">' + select('pc-b', v.b ? v.b[0] : 'Basis', v.b, r.b) + '</div>'
                + '</div>';
        }

        if (r.g === 'CTP' || r.g === 'Press' || r.g === 'Lamination' || r.g === 'Packing' || r.g === 'Transport') {
            return '<div class="d-flex align-items-center gap-1.5 flex-nowrap w-100">'
                + (v.a ? '<div style="flex: 1; min-width: 120px;">' + select('pc-a', v.a[0], v.a, r.a) + '</div>' : '')
                + (v.b ? '<div style="flex: 1; min-width: 100px;">' + select('pc-b', v.b[0], v.b, r.b) + '</div>' : '')
                + '</div>';
        }

        if (r.g === 'Cutting' || r.g === 'Binding') {
            return '<div class="d-flex align-items-center gap-1.5 flex-nowrap w-100">'
                + (v.a ? '<div style="flex: 1; min-width: 140px;">' + select('pc-a', v.a[0], v.a, r.a) + '</div>' : '')
                + '</div>';
        }

        return '<span class="text-muted small ps-1">—</span>';
    }

    function rowHtml(r) {
        var v = variants(r);
        r.vk = variantKey(r);

        var groupOpts = GROUPS.map(function (g) {
            var gTitle = GROUP_TITLES_BN[g] || g;
            return '<option value="' + g + '"' + (g === r.g ? ' selected' : '') + '>' + gTitle + '</option>';
        }).join('');

        return '<tr class="pc-row' + (r.on ? '' : ' is-off table-light opacity-50') + '" data-id="' + r.id + '">'
            + '<td class="text-center align-middle col-chk">'
            + '<input type="checkbox" class="form-check-input pc-chk m-0" ' + (r.on ? 'checked' : '') + ' aria-label="Include">'
            + '</td>'
            + '<td class="align-middle col-type">'
            + '<select class="form-select form-select-sm pc-group-select pc-g" data-id="' + r.id + '" aria-label="Type">' + groupOpts + '</select>'
            + '</td>'
            + '<td class="align-middle col-item">'
            + '<input class="form-control form-control-sm pc-name fw-semibold text-dark" list="pcNames' + r.g + '" value="' + esc(r.n) + '" maxlength="80" aria-label="Item" placeholder="Item name">'
            + '</td>'
            + '<td class="align-middle col-spec">'
            + specHtml(r)
            + '</td>'
            + '<td class="text-end align-middle col-qty">'
            + '<div class="position-relative w-100">'
            + '<input type="number" class="form-control form-control-sm pc-qty text-end font-monospace" min="0" step="any" value="' + esc(r.qty) + '" aria-label="Quantity" placeholder="0">'
            + '<button type="button" class="pc-auto badge bg-primary text-white border-0 position-absolute" style="top: -6px; right: 6px; font-size: 8.5px; padding: 2px 5px; cursor: pointer;" title="Auto Calculated"' + (r.manual && r.auto ? '' : ' hidden') + '>Auto</button>'
            + '</div>'
            + '<small class="pc-qty-note text-muted font-monospace d-block text-end pe-1" style="font-size: 10px;"></small>'
            + '</td>'
            + '<td class="text-end align-middle col-rate">'
            + '<div class="d-flex align-items-center justify-content-end gap-1 w-100">'
            + '<input type="number" class="form-control form-control-sm pc-rate text-end font-monospace' + (!num(r.rate) && r.on && num(r.qty) > 0 ? ' is-invalid border-danger' : '') + '" min="0" step="any" value="' + (r.rate || '') + '" aria-label="Rate" placeholder="0.00">'
            + '<button type="button" class="btn btn-xs btn-outline-secondary pc-pin px-1.5 py-1 rounded" style="font-size: 10px;" title="Save" hidden>Pin</button>'
            + '</div>'
            + '</td>'
            + '<td class="text-end align-middle font-monospace fw-bold fs-6 pc-total text-dark col-total pe-2">'
            + money(lineTotal(r))
            + '</td>'
            + '<td class="text-center align-middle col-del">'
            + '<button type="button" class="btn btn-link btn-sm text-danger p-0 pc-del opacity-75 hover-opacity-100" title="Delete" aria-label="Remove" style="text-decoration:none; font-size:17px; line-height:1;">&times;</button>'
            + '</td>'
            + '</tr>';
    }

    function paint() {
        var j = job();
        var perGroup = {};
        state.rows.forEach(function (r) { perGroup[r.g] = (perGroup[r.g] || 0) + lineTotal(r); });

        var missing = 0;
        el.groups.querySelectorAll('.pc-row').forEach(function (rowEl) {
            var r = byId(rowEl.dataset.id);
            if (!r) return;
            rowEl.classList.toggle('is-off', !r.on);
            rowEl.classList.toggle('table-light', !r.on);
            rowEl.classList.toggle('opacity-50', !r.on);
            
            var tot = rowEl.querySelector('.pc-total');
            if (tot) tot.textContent = money(lineTotal(r));

            var q = rowEl.querySelector('.pc-qty'); 
            if (q && doc.activeElement !== q) q.value = r.qty;

            var u = rowEl.querySelector('.pc-ups'); 
            if (u && doc.activeElement !== u) u.value = r.ups;

            var w = rowEl.querySelector('.pc-waste');
            if (w && doc.activeElement !== w && r.waste != null) w.value = r.waste;

            var rate = rowEl.querySelector('.pc-rate'); 
            if (rate && doc.activeElement !== rate) rate.value = r.rate || '';

            var autoBtn = rowEl.querySelector('.pc-auto'); 
            if (autoBtn) autoBtn.hidden = !(r.manual && r.auto);

            var qNote = rowEl.querySelector('.pc-qty-note');
            if (qNote) {
                if (r.g === 'Paper') {
                    qNote.textContent = Math.round(num(r.qty) * 500).toLocaleString('en-US') + ' Sheets';
                } else if (r.g === 'CTP') {
                    qNote.textContent = (r.auto === 'platesBook' ? j.formas + ' formas × ' + j.colors + ' col' : (r.auto === 'platesCover' ? 'Cover 4 plates' : 'Plates'));
                } else if (r.g === 'Press') {
                    qNote.textContent = (j.q <= 1000 ? 'Min 1,000 Imp' : Math.round(num(r.qty) * 1000).toLocaleString('en-US') + ' imp');
                } else {
                    qNote.textContent = '';
                }
            }

            var lacks = r.on && num(r.qty) > 0 && !num(r.rate);
            if (rate) {
                rate.classList.toggle('is-invalid', lacks);
            }
            if (lacks) missing++;
            pinState(rowEl.querySelector('.pc-pin'), r);
        });

        el.groups.querySelectorAll('[data-sub]').forEach(function (s) { 
            s.textContent = money(perGroup[s.dataset.sub] || 0); 
        });

        var p = priceFor(j.q);
        $('pcDirect').textContent = money(p.direct);
        $('pcOverheadAmt').textContent = money(p.overhead);
        $('pcCost').textContent = money(p.cost);
        $('pcProfitAmt').textContent = money(p.profit);
        $('pcTotal').textContent = money(p.total);
        $('pcUnit2').textContent = j.q > 0 ? money(p.unit, 2) : '৳0.00';
        $('pcRoundedNote').hidden = !p.rounded;
        $('pcFootTotal').textContent = money(p.total);
        $('pcFootUnit').textContent = j.q > 0 ? money(p.unit, 2) : '৳0.00';
        
        $('pcFormas').textContent = j.formas ? String(j.formas) : '0';
        if ($('pcFormasIn') && doc.activeElement !== $('pcFormasIn') && j.formas > 0 && !$('pcFormasIn').value) {
            $('pcFormasIn').value = j.formas;
        }
        $('pcFormasWrap').hidden = !(TYPES[state.type].pages && j.pages);

        var layout = upsFor(j.size, j.sheet);
        $('pcUpsWrap').hidden = !(layout > 0 && state.type !== 'book');
        $('pcUps').textContent = String(layout);

        // Update Live Production KPI Status Strip
        if ($('pcKpiStrip')) {
            var totalPaperReams = 0;
            state.rows.filter(function (r) { return r.g === 'Paper' && r.on; }).forEach(function (r) { totalPaperReams += num(r.qty); });
            var totalPaperSheets = Math.round(totalPaperReams * 500);
            if ($('pcKpiPaperReams')) $('pcKpiPaperReams').textContent = totalPaperReams.toFixed(2) + ' Reams';
            if ($('pcKpiPaperSheets')) $('pcKpiPaperSheets').textContent = totalPaperSheets.toLocaleString('en-US') + ' Press Sheets';

            var wasteVal = 5;
            var paperRow = state.rows.filter(function (r) { return r.g === 'Paper' && r.on; })[0];
            if (paperRow && paperRow.waste != null) wasteVal = paperRow.waste;
            if ($('pcKpiPaperWaste')) $('pcKpiPaperWaste').textContent = wasteVal + '% Waste';

            if ($('pcKpiFormas')) $('pcKpiFormas').textContent = j.formas ? j.formas + ' Formas' : '1 Forma';
            if ($('pcKpiFormaSub')) $('pcKpiFormaSub').textContent = j.pages ? j.pages + ' Pages (' + j.formaSize + ' p/forma)' : (SIZES_BN[j.size] || j.size);
            if ($('pcKpiUpsBadge')) $('pcKpiUpsBadge').textContent = layout > 0 ? layout + ' Ups / Sheet' : (j.formaSize + ' p. Forma');

            var totalPlates = 0, innerPlates = 0, coverPlates = 0;
            state.rows.filter(function (r) { return r.g === 'CTP' && r.on; }).forEach(function (r) {
                var q = num(r.qty);
                totalPlates += q;
                if (/cover/i.test(r.n)) coverPlates += q; else innerPlates += q;
            });
            if ($('pcKpiPlates')) $('pcKpiPlates').textContent = totalPlates + ' Plates';
            if ($('pcKpiPlateSub')) $('pcKpiPlateSub').textContent = 'Inner ' + innerPlates + ' + Cover ' + coverPlates;
            if ($('pcKpiPlateMethodBadge')) $('pcKpiPlateMethodBadge').textContent = j.plateMethod === 'sheetwise' ? 'Sheetwise' : 'Work & Turn';

            var totalImpressions = 0;
            state.rows.filter(function (r) { return r.g === 'Press' && r.on; }).forEach(function (r) { totalImpressions += num(r.qty); });
            if ($('pcKpiImpressions')) $('pcKpiImpressions').textContent = totalImpressions.toFixed(1) + 'k';
            if (j.q <= 1000) {
                if ($('pcKpiImpressionBadge')) $('pcKpiImpressionBadge').textContent = 'Min 1,000';
                if ($('pcKpiImpressionSub')) $('pcKpiImpressionSub').textContent = j.q + ' copies (Min 1,000 rate)';
            } else {
                if ($('pcKpiImpressionBadge')) $('pcKpiImpressionBadge').textContent = 'Per Thousand';
                if ($('pcKpiImpressionSub')) $('pcKpiImpressionSub').textContent = Math.round(totalImpressions * 1000).toLocaleString('en-US') + ' impressions';
            }
        }

        $('pcMissing').hidden = missing === 0;
        $('pcMissing').textContent = missing ? 'Missing Rates: ' + missing : '';

        // Bars
        el.bars.innerHTML = GROUPS.filter(function (g) { return (perGroup[g] || 0) > 0; }).map(function (g) {
            var share = p.direct > 0 ? Math.round((perGroup[g] / p.direct) * 100) : 0;
            var gTitle = GROUP_TITLES_BN[g] || g;
            return '<div class="pc-bar mb-1.5">'
                + '<div class="d-flex justify-content-between small text-muted font-monospace mb-0.5" style="font-size: 11px;">'
                + '<span>' + gTitle + '</span><b>' + money(perGroup[g]) + ' (' + share + '%)</b>'
                + '</div>'
                + '<div class="progress" style="height: 6px;"><div class="progress-bar bg-primary" role="progressbar" style="width: ' + share + '%"></div></div>'
                + '</div>';
        }).join('') || '<div class="pc-empty text-muted small text-center py-2">— No expenses recorded —</div>';

        // Tiers
        if (p.direct > 0) {
            var qs = TIERS.slice();
            if (j.q > 0 && qs.indexOf(j.q) === -1) { qs.push(j.q); qs.sort(function (a, b) { return a - b; }); }
            el.tiers.innerHTML = qs.map(function (q) {
                var t = priceFor(q);
                var isCur = (q === j.q);
                return '<button type="button" class="pc-tier' + (isCur ? ' is-current' : '') + '" data-q="' + q + '">'
                    + '<span class="fw-bold font-monospace">' + q.toLocaleString('en-US') + ' ' + $('pcUnit').value + '</span>'
                    + '<span class="font-monospace fw-bold">' + money(t.unit, 2) + ' /unit</span>'
                    + '</button>';
            }).join('');
            el.tiersWrap.hidden = false;
        } else {
            el.tiersWrap.hidden = true;
        }

        var canInsert = (p.total > 0 && j.q > 0);
        el.add.disabled = !canInsert;
        el.plain.disabled = !canInsert;
        if (el.addDetailed) el.addDetailed.disabled = !canInsert;

        // Update live spec textarea if not manually dirty
        updateSpecBox();

        return { direct: p.direct, cost: p.cost, total: p.total, unit: p.unit, j: j };
    }

    function byId(id) { return state.rows.filter(function (r) { return String(r.id) === String(id); })[0]; }

    // ── Live Specification Generator ────────────────────────────────────────
    function label(r) {
        var v = variants(r);
        var bits = [];
        if (v.a && r.a !== undefined && r.a !== '') bits.push(typeof r.a === 'number' ? r.a + ' GSM' : r.a);
        if (v.b && r.b !== undefined && r.b !== '' && ['Per'].indexOf(v.b[0]) === -1 && ['Side', 'Sides'].indexOf(v.b[0]) === -1) bits.push(r.b);
        if (r.g === 'Lamination' && r.b === 'Both Sides') bits.push('Both Sides');
        return r.n + (bits.length ? ' (' + bits.join(', ') + ')' : '');
    }

    function specText() {
        var j = job();
        var parts = [];
        var sizeLabel = SIZES_BN[j.size] || j.size;
        if (sizeLabel) parts.push('Size: ' + sizeLabel);
        if (j.pages) parts.push('Pages: ' + j.pages + ' (' + (j.formas || 1) + ' Formas)');
        parts.push('Print: ' + j.colors + ' Colors' + (j.sides === 2 ? ' (Both Sides)' : ' (1 Side)'));
        parts.push('Plates: ' + (j.plateMethod === 'sheetwise' ? 'Sheetwise' : 'Work & Turn'));
        if (j.q <= 1000) parts.push('Press: Min 1,000 Imp');

        var by = {};
        state.rows.filter(function (r) { return r.on && lineTotal(r) > 0; }).forEach(function (r) { 
            (by[r.g] = by[r.g] || []).push(label(r)); 
        });

        var comps = [];
        ['Paper', 'CTP', 'Press', 'Lamination', 'Binding', 'Cutting', 'Packing'].forEach(function (g) {
            if (by[g]) {
                var gTitle = GROUP_TITLES_BN[g] || g;
                comps.push(gTitle + ': ' + by[g].join(', '));
            }
        });

        return parts.join(' | ') + (comps.length ? '\n' + comps.join('\n') : '');
    }

    function ratedText() {
        var j = job();
        var parts = [];
        var sizeLabel = SIZES_BN[j.size] || j.size;
        if (sizeLabel) parts.push('Size: ' + sizeLabel);
        if (j.pages) parts.push('Pages: ' + j.pages + ' (' + (j.formas || 1) + ' Formas)');
        parts.push('Print: ' + j.colors + ' Colors' + (j.sides === 2 ? ' (Both Sides)' : ' (1 Side)'));
        parts.push('Plates: ' + (j.plateMethod === 'sheetwise' ? 'Sheetwise' : 'Work & Turn'));
        if (j.q <= 1000) parts.push('Press: Min 1,000 Imp');

        var comps = [];
        GROUPS.forEach(function (g) {
            var items = state.rows.filter(function (r) { return r.g === g && r.on && lineTotal(r) > 0; })
                .map(function (r) { 
                    var bUnit = BASIS_BN[r.g] || BASIS[r.g];
                    return label(r) + ' [' + r.qty + ' ' + bUnit + ' @ ৳' + r.rate + ' = ' + money(lineTotal(r)) + ']'; 
                });
            if (items.length) {
                var gTitle = GROUP_TITLES_BN[g] || g;
                comps.push(gTitle + ': ' + items.join(', '));
            }
        });

        return parts.join(' | ') + (comps.length ? '\n' + comps.join('\n') : '');
    }

    function updateSpecBox() {
        var box = $('pcSpecTextarea');
        if (!box) return;
        if (!box._manuallyEdited) {
            var title = ($('pcTitleIn').value.trim()) || TYPES[state.type].defaultTitle;
            var s = specText();
            box.value = title ? title + '\n' + s : s;
        }
    }

    function sheetText() {
        var s = paint();
        var title = ($('pcTitleIn').value.trim()) || TYPES[state.type].defaultTitle;
        var lines = [
            '==================================================',
            'Cost & Specification Sheet: ' + title,
            'Job Type: ' + TYPES[state.type].label + ' | Quantity: ' + s.j.q + ' ' + $('pcUnit').value,
            '==================================================',
            '',
            'Specification:',
            specText(),
            '',
            'Component Breakdown:'
        ];

        GROUPS.forEach(function (g) {
            var rows = state.rows.filter(function (r) { return r.g === g && r.on && lineTotal(r) > 0; });
            if (rows.length) {
                var gTitle = GROUP_TITLES_BN[g] || g;
                lines.push('--- ' + gTitle + ' (' + g + ') ---');
                rows.forEach(function (r) {
                    var bUnit = BASIS_BN[r.g] || BASIS[r.g];
                    lines.push('  • ' + label(r) + ': ' + r.qty + ' ' + bUnit + ' × ৳' + r.rate + ' = ' + money(lineTotal(r)));
                });
            }
        });

        lines.push('');
        lines.push('Direct Cost: ' + money(s.direct));
        if (num($('pcOverhead').value) > 0) {
            lines.push('Overhead (' + $('pcOverhead').value + '%): ' + money(s.cost - s.direct));
        }
        lines.push('Total Cost: ' + money(s.cost));
        if (num($('pcProfit').value) > 0) {
            lines.push('Profit Margin (' + $('pcProfit').value + '%): ' + money(s.total - s.cost));
        }
        lines.push('--------------------------------------------------');
        lines.push('Total Price: ' + money(s.total));
        lines.push('Rate per ' + $('pcUnit').value + ': ' + money(s.unit, 2));
        lines.push('==================================================');

        return lines.join('\n');
    }

    function toast(text) {
        var t = $('pcToast');
        if (!t) return;
        t.textContent = text; 
        t.hidden = false;
        clearTimeout(t._t);
        t._t = setTimeout(function () { t.hidden = true; }, 2200);
    }

    // ── Insertion Functions ────────────────────────────────────────────────
    // 1. Single consolidated line insertion
    function insert(withRates) {
        var s = paint();
        if (!(s.j.q > 0)) { toast('Enter quantity'); $('pcQty').focus(); return; }
        if (!(s.total > 0)) { toast('Enter rates'); return; }

        var title = $('pcTitleIn').value.trim() || TYPES[state.type].defaultTitle;
        var specBox = $('pcSpecTextarea');
        var spec = (specBox && specBox.value.trim()) ? specBox.value.trim() : (withRates === true ? ratedText() : specText());

        var itemType = 'Printing & Binding';
        if (state.type === 'stationery') itemType = 'Stationery';
        else if (state.type === 'book') itemType = 'Book (Paperback)';

        if (typeof window.addPresetItem === 'function') {
            window.addPresetItem(title, spec, itemType, $('pcUnit').value || 'Pcs', s.unit, s.unit, s.j.q);
            toast('Added to quotation ✓');
        }

        var modal = window.bootstrap && window.bootstrap.Modal.getInstance($('printCostCalculatorModal'));
        if (modal) modal.hide();
    }

    // 2. Itemized Component Breakdown (Add components as individual lines to quotation)
    function insertDetailed() {
        var s = paint();
        if (!(s.j.q > 0)) { toast('Enter quantity'); $('pcQty').focus(); return; }
        if (!(s.total > 0)) { toast('Enter rates'); return; }

        var activeRows = state.rows.filter(function (r) { return r.on && lineTotal(r) > 0; });
        if (!activeRows.length) {
            toast('No active components');
            return;
        }

        if (typeof window.addPresetItem !== 'function') {
            toast('Host method not found');
            return;
        }

        var jobTitle = $('pcTitleIn').value.trim() || TYPES[state.type].defaultTitle;
        var count = 0;
        var markup = (s.direct > 0 && s.total > 0) ? (s.total / s.direct) : 1;

        activeRows.forEach(function (r) {
            var gTitle = GROUP_TITLES_BN[r.g] || r.g;
            var itemTitle = jobTitle + ' — ' + gTitle + ': ' + r.n;
            var specDetail = label(r) + ' (' + r.qty + ' ' + (BASIS_BN[r.g] || BASIS[r.g]) + ' @ ৳' + r.rate + ')';

            var itemType = 'Printing & Binding';
            if (r.g === 'Paper') itemType = 'Paper / Raw Materials';
            else if (r.g === 'Design') itemType = 'Service';
            else if (r.g === 'Transport' || r.g === 'Other') itemType = 'Product';

            var compTotal = round(lineTotal(r) * markup, 2);
            var unitPrice = round(compTotal / s.j.q, 2);
            if (unitPrice <= 0.01) unitPrice = round(num(r.rate) * markup, 2);

            window.addPresetItem(itemTitle, specDetail, itemType, $('pcUnit').value || 'Pcs', unitPrice, unitPrice, s.j.q);
            count++;
        });

        toast(count + ' items added to quotation ✓');

        var modal = window.bootstrap && window.bootstrap.Modal.getInstance($('printCostCalculatorModal'));
        if (modal) modal.hide();
    }

    function resetAll() {
        ['pcTitleIn', 'pcQty', 'pcPages', 'pcOverhead', 'pcProfit'].forEach(function (id) { var el = $(id); if (el) el.value = ''; });
        if ($('pcColors')) $('pcColors').value = '4'; 
        if ($('pcSides')) $('pcSides').value = '1'; 
        if ($('pcRound')) $('pcRound').value = '0';
        if ($('pcFormaSize')) $('pcFormaSize').value = '16';
        if ($('pcSize')) $('pcSize').value = TYPES[state.type].size; 
        if ($('pcSheet')) $('pcSheet').value = '23x36';
        
        var specBox = $('pcSpecTextarea');
        if (specBox) {
            specBox._manuallyEdited = false;
            specBox.value = '';
        }

        applyType(state.type, true);
        saveLast();
    }

    // ── Rate sheet (server) ─────────────────────────────────────────────────
    function root() { return $('printCostCalculatorModal'); }
    function api(method, url, body) {
        var token = (doc.querySelector('meta[name="csrf-token"]') || {}).content || '';
        return fetch(url, {
            method: method, credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
            body: body ? JSON.stringify(body) : undefined
        }).then(function (r) { return r.json().catch(function () { return {}; }).then(function (j) { if (!r.ok) throw j; return j; }); });
    }

    function loadSheet() {
        var rt = root();
        if (!rt || !rt.dataset.ratesUrl) return Promise.resolve();
        return fetch(rt.dataset.ratesUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { if (!r.ok) throw new Error('rates'); return r.json(); })
            .then(function (rows) { indexSheet(rows); sheetLoaded = true; reapplyRates(); renderRates(); })
            .catch(function () {});
    }

    function reapplyRates() {
        state.rows.forEach(function (r) { if (!r.touched) r.rate = rateFor(r); });
        render();
    }

    function upsert(row) {
        var k = keyOf(row.group, row.item, row.kind);
        var rows = SHEET_ROWS.filter(function (x) { return keyOf(x.group, x.item, x.kind) !== k && x.id !== row.id; });
        rows.push(row);
        var order = GROUPS.concat(['Other']);
        rows.sort(function (x, y) {
            var gx = order.indexOf(x.group), gy = order.indexOf(y.group);
            if (gx !== gy) return gx - gy;
            var nx = parseFloat(x.item), ny = parseFloat(y.item);
            if (!isNaN(nx) && !isNaN(ny) && nx !== ny) return nx - ny;
            return (x.item + x.kind).toLowerCase() < (y.item + y.kind).toLowerCase() ? -1 : 1;
        });
        indexSheet(rows);
    }

    function pinState(btn, r) {
        if (!btn) return;
        var s = SHEET[rateKey(r)];
        if (!sheetLoaded || !num(r.rate)) { btn.hidden = true; return; }
        btn.hidden = false;
        var same = s && Math.abs(s.rate - num(r.rate)) < 0.005;
        btn.textContent = !s ? 'Save' : (same ? 'Saved' : 'Update');
        btn.classList.toggle('btn-success', !!same);
        btn.classList.toggle('btn-outline-secondary', !same);
        btn.disabled = !!same;
    }

    function saveToSheet(r) {
        api('POST', root().dataset.ratesStore, sheetPayload(r)).then(function (res) {
            upsert(res.row);
            paint();
            renderRates();
            toast('Saved to rates ✓');
        }).catch(function () { toast('Save failed'); });
    }

    // ── Rate sheet panel ────────────────────────────────────────────────────
    var rateGroup = '', rateQuery = '';

    function renderRates() {
        var body = $('pcRatesBody');
        if (!body) return;
        var q = rateQuery.toLowerCase();
        var rows = SHEET_ROWS.filter(function (x) {
            return (!rateGroup || x.group === rateGroup) && (!q || (x.group + ' ' + x.item + ' ' + x.kind).toLowerCase().indexOf(q) !== -1);
        });

        body.innerHTML = rows.map(function (x) {
            var gTitle = GROUP_TITLES_BN[x.group] || x.group;
            return '<tr data-id="' + x.id + '">'
                + '<td><span class="badge bg-light text-dark border px-2 py-1 rounded-pill">' + esc(gTitle) + '</span></td>'
                + '<td class="fw-semibold">' + esc(x.item) + (x.group === 'Paper' && x.item ? ' GSM' : '') + '</td>'
                + '<td class="text-muted">' + esc(x.kind || '—') + '</td>'
                + '<td class="text-end font-monospace"><input type="number" class="form-control form-control-sm pr-rate text-end font-monospace d-inline-block" min="0" step="0.01" value="' + esc(x.rate) + '" aria-label="Rate" style="width: 110px;"></td>'
                + '<td class="text-center"><button type="button" class="btn btn-link btn-sm text-danger p-0 pr-del" title="Delete" aria-label="Remove"><i class="fa-solid fa-trash-can"></i></button></td>'
                + '</tr>';
        }).join('');

        $('pcRatesEmpty').hidden = rows.length > 0;

        var counts = {};
        SHEET_ROWS.forEach(function (x) { counts[x.group] = (counts[x.group] || 0) + 1; });
        $('pcRatesGroups').innerHTML = '<option value=""' + (rateGroup === '' ? ' selected' : '') + '>All (' + SHEET_ROWS.length + ')</option>'
            + GROUPS.filter(function (g) { return counts[g]; }).map(function (g) {
                var gTitle = GROUP_TITLES_BN[g] || g;
                return '<option value="' + g + '"' + (rateGroup === g ? ' selected' : '') + '>' + gTitle + ' (' + counts[g] + ')</option>';
            }).join('');
    }

    function fillAddLists() {
        var g = $('prGroup').value;
        var key = g === 'Paper' ? 'PaperCover' : g;
        var v = VARIANTS[key] || { a: null, b: null };
        var items = v.a ? v.a[1].map(function (o) { return o[0]; }) : [];
        if (g === 'Paper') { items = gsmsFor({ g: 'Paper', n: 'Cover', a: null }); }
        var kinds = KEY_B[g] && v.b ? v.b[1].map(function (o) { return o[0]; }) : [];
        if (g === 'Paper') kinds = kindsFor({ g: 'Paper', n: 'Cover' });
        $('prItems').innerHTML = items.map(function (i) { return '<option value="' + esc(i) + '">'; }).join('');
        $('prKinds').innerHTML = kinds.map(function (i) { return '<option value="' + esc(i) + '">'; }).join('');
        $('prKind').disabled = !KEY_B[g];
        if (!KEY_B[g]) $('prKind').value = '';
    }

    function openRates(open) {
        $('pcCalcView').hidden = open;
        $('pcFoot').hidden = open;
        $('pcRatesPanel').hidden = !open;
        $('pcRatesBtn').classList.toggle('active', open);
        if (open) { renderRates(); fillAddLists(); }
        else reapplyRates();
    }

    function bindRates() {
        var opts = GROUPS.map(function (g) { 
            var gTitle = GROUP_TITLES_BN[g] || g;
            return '<option value="' + g + '">' + gTitle + '</option>'; 
        }).join('');
        
        $('prGroup').innerHTML = opts;
        $('paGroup').innerHTML = '<option value="">All</option>' + opts;
        $('prGroup').addEventListener('change', fillAddLists);
        $('pcRatesBtn').addEventListener('click', function () { openRates($('pcRatesPanel').hidden); });
        $('pcRatesBack').addEventListener('click', function () { openRates(false); });
        $('pcRatesSearch').addEventListener('input', function (e) { rateQuery = e.target.value; renderRates(); });
        $('pcRatesGroups').addEventListener('change', function (e) { rateGroup = e.target.value; renderRates(); });

        $('pcRatesAdd').addEventListener('submit', function (e) {
            e.preventDefault();
            var g = $('prGroup').value;
            api('POST', root().dataset.ratesStore, { group: g, item: $('prItem').value.trim(), kind: KEY_B[g] ? $('prKind').value.trim() : '', rate: num($('prRate').value) })
                .then(function (res) {
                    upsert(res.row);
                    if (g === 'Paper' && res.row.kind) addKind(res.row.kind);
                    $('prItem').value = ''; $('prKind').value = ''; $('prRate').value = '';
                    renderRates(); buildPaperLists(); fillAddLists();
                    toast(res.created ? 'Added' : 'Updated');
                    $('prItem').focus();
                }).catch(function () { toast('Save failed'); });
        });

        $('pcRatesAdjust').addEventListener('submit', function (e) {
            e.preventDefault();
            api('POST', root().dataset.ratesAdjust, { group: $('paGroup').value || null, mode: $('paMode').value, value: num($('paValue').value) })
                .then(function (res) { 
                    indexSheet(res.rows); 
                    renderRates(); 
                    $('paValue').value = ''; 
                    toast(res.changed + ' rates adjusted ✓'); 
                })
                .catch(function () { toast('Failed to apply'); });
        });

        var body = $('pcRatesBody');
        body.addEventListener('change', function (e) {
            if (!e.target.classList.contains('pr-rate')) return;
            var tr = e.target.closest('tr');
            var row = SHEET_ROWS.filter(function (x) { return String(x.id) === tr.dataset.id; })[0];
            if (!row) return;
            api('POST', root().dataset.ratesStore, { group: row.group, item: row.item, kind: row.kind, rate: num(e.target.value) })
                .then(function (res) { upsert(res.row); toast('Rate saved'); })
                .catch(function () { e.target.value = row.rate; toast('Save failed'); });
        });

        body.addEventListener('click', function (e) {
            var del = e.target.closest('.pr-del');
            if (!del) return;
            var tr = del.closest('tr');
            if (!window.confirm('Are you sure you want to delete this rate?')) return;
            api('DELETE', root().dataset.ratesDelete + '/' + tr.dataset.id)
                .then(function () { indexSheet(SHEET_ROWS.filter(function (x) { return String(x.id) !== tr.dataset.id; })); renderRates(); toast('Deleted'); })
                .catch(function () { toast('Delete failed'); });
        });
    }

    // ── Input & Event Handling ──────────────────────────────────────────────
    function onInput(e) {
        var t = e.target;
        var rowEl = t.closest && t.closest('.pc-row');
        if (rowEl) {
            var r = byId(rowEl.dataset.id);
            if (!r) return;
            if (t.classList.contains('pc-name')) { r.n = t.value; }
            else if (t.classList.contains('pc-qty')) { r.qty = num(t.value); r.manual = true; }
            else if (t.classList.contains('pc-rate')) { r.rate = num(t.value); r.touched = true; saveRate(r); }
            else if (t.classList.contains('pc-ups')) { r.ups = Math.max(1, num(t.value)); r.upsManual = true; r.manual = false; recalcAuto(); }
            else if (t.classList.contains('pc-waste')) { r.waste = num(t.value); r.manual = false; recalcAuto(); }
            paint();
            return;
        }

        if (t.id === 'pcSpecTextarea') {
            t._manuallyEdited = true;
            return;
        }

        if (t.id === 'pcFormasIn') {
            var formaSize = $('pcFormaSize') ? (num($('pcFormaSize').value) || 16) : 16;
            var fVal = num(t.value);
            if (fVal > 0) {
                $('pcPages').value = Math.round(fVal * formaSize);
            } else if (t.value === '') {
                $('pcPages').value = '';
            }
        } else if (t.id === 'pcPages') {
            var formaSize = $('pcFormaSize') ? (num($('pcFormaSize').value) || 16) : 16;
            var pVal = num(t.value);
            if ($('pcFormasIn')) {
                $('pcFormasIn').value = pVal > 0 ? (pVal / formaSize) : '';
            }
        } else if (t.id === 'pcFormaSize') {
            var formaSize = num(t.value) || 16;
            if ($('pcFormasIn') && $('pcFormasIn').value !== '') {
                $('pcPages').value = Math.round(num($('pcFormasIn').value) * formaSize);
            } else if ($('pcPages') && $('pcPages').value !== '') {
                if ($('pcFormasIn')) $('pcFormasIn').value = (num($('pcPages').value) / formaSize);
            }
        }

        recalcAuto();
        paint();
        saveLast();
    }

    function onChange(e) {
        var t = e.target;
        var rowEl = t.closest && t.closest('.pc-row');
        if (!rowEl) { 
            if (t.closest('.pc-job, .pc-summary')) { recalcAuto(); paint(); saveLast(); } 
            return; 
        }
        var r = byId(rowEl.dataset.id);
        if (!r) return;
        if (t.classList.contains('pc-chk')) { r.on = t.checked; paint(); return; }
        if (t.classList.contains('pc-g')) {
            r.g = t.value;
            var names = NAMES[r.g] || ['Custom ' + r.g];
            r.n = names[0];
            r.a = (GROUP_DEFAULT[r.g] || {}).a;
            r.b = (GROUP_DEFAULT[r.g] || {}).b;
            r.auto = GROUP_AUTO[r.g];
            r.rate = rateFor(r);
            r.touched = false;
            r.manual = false;
            recalcAuto();
            render();
            return;
        }
        if (t.classList.contains('pc-name')) {
            r.n = t.value;
            if (variantKey(r) !== r.vk) { 
                r.a = (GROUP_DEFAULT[r.g] || {}).a; 
                r.b = (GROUP_DEFAULT[r.g] || {}).b; 
                r.rate = rateFor(r); 
                r.touched = false; 
                r.manual = false; 
                render(); 
            }
            return;
        }
        if (t.classList.contains('pc-stock')) {
            var parsed = parseStock(t.value, r);
            if (parsed) { r.a = parsed.a; r.b = parsed.b; r.rate = rateFor(r); r.touched = false; r.manual = false; render(); focusRate(r); } else { render(); }
            return;
        }
        if (t.classList.contains('pc-a') || t.classList.contains('pc-b')) {
            var raw = t.value;
            var key = t.classList.contains('pc-a') ? 'a' : 'b';
            r[key] = (key === 'a' && /^\d+(\.\d+)?$/.test(raw)) ? parseFloat(raw) : raw;
            r.rate = rateFor(r); 
            r.touched = false;
            r.manual = false;
            render();
            focusRate(r);
        }
    }

    function focusRate(r) {
        var again = el.groups.querySelector('.pc-row[data-id="' + r.id + '"] .pc-rate');
        if (again && !num(r.rate) && again.focus) again.focus({ preventScroll: true });
    }

    function onClick(e) {
        var t = e.target;
        var rowEl = t.closest && t.closest('.pc-row');
        if (rowEl && (t.classList.contains('pc-pin') || t.closest('.pc-pin'))) {
            var pr = byId(rowEl.dataset.id);
            if (pr) saveToSheet(pr);
            return;
        }
        if (rowEl && (t.classList.contains('pc-del') || t.closest('.pc-del'))) {
            state.rows = state.rows.filter(function (r) { return String(r.id) !== rowEl.dataset.id; });
            render();
            return;
        } else if (rowEl && t.classList.contains('pc-auto')) {
            var r = byId(rowEl.dataset.id);
            if (r) { r.manual = false; render(); }
            return;
        } else if (t.closest && t.closest('[data-q]')) {
            $('pcQty').value = t.closest('[data-q]').dataset.q;
            recalcAuto(); paint(); saveLast();
            return;
        }

        // Add Component (Dropdown items or group header +Add)
        var addBtn = t.closest && t.closest('[data-add]');
        if (addBtn) {
            addComponent(addBtn.dataset.add);
            return;
        }

        // Accordion Group Header Click (toggle expand/collapse)
        var gHead = t.closest && t.closest('.pc-group-head');
        if (gHead && !t.closest('button, input, select, a, .form-check-input')) {
            var gCard = gHead.closest('.pc-group-card');
            if (gCard) {
                gCard.classList.toggle('is-collapsed');
            }
            return;
        }
    }

    function addComponent(group) {
        var names = NAMES[group] || ['Custom ' + group];
        var used = state.rows.filter(function (r) { return r.g === group; }).map(function (r) { return r.n; });
        var name = names.filter(function (n) { return used.indexOf(n) === -1; })[0] || names[0];
        var spec = { g: group, n: name };
        if (group === 'Paper') { spec.waste = 5; }
        state.rows.push(newRow(spec));
        render();

        var gCard = el.groups.querySelector('.pc-group-card[data-group="' + group + '"]');
        if (gCard && gCard.classList.contains('is-collapsed')) {
            gCard.classList.remove('is-collapsed');
        }

        var rows = el.groups.querySelectorAll('[data-group="' + group + '"] .pc-row');
        var last = rows[rows.length - 1];
        if (last) {
            var target = last.querySelector('.pc-stock, .pc-rate, .pc-name');
            if (target && target.focus) target.focus({ preventScroll: true });
        }
    }

    function onKey(e) {
        if (e.key !== 'Enter' || !e.target.classList.contains('pc-rate')) return;
        e.preventDefault();
        var all = Array.prototype.slice.call(el.groups.querySelectorAll('.pc-row:not(.is-off) .pc-rate'));
        var next = all[all.indexOf(e.target) + 1];
        if (next) { next.focus(); next.select(); } else { el.add.focus(); }
    }

    // ── Boot ────────────────────────────────────────────────────────────────
    function boot() {
        var root = $('printCostCalculatorModal');
        if (!root || root.dataset.ready) return;
        root.dataset.ready = '1';
        
        el.groups = $('pcRowsBody') || $('pcGroups'); 
        el.bars = $('pcBars'); 
        el.types = $('pcTypes'); 
        el.add = $('pcAdd'); 
        el.addDetailed = $('pcAddDetailed');
        el.plain = $('pcPlain'); 
        el.tiers = $('pcTiers'); 
        el.tiersWrap = $('pcTiersWrap');

        // Populate Trim Sizes dropdown
        if ($('pcSize')) {
            $('pcSize').innerHTML = Object.keys(SIZES).map(function (k) { 
                return '<option value="' + k + '">' + (SIZES_BN[k] || k) + '</option>'; 
            }).join('');
        }

        // Populate Job Types dropdown (#pcTypeSelect)
        var typeSelect = $('pcTypeSelect');
        if (typeSelect) {
            typeSelect.innerHTML = Object.keys(TYPES).map(function (k) { 
                return '<option value="' + k + '"' + (k === state.type ? ' selected' : '') + '>' + TYPES[k].label + '</option>'; 
            }).join('');
            typeSelect.addEventListener('change', function () {
                applyType(this.value);
            });
        }

        // Populate Job Types tab buttons if present
        if (el.types) {
            el.types.innerHTML = Object.keys(TYPES).map(function (k) { 
                return '<button type="button" class="pc-type' + (k === state.type ? ' is-active' : '') + '" data-type="' + k + '">' + TYPES[k].label + '</button>'; 
            }).join('');
            el.types.addEventListener('click', function (e) { 
                var b = e.target.closest('.pc-type'); 
                if (b) applyType(b.dataset.type); 
            });
        }

        // Populate Component Add Bar if present
        if ($('pcAddBar')) {
            $('pcAddBar').innerHTML = GROUPS.map(function (g) { 
                var gTitle = GROUP_TITLES_BN[g] || g;
                var gIcon = GROUP_ICONS[g] || 'fa-tag';
                return '<button type="button" class="pc-addbtn" data-add="' + g + '" title="Add ' + gTitle + '">'
                    + '<i class="fa-solid ' + gIcon + ' me-1"></i>+' + gTitle 
                    + '</button>'; 
            }).join('');
            $('pcAddBar').addEventListener('click', function (e) { 
                var b = e.target.closest('[data-add]'); 
                if (b) addComponent(b.dataset.add); 
            });
        }

        root.addEventListener('input', onInput);
        root.addEventListener('change', onChange);
        root.addEventListener('click', onClick);
        root.addEventListener('keydown', onKey);

        root.querySelectorAll('[data-qty]').forEach(function (b) { 
            b.addEventListener('click', function () { 
                $('pcQty').value = b.dataset.qty; 
                recalcAuto(); paint(); saveLast(); 
            }); 
        });

        root.querySelectorAll('[data-profit]').forEach(function (b) { 
            b.addEventListener('click', function () { 
                $('pcProfit').value = b.dataset.profit; 
                paint(); saveLast(); 
            }); 
        });

        $('pcReset').addEventListener('click', resetAll);

        // Copy Full Sheet
        $('pcCopy').addEventListener('click', function () {
            var text = sheetText();
            (navigator.clipboard ? navigator.clipboard.writeText(text) : Promise.reject()).then(
                function () { toast('Cost sheet copied ✓'); }, 
                function () { toast('Copy failed'); }
            );
        });

        // Copy Spec Box Text
        var copySpecBtn = $('pcCopySpecBtn');
        if (copySpecBtn) {
            copySpecBtn.addEventListener('click', function () {
                var spec = $('pcSpecTextarea').value;
                if (!spec) return;
                (navigator.clipboard ? navigator.clipboard.writeText(spec) : Promise.reject()).then(
                    function () { toast('Specification copied ✓'); }, 
                    function () { toast('Copy failed'); }
                );
            });
        }

        // Refresh Spec Box
        var refreshSpecBtn = $('pcRefreshSpec');
        if (refreshSpecBtn) {
            refreshSpecBtn.addEventListener('click', function () {
                var specBox = $('pcSpecTextarea');
                if (specBox) {
                    specBox._manuallyEdited = false;
                    updateSpecBox();
                    toast('Specification refreshed');
                }
            });
        }

        // Single Consolidated Line Insert
        el.add.addEventListener('click', function () { insert(true); });
        el.plain.addEventListener('click', function () { insert(false); });

        // Itemized Breakdown Insert (New Feature!)
        if (el.addDetailed) {
            el.addDetailed.addEventListener('click', insertDetailed);
        }

        root.addEventListener('shown.bs.modal', function () { 
            var modalBody = root.querySelector('.modal-body');
            if (modalBody) modalBody.scrollTop = 0;
            var target = ($('pcQty').value) ? $('pcTitleIn') : $('pcQty');
            if (target && target.focus) target.focus({ preventScroll: true });
        });

        applyType('book');
        loadSheet();
    }

    window.openPrintCostCalculator = function () {
        boot();
        var root = $('printCostCalculatorModal');
        if (!root || !window.bootstrap) return;
        window.bootstrap.Modal.getOrCreateInstance(root).show();
    };

    doc.addEventListener('DOMContentLoaded', boot);
})();
