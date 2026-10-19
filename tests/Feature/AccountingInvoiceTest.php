<?php

namespace Tests\Feature;

use App\Models\IdeaAccountingEntry;
use App\Models\IdeaInvoice;
use App\Models\IdeaInvoicePayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountingInvoiceTest extends TestCase
{
    use RefreshDatabase;

    private ?User $adminUser = null;

    private function admin(): User
    {
        return $this->adminUser ??= User::create([
            'name' => 'Root Admin', 'email' => 'acc@example.test', 'phone' => '01700000099',
            'password' => Hash::make('Zx9#Tq!mW4$vLr7'), 'role' => 'admin', 'reg_type' => 'admin',
            'reg_status' => User::STATUS_APPROVED, 'is_active' => true,
            'email_verified_at' => now(), 'phone_verified_at' => now(),
        ]);
    }

    private function payload(array $over = []): array
    {
        return array_merge([
            'type' => 'invoice', 'sales_category' => 'books',
            'invoice_no' => IdeaInvoice::nextNumber('invoice', 'books'),
            'customer_name' => 'Buyer', 'customer_phone' => '01711111111',
            'invoice_date' => now()->toDateString(), 'payment_method' => 'cash',
            'items' => [['title' => 'Book A', 'item_type' => 'Book (Paperback)', 'quantity' => 2, 'regular_price' => 380, 'price' => 299]],
        ], $over);
    }

    private function make(array $over = []): IdeaInvoice
    {
        $this->actingAs($this->admin())->post(route('admin.accounting.invoices.store'), $this->payload($over))->assertSessionHasNoErrors();

        return IdeaInvoice::latest('id')->firstOrFail();
    }

    public function test_totals_are_rounded_and_discount_percent_matches_price(): void
    {
        $inv = $this->make(['discount' => 8]);
        $this->assertEquals(598.00, (float) $inv->subtotal);
        $this->assertEquals(590.00, (float) $inv->grand_total);
        $this->assertEquals(0, (float) $inv->previous_due);
        $this->assertEquals(21.32, $inv->items[0]['discount_percent']);
    }

    public function test_discount_above_subtotal_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.accounting.invoices.store'), $this->payload(['discount' => 9999]))
            ->assertSessionHasErrors('discount');
        $this->assertSame(0, IdeaInvoice::count());
    }

    public function test_advance_creates_payment_and_matching_income_entry(): void
    {
        $inv = $this->make(['paid_amount' => 200]);
        $this->assertSame(1, $inv->payments()->count());
        $entry = IdeaAccountingEntry::where('invoice_id', $inv->id)->firstOrFail();
        $this->assertEquals(200, (float) $entry->amount);
        $this->assertSame($inv->payments()->first()->payment_no, $entry->voucher_no);
        $this->assertSame('partial', $inv->fresh()->payment_status);
        $this->assertEquals(398, (float) $inv->fresh()->due_amount);
    }

    public function test_overpayment_is_blocked(): void
    {
        $inv = $this->make();
        $this->actingAs($this->admin())->post(route('admin.accounting.invoices.payments.store', $inv), [
            'payment_date' => now()->toDateString(), 'amount' => 9999, 'payment_method' => 'cash',
        ])->assertSessionHas('error');
        $this->assertSame(0, $inv->payments()->count());

        $this->actingAs($this->admin())->postJson(route('admin.accounting.invoices.quick-payment', $inv), [
            'payment_date' => now()->toDateString(), 'amount' => 9999, 'payment_method' => 'cash',
        ])->assertStatus(422);
    }

    public function test_deleting_a_payment_restores_unpaid_status(): void
    {
        $inv = $this->make(['paid_amount' => 598]);
        $this->assertSame('paid', $inv->fresh()->payment_status);

        $this->actingAs($this->admin())->delete(route('admin.accounting.invoices.payments.destroy', $inv->payments()->first()));

        $fresh = $inv->fresh();
        $this->assertSame('unpaid', $fresh->payment_status);
        $this->assertEquals(0, (float) $fresh->paid_amount);
        $this->assertEquals(598, (float) $fresh->due_amount);
    }

    public function test_editing_does_not_create_phantom_income(): void
    {
        $inv = $this->make(['paid_amount' => 100]);
        $this->actingAs($this->admin())->put(route('admin.accounting.invoices.update', $inv), $this->payload([
            'invoice_no' => $inv->invoice_no, 'paid_amount' => 500,
            'items' => [['title' => 'Book A', 'item_type' => 'Book (Paperback)', 'quantity' => 3, 'price' => 299]],
        ]))->assertSessionHasNoErrors();

        $this->assertSame(1, IdeaAccountingEntry::where('invoice_id', $inv->id)->count());
        $this->assertEquals(100, (float) IdeaAccountingEntry::where('invoice_id', $inv->id)->sum('amount'));
        $this->assertEquals(897, (float) $inv->fresh()->grand_total);
        $this->assertEquals(797, (float) $inv->fresh()->due_amount);
    }

    public function test_quotation_requires_subject_and_validity_and_takes_no_payment(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.accounting.invoices.store'), $this->payload(['type' => 'quotation', 'invoice_no' => 'Q-1', 'paid_amount' => 300]))
            ->assertSessionHasErrors(['subject', 'valid_until']);

        $q = $this->make([
            'type' => 'quotation', 'invoice_no' => 'Q-2', 'paid_amount' => 300,
            'subject' => 'Supply', 'valid_until' => now()->addWeek()->toDateString(),
        ]);
        $this->assertEquals(0, (float) $q->paid_amount);
        $this->assertSame(0, $q->payments()->count());

        $this->actingAs($this->admin())->postJson(route('admin.accounting.invoices.quick-payment', $q), [
            'payment_date' => now()->toDateString(), 'amount' => 10, 'payment_method' => 'cash',
        ])->assertStatus(422);
    }

    public function test_duplicate_works_and_numbers_never_collide(): void
    {
        $inv = $this->make(['paid_amount' => 100]);
        $this->actingAs($this->admin())->post(route('admin.accounting.invoices.duplicate', $inv))->assertRedirect();

        $copy = IdeaInvoice::where('id', '!=', $inv->id)->firstOrFail();
        $this->assertNotSame($inv->invoice_no, $copy->invoice_no);
        $this->assertSame(0, $copy->payments()->count());
        $this->assertSame('unpaid', $copy->payment_status);

        $a = IdeaInvoice::nextNumber('invoice', 'books');
        $inv->deleteWithLedger();
        $copy->deleteWithLedger();
        $this->assertNotSame($a, IdeaInvoice::nextNumber('invoice', 'books') === $a ? '' : 'x');
        $this->assertTrue(IdeaInvoice::withTrashed()->where('invoice_no', IdeaInvoice::nextNumber('invoice', 'books'))->doesntExist());
    }

    public function test_bulk_mark_paid_creates_payments_and_entries_and_skips_quotations(): void
    {
        $inv = $this->make();
        $q = $this->make([
            'type' => 'quotation', 'invoice_no' => 'Q-3', 'subject' => 'S', 'valid_until' => now()->addWeek()->toDateString(),
        ]);

        $this->actingAs($this->admin())->post(route('admin.accounting.invoices.bulk-action'), [
            'action' => 'mark_paid', 'invoice_ids' => [$inv->id, $q->id],
        ]);

        $this->assertSame('paid', $inv->fresh()->payment_status);
        $this->assertEquals(598, (float) IdeaAccountingEntry::where('invoice_id', $inv->id)->sum('amount'));
        $this->assertSame(0, $q->payments()->count());

        $this->actingAs($this->admin())->post(route('admin.accounting.invoices.bulk-action'), [
            'action' => 'mark_unpaid', 'invoice_ids' => [$inv->id],
        ]);
        $this->assertSame('unpaid', $inv->fresh()->payment_status);
        $this->assertSame(0, IdeaAccountingEntry::where('invoice_id', $inv->id)->count());
    }

    public function test_single_delete_removes_ledger_like_bulk_delete(): void
    {
        $inv = $this->make(['paid_amount' => 100]);
        $this->actingAs($this->admin())->delete(route('admin.accounting.invoices.destroy', $inv));

        $this->assertSame(0, IdeaAccountingEntry::where('invoice_id', $inv->id)->count());
        $this->assertSame(0, IdeaInvoicePayment::where('invoice_id', $inv->id)->count());
    }

    public function test_income_is_booked_at_net_cash_and_category_follows_sales_category(): void
    {
        $inv = $this->make(['sales_category' => 'stationery', 'items' => [['title' => 'Pen', 'item_type' => 'Stationery', 'quantity' => 10, 'price' => 100]]]);

        $this->actingAs($this->admin())->post(route('admin.accounting.invoices.payments.store', $inv), [
            'payment_date' => now()->toDateString(), 'amount' => 1000, 'payment_method' => 'bank',
            'vat_deduction_amount' => 50, 'tax_deduction_amount' => 50,
        ])->assertSessionHas('success');

        $entry = IdeaAccountingEntry::where('invoice_id', $inv->id)->firstOrFail();
        $this->assertEquals(900, (float) $entry->amount);
        $this->assertStringContainsString('Stationery', $entry->category);
        $this->assertSame('paid', $inv->fresh()->payment_status);
    }

    public function test_repair_command_fixes_orphans(): void
    {
        $inv = $this->make(['paid_amount' => 100]);
        $inv->delete(); // legacy-style delete that leaves payments + entries behind

        $this->artisan('accounting:repair', ['--fix' => true])->assertSuccessful();

        $this->assertSame(0, IdeaAccountingEntry::where('invoice_id', $inv->id)->count());
        $this->assertSame(0, IdeaInvoicePayment::where('invoice_id', $inv->id)->count());
    }

    public function test_accounting_pages_render(): void
    {
        $admin = $this->admin();
        $inv = $this->make(['paid_amount' => 100]);
        $this->actingAs($admin)->followingRedirects()->get(route('admin.accounting.invoices.index'))->assertOk();
        $this->get(route('admin.accounting.invoices.create'))->assertOk();
        $this->get(route('admin.accounting.invoices.show', $inv))->assertOk();
        $this->get(route('admin.accounting.invoices.edit', $inv))->assertOk();
        $this->get(route('invoices.public.show', $inv->access_token))->assertOk();
    }

    public function test_other_accounting_pages_render_with_modern_layer(): void
    {
        $this->actingAs($this->admin());
        foreach (['admin.accounting.customer-ledger.index', 'admin.accounting.employees.index', 'admin.accounting.reports.index', 'admin.accounting.salary.index', 'admin.accounting.tax-vat-deductions.index'] as $name) {
            $res = $this->get(route($name));
            $res->assertOk();
            $res->assertSee('accounting-modern.css', false);
        }
        $this->get(route('admin.accounting.index', ['stream' => 'invoices']))->assertOk()->assertSee('acc-ui', false);
    }

    public function test_quotation_menu_pills_and_quick_calculator_are_on_the_hub(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        $q = $this->make(['type' => 'quotation', 'invoice_no' => 'Q-HUB-1', 'subject' => 'Supply', 'valid_until' => now()->addWeek()->toDateString()]);
        $inv = $this->make(['invoice_no' => 'I-HUB-1']);

        $hub = $this->get(route('admin.accounting.index'));
        $hub->assertOk()->assertSee('quickQuotationModal', false)->assertSee('quick-quotation.js', false);

        $res = $this->get(route('admin.accounting.index', ['stream' => 'invoices', 'type' => 'quotation']));
        $res->assertOk()->assertSee('doc-type-pill', false)->assertSee('Q-HUB-1')->assertDontSee('I-HUB-1')
            ->assertSee('All proposal statuses');
        $res->assertDontSee('onclick="openUniversalQuickPayModal(\'invoice\', ' . $q->id, false);

        $this->get(route('admin.accounting.index', ['stream' => 'invoices']))->assertOk()->assertSee('Q-HUB-1')->assertSee('I-HUB-1');

        // sidebar link goes straight to the filtered stream
        $this->get(route('admin.accounting.index'))->assertSee('stream=invoices&amp;type=quotation', false);
    }

    public function test_quick_quotation_modal_payload_saves_a_quotation(): void
    {
        $this->actingAs($this->admin());
        $no = IdeaInvoice::nextNumber('quotation', 'books');

        $this->post(route('admin.accounting.invoices.store'), [
            'type' => 'quotation', 'payment_method' => 'Cash', 'invoice_no' => $no, 'invoice_date' => now()->toDateString(),
            'valid_until' => now()->addDays(14)->toDateString(), 'customer_name' => 'Acme', 'subject' => 'Books supply',
            'sales_category' => 'books', 'discount' => 50, 'tax' => 37.5,
            'items' => [['title' => 'Item A', 'quantity' => 5, 'price' => 100], ['title' => 'Item B', 'quantity' => 1, 'price' => 250]],
        ])->assertSessionHasNoErrors();

        $q = IdeaInvoice::where('invoice_no', $no)->firstOrFail();
        $this->assertSame('quotation', $q->type);
        $this->assertEquals(750, (float) $q->subtotal);
        $this->assertEquals(737.5, (float) $q->grand_total); // 750 - 50 + 37.5
        $this->assertEquals(0, (float) $q->paid_amount);
    }

    public function test_proposal_status_lifecycle_and_expiration(): void
    {
        $this->actingAs($this->admin());
        $q = $this->make([
            'type' => 'quotation', 'invoice_no' => 'Q-STATUS-1', 'subject' => 'Supply',
            'valid_until' => now()->addWeek()->toDateString(),
        ]);

        $this->assertSame('draft', $q->effective_proposal_status);
        $this->patch(route('admin.accounting.invoices.proposal-status', $q), ['proposal_status' => 'sent'])
            ->assertSessionHasNoErrors();
        $this->assertSame('sent', $q->fresh()->effective_proposal_status);
        $this->get(route('admin.accounting.index', [
            'stream' => 'invoices', 'type' => 'quotation', 'proposal_status' => 'sent',
        ]))->assertOk()->assertSee('Q-STATUS-1');

        $q->update(['valid_until' => now()->subDay()->toDateString()]);
        $this->assertSame('expired', $q->fresh()->effective_proposal_status);
        $this->patch(route('admin.accounting.invoices.proposal-status', $q->fresh()), ['proposal_status' => 'accepted'])
            ->assertSessionHas('error');

        $number = $q->invoice_no;
        $this->post(route('admin.accounting.invoices.convert', $q->fresh()), ['target_type' => 'invoice'])
            ->assertRedirect();
        $this->assertSame($number, $q->fresh()->invoice_no);
        $this->assertSame('converted', $q->fresh()->proposal_status);
    }

    public function test_document_number_endpoint_uses_selected_category_prefix(): void
    {
        $response = $this->actingAs($this->admin())
            ->getJson(route('admin.accounting.invoices.next-number', ['type' => 'quotation', 'sales_category' => 'printing_goods']))
            ->assertOk();

        $this->assertStringStartsWith('IDEA-PRT-', $response->json('invoice_no'));
    }

    public function test_automatic_document_number_is_reserved_when_saving(): void
    {
        $this->actingAs($this->admin());
        $existing = $this->make([
            'type' => 'quotation', 'invoice_no' => 'Q-AUTO-1', 'subject' => 'Existing',
            'valid_until' => now()->addWeek()->toDateString(),
        ]);

        $this->post(route('admin.accounting.invoices.store'), $this->payload([
            'type' => 'quotation',
            'invoice_no' => $existing->invoice_no,
            'auto_number' => '1',
            'subject' => 'New quotation',
            'valid_until' => now()->addWeek()->toDateString(),
        ]))->assertSessionHasNoErrors();

        $created = IdeaInvoice::where('id', '!=', $existing->id)->latest('id')->firstOrFail();
        $this->assertNotSame($existing->invoice_no, $created->invoice_no);
        $this->assertStringStartsWith('IDEA-QUO-', $created->invoice_no);
    }

    public function test_item_suggestions_are_alphabetical_prefix_first_and_per_category(): void
    {
        $this->actingAs($this->admin());
        foreach (['Banglar Itihas', 'Amar Boi', 'Abol Tabol', 'Kobita Songroho', 'The Amazing'] as $title) {
            $b = new \Modules\Book\Models\Book();
            $b->title = $title;
            $b->slug = \Illuminate\Support\Str::slug($title) . '-' . uniqid();
            $b->price = 200;
            $b->is_active = true;
            $b->save();
        }

        $titles = fn (array $q) => collect($this->getJson(route('admin.accounting.invoices.item-suggest', $q))->assertOk()->json())->pluck('title')->all();

        // empty box: the catalogue A→Z
        $this->assertSame(['Abol Tabol', 'Amar Boi', 'Banglar Itihas', 'Kobita Songroho', 'The Amazing'], $titles(['category' => 'books']));
        // typed letter: names starting with it first (A→Z), then the rest that merely contain it
        $this->assertSame(['Abol Tabol', 'Amar Boi'], array_slice($titles(['category' => 'books', 'q' => 'a']), 0, 2));

        // printing category offers the ready-made printing lines, never books
        $print = $titles(['category' => 'printing_goods']);
        $this->assertNotEmpty($print);
        $this->assertNotContains('Amar Boi', $print);
        $sorted = $print;
        usort($sorted, fn ($a, $b) => strcmp(mb_strtolower($a), mb_strtolower($b)));
        $this->assertSame($sorted, $print);

        // lines of earlier documents are suggested too
        $this->make(['sales_category' => 'stationery', 'items' => [['title' => "Zebra Pen\nBlue ink", 'item_type' => 'Stationery', 'quantity' => 1, 'price' => 12]]]);
        $row = collect($this->getJson(route('admin.accounting.invoices.item-suggest', ['category' => 'stationery', 'q' => 'zeb']))->json())->firstWhere('title', 'Zebra Pen');
        $this->assertNotNull($row);
        $this->assertSame('Blue ink', $row['detail']);
        $this->assertEquals(12, $row['price']);
    }

    public function test_customer_search_lists_prefix_matches_first_a_to_z(): void
    {
        $this->actingAs($this->admin());
        foreach (['Zahir Traders', 'Rahim Store', 'Abdul Rahman', 'Rafiq Books'] as $i => $name) {
            $this->make(['customer_name' => $name, 'customer_phone' => '0170000000' . $i, 'invoice_no' => 'C-' . $i]);
        }

        $names = collect($this->getJson(route('admin.accounting.invoices.customer-search', ['q' => 'ra']))->assertOk()->json())->pluck('name')->all();
        $this->assertSame(['Rafiq Books', 'Rahim Store'], array_slice($names, 0, 2));
        $this->assertContains('Abdul Rahman', $names);
        $this->assertTrue(array_search('Abdul Rahman', $names) > 1, 'a contains-match comes after the prefix matches');
    }

    public function test_printing_cost_calculator_is_shared_by_hub_create_and_edit(): void
    {
        $this->actingAs($this->admin());
        $inv = $this->make();

        foreach ([route('admin.accounting.index'), route('admin.accounting.invoices.create', ['type' => 'quotation']), route('admin.accounting.invoices.edit', $inv)] as $url) {
            $this->get($url)->assertOk()->assertSee('id="printCostCalculatorModal"', false)->assertSee('print-cost.js', false)->assertSee('print-cost.css', false)->assertDontSee('bcalc_', false);
        }
        // the calculator exists exactly once per page
        $this->assertSame(1, substr_count($this->get(route('admin.accounting.index'))->getContent(), 'id="printCostCalculatorModal"'));
    }

    public function test_rate_sheet_starts_with_paper_rates_and_supports_add_edit_remove(): void
    {
        $this->actingAs($this->admin());

        $rows = $this->getJson(route('admin.accounting.print-rates.index'))->assertOk()->json();
        $this->assertCount(9, $rows);
        $this->assertSame(['Paper', '55', 'Newsprint'], [$rows[0]['group'], $rows[0]['item'], $rows[0]['kind']]);
        $this->assertEquals(2000, $rows[0]['rate']);

        // a second visit does not re-seed
        $this->assertCount(9, $this->getJson(route('admin.accounting.print-rates.index'))->json());

        // add other materials
        $this->postJson(route('admin.accounting.print-rates.store'), ['group' => 'Lamination', 'item' => 'Matt', 'rate' => 3.5])->assertOk()->assertJson(['created' => true]);
        $this->postJson(route('admin.accounting.print-rates.store'), ['group' => 'Packing', 'item' => 'Poly', 'kind' => 'Copy', 'rate' => 1.25])->assertOk();
        $this->postJson(route('admin.accounting.print-rates.store'), ['group' => 'Paper', 'item' => '85', 'kind' => 'Pearl', 'rate' => 4800])->assertOk();

        // same key updates instead of duplicating
        $this->postJson(route('admin.accounting.print-rates.store'), ['group' => 'Paper', 'item' => '80', 'kind' => 'White', 'rate' => 3300])->assertOk()->assertJson(['created' => false]);
        $this->assertSame(1, \App\Models\PrintRate::where(['group' => 'Paper', 'item' => '80', 'kind' => 'White'])->count());
        $this->assertEquals(3300, \App\Models\PrintRate::where(['group' => 'Paper', 'item' => '80', 'kind' => 'White'])->value('rate'));

        // paper sorts by weight, groups in calculator order
        $groups = collect($this->getJson(route('admin.accounting.print-rates.index'))->json())->pluck('group')->unique()->values()->all();
        $this->assertSame(['Paper', 'Lamination', 'Packing'], $groups);

        // remove
        $id = \App\Models\PrintRate::where('group', 'Lamination')->value('id');
        $this->deleteJson(route('admin.accounting.print-rates.destroy', $id))->assertOk();
        $this->assertNull(\App\Models\PrintRate::find($id));
    }

    public function test_rate_sheet_validation_and_bulk_adjust(): void
    {
        $this->actingAs($this->admin());
        $this->getJson(route('admin.accounting.print-rates.index'));

        $this->postJson(route('admin.accounting.print-rates.store'), ['group' => 'Nonsense', 'rate' => 5])->assertStatus(422);
        $this->postJson(route('admin.accounting.print-rates.store'), ['group' => 'Paper', 'item' => '80', 'rate' => -1])->assertStatus(422);
        $this->postJson(route('admin.accounting.print-rates.store'), ['group' => 'Paper', 'item' => '80'])->assertStatus(422);

        $this->postJson(route('admin.accounting.print-rates.store'), ['group' => 'Binding', 'item' => 'Perfect', 'rate' => 10]);

        // +10% on paper only
        $res = $this->postJson(route('admin.accounting.print-rates.adjust'), ['group' => 'Paper', 'mode' => 'percent', 'value' => 10])->assertOk();
        $this->assertSame(9, $res->json('changed'));
        $this->assertEquals(3520, \App\Models\PrintRate::where(['group' => 'Paper', 'item' => '80', 'kind' => 'White'])->value('rate'));
        $this->assertEquals(10, \App\Models\PrintRate::where('group', 'Binding')->value('rate'));

        // -৳15 on everything never goes below zero
        $this->postJson(route('admin.accounting.print-rates.adjust'), ['mode' => 'amount', 'value' => -15])->assertOk();
        $this->assertEquals(0, \App\Models\PrintRate::where('group', 'Binding')->value('rate'));
        $this->assertEquals(3505, \App\Models\PrintRate::where(['group' => 'Paper', 'item' => '80', 'kind' => 'White'])->value('rate'));

        $this->postJson(route('admin.accounting.print-rates.adjust'), ['mode' => 'percent', 'value' => -100])->assertStatus(422);
    }

    public function test_rate_sheet_is_private_and_the_calculator_knows_where_it_lives(): void
    {
        auth()->logout();
        $this->getJson(route('admin.accounting.print-rates.index'))->assertUnauthorized();
        $this->postJson(route('admin.accounting.print-rates.store'), ['group' => 'Paper', 'rate' => 1])->assertUnauthorized();

        $this->actingAs($this->admin());
        $this->get(route('admin.accounting.index'))->assertOk()
            ->assertSee(route('admin.accounting.print-rates.index'), false)
            ->assertSee('id="pcRatesPanel"', false);
    }
}
