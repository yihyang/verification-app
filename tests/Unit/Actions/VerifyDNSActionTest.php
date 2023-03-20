<?php

namespace Tests\Unit\Actions;

use App\Actions\VerifyDNSAction;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VerifyDNSActionTest extends TestCase
{
    public function test_it_should_return_false_when_there_invalid_key_is_provided()
    {
        $this->setupFakeHttpRequest();

        $this->assertFalse(
            (new VerifyDNSAction(
                'DNS-DID',
                'testingdomain.com',
                'INVALID_KEY'
            ))->verify()
        );
    }

    public function test_it_should_return_true_when_there_valid_key_is_provided()
    {
        $this->setupFakeHttpRequest();

        $this->assertTrue(
                (
                    new VerifyDNSAction(
                    'DNS-DID',
                    'testingdomain.com',
                    'VALID_KEY_1'
                    )
                )->verify()
        );
    }

    private function setupFakeHttpRequest()
    {
        Http::preventStrayRequests();

        // return fake data for testing purpose
        Http::fake([
            'https://dns.google/resolve?name=testingdomain.com&type=TXT' => Http::response(
                [
                    'Answer' => [
                        [
                            'name' => 'testingdomain.com',
                            'type' => 16,
                            'TTL' => 300,
                            'data' => 'openatts a=dns-did; p=VALID_KEY_1; v=1.0;;',
                        ],
                        [
                            'name' => 'testingdomain.com',
                            'type' => 16,
                            'TTL' => 300,
                            'data' => 'openatts a=dns-did; p=VALID_KEY_2; v=1.0;;',
                        ],
                    ],
                ]
            )
        ]);
    }
}
