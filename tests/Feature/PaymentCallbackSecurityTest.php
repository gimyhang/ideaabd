<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** A visitor must not be able to mark an order paid by typing the gateway's "success" URL. */
class PaymentCallbackSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function order(): Order
    {
        $o = new Order();
        $o->forceFill(['order_number' => 'ORD-SEC-1', 'total_amount' => 500, 'payment_status' => 'unpaid', 'status' => 'pending', 'customer_name' => 'T', 'customer_phone' => '01700000001', 'customer_address' => 'Dhaka'])->save();

        return $o;
    }

    public function test_forged_sslcommerz_success_does_not_mark_the_order_paid(): void
    {
        Http::fake(['*sslcommerz.com/*' => Http::response(['status' => 'INVALID_TRANSACTION'], 200)]);
        $order = $this->order();

        $this->post('/payment/sslcommerz/success', ['tran_id' => 'ORD-SEC-1', 'val_id' => 'fake', 'amount' => '500'])->assertRedirect(route('payment.fail'));
        $this->assertSame('unpaid', $order->fresh()->payment_status);

        // no validation id at all
        $this->post('/payment/sslcommerz/success', ['tran_id' => 'ORD-SEC-1'])->assertRedirect(route('payment.fail'));
        $this->assertSame('unpaid', $order->fresh()->payment_status);
    }

    public function test_forged_ipn_is_ignored(): void
    {
        Http::fake(['*sslcommerz.com/*' => Http::response(['status' => 'FAILED'], 200)]);
        $order = $this->order();

        $this->post('/payment/sslcommerz/ipn', ['tran_id' => 'ORD-SEC-1', 'status' => 'VALID', 'val_id' => 'x'])->assertOk();
        $this->assertSame('unpaid', $order->fresh()->payment_status);
    }

    public function test_a_fail_request_cannot_undo_a_confirmed_payment(): void
    {
        $order = $this->order();
        $order->forceFill(['payment_status' => 'paid'])->save();

        $this->post('/payment/sslcommerz/fail', ['tran_id' => 'ORD-SEC-1']);
        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_nagad_redirect_parks_the_order_instead_of_marking_it_paid(): void
    {
        $order = $this->order();

        $this->get('/payment/nagad/callback?status_code=00_0000&order_id=ORD-SEC-1&payment_ref_id=REF1');
        $this->post('/payment/nagad/callback', ['status_code' => '00_0000', 'order_id' => 'ORD-SEC-1', 'payment_ref_id' => 'REF1']);

        $this->assertNotSame('paid', $order->fresh()->payment_status);
    }
}
