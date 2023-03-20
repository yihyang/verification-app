<?php

namespace Tests\Unit\Actions\Actions;

use App\Actions\GetDNSTxtRecordsAction;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GetDNSTxtRecordsActionTest extends TestCase
{
    public function test_it_should_return_expected_result()
    {
        $location = 'testingdomain.com';

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
                            'data' => 'openatts a=dns-did; p=did:ethr:0x05b642ff12a4ae545357d82ba4f786f3aed84214#controller; v=1.0;',
                        ],
                        [
                            'name' => 'testingdomain.com',
                            'type' => 16,
                            'TTL' => 300,
                            'data' => 'openatts a=dns-did; p=did:ethr:0x06a464971ea723177ef83df7b39dd63c373a6905#controller; v=1.0;;',
                        ],
                    ],
                ]
            )
        ]);

        $this->assertEquals(
            (new GetDNSTxtRecordsAction($location))->getRecords(),
            [

                [
                    'a' => 'dns-did',
                    'p' => 'did:ethr:0x05b642ff12a4ae545357d82ba4f786f3aed84214#controller',
                    'v' => '1.0',
                ],
                [
                    'a' => 'dns-did',
                    'p' => 'did:ethr:0x06a464971ea723177ef83df7b39dd63c373a6905#controller',
                    'v' => '1.0',
                ],
            ]
        );
    }
}
