/**
 * Book price table (resources/views/admin/books/partials/price-grid.blade.php) — full book form and Quick Add.
 *
 * Per binding row: MRP, Discount % ⇄ Price, Buy % ⇄ Cost, and the margin.
 *  - Of each pair, the box typed last is the fact; changing the MRP recalculates only the other box.
 *    Nothing is filled in by itself, so no discount is ever saved that nobody typed.
 *  - The binding select decides which rows show; a hidden row's inputs are disabled so they are not posted.
 *  - A price at or above the MRP is no discount (warned); a cost above the selling price shows a loss.
 * API: window.BookPriceGrid.init(root?), .reset(grid), .sync(grid), .load(grid)
 */
(function () {
    'use strict';

    const num = el => { const v = parseFloat(el && el.value); return isFinite(v) ? v : 0; };
    const r2 = v => Math.round(v * 100) / 100;
    const pctText = v => String(r2(v));
    const money = v => '৳' + r2(v).toLocaleString('en-US');
    const ROWS_FOR = { hardcover: ['hardcover'], both: ['paperback', 'hardcover'] };

    function rowState(tr) {
        if (!tr._bpg) {
            const f = k => tr.querySelector('[data-bpg="' + k + '"]');
            tr._bpg = { mrp: f('mrp'), pct: f('pct'), sale: f('sale'), buy: f('buy'), cost: f('cost'), out: tr.querySelector('[data-bpg-out="margin"]'), saleAnchor: null, costAnchor: null };
        }
        return tr._bpg;
    }

    // Starting point: what is already saved stays exactly as saved.
    function seed(tr) {
        const s = rowState(tr);
        s.saleAnchor = num(s.sale) > 0 ? 'sale' : (s.pct && s.pct.value !== '' ? 'pct' : null);
        s.costAnchor = num(s.cost) > 0 ? 'cost' : (s.buy && s.buy.value !== '' ? 'buy' : null);
        const mrp = num(s.mrp);
        if (s.saleAnchor === 'sale' && s.pct) s.pct.value = mrp > 0 && num(s.sale) < mrp ? pctText((mrp - num(s.sale)) / mrp * 100) : '';
        if (s.costAnchor === 'cost' && s.buy) s.buy.value = mrp > 0 && num(s.cost) <= mrp ? pctText((mrp - num(s.cost)) / mrp * 100) : '';
    }

    function recalc(tr, changed) {
        const s = rowState(tr);
        if (changed === 'pct') s.saleAnchor = 'pct';
        if (changed === 'sale') s.saleAnchor = 'sale';
        if (changed === 'buy') s.costAnchor = 'buy';
        if (changed === 'cost') s.costAnchor = 'cost';
        const mrp = num(s.mrp);

        if (s.saleAnchor === 'pct' && changed !== 'sale') {
            const p = Math.min(100, Math.max(0, num(s.pct)));
            s.sale.value = mrp > 0 && p > 0 && s.pct.value !== '' ? r2(mrp * (1 - p / 100)) : '';
        } else if (s.saleAnchor === 'sale' && changed !== 'pct') {
            const sale = num(s.sale);
            s.pct.value = mrp > 0 && sale > 0 && sale < mrp ? pctText((mrp - sale) / mrp * 100) : '';
        }
        if (s.costAnchor === 'buy' && changed !== 'cost') {
            const b = Math.min(100, Math.max(0, num(s.buy)));
            s.cost.value = mrp > 0 && s.buy.value !== '' ? r2(mrp * (1 - b / 100)) : '';
        } else if (s.costAnchor === 'cost' && changed !== 'buy') {
            const cost = num(s.cost);
            s.buy.value = mrp > 0 && cost > 0 && cost <= mrp ? pctText((mrp - cost) / mrp * 100) : '';
        }
        paint(tr);
    }

    function paint(tr) {
        const s = rowState(tr);
        const mrp = num(s.mrp), sale = num(s.sale), cost = num(s.cost);
        const badSale = sale > 0 && mrp > 0 && sale >= mrp;
        s.sale.classList.toggle('is-invalid', badSale);
        s.cost.classList.toggle('is-warn', cost > 0 && cost > (sale > 0 && !badSale ? sale : mrp));
        const sell = sale > 0 && !badSale ? sale : mrp;
        if (!s.out) return;
        if (sell > 0 && cost > 0) {
            const m = r2(sell - cost);
            s.out.textContent = (m < 0 ? '−' : '') + money(Math.abs(m)) + ' · ' + pctText(m / sell * 100) + '%';
            s.out.className = 'bpg-margin ' + (m < 0 ? 'is-loss' : 'is-gain');
        } else {
            s.out.textContent = '—';
            s.out.className = 'bpg-margin';
        }
    }

    function noteFor(grid) {
        const box = grid.closest('.bf-table-wrap')?.nextElementSibling;
        if (!box || !box.hasAttribute('data-bpg-note')) return;
        const lines = [];
        visibleRows(grid).forEach(tr => {
            const s = rowState(tr), label = tr.querySelector('.bpg-col-bind')?.textContent.trim() || '';
            const mrp = num(s.mrp), sale = num(s.sale), cost = num(s.cost);
            if (sale > 0 && mrp > 0 && sale >= mrp) lines.push('<span class="text-danger">' + label + ': price is not below the MRP, so no discount is saved.</span>');
            const sell = sale > 0 && sale < mrp ? sale : mrp;
            if (cost > 0 && sell > 0 && cost > sell) lines.push('<span class="text-danger">' + label + ': cost is above the selling price.</span>');
        });
        box.innerHTML = lines.join(' ');
    }

    const visibleRows = grid => Array.from(grid.querySelectorAll('tr[data-bpg-row]')).filter(tr => !tr.hidden);

    // Rows follow the binding select; hidden rows do not post anything.
    // The binding comes from a <select> or from a group of radio buttons.
    function bindingOf(grid) {
        const q = grid.dataset.bindingSelect;
        if (!q) return 'paperback';
        const els = document.querySelectorAll(q);
        if (els.length && els[0].type === 'radio') { const on = Array.from(els).find(r => r.checked); return on ? on.value : 'paperback'; }
        return els.length ? els[0].value : 'paperback';
    }

    function sync(grid) {
        const binding = bindingOf(grid);
        const want = ROWS_FOR[binding] || ['paperback'];
        const required = grid.dataset.required === '1';
        grid.querySelectorAll('tr[data-bpg-row]').forEach(tr => {
            const on = want.includes(tr.dataset.bpgRow);
            const wasHidden = tr.hidden;
            tr.hidden = !on;
            tr.querySelectorAll('input').forEach(i => { i.disabled = !on; });
            const mrp = rowState(tr).mrp;
            if (mrp) mrp.required = on && required;
            // A row appearing for a hardcover-only book with no hardcover price yet starts from the paperback MRP.
            if (on && wasHidden && tr.dataset.bpgRow === 'hardcover' && !num(mrp) && binding === 'hardcover') {
                const pb = grid.querySelector('tr[data-bpg-row="paperback"]');
                if (pb) {
                    const p = rowState(pb);
                    ['mrp', 'pct', 'sale', 'buy', 'cost'].forEach(k => { if (rowState(tr)[k] && p[k]) rowState(tr)[k].value = p[k].value; });
                    seed(tr);
                }
            }
        });
        grid.querySelectorAll('tr[data-bpg-row]').forEach(paint);
        noteFor(grid);
        if (typeof window.updateLiveMockupCard === 'function') { try { window.updateLiveMockupCard(); } catch (e) { /* preview only */ } }
    }

    function reset(grid) {
        grid.querySelectorAll('tr[data-bpg-row]').forEach(tr => {
            tr.querySelectorAll('input').forEach(i => { i.value = ''; i.classList.remove('is-invalid', 'is-warn'); });
            const s = rowState(tr); s.saleAnchor = s.costAnchor = null;
        });
        sync(grid);
    }

    function init(root) {
        (root || document).querySelectorAll('[data-bpg-grid]').forEach(grid => {
            if (grid._bpgReady) return;
            grid._bpgReady = true;
            grid.querySelectorAll('tr[data-bpg-row]').forEach(seed);
            grid.addEventListener('input', e => {
                const tr = e.target.closest('tr[data-bpg-row]');
                const field = e.target.dataset.bpg;
                if (!tr || !field) return;
                recalc(tr, field);
                noteFor(grid);
                if (typeof window.updateLiveMockupCard === 'function') { try { window.updateLiveMockupCard(); } catch (err) { /* preview only */ } }
            });
            if (grid.dataset.bindingSelect) document.querySelectorAll(grid.dataset.bindingSelect).forEach(el => el.addEventListener('change', () => sync(grid)));
            sync(grid);
        });
    }

    // After the boxes were filled from outside (e.g. Quick Edit opening a book): read them again as saved values.
    function load(grid) {
        grid.querySelectorAll('tr[data-bpg-row]').forEach(tr => { tr.hidden = true; tr.querySelectorAll('input').forEach(i => i.classList.remove('is-invalid', 'is-warn')); seed(tr); });
        sync(grid);
    }

    window.BookPriceGrid = { init, reset, sync, load };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => init()); else init();
})();
