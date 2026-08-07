@component('mail::message')
# আপনার অর্ডার গ্রহণ হয়েছে

ধন্যবাদ {{ $customer_name ?? 'গ্রাহক' }}, আপনার অর্ডার (অর্ডার নং: {{ $order_number ?? '—' }}) গ্রহণ করা হয়েছে। নিচে অর্ডারের সংক্ষিপ্ত বিবরণ দেওয়া হলো:

@component('mail::table')
| আইটেম | পরিমাণ | মূল্য |
| :--- | :---: | ---: |
@foreach($items as $item)
| {{ $item['title'] }} | {{ $item['quantity'] }} | {{ $item['total'] }} |
@endforeach
@endcomponent

**মোট:** {{ $total ?? '৳ 0.00' }}

আপনি যদি অর্ডারে কোনো পরিবর্তন চান, অনুগ্রহ করে আমাদের সাপোর্ট-টিমের সাথে যোগাযোগ করুন।

Thanks,

{{ config('app.name') }} ٹیم
@endcomponent
