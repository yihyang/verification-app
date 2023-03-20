<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class VerifyDocumentTest extends TestCase
{
    public function test_it_should_return_error_when_invalid_document_is_uploaded()
    {
        $this->setupFakeHttpRequest();

        $user = $this->setupUser();
        $this->actingAs($user);

        $file = new UploadedFile(storage_path('tests/files/Documents/invalid-document.oa'), 'incomplete-issuer-document.oa');

        $response = $this->json(
            'POST',
            '/api/verify_document',
            [
                'document' => $file,
            ],
        );

        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'issuer' => null,
                    'result' => 'error'
                ],
            ]);

        $this->assertDatabaseHas(
            Verification::class,
            [
                'user_id' => $user->id,
                'file_type' => 'JSON',
                'result' => 'error',
            ],
        );
    }

    public function test_it_should_return_invalid_issuer_when_incomplete_issuer_document_is_uploaded()
    {
        $this->setupFakeHttpRequest();

        $user = $this->setupUser();
        $this->actingAs($user);

        $file = new UploadedFile(storage_path('tests/files/Documents/incomplete-issuer-document.oa'), 'incomplete-issuer-document.oa');

        $response = $this->json(
            'POST',
            '/api/verify_document',
            [
                'document' => $file,
            ],
        );

        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'issuer' => null,
                    'result' => 'invalid_issuer'
                ],
            ]);

        $this->assertDatabaseHas(
            Verification::class,
            [
                'user_id' => $user->id,
                'file_type' => 'JSON',
                'result' => 'invalid_issuer',
            ],
        );
    }

    public function test_it_should_return_invalid_recipient_when_incomplete_recipient_document_is_uploaded()
    {
        $this->setupFakeHttpRequest();

        $user = $this->setupUser();
        $this->actingAs($user);

        $file = new UploadedFile(storage_path('tests/files/Documents/incomplete-recipient-document.oa'), 'incomplete-recipient-document.oa');

        $response = $this->json(
            'POST',
            '/api/verify_document',
            [
                'document' => $file,
            ],
        );

        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'issuer' => 'Accredify',
                    'result' => 'invalid_recipient'
                ],
            ]);

        $this->assertDatabaseHas(
            Verification::class,
            [
                'user_id' => $user->id,
                'file_type' => 'JSON',
                'result' => 'invalid_recipient',
            ],
        );
    }

    public function test_it_should_return_invalid_signature_when_invalid_signature_document_is_uploaded()
    {
        $this->setupFakeHttpRequest();

        $user = $this->setupUser();
        $this->actingAs($user);

        $file = new UploadedFile(storage_path('tests/files/Documents/invalid-signature-document.oa'), 'invalid-signature-document.oa');

        $response = $this->json(
            'POST',
            '/api/verify_document',
            [
                'document' => $file,
            ],
        );

        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'issuer' => 'Accredify',
                    'result' => 'invalid_signature'
                ],
            ]);

        $this->assertDatabaseHas(
            Verification::class,
            [
                'user_id' => $user->id,
                'file_type' => 'JSON',
                'result' => 'invalid_signature',
            ],
        );
    }

    public function test_it_should_return_verified_when_verified_document_is_uploaded()
    {
        $this->setupFakeHttpRequest();

        $user = $this->setupUser();
        $this->actingAs($user);

        $file = new UploadedFile(storage_path('tests/files/Documents/valid-document.oa'), 'valid-document.oa');

        $response = $this->json(
            'POST',
            '/api/verify_document',
            [
                'document' => $file,
            ],
        );

        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'issuer' => 'Accredify',
                    'result' => 'verified'
                ],
            ]);

        $this->assertDatabaseHas(
            Verification::class,
            [
                'user_id' => $user->id,
                'file_type' => 'JSON',
                'result' => 'verified',
            ],
        );
    }

    public function test_it_should_not_capture_verification_records_when_user_is_not_authenticated()
    {
        $this->setupFakeHttpRequest();
        $user = $this->setupUser();

        $file = new UploadedFile(storage_path('tests/files/Documents/valid-document.oa'), 'valid-document.oa');

        $response = $this->json(
            'POST',
            '/api/verify_document',
            [
                'document' => $file,
            ],
        );

        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'issuer' => 'Accredify',
                    'result' => 'verified'
                ],
            ]);
        $this->assertDatabaseMissing(
            Verification::class,
            [
                'user_id' => $user->id,
                'file_type' => 'JSON',
                'result' => 'verified',
            ],
        );
    }

    private function setupFakeHttpRequest()
    {
        Http::preventStrayRequests();

        // return fake data for testing purpose
        Http::fake([
            'https://dns.google/resolve?name=ropstore.accredify.io&type=TXT' => Http::response(
                [
                    'Answer' => [
                        [
                            'name' => 'ropstore.accredify.io',
                            'type' => 16,
                            'TTL' => 300,
                            'data' => 'openatts a=dns-did; p=did:ethr:0x05b642ff12a4ae545357d82ba4f786f3aed84214#controller; v=1.0;',
                        ],
                        [
                            'name' => 'ropstore.accredify.io',
                            'type' => 16,
                            'TTL' => 300,
                            'data' => 'openatts a=dns-did; p=did:ethr:0x06a464971ea723177ef83df7b39dd63c373a6905#controller; v=1.0;;',
                        ],
                    ],
                ]
            )
        ]);
    }

    private function setupUser()
    {
        return User::factory()->create();
    }
}
