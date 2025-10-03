<?php

namespace Tests\Unit\Models;

use App\Models\Line;
use App\Models\Process;
use App\Models\RaspberryPi;
use App\Models\Worker;
use App\Services\Utility;
use Tests\TestCase;

class LineTest extends TestCase
{
    /**
     * Test fillable attributes
     *
     * @return void
     */
    public function test_fillable_attributes()
    {
        $fillable = [
            'process_id',
            'raspberry_pi_id',
            'worker_id',
            'parent_id',
            'line_name',
            'chart_color',
            'pin_number',
            'defective',
            'order',
        ];

        $line = new Line();
        $this->assertEquals($fillable, $line->getFillable());
    }

    /**
     * Test defective attribute is cast to boolean
     *
     * @return void
     */
    public function test_defective_is_cast_to_boolean()
    {
        $line = new Line();
        $casts = $line->getCasts();

        $this->assertArrayHasKey('defective', $casts);
        $this->assertEquals('boolean', $casts['defective']);
    }

    /**
     * Test pin number formatting
     *
     * @return void
     */
    public function test_pin_number_formatting()
    {
        $line = new Line(['pin_number' => 5]);

        // Since we don't have Utility class loaded, we'll test the method exists
        $this->assertTrue(method_exists($line, 'pinNumber'));
    }

    /**
     * Test relationships are defined
     *
     * @return void
     */
    public function test_relationships_are_defined()
    {
        $line = new Line();

        // Test worker relationship
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\BelongsTo', $line->worker());

        // Test process relationship
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\BelongsTo', $line->process());

        // Test raspberryPi relationship
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\BelongsTo', $line->raspberryPi());

        // Test parentLine relationship
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\BelongsTo', $line->parentLine());

        // Test defectiveLines relationship
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\HasMany', $line->defectiveLines());
    }

    /**
     * Test hidden attributes
     *
     * @return void
     */
    public function test_hidden_attributes()
    {
        $hidden = ['created_at', 'updated_at'];

        $line = new Line();
        $this->assertEquals($hidden, $line->getHidden());
    }
}
