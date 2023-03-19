<?php
namespace App\Actions;

class FlattenArrayAction
{

    private array $array;

    public function __construct(array $array)
    {
        $this->array = $array;
    }

    public function flatten() : array
    {
        return $this->nestedFlatten($this->array);
    }

    private function nestedFlatten(array $array, string $parentKey = null) : array
    {
        $result = [];

        foreach ($array as $key => $value) {

            // current level is L1 and therefore no parent key is provided
            if (!$parentKey) {
                $currentLevelKey = $key;
            } else {
                $currentLevelKey = "{$parentKey}.{$key}";
            }

            if (is_array($value)) {
                $result = array_merge($result, $this->nestedFlatten($value, $currentLevelKey));
            } else {
                $result[$currentLevelKey] = $value;
            }
        }

        return $result;
    }
}
