<?php

namespace App\Actions;

class ConvertNestedArrayToSignatureAction
{

    const HASH_SHA256 = 'sha256';
    private array $array;

    public function __construct(array $array)
    {
        $this->array = $array;
    }

    public function convert() : string
    {
        // flatten array
        $flattenedArray = (new FlattenArrayAction($this->array))->flatten();

        // sort array by values
        $hashedArray = $this->convertArrayToHashes($flattenedArray);
        sort($hashedArray);

        // return hashed values
        return $this->convertStringToHash(json_encode($hashedArray));
    }

    private function convertArrayToHashes(array $array) : array
    {
        $result = [];

        foreach ($array as $key => $value) {
            array_push(
                $result,
                $this->convertStringToHash(json_encode([$key => $value])),
            );
        }

        return $result;
    }

    private function convertStringToHash(string $string) : string
    {
        return hash(self::HASH_SHA256, $string);
    }
}
