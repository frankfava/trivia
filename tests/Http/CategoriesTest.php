<?php

namespace Tests\Http;

use App\Models\Category;
use App\Models\Question;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoriesTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    public function can_index_categories()
    {
        $this->makeUserAndAuthenticateWithToken();

        Category::factory(4)->create();

        $res = $this->getJson(route('categories.index', [
            'per_page' => 2,
            'page' => 1,
        ]));

        $res
            ->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    #[Test]
    public function you_need_to_be_authenticated_to_view_categories()
    {
        $this->getJson(route('categories.index'))
            ->assertUnauthorized();
    }

    #[Test]
    public function can_get_single_category()
    {
        $this->makeUserAndAuthenticateWithToken();

        $category = Category::factory()->create();

        $this->getJson(route('categories.show', [$category]))
            ->assertStatus(200)
            ->assertJsonFragment($category->toArray());
    }

    #[Test]
    public function an_category_can_be_deleted()
    {
        $this->makeUserAndAuthenticateWithToken();

        $category = Category::factory()->create();

        $this->deleteJson(route('categories.destroy', [$category]))
            ->assertStatus(204);

        $this->assertCount(0, Category::all());
    }

    #[Test]
    public function cannot_delete_if_question_has_category()
    {
        $this->makeUserAndAuthenticateWithToken();

        $category = Category::factory()
            ->has(Question::factory()->count(1))
            ->create();

        $this->deleteJson(route('categories.destroy', [$category]))
            ->assertStatus(403);

        $this->assertCount(1, Category::all());
    }
}
