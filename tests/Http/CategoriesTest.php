<?php

namespace Tests\Http;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoriesTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        // Cache::flush();
    }

    #[Test]
    public function can_index_categories()
    {
        //
    }

    #[Test]
    public function can_get_single_category()
    {
        //
    }

    #[Test]
    public function can_delete_single_category()
    {
        //
    }

    // @todo: cannot_delete_if_question_has_category
}
