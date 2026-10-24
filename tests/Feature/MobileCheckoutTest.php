<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Book\Models\Book;
use Tests\TestCase;

class MobileCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function book(array $over = []): Book
    {
        return Book::forceCreate(array_merge([
            'title' => 'Order Test Book', 'slug' => 'order-test-book', 'price' => 400, 'discount_price' => 300,
            'hardcover_price' => 700, 'hardcover_discount_price' => 600, 'is_active' => true, 'stock_quantity' => 10,
        ], $over));
    }

    private function order(array $over = [], array $items = null): \Illuminate\Testing\TestResponse
    {
        $book = Book::where('slug', 'order-test-book')->first() ?? $this->book();      // the catalogue refuses a second book with the same title
        $items ??= [['id' => $book->id, 'title' => $book->title, 'price' => 1, 'quantity' => 2, 'format' => 'paperback']];

        return $this->postJson(route('cart.checkout'), array_merge([
            'customer_name' => 'Mobile Buyer', 'customer_phone' => '01712345678', 'customer_address' => 'Rangpur, Bangladesh',
            'district' => 'outside', 'payment_method' => 'cod', 'cart_items' => json_encode($items),
        ], $over));
    }

    public function test_a_guest_can_order_from_a_phone_and_the_server_prices_the_cart(): void
    {
        $this->order()->assertOk()->assertJson(['success' => true]);

        $order = Order::firstOrFail();
        $this->assertSame(2, (int) $order->quantity);
        $this->assertSame('300.00', number_format((float) $order->unit_price, 2, '.', ''));   // the DB price, not the 1 the browser sent
    }

    public function test_a_phone_number_typed_with_a_bangla_keyboard_or_country_code_is_accepted(): void
    {
        $this->order(['customer_phone' => '০১৭১২৩৪৫৬৭৮'])->assertOk()->assertJson(['success' => true]);
        $this->assertSame('01712345678', Order::firstOrFail()->customer_phone);

        $this->order(['customer_phone' => '+880 1712-345678'])->assertOk();
        $this->assertSame('01712345678', Order::orderByDesc('id')->first()->customer_phone);
    }

    public function test_a_wrong_phone_number_gets_a_readable_message(): void
    {
        $this->order(['customer_phone' => 'abc'])->assertUnprocessable()
            ->assertJsonValidationErrors('customer_phone')
            ->assertJsonPath('errors.customer_phone.0', 'মোবাইল নম্বরটি সঠিক নয়। ১১ ডিজিটের নম্বর দিন (যেমন 01XXXXXXXXX)।');
        $this->assertSame(0, Order::count());
    }

    public function test_the_hardcover_choice_is_priced_as_hardcover(): void
    {
        $book = $this->book(['slug' => 'hc-book', 'title' => 'HC Book']);
        $this->order([], [['id' => $book->id, 'title' => $book->title, 'price' => 300, 'quantity' => 1, 'format' => 'hardcover']])->assertOk();

        $this->assertSame('600.00', number_format((float) Order::firstOrFail()->unit_price, 2, '.', ''));
    }

    public function test_the_cart_drawer_keeps_type_and_format_and_shows_real_errors(): void
    {
        $drawer = file_get_contents(resource_path('views/partials/cart-drawer.blade.php'));
        $header = file_get_contents(resource_path('views/layouts/header.blade.php'));

        $this->assertStringContainsString("type: item.type || ''", $drawer);                 // gadgets are no longer ordered as books
        $this->assertStringContainsString('id="drawerOrderError"', $drawer);
        $this->assertStringContainsString('inputmode="numeric"', $drawer);
        $this->assertStringContainsString('res.status === 419', $drawer);
        $this->assertStringContainsString('openCartDrawer({ checkout: true })', $header);   // "buy now" opens the order form
        $this->assertStringContainsString("typeof currentSelectedFormat", $header);         // hardcover / paperback travels with the item
    }
    public function test_a_signed_in_customer_with_nothing_verified_can_still_order_quickly(): void
    {
        $user = \App\Models\User::create([
            'name' => 'New Buyer', 'email' => 'newbuyer@example.test', 'phone' => '01710000033',
            'password' => \Illuminate\Support\Facades\Hash::make('test-password'), 'role' => 'buyer', 'reg_type' => 'customer',
            'reg_status' => 'approved', 'is_active' => true,
        ]);
        $this->assertFalse($user->isPhoneVerified() && $user->isEmailVerified());

        $this->actingAs($user)->order()->assertOk()->assertJson(['success' => true]);
        $this->assertSame($user->id, Order::firstOrFail()->user_id);
    }

    public function test_tracking_asks_for_the_order_number_and_the_phone_used_on_the_order(): void
    {
        $this->order()->assertOk();
        $order = Order::firstOrFail();

        $this->get(route('order.track'))->assertOk()->assertSee('অর্ডার ট্র্যাকিং');

        $this->post(route('order.track.lookup'), ['order_number' => $order->order_number, 'phone' => '০১৭১২৩৪৫৬৭৮'])
            ->assertOk()->assertSee($order->order_number)->assertSee('অর্ডার গৃহীত');

        // wrong phone and unknown number get the same refusal
        $this->from(route('order.track'))->post(route('order.track.lookup'), ['order_number' => $order->order_number, 'phone' => '01799999999'])
            ->assertSessionHasErrors('order_number');
        $this->from(route('order.track'))->post(route('order.track.lookup'), ['order_number' => 'IDP-0000-0000', 'phone' => '01712345678'])
            ->assertSessionHasErrors('order_number');
    }
    public function test_a_quick_order_asks_for_exactly_name_phone_and_address(): void
    {
        // each of the three is required, nothing else is
        foreach (['customer_name', 'customer_phone', 'customer_address'] as $field) {
            $this->order([$field => ''])->assertUnprocessable()->assertJsonValidationErrors($field);
        }
        $this->assertSame(0, Order::count());
        $this->order()->assertOk()->assertJson(['success' => true]);

        $drawer = file_get_contents(resource_path('views/partials/cart-drawer.blade.php'));
        $this->assertStringContainsString('placeholder="আপনার নাম" required>', $drawer);
        $this->assertStringContainsString('placeholder="01XXXXXXXXX" required>', $drawer);
        $this->assertStringContainsString('placeholder="গ্রাম/এলাকা, থানা, জেলা" required>', $drawer);
        $this->assertStringContainsString('<details', $drawer);                                // online payment stays optional, behind one tap
        $this->assertStringContainsString('id="drawerPaymentMethod"', $drawer);                // cash on delivery by default
        $this->assertStringContainsString('idea_quick_customer', $drawer);                     // the next order is pre-filled
    }
}