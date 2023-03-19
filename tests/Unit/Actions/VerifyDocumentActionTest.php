<?php

namespace Tests\Unit\Actions;

use App\Actions\VerifyDocumentAction;
use Mockery;
use Tests\TestCase;


class VerifyDocumentActionTest extends TestCase
{

    // verify recipients
    public function test_it_should_return_invalid_recipient_when_empty_document_is_provided() : void
    {
        $document = [];

        $this->assertEquals(
            (new VerifyDocumentAction($document))->verify(),
            'invalid_recipient'
        );
    }

    public function test_it_should_return_invalid_recipient_when_no_valid_recipient_fields_are_provided(): void
    {
        // no recipient field
        $document = [
            'data' => [],
        ];

        $this->assertEquals(
            (new VerifyDocumentAction($document))->verify(),
            'invalid_recipient'
        );

        // null recipient
        $document = [
            'data' => [
                'recipient' => null,
            ],
        ];

        $this->assertEquals(
            (new VerifyDocumentAction($document))->verify(),
            'invalid_recipient'
        );

        // empty array recipient
        $document = [
            'data' => [
                'recipient' => [],
            ]
        ];

        $this->assertEquals(
            (new VerifyDocumentAction($document))->verify(),
            'invalid_recipient'
        );

        // invalid recipient key / value pair
        $document = [
            'data' => [
                'recipient' => [
                    'testing' => '123',
                ],
            ],
        ];

        $this->assertEquals(
            (new VerifyDocumentAction($document))->verify(),
            'invalid_recipient'
        );

        // invalid email
        $document = [
            'data' => [
                'recipient' => [
                    'name' => 'Test User',
                    'email' => 'not_a_valid_email',
                ],
            ],
        ];

        $this->assertEquals(
            (new VerifyDocumentAction($document))->verify(),
            'invalid_recipient'
        );
    }

    public function test_it_should_not_return_invalid_recipient_when_valid_recipeint_fields_are_provided() : void
    {
        $document = [
            'data' => [
                'recipient' => [
                    'name' => 'Test User',
                    'email' => 'test@user.com',
                ]
            ],
        ];

        $this->assertNotEquals(
            (new VerifyDocumentAction($document))->verify(),
            'invalid_recipient'
        );
    }

    // verify issuers
    public function test_it_should_return_invalid_issuer_when_no_valid_issuer_fields_are_provided() : void
    {
        // no issuer field
        $document = [
            'data' => [
                'recipient' => [
                    'name' => 'Test User',
                    'email' => 'test@user.com',
                ],
            ],
        ];

        $this->assertEquals(
            (new VerifyDocumentAction($document))->verify(),
            'invalid_issuer'
        );

        // null issuer field
        $document = [
            'data' => [
                'recipient' => [
                    'name' => 'Test User',
                    'email' => 'test@user.com',
                ],
            ],
            'issuer' => null,
        ];

        $this->assertEquals(
            (new VerifyDocumentAction($document))->verify(),
            'invalid_issuer'
        );

        // invalid issuer key / value pair
        $document = [
            'data' => [
                'recipient' => [
                    'name' => 'Test User',
                    'email' => 'test@user.com',
                ],
            ],
            'issuer' => [
                'testing' => '123',
            ],
        ];

        $this->assertEquals(
            (new VerifyDocumentAction($document))->verify(),
            'invalid_issuer'
        );

        // invalid issuer key / value pair
        $document = [
            'data' => [
                'recipient' => [
                    'name' => 'Test User',
                    'email' => 'test@user.com',
                ],
            ],
            'issuer' => [
                'testing' => '123',
            ],
        ];

        $this->assertEquals(
            (new VerifyDocumentAction($document))->verify(),
            'invalid_issuer'
        );

        // invalid issuer type
        $document = [
            'data' => [
                'recipient' => [
                    'name' => 'Test User',
                    'email' => 'test@user.com',
                ],
            ],
            'issuer' => [
                'name' => 'Accredify',
                'identityProof' => [
                    'type' => 'INVALID_TYPE',
                    'key' => 'testing-key',
                    'location' => 'ropstore.accredify.io'
                ],
                'testing' => '123',
            ],
        ];

        $this->assertEquals(
            (new VerifyDocumentAction($document))->verify(),
            'invalid_issuer'
        );

        // invalid issuer location
        $document = [
            'data' => [
                'recipient' => [
                    'name' => 'Test User',
                    'email' => 'test@user.com',
                ],
            ],
            'issuer' => [
                'name' => 'Accredify',
                'identityProof' => [
                    'type' => 'DNS-DID',
                    'key' => 'testing-key',
                    'location' => 'INVALID_VALUE'
                ],
                'testing' => '123',
            ],
        ];

        $this->assertEquals(
            (new VerifyDocumentAction($document))->verify(),
            'invalid_issuer'
        );
    }

    public function test_it_should_not_return_invalid_issuer_when_valid_issuer_fields_are_provided()
    {
        // avoid HTTP Call for testing
        $getDNSTxtRecordsActionMock = Mockery::mock('overload:App\Actions\GetDNSTxtRecordsAction');
        $getDNSTxtRecordsActionMock
            ->shouldReceive('getRecords')
            ->andReturn([
                [
                    'a' => 'dns-did',
                    'p' => 'TEST_KEY',
                ]
            ]);

        $document = [
            'data' => [
                'recipient' => [
                    'name' => 'Test User',
                    'email' => 'test@user.com',
                ],
                'issuer' => [
                    'name' => 'Accredify',
                    'identityProof' => [
                        'type' => 'DNS-DID',
                        'key' => 'TEST_KEY',
                        'location' => 'ropstore.accredify.io'
                    ],
                ],
            ],
        ];

        $this->assertNotEquals(
            (new VerifyDocumentAction($document))->verify(),
            'invalid_issuer'
        );
    }

    // verify signature
    public function test_it_should_return_invalid_signature_when_no_relevent_fields_are_provided()
    {
        // avoid HTTP Call for testing
        $getDNSTxtRecordsActionMock = Mockery::mock('overload:App\Actions\GetDNSTxtRecordsAction');
        $getDNSTxtRecordsActionMock
            ->shouldReceive('getRecords')
            ->andReturn([
                [
                    'a' => 'dns-did',
                    'p' => 'TEST_KEY',
                ]
            ]);

        $document = [
            'data' => [
                'recipient' => [
                    'name' => 'Test User',
                    'email' => 'test@user.com',
                ],
                'issuer' => [
                    'name' => 'Accredify',
                    'identityProof' => [
                        'type' => 'DNS-DID',
                        'key' => 'TEST_KEY',
                        'location' => 'ropstore.accredify.io'
                    ],
                ],
            ],
        ];

        $this->assertEquals(
            (new VerifyDocumentAction($document))->verify(),
            'invalid_signature'
        );
    }

    public function test_it_should_return_invalid_signature_when_invalid_fields_are_provided()
    {
        // avoid HTTP Call for testing
        $getDNSTxtRecordsActionMock = Mockery::mock('overload:App\Actions\GetDNSTxtRecordsAction');
        $getDNSTxtRecordsActionMock
            ->shouldReceive('getRecords')
            ->andReturn([
                [
                    'a' => 'dns-did',
                    'p' => 'TEST_KEY',
                ]
            ]);

        $document = [
            'data' => [
                'recipient' => [
                    'name' => 'Test User',
                    'email' => 'test@user.com',
                ],
                'issuer' => [
                    'name' => 'Accredify',
                    'identityProof' => [
                        'type' => 'DNS-DID',
                        'key' => 'TEST_KEY',
                        'location' => 'ropstore.accredify.io'
                    ],
                ],
            ],
            'signature' => [
                'type' => 'INVALID_TYPE',
                'targetHash' => '288f94aadadf486cfdad84b9f4305f7d51eac62db18376d48180cc1dd2047a0e',
            ],
        ];

        $this->assertEquals(
            (new VerifyDocumentAction($document))->verify(),
            'invalid_signature'
        );
    }

    public function test_it_should_not_return_invalid_signature_when_valid_fields_are_provided()
    {
        // avoid HTTP Call for testing
        $getDNSTxtRecordsActionMock = Mockery::mock('overload:App\Actions\GetDNSTxtRecordsAction');
        $getDNSTxtRecordsActionMock
            ->shouldReceive('getRecords')
            ->andReturn([
                [
                    'a' => 'dns-did',
                    'p' => 'TEST_KEY',
                ]
            ]);

        $document = [
            'data' => [
                'id' => '63c79bd9303530645d1cca00',
                'name' => 'Certificate of Completion',
                'recipient' => [
                    'name' => 'Test User',
                    'email' => 'test@user.com',
                ],
                'issuer' => [
                    'name' => 'Accredify',
                    'identityProof' => [
                        'type' => 'DNS-DID',
                        'key' => 'did:ethr:0x05b642ff12a4ae545357d82ba4f786f3aed84214#controller',
                        'location' => 'ropstore.accredify.io'
                    ],
                ],
            ],
            'signature' => [
                'type' => 'SHA3MerkleProof',
                'targetHash' => '288f94aadadf486cfdad84b9f4305f7d51eac62db18376d48180cc1dd2047a0e',
            ],
        ];

        $this->assertNotEquals(
            (new VerifyDocumentAction($document))->verify(),
            'invalid_signature'
        );
    }

    // verify all
    public function test_it_should_return_verified_when_all_conditions_are_met()
    {
        // avoid HTTP Call for testing
        $getDNSTxtRecordsActionMock = Mockery::mock('overload:App\Actions\GetDNSTxtRecordsAction');
        $getDNSTxtRecordsActionMock
            ->shouldReceive('getRecords')
            ->andReturn([
                [
                    'a' => 'dns-did',
                    'p' => 'did:ethr:0x05b642ff12a4ae545357d82ba4f786f3aed84214#controller',
                ]
            ]);

        $document = [
            'data' => [
                'id' => '63c79bd9303530645d1cca00',
                'name' => 'Certificate of Completion',
                'recipient' => [
                    'name' => 'Marty McFly',
                    'email' => 'marty.mcfly@gmail.com',
                ],
                'issuer' => [
                    'name' => 'Accredify',
                    'identityProof' => [
                        'type' => 'DNS-DID',
                        'key' => 'did:ethr:0x05b642ff12a4ae545357d82ba4f786f3aed84214#controller',
                        'location' => 'ropstore.accredify.io'
                    ],
                ],
                "issued" => "2022-12-23T00:00:00+08:00",
            ],
            'signature' => [
                'type' => 'SHA3MerkleProof',
                'targetHash' => '288f94aadadf486cfdad84b9f4305f7d51eac62db18376d48180cc1dd2047a0e',
            ],
        ];

        $this->assertEquals(
            (new VerifyDocumentAction($document))->verify(),
            'verified'
        );
    }
}
//
