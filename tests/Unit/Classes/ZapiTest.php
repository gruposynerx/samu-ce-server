<?php

namespace Tests\Unit\Classes;

use App\Classes\Zapi;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ZapiTest extends TestCase
{
    public function test_send_text_message()
    {
        // Arrange
        $apiBaseUrl = config('external_services.zapi_url') . '/send-text';

        Http::fake([
            $apiBaseUrl => Http::response([
                'zaapId' => '3999984263738042930CD6ECDE9VDWSA',
                'messageId' => 'D241XXXX732339502B68',
                'id' => 'D241XXXX732339502B68',
            ]),
        ]);

        // Act
        $response = app(Zapi::class)->setPhones('85992725107')->sendTextMessage('teste zap');

        // Assert
        $this->assertInstanceOf(Collection::class, $response);
        $this->assertCount(1, $response);

        $this->assertSame('3999984263738042930CD6ECDE9VDWSA', $response->first()->apiId);
        $this->assertSame('D241XXXX732339502B68', $response->first()->messageId);
        $this->assertSame('D241XXXX732339502B68', $response->first()->id);

        Http::assertSent(function ($request) use ($apiBaseUrl) {
            return $request->url() === $apiBaseUrl &&
                   $request['phone'] == '85992725107' &&
                   $request['message'] == 'teste zap';
        });
    }
}
