/*
 * Printing cost calculator.
 * A job is built from components — Design, Paper, CTP, Press, Lamination, Cutting, Binding, Packing,
 * Transport, Other. Each component is one line: name, its choices (GSM + paper kind, finish + sides,
 * method, packing + basis …), quantity × rate = total. Quantities follow the job until edited by hand.
 *
 * Smart parts: sheet layout (ups) from the trim size, price tiers for other quantities, rounding of the
 * rate, remembered rates (per choice) and remembered job values, and highlighting of lines without a rate.
 *
 * Host pages define window.addPresetItem(title, spec, itemType, unit, price, regularPrice, qty).
 */
(function () {
    'use strict';
    var doc = document;

    var GROUPS = ['Design', 'Paper', 'CTP', 'Press', 'Lamination', 'Cutting', 'Binding', 'Packing', 'Transport', 'Other'];
    var BASIS = { Design: 'Qty', Paper: 'Reams', CTP: 'Plates', Press: 'Thousands', Lamination: 'Copies', Cutting: 'Copies', Binding: 'Copies', Packing: 'Qty', Transport: 'Qty', Other: 'Qty' };
    var NAMES = {
        Design: ['Layout', 'Cover', 'Proof', 'Ebook', 'ISBN', 'Dummy'],
        Paper: ['Inner', 'Cover', 'Board', 'Endpaper', 'Jacket'],
        CTP: ['Inner', 'Cover', 'Spot'],
        Press: ['Inner', 'Cover', 'Spot'],
        Lamination: ['Cover', 'Jacket', 'Spot'],
        Cutting: ['Trim', 'Cover', 'Sheet'],
        Binding: ['Book', 'Cover', 'Pad'],
        Packing: ['Book', 'Bundle', 'Carton'],
        Transport: ['Press', 'Delivery', 'Office'],
        Other: ['Ribbon', 'Sticker', 'Misc']
    };

    // ── Choices on each line: two dropdowns (a, b) per group ───────────────
    var GSM_INNER = [45, 50, 55, 60, 65, 70, 80, 90, 100, 110, 120];
    var GSM_COVER = [45, 50, 55, 60, 65, 70, 80, 90, 100, 110, 120, 150, 180, 200, 250, 300, 350];
    var gsmOpts = function (list) { return list.map(function (g) { return [g, g + ' GSM']; }); };
    var plain = function (list) { return list.map(function (v) { return [v, v]; }); };

    var VARIANTS = {
        PaperInner: { a: ['GSM', gsmOpts(GSM_INNER)], b: ['Kind', plain(['Offwhite', 'White', 'Cream', 'Art', 'Matt', 'Newsprint', 'Kraft', 'Bond', 'Glossy'])] },
        PaperCover: { a: ['GSM', gsmOpts(GSM_COVER)], b: ['Kind', plain(['Glossy', 'Matt', 'Swedish', 'Art', 'Duplex', 'Ivory', 'Kraft', 'Grey', 'Offwhite', 'White'])] },
        Design: { a: ['Work', plain(['Layout', 'Cover', 'Proof', 'Ebook', 'ISBN', 'Dummy', 'Logo', 'Illustration'])], b: ['Per', plain(['Job', 'Page'])] },
        CTP: { a: ['Plate', plain(['CTP', 'Film', 'Zinc'])], b: ['Side', plain(['Inner', 'Cover', 'Spot'])] },
        Press: { a: ['Machine', plain(['Offset', 'Digital', 'Screen', 'Flex'])], b: ['Side', plain(['Inner', 'Cover', 'Spot'])] },
        Lamination: { a: ['Finish', plain(['Matt', 'Glossy', 'Velvet', 'UV', 'Foil', 'Emboss', 'Scratch'])], b: ['Sides', plain(['Front', 'Both'])] },
        Cutting: { a: ['Method', plain(['Trim', 'Die', 'Crease', 'Perforate', 'Punch', 'Fold', 'Number', 'Slit'])], b: null },
        Binding: { a: ['Method', plain(['Perfect', 'Sewn', 'Thread', 'Hardcover', 'Case', 'Saddle', 'Pin', 'Spiral', 'Wire', 'Pasting', 'Padding', 'Stapling', 'Fold'])], b: null },
        Packing: { a: ['Material', plain(['Poly', 'Envelope', 'Shrink', 'Bubble', 'Sleeve', 'Bag', 'Band', 'Carton', 'Box'])], b: ['Per', plain(['Copy', 'Dozen', 'Hundred', 'Lot'])] },
        Transport: { a: ['Work', plain(['Labor', 'Carton', 'Transport', 'Loading', 'Delivery', 'Courier'])], b: ['Per', plain(['Lot', 'Copy', 'Hundred', 'Thousand'])] },
        Other: { a: null, b: null }
    };

    // Starting rates only where the old calculator carried them (BDT per ream); every other rate is yours to enter.
    var PAPER_DEFAULT = { '80|white': 3200, '70|white': 2800, '70|cream': 3500, '80|cream': 4000, '100|art': 4500, '120|matt': 5200, '65|white': 2400, '55|newsprint': 2000, '80|kraft': 3000 };

    // Trim sizes in inches (width × height) and the press sheets they are cut from.
    var SIZES = { Demy: [5.5, 8.5], Crown: [7.25, 9.5], Royal: [6.5, 9.5], A3: [11.7, 16.5], A4: [8.3, 11.7], A5: [5.8, 8.3], B5: [6.9, 9.8], Card: [3.5, 2], Poster: [18, 23], Full: [23, 36] };
    var SHEETS = { '23x36': [23, 36], '25x36': [25, 36], '20x30': [20, 30], '18x23': [18, 23] };

    // Component presets. a / b are the dropdown choices; auto is the quantity rule.
    var P = function (g, n, o) { o = o || {}; o.g = g; o.n = n; return o; };
    var TYPES = {
        book: { label: 'Book', unit: 'Copy', size: 'Demy', pages: true, rows: [
            P('Design', 'Layout', { auto: 'per', a: 'Layout', b: 'Page' }), P('Design', 'Cover', { auto: 'per', a: 'Cover', b: 'Job' }),
            P('Paper', 'Inner', { auto: 'paperBook', a: 80, b: 'White', waste: 5 }),
            P('Paper', 'Cover', { auto: 'sheet', ups: 8, waste: 5, flat: true, noSize: true, a: 300, b: 'Glossy' }),
            P('CTP', 'Inner', { auto: 'platesBook', a: 'CTP', b: 'Inner' }), P('CTP', 'Cover', { auto: 'platesCover', a: 'CTP', b: 'Cover' }),
            P('Press', 'Inner', { auto: 'pressBook', a: 'Offset', b: 'Inner' }), P('Press', 'Cover', { auto: 'pressCover', a: 'Offset', b: 'Cover' }),
            P('Lamination', 'Cover', { auto: 'lam', a: 'Matt', b: 'Front' }), P('Cutting', 'Trim', { auto: 'copies', a: 'Trim' }),
            P('Binding', 'Book', { auto: 'copies', a: 'Perfect' }),
            P('Packing', 'Book', { auto: 'per', a: 'Poly', b: 'Copy', off: true }), P('Transport', 'Delivery', { auto: 'per', a: 'Transport', b: 'Lot', off: true })
        ] },
        card: { label: 'Card', unit: 'Pcs', size: 'Card', rows: [
            P('Design', 'Layout', { auto: 'per', a: 'Layout', b: 'Job' }),
            P('Paper', 'Board', { auto: 'sheet', waste: 5, flat: true, a: 300, b: 'Art' }),
            P('CTP', 'Inner', { auto: 'platesJob', a: 'CTP', b: 'Inner' }), P('Press', 'Inner', { auto: 'pressJob', a: 'Offset', b: 'Inner' }),
            P('Lamination', 'Cover', { auto: 'lam', a: 'Matt', b: 'Both' }), P('Cutting', 'Trim', { auto: 'copies', a: 'Trim' }),
            P('Packing', 'Bundle', { auto: 'per', a: 'Box', b: 'Hundred', off: true })
        ] },
        leaflet: { label: 'Leaflet', unit: 'Pcs', size: 'A4', rows: [
            P('Design', 'Layout', { auto: 'per', a: 'Layout', b: 'Job' }),
            P('Paper', 'Inner', { auto: 'sheet', waste: 5, flat: true, a: 120, b: 'Matt' }),
            P('CTP', 'Inner', { auto: 'platesJob', a: 'CTP', b: 'Inner' }), P('Press', 'Inner', { auto: 'pressJob', a: 'Offset', b: 'Inner' }),
            P('Cutting', 'Trim', { auto: 'copies', a: 'Trim' }), P('Packing', 'Bundle', { auto: 'per', a: 'Band', b: 'Hundred', off: true })
        ] },
        poster: { label: 'Poster', unit: 'Pcs', size: 'Poster', rows: [
            P('Design', 'Layout', { auto: 'per', a: 'Layout', b: 'Job' }),
            P('Paper', 'Inner', { auto: 'sheet', waste: 5, flat: true, a: 100, b: 'Art' }),
            P('CTP', 'Inner', { auto: 'platesJob', a: 'CTP', b: 'Inner' }), P('Press', 'Inner', { auto: 'pressJob', a: 'Offset', b: 'Inner' }),
            P('Lamination', 'Cover', { auto: 'lam', a: 'Glossy', b: 'Front', off: true }), P('Cutting', 'Trim', { auto: 'copies', a: 'Trim' })
        ] },
        calendar: { label: 'Calendar', unit: 'Pcs', size: 'A3', pages: true, rows: [
            P('Design', 'Layout', { auto: 'per', a: 'Layout', b: 'Job' }),
            P('Paper', 'Inner', { auto: 'sheet', waste: 5, a: 120, b: 'Matt' }),
            P('CTP', 'Inner', { auto: 'platesJob', a: 'CTP', b: 'Inner' }), P('Press', 'Inner', { auto: 'pressJob', a: 'Offset', b: 'Inner' }),
            P('Cutting', 'Trim', { auto: 'copies', a: 'Trim' }), P('Binding', 'Book', { auto: 'copies', a: 'Spiral' }),
            P('Packing', 'Book', { auto: 'per', a: 'Poly', b: 'Copy', off: true })
        ] },
        box: { label: 'Box', unit: 'Pcs', size: 'A4', rows: [
            P('Design', 'Layout', { auto: 'per', a: 'Layout', b: 'Job' }),
            P('Paper', 'Board', { auto: 'sheet', waste: 8, flat: true, a: 350, b: 'Duplex' }),
            P('CTP', 'Inner', { auto: 'platesJob', a: 'CTP', b: 'Inner' }), P('Press', 'Inner', { auto: 'pressJob', a: 'Offset', b: 'Inner' }),
            P('Lamination', 'Cover', { auto: 'lam', a: 'Matt', b: 'Front' }), P('Cutting', 'Trim', { auto: 'copies', a: 'Die' }),
            P('Binding', 'Book', { auto: 'copies', a: 'Pasting' }), P('Packing', 'Carton', { auto: 'per', a: 'Carton', b: 'Hundred', off: true })
        ] },
        memo: { label: 'Memo', unit: 'Book', size: 'A5', pages: true, rows: [
            P('Design', 'Layout', { auto: 'per', a: 'Layout', b: 'Job' }),
            P('Paper', 'Inner', { auto: 'sheet', waste: 5, a: 70, b: 'White' }),
            P('CTP', 'Inner', { auto: 'platesJob', a: 'CTP', b: 'Inner' }), P('Press', 'Inner', { auto: 'pressJob', a: 'Offset', b: 'Inner' }),
            P('Cutting', 'Trim', { auto: 'copies', a: 'Trim' }), P('Binding', 'Pad', { auto: 'copies', a: 'Pasting' })
        ] },
        custom: { label: 'Custom', unit: 'Pcs', size: '', rows: [] }
    };
    var TIERS = [500, 1000, 2000, 5000, 10000];

    // Rule used when a line is added by hand
    var GROUP_AUTO = { Design: 'per', Paper: 'sheet', CTP: 'platesJob', Press: 'pressJob', Lamination: 'lam', Cutting: 'copies', Binding: 'copies', Packing: 'per', Transport: 'per', Other: 'one' };
    var GROUP_DEFAULT = {
        Design: { a: 'Layout', b: 'Job' }, Paper: { a: 80, b: 'White' }, CTP: { a: 'CTP', b: 'Inner' }, Press: { a: 'Offset', b: 'Inner' },
        Lamination: { a: 'Matt', b: 'Front' }, Cutting: { a: 'Trim' }, Binding: { a: 'Perfect' }, Packing: { a: 'Poly', b: 'Copy' }, Transport: { a: 'Labor', b: 'Lot' }, Other: {}
    };

    var state = { type: 'book', rows: [], seq: 0 };
    var el = {};

    function num(v) { var n = parseFloat(v); return isFinite(n) ? n : 0; }
    function money(v, d) { return '৳' + Number(v || 0).toLocaleString('en-US', { minimumFractionDigits: d == null ? 0 : d, maximumFractionDigits: d == null ? 0 : d }); }
    function round(v, d) { var p = Math.pow(10, d == null ? 2 : d); return Math.round((v + Number.EPSILON) * p) / p; }
    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
    function $(id) { return doc.getElementById(id); }

    // Which variant set a line uses (paper depends on inner vs cover stock).
    function variantKey(r) {
        if (r.g !== 'Paper') return r.g;
        return /cover|board|jacket|card|wrapper|dust/i.test(r.n) ? 'PaperCover' : 'PaperInner';
    }
    function variants(r) { return VARIANTS[variantKey(r)] || { a: null, b: null }; }

    // ── Paper picker: one box that understands "80 white", "300 swedish", chips for one-tap changes ──
    function customKinds() { var k = readStore('pc-kinds'); return Array.isArray(k) ? k : (k.list || []); }
    function addKind(name) {
        var list = customKinds();
        if (list.indexOf(name) === -1) { list.push(name); try { localStorage.setItem('pc-kinds', JSON.stringify({ list: list })); } catch (e) { /* storage off */ } }
    }
    function kindsFor(r) {
        var base = variants(r).b ? variants(r).b[1].map(function (o) { return o[0]; }) : [];
        customKinds().forEach(function (k) { if (base.indexOf(k) === -1) base.push(k); });
        return base;
    }
    function customGsms() { var k = readStore('pc-gsms'); return Array.isArray(k) ? k : (k.list || []); }
    function addGsm(n) {
        var list = customGsms();
        if (list.indexOf(n) === -1) { list.push(n); try { localStorage.setItem('pc-gsms', JSON.stringify({ list: list })); } catch (e) { /* storage off */ } }
    }
    // The standard weights plus any weight you have typed before (85, 150 …), ascending.
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

    // "80", "80 gsm white", "white 80", "Pearl 85" → {a: 80, b: 'White'}; unknown words become new kinds.
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

    // A dropdown of choices; a leading dot marks the ones that already have a rate.
    function pickRow(items, current, cls, label, suffix, dotFor) {
        var opts = items.map(function (v) {
            return '<option value="' + esc(v) + '"' + (String(v) === String(current) ? ' selected' : '') + '>' + (dotFor(v) ? '● ' : '') + esc(v) + (suffix || '') + '</option>';
        }).join('');
        return '<label class="pc-pick">' + label + '<select class="form-select form-select-sm ' + cls + '">' + opts + '</select></label>';
    }

    function paperBlock(r) {
        var list = variantKey(r) === 'PaperCover' ? 'Cover' : 'Inner';
        return '<div class="pc-paper">'
            + '<div class="pc-paper-top"><input class="form-control form-control-sm pc-stock" list="pcPaper' + list + '" value="' + esc(stockText(r)) + '" aria-label="Paper" title="GSM Kind">'
            + (r.auto === 'sheet' ? '<label>Ups<input type="number" class="form-control form-control-sm pc-ups" min="1" step="1" value="' + esc(r.ups) + '"></label>' : '')
            + '<label>Waste %<input type="number" class="form-control form-control-sm pc-waste" min="0" step="1" value="' + esc(r.waste) + '"></label></div>'
            + '<div class="pc-picks">' + pickRow(gsmsFor(r), r.a, 'pc-a', 'GSM', '', function (g) { return knownRate('Paper', g, r.b); })
            + pickRow(kindsFor(r), r.b, 'pc-b', 'Kind', '', function (k) { return knownRate('Paper', r.a, k); }) + '</div>'
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

    // ── Browser memory ─────────────────────────────────────────────────────
    function store(key, fn) {
        try { var all = JSON.parse(localStorage.getItem(key) || '{}'); fn(all); localStorage.setItem(key, JSON.stringify(all)); } catch (e) { /* storage off */ }
    }
    function readStore(key) { try { return JSON.parse(localStorage.getItem(key) || '{}'); } catch (e) { return {}; } }
    // A rate belongs to the choices, not to the label: "Paper 80 GSM White" costs the same inner or cover.
    // Rate sheet (shared, on the server) → this browser's remembered rates → built-in starter paper rates.
    // The kind only matters for rates that change with it: paper kind, and the basis of packing / transport / design.
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
            m[state.type] = { qty: $('pcQty').value, pages: $('pcPages').value, colors: $('pcColors').value, sides: $('pcSides').value, size: $('pcSize').value, sheet: $('pcSheet').value, overhead: $('pcOverhead').value, profit: $('pcProfit').value, round: $('pcRound').value };
        });
    }
    function loadLast(type) {
        var v = readStore('pc-last')[type];
        var d = TYPES[type];
        $('pcQty').value = v ? v.qty : '';
        $('pcPages').value = v && d.pages ? v.pages : '';
        $('pcColors').value = v ? v.colors : '4';
        $('pcSides').value = v ? v.sides : '1';
        $('pcSize').value = v && v.size !== undefined ? v.size : d.size;
        $('pcSheet').value = v ? v.sheet : '23x36';
        $('pcOverhead').value = v ? v.overhead : '';
        $('pcProfit').value = v ? v.profit : '';
        $('pcRound').value = v ? v.round : '0';
    }

    // ── Job values and derived numbers ──────────────────────────────────────
    function job(qOverride) {
        var q = qOverride != null ? qOverride : num($('pcQty').value), pages = num($('pcPages').value);
        return {
            q: q, pages: pages,
            colors: num($('pcColors').value) || 4,
            sides: num($('pcSides').value) || 1,
            formas: pages > 0 ? Math.ceil(pages / 16) : 0,
            size: $('pcSize').value,
            sheet: $('pcSheet').value
        };
    }

    // Pieces of the trim size per press sheet (best of both orientations).
    function upsFor(size, sheet) {
        var p = SIZES[size], s = SHEETS[sheet];
        if (!p || !s) return 0;
        var a = Math.floor(s[0] / p[0]) * Math.floor(s[1] / p[1]);
        var b = Math.floor(s[0] / p[1]) * Math.floor(s[1] / p[0]);
        return Math.max(a, b);
    }

    // Quantity of a "per" line: how the packing / transport / design charge is counted.
    function perQty(r, j) {
        switch (r.b) {
            case 'Copy': return j.q;
            case 'Dozen': return Math.ceil(j.q / 12);
            case 'Hundred': return Math.ceil(j.q / 100);
            case 'Thousand': return Math.ceil(j.q / 1000);
            case 'Page': return j.pages || 1;
            default: return 1; // Lot, Job
        }
    }

    function auto(r, j) {
        var q = j.q;
        switch (r.auto) {
            case 'one': return 1;
            case 'copies': return q;
            case 'per': return perQty(r, j);
            case 'lam': return q * (r.b === 'Both' ? 2 : 1);
            case 'paperBook': return j.formas ? Math.ceil((j.formas * q / 500) * (1 + num(r.waste) / 100) * 10) / 10 : 0;
            case 'sheet': {
                var mult = (!r.flat && j.pages > 0 && TYPES[state.type].pages) ? j.pages : 1;
                var sheets = (q * mult / Math.max(1, num(r.ups))) * (1 + num(r.waste) / 100);
                return Math.ceil(sheets / 500 * 10) / 10;
            }
            case 'platesBook': return j.formas * j.colors;
            case 'platesCover': return 4;
            case 'platesJob': return j.colors * j.sides;
            case 'pressBook': return round(j.formas * Math.max(1, q / 1000), 1);
            case 'pressCover': return round(Math.max(1, q / 1000), 1);
            case 'pressJob': return round(Math.max(1, q / 1000) * j.sides, 1);
        }
        return 0;
    }

    // ── Rows ────────────────────────────────────────────────────────────────
    function newRow(spec) {
        var d = GROUP_DEFAULT[spec.g] || {};
        var r = {
            id: ++state.seq, g: spec.g, n: spec.n, on: !spec.off, auto: spec.auto || GROUP_AUTO[spec.g], manual: false,
            qty: 0, rate: 0, ups: spec.ups || 8, upsManual: !!spec.ups, waste: spec.waste == null ? 5 : spec.waste,
            flat: !!spec.flat, noSize: !!spec.noSize,
            a: spec.a !== undefined ? spec.a : d.a, b: spec.b !== undefined ? spec.b : d.b
        };
        r.rate = rateFor(r); r.touched = false;
        return r;
    }

    function applyType(type, keepJob) {
        state.type = type;
        var t = TYPES[type];
        state.rows = t.rows.map(newRow);
        $('pcUnit').value = t.unit;
        $('pcPagesWrap').hidden = !t.pages;
        if (!keepJob) loadLast(type);
        el.types.querySelectorAll('.pc-type').forEach(function (b) { b.classList.toggle('is-active', b.dataset.type === type); });
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

    // ── Totals (also used for the quantity tiers) ──────────────────────────
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
        var overhead = round(direct * num($('pcOverhead').value) / 100, 2);
        var cost = round(direct + overhead, 2);
        var profit = round(cost * num($('pcProfit').value) / 100, 2);
        var total = round(cost + profit, 2);
        var unit = q > 0 ? total / q : 0;
        var step = num($('pcRound').value);
        var rounded = false;
        if (step > 0 && unit > 0) { var up = Math.ceil(unit / step - 1e-9) * step; rounded = up !== unit; unit = up; total = round(unit * q, 2); }
        else unit = round(unit, 2);
        return { direct: direct, overhead: overhead, cost: cost, profit: profit, total: total, unit: round(unit, 2), rounded: rounded };
    }

    // ── Render ──────────────────────────────────────────────────────────────
    function render() {
        recalcAuto();
        var html = '';
        GROUPS.forEach(function (g) {
            var rows = state.rows.filter(function (r) { return r.g === g; });
            if (!rows.length) return;
            html += '<section class="pc-card" data-group="' + g + '"><header class="pc-card-head"><h6 class="pc-h">' + g + '</h6><span class="pc-sub" data-sub="' + g + '"></span></header>';
            html += '<div class="pc-cols" aria-hidden="true"><span></span><span>Item</span><span>' + BASIS[g] + '</span><span>Rate</span><span>Total</span><span></span></div>';
            rows.forEach(function (r) { html += rowHtml(r); });
            html += '</section>';
        });
        el.groups.innerHTML = html || '<div class="pc-empty">Add</div>';
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

    function rowHtml(r) {
        var v = variants(r);
        r.vk = variantKey(r);
        var extra = '';
        var picks = select('pc-a', v.a ? v.a[0] : '', v.a, r.a) + select('pc-b', v.b ? v.b[0] : '', v.b, r.b);
        if (r.g === 'Paper') { extra = paperBlock(r); picks = ''; }
        return '<div class="pc-row' + (r.on ? '' : ' is-off') + '" data-id="' + r.id + '">'
            + '<label class="pc-on"><input type="checkbox" class="pc-chk" ' + (r.on ? 'checked' : '') + ' aria-label="Include"></label>'
            + '<div class="pc-name-wrap"><input class="form-control form-control-sm pc-name" list="pcNames' + r.g + '" value="' + esc(r.n) + '" maxlength="60" aria-label="Item">'
            + picks + extra + '</div>'
            + '<div class="pc-qty-wrap" data-label="' + BASIS[r.g] + '"><input type="number" class="form-control form-control-sm pc-qty text-end" min="0" step="any" value="' + esc(r.qty) + '" aria-label="Quantity">'
            + '<button type="button" class="pc-auto" title="Auto"' + (r.manual && r.auto ? '' : ' hidden') + '>Auto</button></div>'
            + '<div class="pc-rate-wrap" data-label="Rate"><input type="number" class="form-control form-control-sm pc-rate text-end" min="0" step="any" value="' + (r.rate || '') + '" aria-label="Rate"><button type="button" class="pc-pin" hidden>Save</button></div>'
            + '<output class="pc-total" data-label="Total">' + money(lineTotal(r)) + '</output>'
            + '<button type="button" class="pc-del" aria-label="Remove">×</button>'
            + '</div>';
    }

    // Update numbers without rebuilding inputs (keeps focus while typing).
    function paint() {
        var j = job();
        var perGroup = {};
        state.rows.forEach(function (r) { perGroup[r.g] = (perGroup[r.g] || 0) + lineTotal(r); });

        var missing = 0;
        el.groups.querySelectorAll('.pc-row').forEach(function (rowEl) {
            var r = byId(rowEl.dataset.id);
            if (!r) return;
            rowEl.classList.toggle('is-off', !r.on);
            rowEl.querySelector('.pc-total').textContent = money(lineTotal(r));
            var q = rowEl.querySelector('.pc-qty'); if (doc.activeElement !== q) q.value = r.qty;
            var u = rowEl.querySelector('.pc-ups'); if (u && doc.activeElement !== u) u.value = r.ups;
            var rate = rowEl.querySelector('.pc-rate'); if (doc.activeElement !== rate) rate.value = r.rate || '';
            var autoBtn = rowEl.querySelector('.pc-auto'); if (autoBtn) autoBtn.hidden = !(r.manual && r.auto);
            var lacks = r.on && num(r.qty) > 0 && !num(r.rate);
            rate.classList.toggle('is-missing', lacks);
            if (lacks) missing++;
            pinState(rowEl.querySelector('.pc-pin'), r);
        });
        el.groups.querySelectorAll('[data-sub]').forEach(function (s) { s.textContent = money(perGroup[s.dataset.sub] || 0); });

        var p = priceFor(j.q);
        $('pcDirect').textContent = money(p.direct);
        $('pcOverheadAmt').textContent = money(p.overhead);
        $('pcCost').textContent = money(p.cost);
        $('pcProfitAmt').textContent = money(p.profit);
        $('pcTotal').textContent = money(p.total);
        $('pcUnit2').textContent = j.q > 0 ? money(p.unit, 2) : '৳0';
        $('pcRoundedNote').hidden = !p.rounded;
        $('pcFootTotal').textContent = money(p.total);
        $('pcFootUnit').textContent = j.q > 0 ? money(p.unit, 2) : '৳0';
        $('pcFormas').textContent = j.formas ? String(j.formas) : '0';
        $('pcFormasWrap').hidden = !(TYPES[state.type].pages && j.formas);
        var layout = upsFor(j.size, j.sheet);
        $('pcUpsWrap').hidden = !(layout > 0 && state.type !== 'book');
        $('pcUps').textContent = String(layout);
        $('pcMissing').hidden = missing === 0;
        $('pcMissing').textContent = missing ? 'Rate ×' + missing : '';

        el.bars.innerHTML = GROUPS.filter(function (g) { return (perGroup[g] || 0) > 0; }).map(function (g) {
            var share = p.direct > 0 ? Math.round((perGroup[g] / p.direct) * 100) : 0;
            return '<div class="pc-bar"><span>' + g + '</span><i style="--w:' + share + '%"></i><b>' + money(perGroup[g]) + '</b></div>';
        }).join('') || '<div class="pc-empty">—</div>';

        if (p.direct > 0) {
            var qs = TIERS.slice();
            if (j.q > 0 && qs.indexOf(j.q) === -1) { qs.push(j.q); qs.sort(function (a, b) { return a - b; }); }
            el.tiers.innerHTML = qs.map(function (q) {
                var t = priceFor(q);
                return '<button type="button" class="pc-tier' + (q === j.q ? ' is-current' : '') + '" data-q="' + q + '"><span>' + q.toLocaleString('en-US') + '</span><b>' + money(t.unit, 2) + '</b></button>';
            }).join('');
            el.tiersWrap.hidden = false;
        } else {
            el.tiersWrap.hidden = true;
        }

        el.add.disabled = el.plain.disabled = !(p.total > 0 && j.q > 0);
        return { direct: p.direct, cost: p.cost, total: p.total, unit: p.unit, j: j };
    }

    function byId(id) { return state.rows.filter(function (r) { return String(r.id) === String(id); })[0]; }

    // ── Events ──────────────────────────────────────────────────────────────
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
        recalcAuto();
        paint();
        saveLast();
    }

    function onChange(e) {
        var t = e.target;
        var rowEl = t.closest && t.closest('.pc-row');
        if (!rowEl) { if (t.closest('.pc-job, .pc-summary')) { recalcAuto(); paint(); saveLast(); } return; }
        var r = byId(rowEl.dataset.id);
        if (!r) return;
        if (t.classList.contains('pc-chk')) { r.on = t.checked; paint(); return; }
        if (t.classList.contains('pc-name')) {
            // A new name can move paper between inner and cover stock; keep the choices valid.
            r.n = t.value;
            if (variantKey(r) !== r.vk) { r.a = (GROUP_DEFAULT[r.g] || {}).a; r.b = (GROUP_DEFAULT[r.g] || {}).b; r.rate = rateFor(r); r.touched = false; r.manual = false; render(); }
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
            // The rate follows the choice: remembered rate, else a known starting rate, else blank to be entered.
            r.rate = rateFor(r); r.touched = false;
            r.manual = false;
            render();
            var again = el.groups.querySelector('.pc-row[data-id="' + r.id + '"] .pc-rate');
            if (again && !num(r.rate)) again.focus();
        }
    }

    function focusRate(r) {
        var again = el.groups.querySelector('.pc-row[data-id="' + r.id + '"] .pc-rate');
        if (again && !num(r.rate)) again.focus();
    }

    function onClick(e) {
        var t = e.target;
        var rowEl = t.closest && t.closest('.pc-row');
        if (rowEl && t.classList.contains('pc-pin')) {
            var pr = byId(rowEl.dataset.id);
            if (pr) saveToSheet(pr);
            return;
        }
        if (rowEl && t.classList.contains('pc-del')) {
            state.rows = state.rows.filter(function (r) { return String(r.id) !== rowEl.dataset.id; });
            render();
        } else if (rowEl && t.classList.contains('pc-auto')) {
            var r = byId(rowEl.dataset.id);
            if (r) { r.manual = false; render(); }
        } else if (t.closest && t.closest('.pc-tier')) {
            $('pcQty').value = t.closest('.pc-tier').dataset.q;
            recalcAuto(); paint(); saveLast();
        }
    }

    function addComponent(group) {
        var names = NAMES[group];
        var used = state.rows.filter(function (r) { return r.g === group; }).map(function (r) { return r.n; });
        var name = names.filter(function (n) { return used.indexOf(n) === -1; })[0] || names[0];
        var spec = { g: group, n: name };
        if (group === 'Paper') { spec.waste = 5; }
        state.rows.push(newRow(spec));
        render();
        var rows = el.groups.querySelectorAll('[data-group="' + group + '"] .pc-row');
        var last = rows[rows.length - 1];
        if (last) last.querySelector('.pc-a, .pc-rate').focus();
    }

    // Enter in a rate box jumps to the next rate box, so a whole job can be priced from the keyboard.
    function onKey(e) {
        if (e.key !== 'Enter' || !e.target.classList.contains('pc-rate')) return;
        e.preventDefault();
        var all = Array.prototype.slice.call(el.groups.querySelectorAll('.pc-row:not(.is-off) .pc-rate'));
        var next = all[all.indexOf(e.target) + 1];
        if (next) { next.focus(); next.select(); } else { el.add.focus(); }
    }

    // ── Output ──────────────────────────────────────────────────────────────
    function label(r) {
        var v = variants(r);
        var bits = [];
        if (v.a && r.a !== undefined && r.a !== '') bits.push(typeof r.a === 'number' ? r.a + ' GSM' : r.a);
        if (v.b && r.b !== undefined && r.b !== '' && ['Per'].indexOf(v.b[0]) === -1 && ['Side', 'Sides'].indexOf(v.b[0]) === -1) bits.push(r.b);
        if (r.g === 'Lamination' && r.b === 'Both') bits.push('both sides');
        return r.n + (bits.length ? ' ' + bits.join(' ') : '');
    }

    function specText() {
        var j = job();
        var parts = [];
        if (j.size) parts.push(j.size);
        if (j.pages) parts.push(j.pages + ' pages');
        parts.push(j.colors + ' color' + (j.sides === 2 ? ', 2 sides' : ''));
        var by = {};
        state.rows.filter(function (r) { return r.on && lineTotal(r) > 0; }).forEach(function (r) { (by[r.g] = by[r.g] || []).push(label(r)); });
        var comps = ['Paper', 'CTP', 'Lamination', 'Cutting', 'Binding', 'Packing'].filter(function (g) { return by[g]; })
            .map(function (g) { return g + ' (' + by[g].join(', ') + ')'; });
        return parts.join(', ') + (comps.length ? '. ' + comps.join('. ') + '.' : '');
    }

    // Detail text that also shows what each line costs: "Paper (Inner 80 GSM White 12 × 3200)".
    function ratedText() {
        var j = job();
        var parts = [];
        if (j.size) parts.push(j.size);
        if (j.pages) parts.push(j.pages + ' pages');
        parts.push(j.colors + ' color' + (j.sides === 2 ? ', 2 sides' : ''));
        var comps = [];
        GROUPS.forEach(function (g) {
            var items = state.rows.filter(function (r) { return r.g === g && r.on && lineTotal(r) > 0; })
                .map(function (r) { return label(r) + ' ' + r.qty + ' × ' + r.rate; });
            if (items.length) comps.push(g + ' (' + items.join(', ') + ')');
        });
        return parts.join(', ') + (comps.length ? '. ' + comps.join('. ') + '.' : '');
    }

    function sheetText() {
        var s = paint();
        var lines = ['COST — ' + (($('pcTitleIn').value.trim()) || TYPES[state.type].label), specText(), ''];
        lines.push('Quantity: ' + s.j.q + ' ' + $('pcUnit').value);
        GROUPS.forEach(function (g) {
            state.rows.filter(function (r) { return r.g === g && r.on && lineTotal(r) > 0; }).forEach(function (r) {
                lines.push(g + ' · ' + label(r) + ': ' + r.qty + ' × ' + r.rate + ' = ' + money(lineTotal(r)));
            });
        });
        lines.push('', 'Cost: ' + money(s.cost), 'Total: ' + money(s.total), 'Rate: ' + money(s.unit, 2) + ' / ' + $('pcUnit').value);
        return lines.join('\n');
    }

    function toast(text) {
        var t = $('pcToast');
        t.textContent = text; t.hidden = false;
        clearTimeout(t._t);
        t._t = setTimeout(function () { t.hidden = true; }, 2200);
    }

    function insert(withRates) {
        var s = paint();
        if (!(s.j.q > 0)) { toast('Quantity'); $('pcQty').focus(); return; }
        if (!(s.total > 0)) { toast('Rate'); return; }
        var title = $('pcTitleIn').value.trim() || (TYPES[state.type].label + ' printing');
        if (typeof window.addPresetItem === 'function') {
            window.addPresetItem(title, withRates === true ? ratedText() : specText(), 'Printing & Binding', $('pcUnit').value || 'Pcs', s.unit, s.unit, s.j.q);
        }
        var modal = window.bootstrap && window.bootstrap.Modal.getInstance($('printCostCalculatorModal'));
        if (modal) modal.hide();
    }

    function resetAll() {
        ['pcTitleIn', 'pcQty', 'pcPages', 'pcOverhead', 'pcProfit'].forEach(function (id) { $(id).value = ''; });
        $('pcColors').value = '4'; $('pcSides').value = '1'; $('pcRound').value = '0';
        $('pcSize').value = TYPES[state.type].size; $('pcSheet').value = '23x36';
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
        return fetch(root().dataset.ratesUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { if (!r.ok) throw new Error('rates'); return r.json(); })
            .then(function (rows) { indexSheet(rows); sheetLoaded = true; reapplyRates(); renderRates(); })
            .catch(function () { /* offline or not allowed: browser memory and starter rates still work */ });
    }
    // Lines whose rate was not typed by hand follow the sheet.
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
        btn.classList.toggle('is-saved', !!same);
        btn.disabled = !!same;
    }

    function saveToSheet(r) {
        api('POST', root().dataset.ratesStore, sheetPayload(r)).then(function (res) {
            upsert(res.row);
            paint();
            renderRates();
            toast('Saved');
        }).catch(function () { toast('Not saved'); });
    }

    // ── Rate sheet panel ────────────────────────────────────────────────────
    var rateGroup = '', rateQuery = '';
    function perOf(group, kind) { return KEY_B[group] && group !== 'Paper' ? (kind || '') : ''; }

    function renderRates() {
        var body = $('pcRatesBody');
        if (!body) return;
        var q = rateQuery.toLowerCase();
        var rows = SHEET_ROWS.filter(function (x) {
            return (!rateGroup || x.group === rateGroup) && (!q || (x.group + ' ' + x.item + ' ' + x.kind).toLowerCase().indexOf(q) !== -1);
        });
        body.innerHTML = rows.map(function (x) {
            return '<tr data-id="' + x.id + '"><td><span class="pc-gtag" data-group="' + esc(x.group) + '">' + esc(x.group) + '</span></td>'
                + '<td class="fw-semibold">' + esc(x.item) + (x.group === 'Paper' && x.item ? ' GSM' : '') + '</td>'
                + '<td>' + esc(x.kind || '—') + '</td>'
                + '<td class="text-end"><input type="number" class="form-control form-control-sm pr-rate text-end" min="0" step="0.01" value="' + esc(x.rate) + '" aria-label="Rate"></td>'
                + '<td class="text-end"><button type="button" class="pc-del pr-del" aria-label="Remove">×</button></td></tr>';
        }).join('');
        $('pcRatesEmpty').hidden = rows.length > 0;

        var counts = {};
        SHEET_ROWS.forEach(function (x) { counts[x.group] = (counts[x.group] || 0) + 1; });
        $('pcRatesGroups').innerHTML = '<option value=""' + (rateGroup === '' ? ' selected' : '') + '>All ' + SHEET_ROWS.length + '</option>'
            + GROUPS.filter(function (g) { return counts[g]; }).map(function (g) {
                return '<option value="' + g + '"' + (rateGroup === g ? ' selected' : '') + '>' + g + ' ' + counts[g] + '</option>';
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
        $('pcRatesBtn').classList.toggle('is-active', open);
        if (open) { renderRates(); fillAddLists(); }
        else reapplyRates();
    }

    function bindRates() {
        var opts = GROUPS.map(function (g) { return '<option value="' + g + '">' + g + '</option>'; }).join('');
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
                }).catch(function () { toast('Not saved'); });
        });

        $('pcRatesAdjust').addEventListener('submit', function (e) {
            e.preventDefault();
            api('POST', root().dataset.ratesAdjust, { group: $('paGroup').value || null, mode: $('paMode').value, value: num($('paValue').value) })
                .then(function (res) { indexSheet(res.rows); renderRates(); $('paValue').value = ''; toast(res.changed + ' ✓'); })
                .catch(function () { toast('Not applied'); });
        });

        var body = $('pcRatesBody');
        body.addEventListener('change', function (e) {
            if (!e.target.classList.contains('pr-rate')) return;
            var tr = e.target.closest('tr');
            var row = SHEET_ROWS.filter(function (x) { return String(x.id) === tr.dataset.id; })[0];
            if (!row) return;
            api('POST', root().dataset.ratesStore, { group: row.group, item: row.item, kind: row.kind, rate: num(e.target.value) })
                .then(function (res) { upsert(res.row); toast('Saved'); })
                .catch(function () { e.target.value = row.rate; toast('Not saved'); });
        });
        body.addEventListener('click', function (e) {
            var del = e.target.closest('.pr-del');
            if (!del) return;
            var tr = del.closest('tr');
            if (!window.confirm('Remove?')) return;
            api('DELETE', root().dataset.ratesDelete + '/' + tr.dataset.id)
                .then(function () { indexSheet(SHEET_ROWS.filter(function (x) { return String(x.id) !== tr.dataset.id; })); renderRates(); toast('Removed'); })
                .catch(function () { toast('Not removed'); });
        });
    }

    // ── Boot ────────────────────────────────────────────────────────────────
    function boot() {
        var root = $('printCostCalculatorModal');
        if (!root || root.dataset.ready) return;
        root.dataset.ready = '1';
        el.groups = $('pcGroups'); el.bars = $('pcBars'); el.types = $('pcTypes'); el.add = $('pcAdd'); el.plain = $('pcPlain'); el.tiers = $('pcTiers'); el.tiersWrap = $('pcTiersWrap');

        $('pcSize').innerHTML = '<option value="">—</option>' + Object.keys(SIZES).map(function (k) { return '<option value="' + k + '">' + k + '</option>'; }).join('');
        el.types.innerHTML = Object.keys(TYPES).map(function (k) { return '<button type="button" class="pc-type" data-type="' + k + '">' + TYPES[k].label + '</button>'; }).join('');
        $('pcAddBar').innerHTML = GROUPS.map(function (g) { return '<button type="button" class="pc-addbtn" data-add="' + g + '">' + g + '</button>'; }).join('');
        $('pcLists').innerHTML = GROUPS.map(function (g) { return '<datalist id="pcNames' + g + '">' + NAMES[g].map(function (n) { return '<option value="' + n + '">'; }).join('') + '</datalist>'; }).join('');

        buildPaperLists();
        bindRates();
        el.types.addEventListener('click', function (e) { var b = e.target.closest('.pc-type'); if (b) applyType(b.dataset.type); });
        $('pcAddBar').addEventListener('click', function (e) { var b = e.target.closest('[data-add]'); if (b) addComponent(b.dataset.add); });
        root.addEventListener('input', onInput);
        root.addEventListener('change', onChange);
        root.addEventListener('click', onClick);
        root.addEventListener('keydown', onKey);
        root.querySelectorAll('[data-qty]').forEach(function (b) { b.addEventListener('click', function () { $('pcQty').value = b.dataset.qty; recalcAuto(); paint(); saveLast(); }); });
        root.querySelectorAll('[data-profit]').forEach(function (b) { b.addEventListener('click', function () { $('pcProfit').value = b.dataset.profit; paint(); saveLast(); }); });
        $('pcReset').addEventListener('click', resetAll);
        $('pcCopy').addEventListener('click', function () {
            var text = sheetText();
            (navigator.clipboard ? navigator.clipboard.writeText(text) : Promise.reject()).then(function () { toast('Copied'); }, function () { toast('Not copied'); });
        });
        el.add.addEventListener('click', function () { insert(true); });
        el.plain.addEventListener('click', function () { insert(false); });
        root.addEventListener('shown.bs.modal', function () { (($('pcQty').value) ? $('pcTitleIn') : $('pcQty')).focus(); });

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
