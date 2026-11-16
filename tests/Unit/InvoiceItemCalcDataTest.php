<?php

namespace Tests\Unit;

use App\Services\IdeaInvoiceItemService;
use PHPUnit\Framework\TestCase;

/**
 * The print-cost calculator posts a JSON snapshot with every line it creates. It must survive
 * item processing so that "recalculate" can reopen the exact job after the invoice is saved.
 */
class InvoiceItemCalcDataTest extends TestCase
{
    private function line(array $over = []): array
    {
        return array_merge([
            'title' => 'Poster Printing',
            'item_type' => 'Printing & Binding',
            'unit' => 'Pcs',
            'quantity' => 100,
            'price' => 12.5,
            'regular_price' => 12.5,
        ], $over);
    }

    public function test_calc_snapshot_json_string_is_kept_as_an_array(): void
    {
        $snapshot = ['type' => 'poster', 'qty' => '100', 'colors' => '4', 'rows' => [['g' => 'Press', 'a' => 'Offset 4-Color']]];

        $result = IdeaInvoiceItemService::processItems([$this->line(['calc_data' => json_encode($snapshot)])], 'printing_goods');

        $this->assertSame($snapshot, $result['items'][0]['calc_data']);
        $this->assertSame(1250.0, $result['subtotal']);
    }

    public function test_missing_or_invalid_calc_data_is_not_stored(): void
    {
        foreach ([null, '', '   ', 'not json', '[]', '"text"'] as $bad) {
            $result = IdeaInvoiceItemService::processItems([$this->line(['calc_data' => $bad])], 'printing_goods');
            $this->assertArrayNotHasKey('calc_data', $result['items'][0], 'bad calc_data should be dropped: ' . var_export($bad, true));
        }

        $result = IdeaInvoiceItemService::processItems([$this->line()], 'printing_goods');
        $this->assertArrayNotHasKey('calc_data', $result['items'][0]);
    }

    public function test_oversized_calc_data_is_dropped(): void
    {
        $huge = json_encode(['blob' => str_repeat('x', 70000)]);

        $result = IdeaInvoiceItemService::processItems([$this->line(['calc_data' => $huge])], 'printing_goods');

        $this->assertArrayNotHasKey('calc_data', $result['items'][0]);
    }
}
