<?php

use App\Actions\ConverNestedArrayToHashAction;
use Tests\TestCase;
use App\Actions\ConvertNestedArrayToSignatureAction;

class ConvertNestedArrayToSignatureActionTest extends TestCase
{
    public function test_it_should_convert_nested_array_to_signature()
    {
        $array = [
            "id" => "63c79bd9303530645d1cca00",
            "name" => "Certificate of Completion",
            "recipient" => [
                "name" => "Marty McFly",
                "email"=> "marty.mcfly@gmail.com"
            ],
            "issuer" => [
                "name" => "Accredify",
                "identityProof" => [
                    "type" => "DNS-DID",
                    "key" => "did:ethr:0x05b642ff12a4ae545357d82ba4f786f3aed84214#controller",
                    "location" => "ropstore.accredify.io"
                ],
            ],
            "issued" => "2022-12-23T00:00:00+08:00",
        ];

        $this->assertEquals(
            (new ConvertNestedArrayToSignatureAction($array))->convert(),
            "288f94aadadf486cfdad84b9f4305f7d51eac62db18376d48180cc1dd2047a0e",
        );
    }
}
