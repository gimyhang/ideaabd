<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** Pages that used to answer 500 because of a wrong layout or route name. */
class PageRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_result_pages_render(): void
    {
        foreach (['/payment/success', '/payment/fail', '/api/payment/success', '/api/payment/fail', '/api/payment/cancel'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_my_account_opens_for_a_buyer_with_an_order_and_links_to_tracking(): void
    {
        $user = User::create([
            'name' => 'Buyer', 'email' => 'regress-buyer@example.test', 'phone' => '01711000997',
            'password' => Hash::make('Zx9#Tq!mW4$vLr7'), 'role' => 'buyer', 'reg_type' => 'customer',
            'reg_status' => User::STATUS_APPROVED, 'is_active' => true,
        ]);
        Order::create([
            'order_number' => 'IDP-REG-1', 'user_id' => $user->id, 'customer_name' => 'Buyer', 'customer_phone' => '01711000997',
            'customer_address' => 'Addr', 'total_amount' => 100, 'status' => 'pending',
        ]);

        $this->actingAs($user)->get('/my-account')->assertOk()->assertSee('/track-order?order_number=IDP-REG-1', false);
    }

    public function test_tracking_form_is_prefilled_from_the_link(): void
    {
        $this->get('/track-order?order_number=IDP-REG-1')->assertOk()->assertSee('value="IDP-REG-1"', false);
    }
}
