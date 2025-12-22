<?php

namespace Tests\Api;

use App\Models\Project;
use Facades\Admin\Static\Color;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_project_with_name_attribute_only()
    {
        $this->signIn();

        $response = $this->postJson('/api/projects', ['name' => 'Sally']);

        $response
            ->assertStatus(200)
            ->assertJson([
                'error' => false,
            ]);

        $this->assertDatabaseHas('projects', [
            'name' => 'Sally',
        ]);
    }

    /** @test */
    public function it_has_default_project_color()
    {
        $response = $this->postJson('/api/projects', ['name' => 'Sally']);

        $project = Project::first();

        $response
            ->assertStatus(200)
            ->assertJson([
                'error' => false,
            ]);

        $this->assertDatabaseHas('projects', [
            'id' => 1,
            'name' => 'Sally',
        ])
        ->assertEquals(Color::get('default'), $project->meta['color']);
    }
}
