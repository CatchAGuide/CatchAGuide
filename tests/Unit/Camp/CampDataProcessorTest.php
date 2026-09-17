<?php

namespace Tests\Unit\Camp;

use App\Services\Camp\CampDataProcessor;
use Illuminate\Http\Request;
use Tests\TestCase;

class CampDataProcessorTest extends TestCase
{
    public function test_target_fish_csv_is_stored_as_an_array(): void
    {
        $data = $this->processor()->processRequestData(Request::create('/admin/camps', 'POST', [
            'title' => 'Camp',
            'target_fish' => 'Flussbarsch,Hecht,Äsche',
        ]));

        $this->assertSame(['Flussbarsch', 'Hecht', 'Äsche'], $data['target_fish']);
    }

    public function test_target_fish_tagify_json_prefers_ids(): void
    {
        $data = $this->processor()->processRequestData(Request::create('/admin/camps', 'POST', [
            'title' => 'Camp',
            'target_fish' => json_encode([
                ['id' => 12, 'value' => 'Flussbarsch'],
                ['value' => 'Custom carp'],
            ]),
        ]));

        $this->assertSame([12, 'Custom carp'], $data['target_fish']);
    }

    private function processor(): CampDataProcessor
    {
        return new CampDataProcessor();
    }
}
