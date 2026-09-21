/**
 * Invoice Dynamic Engine & Item Manager
 * Idea Enterprise Accounting
 */

(function(window) {
    'use strict';

    const InvoiceManager = {
        bookKeywords: ['book', 'hardcover', 'paperback', 'standard', 'novel', 'publication'],
        
        isBookType: function(typeVal) {
            if (!typeVal) return true;
            const lower = String(typeVal).toLowerCase().trim();
            const nonBook = ['stationery', 'printing', 'paper', 'service', 'product', 'other', 'bill', 'raw'];
            for (let i = 0; i < nonBook.length; i++) {
                if (lower.indexOf(nonBook[i]) !== -1) return false;
            }
            for (let j = 0; j < this.bookKeywords.length; j++) {
                if (lower.indexOf(this.bookKeywords[j]) !== -1) return true;
            }
            return false;
        },

        getDefaultUnit: function(typeVal, category) {
            if (!typeVal) return (category === 'books' || !category) ? 'Copy' : 'Pcs';
            const lower = String(typeVal).toLowerCase().trim();
            if (lower.indexOf('paper') !== -1 || lower.indexOf('ream') !== -1) return 'Ream';
            if (lower.indexOf('book') !== -1) return 'Copy';
            if (lower.indexOf('service') !== -1) return 'Item';
            if (lower.indexOf('printing') !== -1) return 'Copy';
            return 'Pcs';
        },

        formatMoney: function(num) {
            return '৳' + Number(num || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        calculateRow: function(qty, regularPrice, discountPct, unitPrice, changedField) {
            let q = parseFloat(qty) || 0;
            let reg = parseFloat(regularPrice) || 0;
            let disc = parseFloat(discountPct) || 0;
            let net = parseFloat(unitPrice) || 0;

            if (changedField === 'regular_price' || changedField === 'discount_percent') {
                if (reg > 0 && disc >= 0) {
                    net = Math.max(0, reg - (reg * (disc / 100)));
                } else if (reg > 0 && disc === 0) {
                    net = reg;
                }
            } else if (changedField === 'unit_price') {
                if (reg > 0 && reg > net) {
                    disc = Math.round(((reg - net) / reg) * 10000) / 100;
                } else if (reg === 0) {
                    reg = net;
                    disc = 0;
                }
            }

            const subtotal = Math.max(0, q * net);

            return {
                quantity: q,
                regular_price: reg,
                discount_percent: disc,
                unit_price: net,
                subtotal: subtotal
            };
        }
    };

    window.InvoiceManager = InvoiceManager;
})(window);
