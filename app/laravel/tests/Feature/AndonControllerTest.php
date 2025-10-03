<?php

namespace Tests\Feature;

use App\Models\Process;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AndonControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test andon edit page requires authentication
     *
     * @return void
     */
    public function test_andon_edit_requires_authentication()
    {
        $response = $this->get('/home/edit');

        $response->assertRedirect('/login');
    }

    /**
     * Test andon edit page is accessible when authenticated
     *
     * @return void
     */
    public function test_andon_edit_accessible_when_authenticated()
    {
        $response = $this->actingAs($this->user)->get('/home/edit');

        $response->assertStatus(200);
        $response->assertViewIs('andon.config');
        $response->assertViewHas(['processes', 'config', 'columns', 'easing']);
    }

    /**
     * Test andon configuration update with valid data
     *
     * @return void
     */
    public function test_andon_update_with_valid_data()
    {
        $validData = [
            'row_count' => 2,
            'column_count' => 4,
            'auto_play_speed' => 1000,
            'slide_speed' => 500,
            'easing' => 'ease-in-out',
            'item_column_count' => 6,
            'is_show_part_number' => true,
            'is_show_start' => true,
            'is_show_good_count' => true,
            'is_show_good_rate' => true,
            'is_show_defective_count' => true,
            'is_show_defective_rate' => true,
            'is_show_plan_count' => true,
            'is_show_achievement_rate' => true,
            'is_show_cycle_time' => true,
            'is_show_time_operating_rate' => true,
            'is_show_performance_operating_rate' => true,
            'is_show_overall_equipment_effectiveness' => true,
        ];

        $response = $this->actingAs($this->user)->put('/home', $validData);

        $response->assertRedirect('/home');
        // Note: Success depends on business logic validation
    }

    /**
     * Test andon configuration update with invalid data
     *
     * @return void
     */
    public function test_andon_update_with_invalid_data()
    {
        $invalidData = [
            'row_count' => '',
            'column_count' => 'invalid',
            'auto_play_speed' => '',
            'slide_speed' => '',
            'easing' => '',
            'item_column_count' => '',
        ];

        $response = $this->actingAs($this->user)->put('/home', $invalidData);

        $response->assertSessionHasErrors(['row_count', 'column_count', 'auto_play_speed', 'slide_speed', 'easing', 'item_column_count']);
    }

    /**
     * Test andon update requires authentication
     *
     * @return void
     */
    public function test_andon_update_requires_authentication()
    {
        $response = $this->put('/home', []);

        $response->assertRedirect('/login');
    }

    /**
     * Test home page displays processes data
     *
     * @return void
     */
    public function test_home_page_displays_processes_data()
    {
        $process = Process::factory()->create(['process_name' => 'Test Process']);

        $response = $this->actingAs($this->user)->get('/home');

        $response->assertStatus(200);
        $response->assertSee('Test Process');
    }

    /**
     * Test andon configuration page displays form elements
     *
     * @return void
     */
    public function test_andon_config_page_displays_form_elements()
    {
        $response = $this->actingAs($this->user)->get('/home/edit');

        $response->assertStatus(200);
        $response->assertSee('column_count');
        $response->assertSee('easing');
        $response->assertSee('row_count');
    }
}
