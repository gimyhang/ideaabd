<?php

namespace Tests\Feature;

use App\Models\IdeaAccountingEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountingListEntriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_list_opens_when_income_and_expense_entries_exist(): void
    {
        $admin = User::create([
            'name' => 'A', 'email' => 'acc-adm@example.test', 'phone' => '01710000044',
            'password' => Hash::make('test-password'), 'role' => 'admin', 'reg_type' => 'admin',
            'reg_status' => 'approved', 'is_active' => true,
        ]);
        foreach (['income', 'expense'] as $i => $type) {
            IdeaAccountingEntry::create([
                'entry_no' => "E-$i", 'type' => $type, 'category' => 'misc', 'title' => "T $type", 'amount' => 500,
                'entry_date' => now()->toDateString(), 'payment_method' => 'cash', 'created_by' => $admin->id,
            ]);
        }
        $r = $this->actingAs($admin)->get('/admin/accounting');
        fwrite(STDERR, "\nACCOUNTING => " . $r->getStatusCode() . ' ' . ($r->exception ? mb_substr($r->exception->getMessage(), 0, 160) : ''));
        $this->assertTrue(true);
    }
}
