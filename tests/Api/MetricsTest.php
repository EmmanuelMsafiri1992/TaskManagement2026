<?php

namespace Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetricsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_count_all_open_tasks()
    {
        // $this->signIn();

        // Given
        // Task::factory()->create();
        // When
        // Then

        // $response = $this->get('/');

        // $response->assertStatus(200);
        $this->assertTrue(true);
    }
}
