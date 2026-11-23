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

    var BASIS = {
        Design: 'Qty', Paper: 'Reams', CTP: 'Plates', Press: '× 1,000',
        Lamination: 'Copies', Cutting: 'Copies', Binding: 'Copies',
        Packing: 'Qty', Transport: 'Qty', Other: 'Qty'
    };

    var NAMES = {
        Design: ['Layout Design', 'Cover Design', 'Proof Reading', 'Ebook Conversion', 'ISBN Processing', 'Dummy Copy'],
        Paper: ['Inner Paper', 'Cover Board', 'Hard Board / Grey Board', 'Endpaper', 'Jacket Paper'],
        CTP: ['Inner Plates', 'Cover Plates', 'Spot UV Plate', 'Foil Die Plate', 'Emboss Die Plate'],
        Press: ['Inner Press', 'Cover Press', 'Spot Color Press', 'Digital Press'],
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
            a: ['Machine', plain(['Offset 5-Color', 'Offset 4-Color', 'Offset 2-Color', 'Offset 1-Color', 'Digital Laser', 'Screen Print', 'Flex Banner', 'Letterpress'])],
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
        DemyQuarto: [8.5, 11.0],
        DemyMini: [4.25, 5.5],
        Crown: [5.0, 7.3],
        CrownQuarto: [7.25, 9.5],
        CrownMini: [3.6, 5.0],
        Royal: [6.25, 9.25],
        RoyalQuarto: [9.25, 12.5],
        A4: [8.3, 11.7],
        A5: [5.8, 8.3],
        A6: [4.1, 5.8],
        A3: [11.7, 16.5],
        B5: [6.9, 9.8],
        B4: [9.8, 13.8],
        Card: [3.5, 2.0],
        Poster: [18.0, 23.0],
        Envelope: [10.0, 4.5],
        Full: [23.0, 36.0]
    };

    var SIZES_BN = {
        Demy: 'Demy (5.5"×8.5")',
        DemyQuarto: 'Demy 1/4 (8.5"×11")',
        DemyMini: 'Demy 1/16 (4.25"×5.5")',
        Crown: 'Crown (5"×7.3")',
        CrownQuarto: 'Crown 1/4 (7.25"×9.5")',
        CrownMini: 'Crown 1/16 (3.6"×5")',
        Royal: 'Royal (6.25"×9.25")',
        RoyalQuarto: 'Royal 1/4 (9.25"×12.5")',
        A4: 'A4 (8.3"×11.7")',
        A5: 'A5 (5.8"×8.3")',
        A6: 'A6 (4.1"×5.8")',
        A3: 'A3 (11.7"×16.5")',
        B5: 'B5 (6.9"×9.8")',
        B4: 'B4 (9.8"×13.8")',
        Card: 'Card (3.5"×2")',
        Poster: 'Poster (18"×23")',
        Envelope: 'Envelope (10"×4.5")',
        Full: 'Sheet (23"×36")'
    };

    var SHEETS = {
        '23x36': [23, 36],
        '20x30': [20, 30],
        '25x37': [25, 37],
        '27x38': [27, 38],
        '31x43': [31, 43],
        '18x23': [18, 23],
        '15x20': [15, 20],
        '18.5x25': [18.5, 25]
    };

    // Cut (machine) sheets print one forma per sheet; every other stock sheet is cut in
    // half across its long side to get the machine sheet (23x36 -> 23x18, 20x30 -> 20x15 ...).
    var CUT_SHEETS = { '18x23': 1, '15x20': 1, '18.5x25': 1 };
    var FORMA_SIZES = [4, 8, 12, 16, 24, 32];

    var SIZE_DEFAULTS = {
        Demy:        { sheet: '23x36', machine: '18×23', formaSize: 16, platePages: 8, ups: 8 },
        DemyQuarto:  { sheet: '23x36', machine: '18×23', formaSize: 8,  platePages: 4, ups: 4 },
        DemyMini:    { sheet: '23x36', machine: '18×23', formaSize: 32, platePages: 16, ups: 16 },
        Crown:       { sheet: '20x30', machine: '15×20', formaSize: 16, platePages: 8, ups: 8 },
        CrownQuarto: { sheet: '20x30', machine: '15×20', formaSize: 8,  platePages: 4, ups: 4 },
        CrownMini:   { sheet: '20x30', machine: '15×20', formaSize: 32, platePages: 16, ups: 16 },
        Royal:       { sheet: '25x37', machine: '18.5×25', formaSize: 16, platePages: 8, ups: 8 },
        RoyalQuarto: { sheet: '25x37', machine: '18.5×25', formaSize: 8,  platePages: 4, ups: 4 },
        A4:          { sheet: '23x36', machine: '18×23', formaSize: 8,  platePages: 4, ups: 4 },
        A5:          { sheet: '23x36', machine: '18×23', formaSize: 16, platePages: 8, ups: 8 },
        A6:          { sheet: '23x36', machine: '18×23', formaSize: 32, platePages: 16, ups: 16 },
        A3:          { sheet: '23x36', machine: '18×23', formaSize: 4,  platePages: 2, ups: 2 },
        B5:          { sheet: '27x38', machine: '19×27', formaSize: 16, platePages: 8, ups: 8 },
        B4:          { sheet: '27x38', machine: '19×27', formaSize: 8,  platePages: 4, ups: 4 },
        Card:        { sheet: '20x30', machine: '10×15', formaSize: 1,  platePages: 1, ups: 25 },
        Poster:      { sheet: '23x36', machine: '18×23', formaSize: 1,  platePages: 1, ups: 2 }
    };

    var P = function (g, n, o) { o = o || {}; o.g = g; o.n = n; return o; };

    var TYPES = {
        book: {
            label: 'Book', unit: 'Copy', size: 'Demy', pages: true, pagesDef: 128, colors: 1, defaultTitle: 'Book Printing', rows: [
                P('Design', 'Layout Design', { auto: 'per', a: 'Layout Design', b: 'Page' }),
                P('Design', 'Cover Design', { auto: 'per', a: 'Cover Design', b: 'Job' }),
                P('Paper', 'Inner Paper', { auto: 'paperBook', a: 80, b: 'Offwhite', waste: 5 }),
                P('Paper', 'Cover Board', { auto: 'sheet', ups: 8, waste: 5, flat: true, spread: true, a: 300, b: 'Art Card' }),
                P('CTP', 'Inner Plates', { auto: 'platesBook', a: 'CTP Thermal', b: 'Inner' }),
                P('CTP', 'Cover Plates', { auto: 'platesCover', a: 'CTP Thermal', b: 'Cover' }),
                P('Press', 'Inner Press', { auto: 'pressBook', a: 'Offset 1-Color', b: 'Inner' }),
                P('Press', 'Cover Press', { auto: 'pressCover', a: 'Offset 4-Color', b: 'Cover' }),
                P('Lamination', 'Cover Lamination', { auto: 'lam', a: 'Matt Lamination', b: 'Front (Single)' }),
                P('Cutting', 'Final 3-Knife Trim', { auto: 'copies', a: '3-Knife Trim' }),
                P('Binding', 'Perfect Glue Binding', { auto: 'copies', a: 'Perfect Binding' }),
                P('Packing', 'Poly Bag Wrapping', { auto: 'per', a: 'Poly Bag Wrap', b: 'Copy', off: true }),
                P('Transport', 'Press to Office Delivery', { auto: 'per', a: 'Press to Office', b: 'Lot', off: true })
            ]
        },
        magazine: {
            label: 'Magazine', unit: 'Copy', size: 'A4', pages: true, pagesDef: 48, colors: 4, defaultTitle: 'Magazine Printing', rows: [
                P('Design', 'Layout Design', { auto: 'per', a: 'Layout Design', b: 'Page' }),
                P('Design', 'Cover Design', { auto: 'per', a: 'Cover Design', b: 'Job' }),
                P('Paper', 'Inner Paper', { auto: 'paperBook', a: 100, b: 'Art Paper', waste: 5 }),
                P('Paper', 'Cover Board', { auto: 'sheet', ups: 4, waste: 5, flat: true, spread: true, a: 250, b: 'Art Card' }),
                P('CTP', 'Inner Plates', { auto: 'platesBook', a: 'CTP Thermal', b: 'Inner' }),
                P('CTP', 'Cover Plates', { auto: 'platesCover', a: 'CTP Thermal', b: 'Cover' }),
                P('Press', 'Inner Press', { auto: 'pressBook', a: 'Offset 4-Color', b: 'Inner' }),
                P('Press', 'Cover Press', { auto: 'pressCover', a: 'Offset 4-Color', b: 'Cover' }),
                P('Lamination', 'Cover Lamination', { auto: 'lam', a: 'Glossy Lamination', b: 'Front (Single)' }),
                P('Cutting', 'Final 3-Knife Trim', { auto: 'copies', a: '3-Knife Trim' }),
                P('Binding', 'Saddle Stitch (Pin)', { auto: 'copies', a: 'Saddle Stitch (Pin)' }),
                P('Packing', 'Master Carton', { auto: 'per', a: 'Master Carton', b: 'Hundred', off: true })
            ]
        },
        notebook: {
            label: 'Notebook', unit: 'Book', size: 'Demy', pages: true, pagesDef: 192, colors: 2, defaultTitle: 'Executive Notebook & Diary', rows: [
                P('Design', 'Layout Design', { auto: 'per', a: 'Layout Design', b: 'Job' }),
                P('Paper', 'Inner Paper', { auto: 'paperBook', a: 80, b: 'Cream Munken', waste: 5 }),
                P('Paper', 'Cover Board', { auto: 'sheet', ups: 8, waste: 5, flat: true, spread: true, a: 350, b: 'Grey Board 2.0mm' }),
                P('CTP', 'Inner Plates', { auto: 'platesBook', a: 'CTP Thermal', b: 'Inner' }),
                P('Press', 'Inner Press', { auto: 'pressBook', a: 'Offset 2-Color', b: 'Inner' }),
                P('Lamination', 'Cover Lamination', { auto: 'lam', a: 'Velvet Soft-Touch', b: 'Front (Single)' }),
                P('Binding', 'Hardcover Binding', { auto: 'copies', a: 'Hardcover / Case' }),
                P('Other', 'Ribbon Bookmark', { auto: 'copies', a: 'Ribbon Bookmark', b: 'Copy' })
            ]
        },
        brochure: {
            label: 'Brochure', unit: 'Pcs', size: 'A4', colors: 4, defaultTitle: 'Multicolor Tri-Fold Brochure', rows: [
                P('Design', 'Layout Design', { auto: 'per', a: 'Layout Design', b: 'Job' }),
                P('Paper', 'Inner Paper', { auto: 'sheet', waste: 5, flat: true, a: 170, b: 'Matt Art Paper' }),
                P('CTP', 'Inner Plates', { auto: 'platesJob', a: 'CTP Thermal', b: 'Inner' }),
                P('Press', 'Inner Press', { auto: 'pressJob', a: 'Offset 4-Color', b: 'Inner' }),
                P('Lamination', 'Cover Lamination', { auto: 'lam', a: 'Matt Lamination', b: 'Both Sides' }),
                P('Cutting', 'Creasing & Scoring', { auto: 'copies', a: 'Creasing' }),
                P('Packing', 'Master Carton', { auto: 'per', a: 'Master Carton', b: 'Hundred', off: true })
            ]
        },
        card: {
            label: 'Card', unit: 'Pcs', size: 'Card', colors: 4, defaultTitle: 'Premium Visiting Cards', rows: [
                P('Design', 'Layout Design', { auto: 'per', a: 'Layout Design', b: 'Job' }),
                P('Paper', 'Cover Board', { auto: 'sheet', waste: 5, flat: true, a: 300, b: 'Art Card' }),
                P('CTP', 'Inner Plates', { auto: 'platesJob', a: 'CTP Thermal', b: 'Inner' }),
                P('Press', 'Inner Press', { auto: 'pressJob', a: 'Offset 4-Color', b: 'Inner' }),
                P('Lamination', 'Cover Lamination', { auto: 'lam', a: 'Matt Lamination', b: 'Both Sides' }),
                P('Lamination', 'Spot UV Coating', { auto: 'copies', a: 'Spot UV', b: 'Front (Single)', off: true }),
                P('Cutting', 'Die Cutting', { auto: 'copies', a: 'Die Cut' }),
                P('Packing', 'Master Carton', { auto: 'per', a: 'Master Carton', b: 'Hundred', off: true })
            ]
        },
        leaflet: {
            label: 'Leaflet', unit: 'Pcs', size: 'A4', colors: 4, defaultTitle: 'Color Flyer / Leaflet', rows: [
                P('Design', 'Layout Design', { auto: 'per', a: 'Layout Design', b: 'Job' }),
                P('Paper', 'Inner Paper', { auto: 'sheet', waste: 5, flat: true, a: 120, b: 'Matt Art Paper' }),
                P('CTP', 'Inner Plates', { auto: 'platesJob', a: 'CTP Thermal', b: 'Inner' }),
                P('Press', 'Inner Press', { auto: 'pressJob', a: 'Offset 4-Color', b: 'Inner' }),
                P('Cutting', 'Final 3-Knife Trim', { auto: 'copies', a: '3-Knife Trim' }),
                P('Packing', 'Paper Band', { auto: 'per', a: 'Paper Band', b: 'Hundred', off: true })
            ]
        },
        poster: {
            label: 'Poster', unit: 'Pcs', size: 'Poster', colors: 4, defaultTitle: 'Offset Color Poster', rows: [
                P('Design', 'Layout Design', { auto: 'per', a: 'Layout Design', b: 'Job' }),
                P('Paper', 'Inner Paper', { auto: 'sheet', waste: 5, flat: true, a: 100, b: 'Art Paper' }),
                P('CTP', 'Inner Plates', { auto: 'platesJob', a: 'CTP Thermal', b: 'Inner' }),
                P('Press', 'Inner Press', { auto: 'pressJob', a: 'Offset 4-Color', b: 'Inner' }),
                P('Lamination', 'Cover Lamination', { auto: 'lam', a: 'Glossy Lamination', b: 'Front (Single)', off: true }),
                P('Cutting', 'Final 3-Knife Trim', { auto: 'copies', a: '3-Knife Trim' })
            ]
        },
        calendar: {
            label: 'Calendar', unit: 'Pcs', size: 'A3', pages: true, pagesDef: 12, noForma: true, colors: 4, defaultTitle: 'Wall / Desk Calendar', rows: [
                P('Design', 'Layout Design', { auto: 'per', a: 'Layout Design', b: 'Job' }),
                P('Paper', 'Inner Paper', { auto: 'sheet', waste: 5, a: 150, b: 'Matt Art Paper' }),
                P('CTP', 'Inner Plates', { auto: 'platesLeaf', a: 'CTP Thermal', b: 'Inner' }),
                P('Press', 'Inner Press', { auto: 'pressLeaf', a: 'Offset 4-Color', b: 'Inner' }),
                P('Cutting', 'Final 3-Knife Trim', { auto: 'copies', a: '3-Knife Trim' }),
                P('Binding', 'Spiral Ring', { auto: 'copies', a: 'Spiral Ring' }),
                P('Packing', 'Poly Bag Wrapping', { auto: 'per', a: 'Poly Bag Wrap', b: 'Copy', off: true })
            ]
        },
        box: {
            label: 'Box', unit: 'Pcs', size: 'A4', colors: 4, defaultTitle: 'Product Box / Carton', rows: [
                P('Design', 'Layout Design', { auto: 'per', a: 'Layout Design', b: 'Job' }),
                P('Paper', 'Cover Board', { auto: 'sheet', waste: 8, flat: true, a: 350, b: 'Duplex Board' }),
                P('CTP', 'Inner Plates', { auto: 'platesJob', a: 'CTP Thermal', b: 'Inner' }),
                P('Press', 'Inner Press', { auto: 'pressJob', a: 'Offset 4-Color', b: 'Inner' }),
                P('Lamination', 'Cover Lamination', { auto: 'lam', a: 'Matt Lamination', b: 'Front (Single)' }),
                P('Cutting', 'Die Cutting', { auto: 'copies', a: 'Die Cut' }),
                P('Binding', 'Pad Pasting', { auto: 'copies', a: 'Pad Pasting' }),
                P('Packing', 'Master Carton', { auto: 'per', a: 'Master Carton', b: 'Hundred', off: true })
            ]
        },
        memo: {
            label: 'Memo', unit: 'Book', size: 'A5', pages: true, pagesDef: 50, leaf: true, noForma: true, colors: 1, defaultTitle: 'Cash Memo / Bill Pad (3-Part)', rows: [
                P('Design', 'Layout Design', { auto: 'per', a: 'Layout Design', b: 'Job' }),
                P('Paper', 'Inner Paper', { auto: 'sheet', waste: 5, a: 55, b: 'NCR Top' }),
                P('Paper', 'Inner Paper', { auto: 'sheet', waste: 5, a: 55, b: 'NCR Middle' }),
                P('Paper', 'Cover Board', { auto: 'sheet', waste: 5, flat: true, a: 200, b: 'Duplex Board' }),
                P('CTP', 'Inner Plates', { auto: 'platesJob', a: 'CTP Thermal', b: 'Inner' }),
                P('Press', 'Inner Press', { auto: 'pressJob', a: 'Offset 1-Color', b: 'Inner' }),
                P('Cutting', 'Perforation', { auto: 'copies', a: 'Perforation' }),
                P('Cutting', 'Numbering', { auto: 'copies', a: 'Numbering' }),
                P('Binding', 'Pad Pasting', { auto: 'copies', a: 'Pad Pasting' })
            ]
        },
        envelope: {
            label: 'Envelope', unit: 'Pcs', size: 'Envelope', colors: 4, defaultTitle: 'Printed Office Envelopes', rows: [
                P('Design', 'Layout Design', { auto: 'per', a: 'Layout Design', b: 'Job' }),
                P('Paper', 'Inner Paper', { auto: 'sheet', waste: 5, flat: true, a: 100, b: 'Offset White' }),
                P('CTP', 'Inner Plates', { auto: 'platesJob', a: 'CTP Thermal', b: 'Inner' }),
                P('Press', 'Inner Press', { auto: 'pressJob', a: 'Offset 4-Color', b: 'Inner' }),
                P('Cutting', 'Die Cutting', { auto: 'copies', a: 'Die Cut' }),
                P('Binding', 'Pad Pasting', { auto: 'copies', a: 'Pad Pasting' }),
                P('Packing', 'Master Carton', { auto: 'per', a: 'Master Carton', b: 'Hundred', off: true })
            ]
        },
        stationery: {
            label: 'Stationery', unit: 'Pcs', size: '', colors: 1, defaultTitle: 'Corporate Stationery Items', rows: [
                P('Design', 'Layout Design', { auto: 'per', a: 'Layout Design', b: 'Job' }),
                P('Other', 'Miscellaneous', { auto: 'copies', a: 'Corporate Item', b: 'Copy' }),
                P('Packing', 'Master Carton', { auto: 'per', a: 'Master Carton', b: 'Hundred' }),
                P('Transport', 'Office to Client Door', { auto: 'per', a: 'Office to Client', b: 'Lot' })
            ]
        },
        custom: { label: 'Custom', unit: 'Pcs', size: 'Demy', defaultTitle: 'Custom Production Item', rows: [] }
    };

    var TIERS = [250, 500, 1000, 2000, 3000, 5000, 10000];

    // Books, magazines and notebooks are printed in formas (16 / 32 / custom pages per forma);
    // calendars and memos are not.
    function isFormaType(type) {
        var t = TYPES[type] || {};
        return !!t.pages && !t.noForma;
    }

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
        if (list.indexOf(name) === -1) { list.push(name); try { localStorage.setItem('pc-kinds', JSON.stringify({ list: list })); } catch (e) { } }
    }
    function kindsFor(r) {
        var base = variants(r).b ? variants(r).b[1].map(function (o) { return o[0]; }) : [];
        customKinds().forEach(function (k) { if (base.indexOf(k) === -1) base.push(k); });
        return base;
    }
    function customGsms() { var k = readStore('pc-gsms'); return Array.isArray(k) ? k : (k.list || []); }
    function addGsm(n) {
        var list = customGsms();
        if (list.indexOf(n) === -1) { list.push(n); try { localStorage.setItem('pc-gsms', JSON.stringify({ list: list })); } catch (e) { } }
    }
    function gsmsFor(r) {
        var base = variants(r).a ? variants(r).a[1].map(function (o) { return o[0]; }) : [];
        customGsms().concat(r.a ? [r.a] : []).forEach(function (g) { if (base.indexOf(g) === -1) base.push(g); });
        return base.sort(function (x, y) { return x - y; });
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

        GROUPS.concat(['Other']).forEach(function (g) {
            var id = 'pcNames' + g;
            var dl = $(id);
            if (!dl) { dl = doc.createElement('datalist'); dl.id = id; host.appendChild(dl); }
            var names = NAMES[g] || [];
            dl.innerHTML = names.map(function (n) { return '<option value="' + esc(n) + '">'; }).join('');
        });
    }

    // ── Local Storage Cache ────────────────────────────────────────────────
    function store(key, fn) {
        try { var all = JSON.parse(localStorage.getItem(key) || '{}'); fn(all); localStorage.setItem(key, JSON.stringify(all)); } catch (e) { }
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
        return { group: r.g, item: String(plain ? r.n : (r.a == null ? '' : r.a)), kind: (!plain && KEY_B[r.g]) ? String(r.b == null ? '' : r.b) : '', rate: round(num(r.rate) * paperUnitFactor(r), 4) };
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
    // Saved rates for paper are per ream; a sheet-priced row (cover board) shows that as per sheet.
    function rateFor(r) {
        var s = SHEET[rateKey(r)];
        var m = readStore('pc-rates')[memKey(r)];
        var perReam = s ? s.rate : (m != null ? m : defaultRate(r));
        return round(perReam / paperUnitFactor(r), 4);
    }
    function saveRate(r) { store('pc-rates', function (m) { m[memKey(r)] = round(r.rate * paperUnitFactor(r), 4); }); }

    // Cover-board rates typed before paper could be priced per sheet were saved as "per ream"
    // numbers (e.g. 18). Drop those once so they cannot be misread as a per-ream price.
    function migrateRateMemo() {
        store('pc-rates', function (m) {
            if (m.__v === 2) return;
            var cover = VARIANTS.PaperCover.b[1].map(function (o) { return norm(o[0]); });
            Object.keys(m).forEach(function (k) {
                var p = k.split('|');
                if (p[0] === 'paper' && cover.indexOf(p[2]) !== -1) delete m[k];
            });
            m.__v = 2;
        });
    }
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
                size: $('pcSize').value,
                sheet: $('pcSheet').value,
                overhead: $('pcOverhead').value,
                profit: $('pcProfit').value,
                round: $('pcRound').value
            };
        });
    }
    function fmtPages(f) { return f % 1 === 0 ? String(f) : String(Math.round(f * 100) / 100); }

    // Pages per forma: 16 or 32 normally, any whole number for a custom size.
    function setFormaSize(val) {
        var el = $('pcFormaSize');
        if (!el) return;
        var n = parseInt(val, 10);
        el.value = String(n >= 2 ? n : 16);
    }

    function loadLast(type) {
        var v = readStore('pc-last')[type];
        var d = TYPES[type];
        var curSize = v && v.size !== undefined ? v.size : d.size;
        var sDef = SIZE_DEFAULTS[curSize] || { sheet: '23x36', formaSize: 16 };
        $('pcTitleIn').value = v && v.title !== undefined ? v.title : (d.defaultTitle || '');
        $('pcQty').value = v && v.qty !== undefined ? v.qty : '1000';
        $('pcPages').value = d.pages ? (v && v.pages !== undefined ? v.pages : String(d.pagesDef || 128)) : '';
        $('pcSize').value = curSize;
        $('pcSheet').value = v && v.sheet ? v.sheet : (sDef.sheet || '23x36');
        setFormaSize(v && v.formaSize ? v.formaSize : formaSizeFor(curSize, $('pcSheet').value));
        state.geoSrc = 'pages';
        if ($('pcFormasIn')) {
            var pg = num($('pcPages').value);
            if (d.pages && pg > 0) {
                $('pcFormasIn').value = fmtPages(pg / (num($('pcFormaSize').value) || 16));
            } else {
                $('pcFormasIn').value = d.pages && v && v.formas ? v.formas : '';
                if ($('pcFormasIn').value !== '') state.geoSrc = 'formas';
            }
        }
        $('pcColors').value = v && v.colors ? v.colors : String(d.colors || 4);
        $('pcSides').value = v && v.sides ? v.sides : '1';
        $('pcOverhead').value = v && v.overhead !== undefined && v.overhead !== '' ? v.overhead : '0';
        $('pcProfit').value = v && v.profit !== undefined && v.profit !== '' ? v.profit : '20';
        $('pcRound').value = v && v.round !== undefined ? v.round : '0';
    }

    // ── Geometry helpers ───────────────────────────────────────────────────
    // Machine sheet = the sheet that really goes through the press: a cut sheet itself,
    // or half of a full stock sheet. One machine sheet carries exactly one forma.
    function machineDims(sheet) {
        var s = SHEETS[sheet];
        if (!s) return null;
        return CUT_SHEETS[sheet] ? s : [s[0], s[1] / 2];
    }

    function upsOn(size, dims) {
        var p = SIZES[size];
        if (!p || !dims) return 0;
        var a = Math.floor(dims[0] / p[0]) * Math.floor(dims[1] / p[1]);
        var b = Math.floor(dims[0] / p[1]) * Math.floor(dims[1] / p[0]);
        return Math.max(a, b);
    }

    // Trim pieces that fit one side of the whole stock sheet.
    function upsFor(size, sheet) { return upsOn(size, SHEETS[sheet]); }

    // Pages per forma for a trim size on a stock sheet: the calibrated table wins for its own
    // default sheet; any other sheet is derived from the machine sheet (both sides, multiple of 4).
    function formaSizeFor(size, sheet) {
        var def = SIZE_DEFAULTS[size];
        if (def && def.sheet === sheet && def.formaSize >= 4) return def.formaSize;
        var ups = upsOn(size, machineDims(sheet));
        if (!ups) return def && def.formaSize >= 4 ? def.formaSize : 16;
        // Largest real signature size (4/8/12/16/24/32 pages) the machine sheet can carry.
        var pages = ups * 2;
        var fit = FORMA_SIZES.filter(function (n) { return n <= pages; });
        return fit.length ? fit[fit.length - 1] : 4;
    }

    // Cover spread (front + spine + back) pieces that fit one side of the stock sheet.
    // Spine width follows the book: leaves × paper caliper (offset paper ≈ 1.25 µm per GSM,
    // i.e. 0.0000492" per GSM per leaf) plus ~0.02" for glue. Bleed is 0.125" on every edge.
    var BLEED = 0.125;
    var MIN_SPINE = 0.1;
    function innerGsm() {
        var row = state.rows.filter(function (x) { return x.on && x.g === 'Paper' && variantKey(x) === 'PaperInner' && num(x.a) > 0; })[0];
        return row ? num(row.a) : 80;
    }
    function spineFor(pages) {
        if (!(pages > 0)) return MIN_SPINE;
        return Math.max(MIN_SPINE, round((pages / 2) * innerGsm() * 0.0000492 + 0.02, 3));
    }
    function coverUpsFor(size, sheet, pages) {
        var p = SIZES[size], s = SHEETS[sheet];
        if (!p || !s) return 0;
        // Flat cover with bleed: back + spine + front across, trim height down.
        var spread = [p[0] * 2 + spineFor(pages) + BLEED * 2, p[1] + BLEED * 2];
        var a = Math.floor(s[0] / spread[0]) * Math.floor(s[1] / spread[1]);
        var b = Math.floor(s[0] / spread[1]) * Math.floor(s[1] / spread[0]);
        return Math.max(a, b);
    }

    // Ups actually used by a sheet-based paper row: manual override > cover-spread geometry >
    // trim geometry > the row's own stored value.
    function sheetUps(r, j) {
        if (r.upsManual) return Math.max(1, num(r.ups));
        if (r.spread && j.coverUps > 0) return j.coverUps;
        if (!r.spread && !r.noSize && j.ups > 0) return j.ups;
        return Math.max(1, num(r.ups));
    }

    // Ups used for press runs of flat jobs: follows the main sheet-based paper row.
    function pressUps(j) {
        var row = state.rows.filter(function (x) { return x.on && x.g === 'Paper' && x.auto === 'sheet'; })[0];
        return row ? sheetUps(row, j) : Math.max(1, j.ups);
    }

    // ── Calculations Engine ────────────────────────────────────────────────
    function job(qOverride) {
        var q = Math.max(0, qOverride != null ? qOverride : num($('pcQty').value));
        var formaSize = $('pcFormaSize') ? Math.max(2, Math.round(num($('pcFormaSize').value)) || 16) : 16;
        var formasIn = $('pcFormasIn');
        var pagesIn = $('pcPages');
        var pages = pagesIn ? Math.max(0, num(pagesIn.value)) : 0;
        var typed = formasIn && formasIn.value !== '' ? Math.max(0, num(formasIn.value)) : 0;
        var formas = 0;

        // Whichever of Pages / Formas the user touched last is the source of truth.
        if (state.geoSrc === 'formas' && typed > 0) {
            formas = typed;
            if (!pages) pages = Math.round(typed * formaSize);
        } else if (pages > 0) {
            formas = pages / formaSize;
        } else if (typed > 0) {
            formas = typed;
            pages = Math.round(typed * formaSize);
        }

        var def = TYPES[state.type] || {};
        var size = $('pcSize').value;
        var sheet = $('pcSheet').value;
        var jb = {
            q: q,
            pages: pages,
            formaSize: formaSize,
            formas: formas,
            colors: num($('pcColors').value) || 4,
            sides: num($('pcSides').value) || 1,
            size: size,
            sheet: sheet,
            ups: upsFor(size, sheet),
            coverUps: coverUpsFor(size, sheet, pages),
            spine: spineFor(pages),
            leaves: (def.leaf && pages > 0) ? pages : 1,
            parts: 1
        };
        if (def.leaf) {
            // Multi-part pads (NCR top/middle/bottom): every printed part passes the press.
            var parts = state.rows.filter(function (x) { return x.on && x.g === 'Paper' && x.auto === 'sheet' && !/cover|board/i.test(x.n); }).length;
            jb.parts = Math.max(1, parts);
        }
        return jb;
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

    // One forma always needs 2 plates per colour (one for each side of the sheet), whatever the
    // trim size or forma size. Press passes only double for two-sided flat work.
    var PLATES_PER_FORMA = 2;
    function pressSides(j) { return j.sides === 2 ? 2 : 1; }

    // Paper is priced per ream by default; cover board / card lines are priced per sheet.
    var SHEETS_PER_REAM = 500;
    function paperUnitFactor(r) { return (r.g === 'Paper' && r.unit === 'sheet') ? SHEETS_PER_REAM : 1; }
    function defaultPaperUnit(r) { return (r.g === 'Paper' && variantKey(r) === 'PaperCover') ? 'sheet' : 'ream'; }
    // Reams -> the quantity in the row's own unit (whole sheets are never fractional).
    function paperQty(r, reams) {
        return r.unit === 'sheet' ? Math.ceil(reams * SHEETS_PER_REAM - 1e-9) : round(reams, 2);
    }
    function reamsOf(r) { return num(r.qty) / paperUnitFactor(r); }

    // Pages one whole stock sheet can carry, both sides. A full sheet is cut into two machine
    // sheets, each carrying one forma. Calibrated default sheets use the calibrated forma size,
    // any other sheet is worked out from the machine sheet geometry.
    function pagesPerSheet(size, sheet, formaSize) {
        var perMachine = CUT_SHEETS[sheet] ? 1 : 2;
        var def = SIZE_DEFAULTS[size];
        if (def && def.sheet === sheet && def.formaSize >= 4) return perMachine * def.formaSize;
        var ups = upsOn(size, machineDims(sheet));
        return ups ? perMachine * ups * 2 : perMachine * formaSize;
    }

    // Press impressions are billed per 1,000 with a 1,000 minimum per pass (Bangladesh offset standard).
    // Returns the raw count in thousands (before the minimum) for a press row.
    function pressRaw(r, j) {
        switch (r.auto) {
            case 'pressBook':
            case 'pressLeaf':
                return j.q / 1000;                       // every copy runs once per forma/layout
            case 'pressCover':
                return coverSheets(j) / 1000;            // covers print several-up: sheets, not copies, go through
            case 'pressJob':
                return Math.ceil(j.q * j.leaves / pressUps(j)) / 1000; // flat work: sheets through the press
        }
        return 0;
    }

    // Cover sheets through the press: copies / covers per sheet (the cover board line's ups when it
    // is in use, else the cover-spread geometry). Press waste is covered by the 1,000 minimum.
    function coverSheets(j) {
        var row = state.rows.filter(function (x) { return x.on && x.g === 'Paper' && x.auto === 'sheet' && x.spread; })[0];
        var ups = row ? sheetUps(row, j) : Math.max(1, j.coverUps || 1);
        return j.q > 0 ? Math.ceil(j.q / ups) : 0;
    }

    // Whole formas print sheetwise: 2 plates per colour (front + back). A part forma (half or less)
    // is printed work-and-turn on one plate per colour; more than half needs both sides.
    function formaPlateSets(j) {
        var whole = Math.floor(j.formas + 1e-9);
        var part = j.formas - whole;
        if (part < 1e-6) return whole * PLATES_PER_FORMA;
        return whole * PLATES_PER_FORMA + (part <= 0.5 + 1e-9 ? 1 : PLATES_PER_FORMA);
    }

    function isPress(r) { return r.auto === 'pressBook' || r.auto === 'pressCover' || r.auto === 'pressLeaf' || r.auto === 'pressJob'; }

    function leafLayouts(j) { return j.pages > 0 ? Math.ceil(j.pages / pressUps(j)) : 0; }

    function auto(r, j) {
        var q = j.q;

        switch (r.auto) {
            case 'one': return 1;
            case 'copies': return q;
            case 'per': return perQty(r, j);
            case 'lam': return q * (r.b === 'Both Sides' ? 2 : 1);
            case 'paperBook': {
                // Inner paper: copies x formas / formas-per-sheet, plus waste.
                if (!j.formas || q <= 0) return 0;
                var perSheet = Math.max(1, Math.floor(pagesPerSheet(j.size, j.sheet, j.formaSize) / j.formaSize + 1e-9));
                var bookSheets = Math.ceil(j.formas * q / perSheet - 1e-9) * (1 + num(r.waste) / 100);
                return paperQty(r, bookSheets / SHEETS_PER_REAM);
            }
            case 'sheet': {
                // Cover board / flat stock: copies x leaves / ups, plus waste.
                var mult = (!r.flat && j.pages > 0 && TYPES[state.type].pages) ? j.pages : 1;
                var sheets = Math.ceil(q * mult / sheetUps(r, j) - 1e-9) * (1 + num(r.waste) / 100);
                return paperQty(r, sheets / SHEETS_PER_REAM);
            }
            case 'platesBook':
                return formaPlateSets(j) * j.colors;
            case 'platesLeaf':
                return leafLayouts(j) * j.colors * (j.sides === 2 ? PLATES_PER_FORMA : 1);
            case 'platesCover':
                // Book cover is standard CMYK; "Outside + inside" doubles it.
                return 4 * ((r.b === 'Both Sides' || j.sides === 2) ? 2 : 1);
            case 'platesJob':
                return j.colors * (j.sides === 2 ? PLATES_PER_FORMA : 1);
            case 'pressBook':
                if (q <= 0) return 0;
                return round(Math.ceil(j.formas) * Math.max(1, pressRaw(r, j)), 3);
            case 'pressLeaf':
                if (q <= 0) return 0;
                return round(leafLayouts(j) * Math.max(1, pressRaw(r, j)) * pressSides(j), 3);
            case 'pressCover':
                if (q <= 0) return 0;
                return round(Math.max(1, pressRaw(r, j)) * ((r.b === 'Both Sides' || j.sides === 2) ? 2 : 1), 3);
            case 'pressJob':
                if (q <= 0) return 0;
                return round(Math.max(1, pressRaw(r, j)) * pressSides(j) * j.parts, 3);
        }
        return 0;
    }

    var VALID_AUTO = {};
    ['one', 'copies', 'per', 'lam', 'paperBook', 'sheet', 'platesBook', 'platesLeaf', 'platesCover', 'platesJob',
        'pressBook', 'pressLeaf', 'pressCover', 'pressJob'].forEach(function (k) { VALID_AUTO[k] = 1; });

    // Lines whose own name would only repeat their first dropdown show no name box: the name is
    // that dropdown's value (Layout Design, 3-Knife Trim, Perfect Binding, ...).
    var DERIVED_NAME = { Design: 1, Cutting: 1, Binding: 1, Packing: 1, Transport: 1 };
    function isDerived(r) { return !!DERIVED_NAME[r.g] && !!variants(r).a; }
    function syncName(r) { if (isDerived(r) && r.a != null && r.a !== '') r.n = String(r.a); }

    function newRow(spec) {
        var d = GROUP_DEFAULT[spec.g] || {};
        var r = {
            id: ++state.seq, g: spec.g, n: spec.n, on: !spec.off, auto: spec.auto || GROUP_AUTO[spec.g], manual: false,
            qty: 0, rate: 0, ups: spec.ups || 8, upsManual: false, waste: spec.waste == null ? 5 : spec.waste,
            flat: !!spec.flat, noSize: !!spec.noSize, spread: !!spec.spread, machineLocked: false,
            a: spec.a !== undefined ? spec.a : d.a, b: spec.b !== undefined ? spec.b : d.b
        };
        r.unit = spec.unit || defaultPaperUnit(r);
        syncName(r);
        r.rate = rateFor(r);
        r.touched = false;
        return r;
    }

    // Rows are saved with the invoice line so "recalculate" restores exactly what was priced.
    function serializeRows() {
        return state.rows.map(function (r) {
            return {
                g: r.g, n: r.n, on: r.on, auto: r.auto, manual: r.manual, qty: r.manual ? r.qty : undefined,
                rate: r.rate, touched: r.touched, ups: r.ups, upsManual: r.upsManual, waste: r.waste,
                flat: r.flat, spread: r.spread, noSize: r.noSize, a: r.a, b: r.b, machineLocked: r.machineLocked, unit: r.unit
            };
        });
    }

    function restoreRows(list) {
        return list.filter(function (x) { return x && GROUPS.indexOf(x.g) !== -1; }).map(function (x) {
            var r = newRow({
                g: x.g, n: String(x.n == null ? '' : x.n).slice(0, 80), auto: VALID_AUTO[x.auto] ? x.auto : undefined,
                a: x.a, b: x.b, waste: x.waste, ups: x.ups, flat: x.flat, spread: x.spread, noSize: x.noSize, off: x.on === false,
                // Lines saved before paper had a unit were all priced per ream: keep them exactly as priced.
                unit: x.unit === 'sheet' ? 'sheet' : 'ream'
            });
            r.upsManual = !!x.upsManual;
            r.machineLocked = !!x.machineLocked;
            if (x.manual && x.qty != null) { r.manual = true; r.qty = num(x.qty); }
            r.rate = rateFor(r);
            if (x.touched) { r.touched = true; r.rate = num(x.rate); }   // typed rates stay; the rest follow today's rate sheet
            return r;
        });
    }

    // ── Colors <-> press machine ───────────────────────────────────────────
    var MACHINE_BY_COLORS = { 1: 'Offset 1-Color', 2: 'Offset 2-Color', 4: 'Offset 4-Color', 5: 'Offset 5-Color' };

    function isBodyPress(r) { return r.g === 'Press' && (r.auto === 'pressBook' || r.auto === 'pressJob' || r.auto === 'pressLeaf'); }

    // Picking the number of colors selects the matching offset machine (and its rate) on the body press line.
    function syncColorsToPress() {
        var m = MACHINE_BY_COLORS[num($('pcColors').value)];
        if (!m) return false;
        var changed = false;
        state.rows.forEach(function (r) {
            if (isBodyPress(r) && !r.machineLocked && r.a !== m) {
                r.a = m;
                r.rate = rateFor(r);
                r.touched = false;
                changed = true;
            }
        });
        return changed;
    }

    // ...and choosing an offset machine on that line sets the Colors field to match.
    function syncPressToColors(r) {
        var m = /^Offset (\d)-Color$/.exec(String(r.a || ''));
        r.machineLocked = !m;
        if (m && $('pcColors').querySelector('option[value="' + m[1] + '"]')) $('pcColors').value = m[1];
    }

    function resetSpecBox() {
        var box = $('pcSpecTextarea');
        if (box) box._manuallyEdited = false;
    }

    function applyType(type, keepJob) {
        if (!TYPES[type]) type = 'book';
        state.type = type;
        var t = TYPES[type];
        state.rows = t.rows.map(newRow);
        $('pcUnit').value = t.unit;
        $('pcPagesWrap').hidden = !t.pages;
        if ($('pcFormasField')) $('pcFormasField').hidden = !t.pages || !!t.noForma;
        if ($('pcPagesLbl')) $('pcPagesLbl').textContent = t.leaf ? 'Leaves' : 'Pages';
        // For a book "Sides" really asks how the cover is printed; flat work prints 1 or 2 sides.
        if ($('pcSidesLbl') && $('pcSides')) {
            var cover = isFormaType(type);
            $('pcSidesLbl').textContent = cover ? 'Cover print' : 'Sides';
            $('pcSides').options[0].textContent = cover ? 'Outside only (4+0)' : '1 Side';
            $('pcSides').options[1].textContent = cover ? 'Outside + inside (4+4)' : '2 Sides';
        }
        var jobCard = $('pcTitleIn') ? $('pcTitleIn').closest('.pc-job') : null;
        if (jobCard) jobCard.classList.toggle('pc-job-no-pages', !t.pages);
        if (!keepJob) {
            loadLast(type);
        } else if (!t.pages) {
            if ($('pcPages')) $('pcPages').value = '';
            if ($('pcFormasIn')) $('pcFormasIn').value = '';
        }
        resetSpecBox();
        syncColorsToPress();

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
        state.rows.forEach(function (r) {
            if (r.auto === 'sheet') r.ups = sheetUps(r, j);
            if (!r.manual) r.qty = auto(r, j);
        });
    }

    // Price for q copies. Tiers re-run every automatic line at q; a line the user typed by hand
    // keeps its ratio to the automatic figure, so it scales with the tier instead of staying frozen.
    function priceFor(q) {
        var j = job(q);
        var cur = job();
        var direct = 0;
        state.rows.forEach(function (r) {
            if (!r.on) return;
            var qty;
            if (!r.manual) {
                qty = auto(r, j);
            } else if (q === cur.q) {
                qty = num(r.qty);
            } else {
                var base = auto(r, cur);
                qty = base > 0 ? num(r.qty) * auto(r, j) / base : num(r.qty);
            }
            direct += round(qty * num(r.rate), 2);
        });
        var overheadPct = Math.min(100, Math.max(0, num($('pcOverhead').value)));
        var overhead = round(direct * overheadPct / 100, 2);
        var cost = round(direct + overhead, 2);
        var profitPct = Math.min(500, Math.max(0, num($('pcProfit').value)));
        var profit = round(cost * profitPct / 100, 2);
        var total = round(cost + profit, 2);
        var unit = q > 0 ? total / q : 0;
        var step = num($('pcRound').value);
        var rounded = false;
        if (step > 0 && unit > 0) {
            var up = round(Math.ceil(unit / step - 1e-9) * step, 4);
            rounded = Math.abs(up - unit) > 1e-9;
            unit = up;
        } else {
            unit = round(unit, 2);
        }
        // The invoice line is qty × rate, so the total is exactly that; profit takes the rounding
        // difference so Cost + Profit always equals the Total shown.
        if (q > 0) {
            total = round(round(unit, 2) * q, 2);
            profit = round(total - cost, 2);
        }
        return { direct: direct, overhead: overhead, cost: cost, profit: profit, total: total, unit: round(unit, 2), rounded: rounded };
    }

    // ── Table Rendering ─────────────────────────────────────────────────────
    function render() {
        recalcAuto();
        var html = state.rows.map(rowHtml).join('');
        el.groups.innerHTML = html || '<tr><td colspan="7" class="text-center text-muted py-4">Empty</td></tr>';
        if ($('pcRowCountBadge')) $('pcRowCountBadge').textContent = state.rows.length;
        paint();
    }

    function select(cls, label, spec, current) {
        if (!spec) return '';
        var opts = spec[1];
        var has = opts.some(function (o) { return String(o[0]) === String(current); });
        return '<select class="form-select form-select-sm pc-var ' + cls + '" aria-label="' + esc(label) + '">'
            + (has ? '' : '<option value="" selected>' + esc(label) + '</option>')
            + opts.map(function (o) { return '<option value="' + esc(o[0]) + '"' + (String(o[0]) === String(current) ? ' selected' : '') + '>' + esc(o[1]) + '</option>'; }).join('') + '</select>';
    }

    // The second line of an item: only controls that add information. Lines whose name would just
    // repeat a dropdown have no name box at all (see isDerived), and paper has one stock box
    // instead of the same GSM / type three times.
    function specHtml(r) {
        var v = variants(r);
        var wrap = function (inner) { return '<div class="d-flex align-items-center gap-1.5 flex-wrap w-100 pc-spec-row">' + inner + '</div>'; };
        var sel = function (cls, spec, cur, flex) { return spec ? '<div style="flex: ' + flex + ';">' + select(cls, spec[0], spec, cur) + '</div>' : ''; };

        if (r.g === 'Paper') {
            var list = variantKey(r) === 'PaperCover' ? 'Cover' : 'Inner';
            return wrap(
                '<input class="form-control form-control-sm pc-stock font-monospace" list="pcPaper' + list + '" value="' + esc(stockText(r)) + '" title="GSM and paper kind, e.g. 80 GSM Offwhite" aria-label="Paper" placeholder="e.g. 80 GSM Offwhite" style="flex: 2 1 170px; min-width: 150px;">'
                + (r.auto === 'sheet' ? '<div class="input-group input-group-sm pc-spec-mini" style="width: 82px;" title="Pieces that fit on one sheet"><span class="input-group-text px-1 text-muted" style="font-size: 10px;">Ups</span><input type="number" class="form-control form-control-sm pc-ups text-end font-monospace px-1" min="1" step="1" value="' + esc(r.ups) + '"></div>' : '')
                + '<div class="input-group input-group-sm pc-spec-mini" style="width: 88px;" title="Waste percentage"><span class="input-group-text px-1 text-muted" style="font-size: 10px;">W%</span><input type="number" class="form-control form-control-sm pc-waste text-end font-monospace px-1" min="0" step="1" value="' + esc(r.waste) + '"></div>'
                + '<select class="form-select form-select-sm pc-punit" style="width: 96px;" aria-label="Priced per" title="Quantity and rate are per ream or per sheet">'
                + '<option value="ream"' + (r.unit === 'sheet' ? '' : ' selected') + '>Ream</option><option value="sheet"' + (r.unit === 'sheet' ? ' selected' : '') + '>Sheet</option></select>'
            );
        }
        if (r.g === 'Design' || r.g === 'Packing' || r.g === 'Transport') {
            return wrap(sel('pc-a', v.a, r.a, '2 1 180px') + sel('pc-b', v.b, r.b, '1 1 110px'));
        }
        if (r.g === 'Cutting' || r.g === 'Binding') {
            return wrap(sel('pc-a', v.a, r.a, '1 1 200px'));
        }
        if (r.g === 'CTP' || r.g === 'Press') {
            // Inner / Cover is already in the line name, so only the plate kind / machine is asked.
            return wrap(sel('pc-a', v.a, r.a, '1 1 200px'));
        }
        if (r.g === 'Lamination') {
            return wrap(sel('pc-a', v.a, r.a, '2 1 180px') + sel('pc-b', v.b, r.b, '1 1 140px'));
        }
        return '';
    }

    // What one rate buys, shown under the rate so nobody types a per-sheet price into a per-ream line.
    function rateUnitText(r) {
        switch (r.g) {
            case 'Paper': return r.unit === 'sheet' ? 'per sheet' : 'per ream';
            case 'CTP': return 'per plate';
            case 'Press':
                if (r.auto === 'pressBook') return 'forma / 1,000';
                if (r.auto === 'pressCover') return 'side / 1,000';
                if (r.auto === 'pressLeaf') return 'layout / 1,000';
                return 'per 1,000';
            case 'Design': return r.b === 'Page' ? 'per page' : 'per job';
            case 'Packing':
            case 'Transport': return r.b ? 'per ' + String(r.b).toLowerCase() : '';
            case 'Lamination':
            case 'Cutting':
            case 'Binding': return 'per copy';
        }
        return '';
    }

    function rowHtml(r) {
        var v = variants(r);
        r.vk = variantKey(r);

        var groupOpts = GROUPS.map(function (g) {
            var gTitle = GROUP_TITLES_BN[g] || g;
            return '<option value="' + g + '"' + (g === r.g ? ' selected' : '') + '>' + gTitle + '</option>';
        }).join('');

        var spec = specHtml(r);

        return '<tr class="pc-row' + (r.on ? '' : ' is-off table-light opacity-50') + '" data-id="' + r.id + '" data-group="' + r.g + '">'
            + '<td class="text-center align-top pt-2.5 col-chk">'
            + '<input type="checkbox" class="form-check-input pc-chk m-0" ' + (r.on ? 'checked' : '') + ' aria-label="Include">'
            + '</td>'
            + '<td class="align-top pt-2 col-type">'
            + '<select class="form-select form-select-sm pc-group-select pc-g" data-id="' + r.id + '" aria-label="Type">' + groupOpts + '</select>'
            + '</td>'
            + '<td class="align-top py-2 col-item-spec">'
            + (isDerived(r) ? '' : '<div class="pc-item-row' + (spec ? ' mb-1.5' : '') + '">'
                + '<input class="form-control form-control-sm pc-name fw-semibold text-dark" list="pcNames' + r.g + '" value="' + esc(r.n) + '" maxlength="80" aria-label="Item">'
                + '</div>')
            + (spec ? '<div class="pc-spec-wrap">' + spec + '</div>' : '')
            + '</td>'
            + '<td class="text-end align-top pt-2 col-qty">'
            + '<input type="number" class="form-control form-control-sm pc-qty text-end font-monospace fw-bold" min="0" step="any" value="' + esc(r.qty) + '" aria-label="Quantity">'
            + '<div class="d-flex justify-content-end align-items-center gap-1 mt-1 pe-1" style="min-height: 18px;">'
            + '<button type="button" class="btn btn-link p-0 pc-auto text-decoration-none" style="font-size: 11px; line-height: 18px;" title="Back to automatic quantity" hidden>Auto</button>'
            + '<span class="pc-qty-note text-muted text-end"></span>'
            + '</div>'
            + '</td>'
            + '<td class="text-end align-top pt-2 col-rate">'
            + '<input type="number" class="form-control form-control-sm pc-rate text-end font-monospace fw-bold' + (!num(r.rate) && r.on && num(r.qty) > 0 ? ' is-invalid border-danger' : '') + '" min="0" step="any" value="' + (r.rate || '') + '" aria-label="Rate">'
            + '<div class="d-flex justify-content-end align-items-center gap-1 mt-1" style="min-height: 18px;"><span class="pc-rate-unit text-muted" style="font-size: 11px; line-height: 18px;">' + rateUnitText(r) + '</span><button type="button" class="btn btn-xs btn-outline-secondary pc-pin px-1.5 py-0 rounded" style="font-size: 10px; line-height: 16px;" title="Save this rate to the rate sheet" hidden>Save</button></div>'
            + '</td>'
            + '<td class="text-end align-top pt-2 col-total pe-2">'
            + '<div class="font-monospace fw-bold fs-6 pc-total text-dark pt-1">' + money(lineTotal(r)) + '</div>'
            + '</td>'
            + '<td class="text-center align-top pt-2 col-del">'
            + '<button type="button" class="btn btn-link btn-sm text-danger p-0 pc-del opacity-75 hover-opacity-100" title="Delete" aria-label="Remove" style="text-decoration:none; font-size:20px; line-height:1; margin-top: 3px;">&times;</button>'
            + '</td>'
            + '</tr>';
    }

    function fmtInt(n) { return Math.round(num(n)).toLocaleString('en-US'); }
    // Thousands of impressions, up to 3 decimals, no trailing zeros (1.5 / 12 / 1.234).
    function fmtK(n) { return String(Math.round(num(n) * 1000) / 1000); }

    // Production totals shown in the KPI strip, the summary text and the invoice spec.
    // Text pages and cover board are different stocks, so they are counted apart (never added together).
    function totals() {
        var t = { reams: 0, sheets: 0, coverReams: 0, coverSheets: 0, plates: 0, innerPlates: 0, coverPlates: 0, imp: 0, innerImp: 0, coverImp: 0 };
        state.rows.forEach(function (r) {
            if (!r.on) return;
            var q = num(r.qty);
            if (r.g === 'Paper') {
                var rm = reamsOf(r);
                if (variantKey(r) === 'PaperCover') { t.coverReams += rm; t.coverSheets += Math.round(rm * SHEETS_PER_REAM); }
                else { t.reams += rm; t.sheets += Math.round(rm * SHEETS_PER_REAM); }
            }
            else if (r.g === 'CTP') { t.plates += q; if (/cover/i.test(r.n) || r.auto === 'platesCover') t.coverPlates += q; else t.innerPlates += q; }
            else if (r.g === 'Press') { t.imp += q; if (r.auto === 'pressCover' || /cover/i.test(r.n)) t.coverImp += q; else t.innerImp += q; }
        });
        return t;
    }

    function reamText(reams) {
        var rm = Math.floor(reams + 1e-9);
        var dis = Math.round((reams - rm) * 20);
        if (dis === 20) { rm++; dis = 0; }
        var parts = [];
        if (rm > 0) parts.push(rm + ' Rm');
        if (dis > 0) parts.push(dis + ' Dista');
        return parts.join(' ') || '0 Rm';
    }

    // True when the cover is part of the job (a cover plate or cover press line is switched on).
    function hasCover() {
        return state.rows.some(function (r) { return r.on && (r.auto === 'platesCover' || r.auto === 'pressCover'); });
    }

    // Short "how this number was reached" note under each automatic quantity.
    function qtyNote(r, j) {
        var q = num(r.qty);
        if (r.g === 'Paper') {
            // Always show the other unit too: sheets for a ream line, reams/dista for a sheet line.
            var reams = reamsOf(r);
            var rm = Math.floor(reams + 1e-9);
            var dis = Math.round((reams - rm) * 20);
            var parts = [];
            if (rm > 0) parts.push(rm + ' Ream' + (rm > 1 ? 's' : ''));
            if (dis > 0) parts.push(dis + ' Dista');
            var coverInfo = (r.spread && r.auto === 'sheet') ? sheetUps(r, j) + ' covers/sheet · spine ' + j.spine + '"' : '';
            if (r.unit === 'sheet') return [coverInfo, parts.length ? '= ' + parts.join(', ') : ''].filter(Boolean).join('\n');
            return fmtInt(q * SHEETS_PER_REAM) + ' Sheets' + (parts.length ? '\n' + parts.join(', ') : '');
        }
        var pm = PLATES_PER_FORMA;
        switch (r.auto) {
            case 'platesBook': {
                var wholeF = Math.floor(j.formas + 1e-9), partF = j.formas - wholeF;
                return wholeF + ' formas × ' + j.colors + ' col × ' + pm + (partF > 1e-6 ? ' + part forma ' + (partF <= 0.5 + 1e-9 ? j.colors + ' (work & turn)' : j.colors * pm) : '');
            }
            case 'platesLeaf': return leafLayouts(j) + ' layouts × ' + j.colors + ' col' + (j.sides === 2 ? ' × ' + pm : '');
            case 'platesCover': return '4 col (CMYK)' + ((r.b === 'Both Sides' || j.sides === 2) ? ' × 2 sides' : '');
            case 'platesJob': return j.colors + ' col' + (j.sides === 2 ? ' × ' + pm : '');
            case 'pressBook': return j.q > 0 ? Math.ceil(j.formas) + ' formas × ' + fmtK(Math.max(1, pressRaw(r, j))) + 'k' + (pressRaw(r, j) < 1 ? ' (min)' : '') : '';
            case 'pressLeaf': return j.q > 0 ? leafLayouts(j) + ' layouts × ' + fmtK(Math.max(1, pressRaw(r, j))) + 'k' + (pressRaw(r, j) < 1 ? ' (min)' : '') : '';
            case 'pressCover': {
                if (!(j.q > 0)) return '';
                var cs = (r.b === 'Both Sides' || j.sides === 2) ? 2 : 1;
                return fmtInt(coverSheets(j)) + ' sheets × ' + cs + ' side' + (cs > 1 ? 's' : '') + (pressRaw(r, j) < 1 ? ' (min 1k)' : '');
            }
            case 'pressJob':
                if (!(j.q > 0)) return '';
                return fmtInt(Math.ceil(j.q * j.leaves / pressUps(j))) + ' sheets' + (pressRaw(r, j) < 1 ? ' (min 1k)' : '');
        }
        return '';
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

            var ru = rowEl.querySelector('.pc-rate-unit');
            if (ru) ru.textContent = rateUnitText(r);

            var autoBtn = rowEl.querySelector('.pc-auto');
            if (autoBtn) autoBtn.hidden = !(r.manual && r.auto);

            var qNote = rowEl.querySelector('.pc-qty-note');
            if (qNote) qNote.textContent = r.manual ? 'manual' : qtyNote(r, j);

            var lacks = r.on && num(r.qty) > 0 && !num(r.rate);
            if (rate) {
                rate.classList.toggle('is-invalid', lacks);
                rate.classList.toggle('border-danger', lacks);   // drawn red when empty; clears once a rate is typed
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

        var tot = totals();
        var isPaged = !!TYPES[state.type].pages;
        var formasStr = j.formas ? fmtPages(Math.round(j.formas * 100) / 100) : '0';
        // Pages and Formas always mirror each other; fill the quiet one in (never while it is being typed).
        if (isPaged && j.formas > 0) {
            if ($('pcFormasIn') && doc.activeElement !== $('pcFormasIn') && state.geoSrc !== 'formas') $('pcFormasIn').value = formasStr;
            if ($('pcPages') && doc.activeElement !== $('pcPages') && state.geoSrc === 'formas') $('pcPages').value = j.pages || '';
        }


        // Live production strip
        if ($('pcKpiStrip')) {
            // Main stock in reams; cover board (a different paper) shown beside it in sheets.
            // Only board / card stock (cards, boxes): it is bought by the sheet, so sheets lead.
            $('pcKpiStrip').hidden = !(tot.reams > 0 || tot.coverReams > 0 || tot.plates > 0 || tot.imp > 0);
            if (tot.reams > 0) {
                if ($('pcKpiPaperReams')) $('pcKpiPaperReams').textContent = tot.reams.toFixed(2) + ' Reams';
                if ($('pcKpiPaperSheets')) $('pcKpiPaperSheets').textContent = tot.sheets.toLocaleString('en-US') + ' Sheets = ' + reamText(tot.reams)
                    + (tot.coverSheets > 0 ? ' · Cover ' + tot.coverSheets.toLocaleString('en-US') + ' Sheets' : '');
            } else {
                if ($('pcKpiPaperReams')) $('pcKpiPaperReams').textContent = tot.coverSheets.toLocaleString('en-US') + ' Sheets';
                if ($('pcKpiPaperSheets')) $('pcKpiPaperSheets').textContent = reamText(tot.coverReams);
            }
            var paperRow = state.rows.filter(function (r) { return r.g === 'Paper' && r.on; })[0];
            if ($('pcKpiPaperWaste')) $('pcKpiPaperWaste').textContent = (paperRow ? num(paperRow.waste) : 0) + '% Waste';

            var perForma = PLATES_PER_FORMA;
            if (isPaged && state.type !== 'calendar' && state.type !== 'memo') {
                if ($('pcKpiFormasLbl')) $('pcKpiFormasLbl').textContent = 'Formas';
                if ($('pcKpiFormas')) $('pcKpiFormas').textContent = formasStr + ' Formas';
                if ($('pcKpiFormaSub')) $('pcKpiFormaSub').textContent = !j.pages ? 'Enter pages'
                    : (j.pages % 4 !== 0 && !TYPES[state.type].leaf ? j.pages + ' Pages · not a multiple of 4' : j.pages + ' Pages · ' + j.formaSize + 'p Forma · ' + (j.formaSize / 2) + 'p/Side');
            } else {
                if ($('pcKpiFormasLbl')) $('pcKpiFormasLbl').textContent = 'Layout';
                if ($('pcKpiFormas')) $('pcKpiFormas').textContent = j.ups + ' Ups';
                if ($('pcKpiFormaSub')) $('pcKpiFormaSub').textContent = (j.ups > 0 ? Math.ceil(j.q * j.leaves / pressUps(j)).toLocaleString('en-US') + ' Sheets' : 'Pick a size');
            }
            if ($('pcKpiUpsBadge')) $('pcKpiUpsBadge').textContent = SIZES_BN[j.size] ? SIZES_BN[j.size].split(' ')[0] : (j.size || '—');

            var formaJob = isPaged && state.type !== 'calendar' && state.type !== 'memo';
            if ($('pcKpiPlates')) $('pcKpiPlates').textContent = fmtInt(tot.plates) + ' Plates';
            if ($('pcKpiPlateSub')) $('pcKpiPlateSub').textContent = tot.coverPlates > 0 ? 'Inner ' + fmtInt(tot.innerPlates) + ' + Cover ' + fmtInt(tot.coverPlates) : fmtInt(tot.innerPlates) + ' for the job';
            if ($('pcKpiPlateMethodBadge')) $('pcKpiPlateMethodBadge').textContent = j.colors + (j.colors === 1 ? ' Color' : ' Colors')
                + (formaJob ? ' · ' + (j.colors * perForma) + ' per Forma' : '');

            // Press lines are counted in units of 1,000 (a forma, a cover side or a flat run each).
            if ($('pcKpiImpressions')) $('pcKpiImpressions').textContent = fmtK(tot.imp) + ' × 1,000';
            var minHit = state.rows.some(function (r) { return r.on && isPress(r) && j.q > 0 && pressRaw(r, j) < 1; });
            if ($('pcKpiImpressionBadge')) $('pcKpiImpressionBadge').textContent = minHit ? 'Min 1,000 applied' : 'Per 1,000';
            if ($('pcKpiImpressionSub')) $('pcKpiImpressionSub').textContent = formaJob
                ? 'Inner ' + fmtK(tot.innerImp) + (tot.coverImp > 0 ? ' + Cover ' + fmtK(tot.coverImp) : '')
                : fmtInt(Math.round(tot.imp * 1000)) + ' impressions';
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
    function basisOf(r) {
        if (r.g === 'Paper') return r.unit === 'sheet' ? 'Sheets' : 'Reams';
        if (r.g === 'Design') return r.b === 'Page' ? 'Pages' : 'Job';
        if ((r.g === 'Packing' || r.g === 'Transport') && r.b) return r.b === 'Copy' ? 'Copies' : (r.b === 'Lot' ? 'Lot' : r.b + 's');
        return BASIS[r.g];
    }

    // "Name (detail, detail)", never repeating something the name already says.
    function label(r) {
        var v = variants(r);
        var bits = [];
        var add = function (s) {
            s = String(s);
            if (s && r.n.toLowerCase().indexOf(s.toLowerCase()) === -1 && bits.indexOf(s) === -1) bits.push(s);
        };
        if (v.a && r.a !== undefined && r.a !== '') add(typeof r.a === 'number' ? r.a + ' GSM' : r.a);
        if (v.b && r.b !== undefined && r.b !== '' && ['Per'].indexOf(v.b[0]) === -1 && ['Side', 'Sides'].indexOf(v.b[0]) === -1) add(r.b);
        if (r.g === 'Lamination' && r.b === 'Both Sides') add('Both Sides');
        return r.n + (bits.length ? ' (' + bits.join(', ') + ')' : '');
    }

    // First line(s) of every spec: size, pages/formas, colors, plates and press totals.
    function specHead(j) {
        var parts = [];
        var sizeLabel = SIZES_BN[j.size] || j.size;
        if (sizeLabel) parts.push('Size: ' + sizeLabel);
        var def = TYPES[state.type] || {};
        if (j.pages) {
            if (def.leaf) parts.push('Leaves: ' + j.pages);
            else if (state.type === 'calendar') parts.push('Pages: ' + j.pages);
            else parts.push('Pages: ' + j.pages + ' (' + fmtPages(Math.round(j.formas * 100) / 100) + ' Formas | ' + j.formaSize + 'p Forma, ' + (j.formaSize / 2) + 'p/Side)');
        }
        var t = totals();
        if (isFormaType(state.type)) {
            parts.push('Inner: ' + j.colors + (j.colors === 1 ? ' Color' : ' Colors')
                + (hasCover() ? ' | Cover: 4 Colors' + (j.sides === 2 ? ' (outside + inside)' : '') : ''));
            if (t.plates > 0) parts.push('Plates: ' + fmtInt(t.plates) + (t.coverPlates > 0 ? ' (Inner ' + fmtInt(t.innerPlates) + ' + Cover ' + fmtInt(t.coverPlates) + ')' : ''));
        } else {
            parts.push('Print: ' + j.colors + (j.colors === 1 ? ' Color' : ' Colors') + (j.sides === 2 ? ' (Both Sides)' : ' (1 Side)'));
            if (t.plates > 0) parts.push('Plates: ' + fmtInt(t.plates));
            if (t.imp > 0) parts.push('Impressions: ' + fmtInt(Math.round(t.imp * 1000)));
        }
        return parts;
    }

    function specText() {
        var j = job();
        var parts = specHead(j);

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
        var parts = specHead(j);

        var comps = [];
        GROUPS.forEach(function (g) {
            var items = state.rows.filter(function (r) { return r.g === g && r.on && lineTotal(r) > 0; })
                .map(function (r) {
                    var bUnit = basisOf(r);
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
                    var bUnit = basisOf(r);
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
        // Insert = spec with each line's qty @ rate = amount; Plain = spec only. Text typed by hand
        // in the preview box always wins. The invoice field holds 1,000 characters at most.
        var SPEC_MAX = 1000;
        var spec;
        if (specBox && specBox._manuallyEdited && specBox.value.trim()) {
            spec = specBox.value.trim();
        } else if (withRates === true) {
            spec = ratedText();
            if (spec.length > SPEC_MAX) { spec = specText(); toast('Too long to show rates, inserted without rates'); }
        } else {
            spec = specText();
        }
        if (spec.length > SPEC_MAX) spec = spec.slice(0, SPEC_MAX - 1).replace(/\s+\S*$/, '') + '…';

        var itemType = 'Printing & Binding';
        if (state.type === 'stationery') itemType = 'Stationery';
        else if (state.type === 'book') itemType = 'Book (Paperback)';

        // Detailed Snapshot for full recalculation support
        var calcSnapshot = {
            type: state.type,
            title: $('pcTitleIn').value.trim(),
            qty: $('pcQty').value,
            pages: $('pcPages') ? $('pcPages').value : '',
            formas: $('pcFormasIn') ? $('pcFormasIn').value : '',
            formaSize: $('pcFormaSize') ? $('pcFormaSize').value : '16',
            colors: $('pcColors') ? $('pcColors').value : '4',
            sides: $('pcSides') ? $('pcSides').value : '1',
            size: $('pcSize') ? $('pcSize').value : '',
            sheet: $('pcSheet') ? $('pcSheet').value : '',
            overhead: $('pcOverhead') ? $('pcOverhead').value : '',
            profit: $('pcProfit') ? $('pcProfit').value : '',
            round: $('pcRound') ? $('pcRound').value : '',
            unit: $('pcUnit') ? $('pcUnit').value : '',
            rows: serializeRows(),
            unitCost: s.unit,
            totalCost: s.total
        };

        if (typeof window.applyCalcToInvoiceItem === 'function') {
            window.applyCalcToInvoiceItem(title, spec, itemType, $('pcUnit').value || 'Pcs', s.unit, s.unit, s.j.q, calcSnapshot);
        } else if (typeof window.addPresetItem === 'function') {
            window.addPresetItem(title, spec, itemType, $('pcUnit').value || 'Pcs', s.unit, s.unit, s.j.q, calcSnapshot);
            toast('Added to quotation');
        }

        resetSpecBox();
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

        // 1) Each part carries its share of overhead + profit. A share that cannot be written as
        //    copies × a 2-decimal price (within half a taka) is billed as one lot instead.
        var lines = activeRows.map(function (r) {
            var gTitle = GROUP_TITLES_BN[r.g] || r.g;
            var itemType = 'Printing & Binding';
            if (r.g === 'Paper') itemType = 'Paper / Raw Materials';
            else if (r.g === 'Design') itemType = 'Service';
            else if (r.g === 'Transport' || r.g === 'Other') itemType = 'Product';
            var share = round(lineTotal(r) * markup, 2);
            var unitPrice = round(share / s.j.q, 2);
            var line = { title: jobTitle + ' — ' + gTitle + ': ' + r.n, spec: label(r) + ' (' + r.qty + ' ' + basisOf(r) + ' @ ৳' + r.rate + ')',
                type: itemType, unit: $('pcUnit').value || 'Pcs', qty: s.j.q, price: unitPrice };
            if (Math.abs(unitPrice * s.j.q - share) > 0.5) { line.qty = 1; line.unit = 'Lot'; line.price = share; }
            return line;
        });
        // 2) The paisa left over goes to the biggest lot line, so the lines add up to exactly the total.
        var placed = lines.reduce(function (sum, l) { return round(sum + round(l.price * l.qty, 2), 2); }, 0);
        var diff = round(s.total - placed, 2);
        if (Math.abs(diff) >= 0.01) {
            var lots = lines.filter(function (l) { return l.qty === 1; });
            var host = (lots.length ? lots : lines).reduce(function (a, b) { return a.price * a.qty >= b.price * b.qty ? a : b; });
            if (host.qty !== 1) { host.price = round(host.price * host.qty, 2); host.qty = 1; host.unit = 'Lot'; }
            host.price = round(host.price + diff, 2);
        }
        lines.forEach(function (l) {
            window.addPresetItem(l.title, l.spec, l.type, l.unit, l.price, l.price, l.qty);
            count++;
        });

        toast(count + ' items added to quotation');

        resetSpecBox();
        var modal = window.bootstrap && window.bootstrap.Modal.getInstance($('printCostCalculatorModal'));
        if (modal) modal.hide();
    }

    // Reset = this job type back to its factory defaults (quantity, pages, colors, size, margins).
    function resetAll() {
        store('pc-last', function (m) { delete m[state.type]; });
        var specBox = $('pcSpecTextarea');
        if (specBox) specBox.value = '';
        applyType(state.type);
        toast('Reset to defaults');
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
            .catch(function () { });
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
        var same = s && Math.abs(s.rate - num(r.rate) * paperUnitFactor(r)) < 0.005;
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
            toast('Saved to rates');
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
                + '<td class="text-center"><button type="button" class="btn btn-link btn-sm text-danger p-0 pr-del" title="Delete" aria-label="Remove">&times;</button></td>'
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
                    toast(res.changed + ' rates adjusted');
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

    // ── Pages ⇄ Formas ⇄ Forma size ⇄ Trim ⇄ Sheet synchronizer ────────────
    function syncBookGeometry(trigger) {
        var sizeEl = $('pcSize');
        if (!sizeEl) return;
        var sizeKey = sizeEl.value;
        var sheetEl = $('pcSheet');
        var pagesEl = $('pcPages');
        var formasEl = $('pcFormasIn');
        var def = SIZE_DEFAULTS[sizeKey];

        if (trigger === 'size') {
            // A new trim size brings its calibrated sheet and forma size.
            if (sheetEl && def && def.sheet) sheetEl.value = def.sheet;
            setFormaSize(formaSizeFor(sizeKey, sheetEl ? sheetEl.value : ''));
        } else if (trigger === 'sheet') {
            setFormaSize(formaSizeFor(sizeKey, sheetEl ? sheetEl.value : ''));
        }

        var formaSize = num($('pcFormaSize') && $('pcFormaSize').value) || 16;

        if (trigger === 'formas') {
            state.geoSrc = 'formas';
            if (formasEl && pagesEl) {
                var fVal = num(formasEl.value);
                pagesEl.value = fVal > 0 ? Math.round(fVal * formaSize) : '';
            }
        } else if (formasEl && pagesEl) {
            // pages / size / sheet / formaSize: the page count is the fact, formas follow it.
            state.geoSrc = 'pages';
            var pVal = num(pagesEl.value);
            if (pVal > 0) formasEl.value = fmtPages(Math.round(pVal / formaSize * 100) / 100);
            else if (trigger === 'pages') formasEl.value = '';
        }
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
            else if (t.classList.contains('pc-waste')) { r.waste = Math.max(0, num(t.value)); r.manual = false; recalcAuto(); }
            paint();
            return;
        }

        if (t.id === 'pcSpecTextarea') {
            t._manuallyEdited = true;
            return;
        }

        if (t.id === 'pcFormasIn') {
            syncBookGeometry('formas');
        } else if (t.id === 'pcPages') {
            syncBookGeometry('pages');
        } else if (t.id === 'pcFormaSize') {
            syncBookGeometry('formaSize');

        }

        recalcAuto();
        paint();
        saveLast();
    }

    function onChange(e) {
        var t = e.target;
        var rowEl = t.closest && t.closest('.pc-row');
        if (!rowEl) {
            if (t.id === 'pcSize') {
                syncBookGeometry('size');
            } else if (t.id === 'pcSheet') {
                syncBookGeometry('sheet');
            } else if (t.id === 'pcFormaSize') {
                syncBookGeometry('formaSize');
            } else if (t.id === 'pcColors') {
                // Colors drive the press machine line: rebuild rows so the machine <select> follows.
                if (syncColorsToPress()) { render(); saveLast(); return; }
            }
            if (t.closest('.pc-job, .pc-summary')) { recalcAuto(); paint(); saveLast(); }
            return;
        }
        var r = byId(rowEl.dataset.id);
        if (!r) return;
        if (t.classList.contains('pc-chk')) { r.on = t.checked; paint(); saveLast(); return; }
        if (t.classList.contains('pc-g')) {
            r.g = t.value;
            var names = NAMES[r.g] || ['Custom ' + r.g];
            r.n = names[0];
            r.a = (GROUP_DEFAULT[r.g] || {}).a;
            r.b = (GROUP_DEFAULT[r.g] || {}).b;
            r.auto = GROUP_AUTO[r.g];
            syncName(r);
            r.unit = defaultPaperUnit(r);
            r.spread = false;
            r.machineLocked = false;
            if (r.g === 'Paper' && state.type === 'book' && /inner/i.test(r.n)) {
                r.auto = 'paperBook';
            }
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
                r.unit = defaultPaperUnit(r);
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
            if (parsed) {
                r.a = parsed.a;
                r.b = parsed.b;
                r.rate = rateFor(r);
                r.touched = false;
                r.manual = false;
                var aSelect = rowEl.querySelector('.pc-a');
                var bSelect = rowEl.querySelector('.pc-b');
                if (aSelect) aSelect.value = String(r.a);
                if (bSelect) bSelect.value = String(r.b);
                paint();
                focusRate(r);
            } else {
                paint();
            }
            return;
        }
        if (t.classList.contains('pc-punit')) {
            r.unit = t.value === 'sheet' ? 'sheet' : 'ream';
            r.rate = rateFor(r);
            r.touched = false;
            r.manual = false;
            render();
            if (!num(r.rate)) focusRate(r);
            return;
        }
        if (t.classList.contains('pc-a') || t.classList.contains('pc-b')) {
            var raw = t.value;
            var key = t.classList.contains('pc-a') ? 'a' : 'b';
            r[key] = (key === 'a' && /^\d+(\.\d+)?$/.test(raw)) ? parseFloat(raw) : raw;
            if (key === 'a') syncName(r);
            r.rate = rateFor(r);
            r.touched = false;
            r.manual = false;
            if (r.g === 'Paper') {
                var stockInput = rowEl.querySelector('.pc-stock');
                if (stockInput) stockInput.value = stockText(r);
            }
            if (key === 'a' && isBodyPress(r)) {
                syncPressToColors(r);
                recalcAuto();
                saveLast();
            }
            paint();
            if (!num(r.rate)) focusRate(r);
            return;
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
            var gCard = gHead.closest('.pc-group-card, .pc-spec-preview');
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

        var rows = el.groups.querySelectorAll('.pc-row[data-group="' + group + '"]');
        var last = rows[rows.length - 1];
        if (last) {
            var target = last.querySelector('.pc-stock, .pc-name, .pc-rate');
            if (target && target.focus) target.focus({ preventScroll: true });
            if (last.scrollIntoView) last.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
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

        el.groups = $('pcRowsBody');
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

        // Populate Job Types tab buttons
        if (el.types) {
            el.types.innerHTML = Object.keys(TYPES).map(function (k) {
                return '<button type="button" class="pc-type' + (k === state.type ? ' is-active' : '') + '" data-type="' + k + '">' + TYPES[k].label + '</button>';
            }).join('');
            el.types.addEventListener('click', function (e) {
                var b = e.target.closest('.pc-type');
                if (b) applyType(b.dataset.type);
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
                function () { toast('Cost sheet copied'); },
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
                    function () { toast('Specification copied'); },
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

        bindRates();

        root.addEventListener('shown.bs.modal', function () {
            // Always reopen on the calculator, never on a leftover Rates panel.
            if ($('pcRatesPanel') && !$('pcRatesPanel').hidden) openRates(false);
            var modalBody = root.querySelector('.modal-body');
            if (modalBody) modalBody.scrollTop = 0;
            var target = ($('pcQty').value) ? $('pcTitleIn') : $('pcQty');
            if (target && target.focus) target.focus({ preventScroll: true });
        });

        migrateRateMemo();
        buildPaperLists();
        applyType('book');
        loadSheet();
    }

    window.openPrintCostCalculator = function (targetRowIndex, snapshot, rowInfo) {
        boot();
        window.currentCalcTargetRowIndex = (targetRowIndex !== undefined && targetRowIndex !== null) ? targetRowIndex : null;
        var root = $('printCostCalculatorModal');
        if (!root || !window.bootstrap) return;

        // A fresh item starts from the last saved job, not from the row that was edited before.
        if (!snapshot && !rowInfo && state.fromSnapshot) {
            state.fromSnapshot = false;
            applyType(state.type);
        }

        window.bootstrap.Modal.getOrCreateInstance(root).show();

        if (snapshot || rowInfo) {
            var info = Object.assign({}, rowInfo || {}, { rowIndex: targetRowIndex });
            window.loadCalcSnapshotIntoModal(snapshot, info);
        } else {
            window.loadCalcSnapshotIntoModal(null, null);
        }
    };

    // Item-type text of an existing invoice line -> closest calculator job type.
    function typeFromItem(itemType) {
        var it = String(itemType || '').toLowerCase();
        if (/banner|poster/.test(it)) return 'poster';
        if (/leaflet|flyer/.test(it)) return 'leaflet';
        if (/brochure/.test(it)) return 'brochure';
        if (/card/.test(it)) return 'card';
        if (/memo|pad/.test(it)) return 'memo';
        if (/stationery/.test(it)) return 'stationery';
        return 'book';
    }

    function setIfOption(id, value) {
        var sel = $(id);
        if (!sel || value === undefined || value === null) return;
        var v = String(value);
        if (Array.prototype.some.call(sel.options, function (o) { return o.value === v; })) sel.value = v;
    }

    window.loadCalcSnapshotIntoModal = function (snapshot, rowInfo) {
        boot();
        var rootEl = $('printCostCalculatorModal');
        if (!rootEl) return;
        var alertBox = $('pcItemTargetAlert');
        if (!alertBox) {
            alertBox = document.createElement('div');
            alertBox.id = 'pcItemTargetAlert';
            var modalBody = rootEl.querySelector('.modal-body');
            if (modalBody) modalBody.insertBefore(alertBox, modalBody.firstChild);
        }

        if (rowInfo && rowInfo.rowIndex !== undefined && rowInfo.rowIndex !== null) {
            var rowNum = parseInt(rowInfo.rowIndex, 10) + 1;
            alertBox.className = 'pc-target d-flex align-items-center justify-content-between';
            alertBox.innerHTML = '<div>Editing <strong>Item #' + rowNum + '</strong></div>' +
                '<button type="button" class="btn btn-xs btn-outline-dark rounded-pill py-0.5 px-2" onclick="window.currentCalcTargetRowIndex=null; var ab=document.getElementById(\'pcItemTargetAlert\'); if(ab) ab.style.display=\'none\'; var pb=document.getElementById(\'pcAdd\'); if(pb){pb.innerHTML=\'Insert\'; pb.classList.remove(\'btn-success\'); pb.classList.add(\'btn-primary\');}">' +
                'New</button>';
            alertBox.style.display = '';

            if ($('pcAdd')) {
                $('pcAdd').innerHTML = 'Update';
                $('pcAdd').classList.remove('btn-primary');
                $('pcAdd').classList.add('btn-success');
            }
        } else {
            alertBox.style.display = 'none';
            if ($('pcAdd')) {
                $('pcAdd').innerHTML = 'Insert';
                $('pcAdd').classList.remove('btn-success');
                $('pcAdd').classList.add('btn-primary');
            }
        }

        if (snapshot && typeof snapshot === 'object') {
            state.fromSnapshot = true;
            applyType(TYPES[snapshot.type] ? snapshot.type : 'book', true);
            if (snapshot.title !== undefined) $('pcTitleIn').value = snapshot.title;
            if (snapshot.qty !== undefined) $('pcQty').value = snapshot.qty;
            if (snapshot.unit) setIfOption('pcUnit', snapshot.unit);
            if (snapshot.size !== undefined) setIfOption('pcSize', snapshot.size);
            if (snapshot.sheet !== undefined) setIfOption('pcSheet', snapshot.sheet);
            if (snapshot.colors !== undefined) setIfOption('pcColors', snapshot.colors);
            if (snapshot.sides !== undefined) setIfOption('pcSides', snapshot.sides);
            if (snapshot.overhead !== undefined) $('pcOverhead').value = snapshot.overhead;
            if (snapshot.profit !== undefined) $('pcProfit').value = snapshot.profit;
            if (snapshot.round !== undefined) setIfOption('pcRound', snapshot.round);
            if (TYPES[state.type].pages) {
                setFormaSize(snapshot.formaSize || formaSizeFor($('pcSize').value, $('pcSheet').value));
                $('pcPages').value = snapshot.pages !== undefined ? snapshot.pages : '';
                $('pcFormasIn').value = snapshot.formas !== undefined ? snapshot.formas : '';
                state.geoSrc = num($('pcPages').value) > 0 ? 'pages' : 'formas';
                syncBookGeometry(state.geoSrc === 'pages' ? 'pages' : 'formas');
            }
            if (Array.isArray(snapshot.rows) && snapshot.rows.length) state.rows = restoreRows(snapshot.rows);
            else syncColorsToPress();
            resetSpecBox();
            render();
        } else if (rowInfo) {
            state.fromSnapshot = true;
            applyType(typeFromItem(rowInfo.itemType));
            if (rowInfo.title) $('pcTitleIn').value = rowInfo.title;
            if (rowInfo.qty) $('pcQty').value = rowInfo.qty;
            if (rowInfo.unit) setIfOption('pcUnit', rowInfo.unit);
            render();
        }
    };

    doc.addEventListener('DOMContentLoaded', boot);
})();
