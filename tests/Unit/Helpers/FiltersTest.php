<?php

namespace Tests\Unit\Helpers;

use Core\Helpers\Filters;
use Tests\TestCase;

class FiltersTest extends TestCase
{
    public function test_transform_string_to_bool()
    {
        $field = 'true';

        $this->assertTrue(Filters::transformBoolean($field));
    }

    public function test_nullable_is_nullable()
    {
        $field = null;

        $this->assertNull(Filters::transformBoolean($field));
    }

    public function test_other_value_is_false()
    {
        $field = 'something other than true';

        $this->assertFalse(Filters::transformBoolean($field));
    }

    public function test_true_integer_is_false()
    {
        $field = 1;

        $this->assertFalse(Filters::transformBoolean($field));
    }

    public function test_array_is_false()
    {
        $field = [];

        $this->assertFalse(Filters::transformBoolean($field));
    }
}
