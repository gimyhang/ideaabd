# Localhost সমস্যা নোট

পরীক্ষার ঠিকানা: `http://127.0.0.1:8000/`

- হোমপেজ `200 OK` দিয়ে খুলেছে; কোনো Laravel error page দেখা যায়নি।
- Git-এ unresolved merge conflict পাওয়া যায়নি।
- ব্রাউজারে `fonts.maateen.me` থেকে Nikosh WOFF2 ও TTF ফন্ট decode করতে ব্যর্থ হয়েছে; OTS parsing warning-ও এসেছে। বাংলা ফন্ট fallback-এ দেখাতে পারে।
- তিনটি resource request-এ `ERR_CONNECTION_REFUSED` এসেছে। সংক্ষিপ্ত ব্রাউজার লগে resource-এর URL শনাক্ত করা যায়নি।
- `git diff --check`-এ trailing whitespace পাওয়া গেছে:
  - `resources/views/admin/event_campaigns/index.blade.php`: 38, 320, 336–337, 548, 744
  - `resources/views/admin/event_campaigns/partials/form_studio.blade.php`: 22–23, 26–27, 30–31
  - `resources/views/frontend/products/index.blade.php`: 18

নোট: পরীক্ষার সময় বিদ্যমান ওয়ার্কট্রির পরিবর্তনগুলো অপরিবর্তিত রাখা হয়েছে।
