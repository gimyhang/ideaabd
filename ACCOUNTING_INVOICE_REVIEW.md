# Accounting: Invoice ও Quotation পর্যালোচনা

পর্যালোচনার ঠিকানা: `http://127.0.0.1:8000/admin/accounting?stream=invoices`

## বর্তমান ডেটা ও হিসাব

- এই লোকাল ডেটাবেসে সক্রিয় ডকুমেন্ট ১টি: `IDEA-INV-20261002-001` — Invoice, Books category, ১টি বই।
- ইনভয়েস নম্বর ডুপ্লিকেট: **০**। একই invoice number একাধিকবার পাওয়া যায়নি।
- বইটির সংরক্ষিত হিসাব: নিয়মিত মূল্য ৳৩৮০, ছাড় ২১%, net unit price ৳২৯৯, quantity ১, line subtotal ৳২৯৯।
- ইনভয়েসে subtotal ও grand total ৳২৯৯; discount, tax, previous due ও paid ৳০; due ৳২৯৯; status `unpaid`। এই সংরক্ষিত grand total ও due পরস্পরের সঙ্গে মেলে।
- Accounting overview: turnover ৳৭৯৯ (invoice ৳২৯৯ + online order ৳৫০০), collected ৳০, due ৳৭৯৯, expense ৳০, net cash ৳০। প্রদর্শিত সংখ্যাগুলো এই breakdown-এর সঙ্গে মেলে।
- বর্তমানে invoice/quotation/challan/tender-এর বিভাগভিত্তিক তুলনা করার মতো ডেটা নেই: শুধু Books invoice আছে; Stationery, Printing, Other, Quotation, Challan ও Tender-এ রেকর্ড নেই। তাই অন্য ক্যাটাগরির হিসাব বাস্তব ডেটা দিয়ে নিশ্চিত করা যায়নি।

## পাওয়া সমস্যা ও ঝুঁকি

1. **লাইন-ছাড়ের শতাংশ ও মূল্য পুরোপুরি মেলে না।** ৳৩৮০ থেকে ২১% ছাড় হলে net মূল্য হওয়ার কথা ৳৩০০.২০, কিন্তু সংরক্ষিত net মূল্য ৳২৯৯। বর্তমানে ফর্ম net price থেকে discount percentage-কে পূর্ণ সংখ্যায় round করে; তাই প্রদর্শিত `21%` আসলে প্রায় `21.32%` ছাড়। মোট ৳২৯৯ net price ধরে হিসাব হয়েছে, কিন্তু ছাড়ের হার বিভ্রান্তিকর।
2. **Quotation/Tender subject বাধ্যতামূলক দেখালেও সার্ভারে ঐচ্ছিক।** ফর্মে subject-এর পাশে `*` আছে, কিন্তু backend validation-এ subject nullable। ফলে subject ছাড়াই quotation বা tender সেভ হতে পারে।
3. **Quotation/Tender-এর payment control লুকানো, নিষ্ক্রিয় নয়।** Invoice/Challan থেকে document type বদলালে hidden `paid_amount` জমা থাকতে পারে; backend সেটি গ্রহণ করে quotation/tender-এ `paid`/`partial` status দিতে পারে, অথচ প্রাথমিক payment ledger entry শুধু Invoice/Challan-এর জন্য তৈরি হয়। এতে document status ও প্রকৃত আদায়ের হিসাব আলাদা হওয়ার ঝুঁকি আছে।
4. **Previous due সব document type-এর জন্য জমার সুযোগ আছে।** ফর্মের ব্যাখ্যায় এটি invoice/challan-এর কথা বললেও Quotation/Tender-এও field ও backend calculation প্রযোজ্য; প্রস্তাবিত quotation মোটে পুরনো বকেয়া যোগ হয়ে যেতে পারে।
5. **Sales category বদলালে বিদ্যমান item type বদলায় না।** নতুন ফর্মের প্রথম row `Book (Hardcover)` দিয়ে শুরু হয়; category `Stationery`, `Printing` বা `Other` করলেও row-এর type/unit স্বয়ংক্রিয়ভাবে বদলায় না। item service type দেখে book-link নির্ধারণ করে—ভুল type রেখে দিলে category ও item classification-এ অমিল হতে পারে।
6. **Order sync-এর duplicate guard কেবল reference নম্বরের উপর নির্ভরশীল।** Accounting hub যেকোনো type-এর document-এর `reference_no`-কে synced order ধরে online order বাদ দেয়; `reference_no`-তে database uniqueness নেই। কোনো Quotation/Tender-এর reference online order number-এর সঙ্গে মিলে গেলে order turnover থেকে বাদ পড়তে পারে, অথচ Quotation/Tender মোট invoiced turnover-এ যোগ হয় না। বর্তমান একমাত্র invoice-এ এমন reference নেই।
7. **Invoice stream-এ KPI scope অস্পষ্ট।** তালিকায় একটি invoice থাকলেও ওপরের turnover-এ online order-ও যোগ হচ্ছে। এটি global accounting summary, কিন্তু বর্তমান filter/tab অনুযায়ী KPI সীমিত নয়—লেবেল বা scope আরও স্পষ্ট হওয়া দরকার।

## আধুনিক ডিজাইনের জন্য করণীয়

- Invoice, Quotation, Challan ও Tender-কে আলাদা tab/workflow-তে সাজিয়ে প্রতিটির প্রাসঙ্গিক field-ই দেখান; document type বদলালে অপ্রযোজ্য field disable/clear-ও করুন।
- Quotation/Tender-এ subject ও validity date বাধ্যতামূলক করুন; invoice/challan-এ payment, due date ও previous due রাখুন।
- Sales category অনুযায়ী line-item-এর default type, unit ও lookup সীমাবদ্ধ করুন; category/type mismatch হলে save আটকান বা স্পষ্ট সতর্কতা দিন।
- ছাড়ের জন্য net price বা percentage—একটি authoritative input নির্ধারণ করুন; percentage দেখালে অন্তত ২ decimal-এ সঠিক হার দেখান, অথবা ছাড়ের টাকার অঙ্ক দেখান।
- Order sync-কে শুধু বৈধ online-order reference-সহ Invoice-এ সীমাবদ্ধ করুন; একই order-এ দ্বিতীয় invoice ঠেকাতে database-level uniqueness/transactional guard দিন।
- Discount subtotal ছাড়িয়ে গেলে ও paid amount grand total ছাড়ালে validation error দিন; quote/tender-এ payment গ্রহণ বন্ধ রাখুন।
- Category, document type, payment status ও সময়সীমা অনুযায়ী পরিসংখ্যান দিন; global KPI হলে “সব stream”/“সর্বমোট” বলে scope লিখুন।
- লম্বা, ১১০০px-সর্বনিম্ন-প্রস্থের তালিকা ছোট পর্দায় responsive card/list-এ রূপান্তর করুন; sticky filter bar, সংক্ষিপ্ত action menu, সামঞ্জস্যপূর্ণ typography/color এবং স্পষ্ট empty/loading/error state যোগ করুন।

## কোডে প্রাসঙ্গিক স্থান

- `app/Http/Controllers/Admin/IdeaAccountingController.php`
- `app/Services/IdeaInvoiceItemService.php`
- `app/Models/IdeaInvoice.php`
- `resources/views/admin/accounting/invoices/create.blade.php`
- `resources/views/admin/accounting/invoices/index.blade.php`
- `resources/views/admin/accounting/index.blade.php`

এটি পর্যালোচনা নোট; কোনো accounting record বা application code পরিবর্তন করা হয়নি।
