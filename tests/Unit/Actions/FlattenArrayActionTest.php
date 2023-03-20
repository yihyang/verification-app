<?php
use App\Actions\FlattenArrayAction;

use Tests\TestCase;

class FlattenArrayActionTest extends TestCase
{
    public function test_it_should_flatten_single_level_array()
    {
        $array = [
            'a' => 1,
            'b' => 2,
        ];

        $this->assertEquals(
            (new FlattenArrayAction($array))->flatten(),
            [
                'a' => 1,
                'b' => 2,
            ]
        );
    }

    public function test_it_should_flatten_nested_array()
    {
        $array = [
            'a' => [
                'b' => 1,
                'c' => [
                    'd' => 2
                ]
            ],
            'e' => 3,
        ];

        $this->assertEquals(
            (new FlattenArrayAction($array))->flatten(),
            [
                'a.b' => 1,
                'a.c.d' => 2,
                'e' => 3,
            ]
        );
    }
}
