<?php

namespace Tests\Feature;

use Tests\TestCase;

class AndonControllerTest extends TestCase
{

    /**
     * Test home page redirects to login when unauthenticated
     *
     * @return void
     */
    public function test_home_page_redirects_when_unauthenticated()
    {
        $response = $this->get('/home');

        $response->assertRedirect('/login');
    }

    /**
     * Test home page is accessible when authenticated
     *
     * @return void
     */
    public function test_home_page_accessible_when_authenticated()
    {
        // Simplified test without database dependency
        $this->markTestSkipped('Database factories not set up yet');
    }

    /**
     * Test root path redirects to home
     *
     * @return void
     */
    public function test_root_redirects_to_home()
    {
        $response = $this->get('/');

        $response->assertRedirect('/home');
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
        // Simplified test without database dependency
        $this->markTestSkipped('Database factories not set up yet');
    }
}
