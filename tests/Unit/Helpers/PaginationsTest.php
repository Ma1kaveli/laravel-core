<?php

namespace Tests\Unit\Helpers;

use Core\Helpers\Paginations;

use Tests\TestCase;

class PaginationsTest extends TestCase
{
    public function test_per_page_is_25()
    {
        $field = 25;

        $this->assertTrue(
            Paginations::generateEmpty()->perPage() === $field
        );
    }

    public function test_items_empty()
    {
        $field = [];

        $this->assertTrue(
            count(Paginations::generateEmpty()->items()) === 0
        );
    }
}
