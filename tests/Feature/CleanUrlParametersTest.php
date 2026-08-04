<?php

namespace Tests\Feature;

use Tests\TestCase;

class CleanUrlParametersTest extends TestCase
{
    /**
     * Test that malformed URLs with HTML-encoded parameters get redirected to clean URLs.
     */
    public function test_html_encoded_parameters_are_cleaned()
    {
        // Simulate a URL with HTML-encoded parameters
        $malformedUrl = '/guidings?amp;placeLat=39.6952629&amp;placeLng=3.0175712&amp;water[0]=2&amp;page=3';
        
        $response = $this->get($malformedUrl);
        
        // Should redirect to clean URL
        $response->assertStatus(301);
        $response->assertRedirect('/guidings?placeLat=39.6952629&placeLng=3.0175712&water%5B0%5D=2&page=3');
    }
    
    /**
     * Test that clean URLs pass through without redirect.
     */
    public function test_clean_urls_pass_through()
    {
        $cleanUrl = '/guidings?placeLat=39.6952629&placeLng=3.0175712&page=3';
        
        $response = $this->get($cleanUrl);
        
        // Should not redirect (status may vary based on route, but should not be 301)
        $this->assertNotEquals(301, $response->getStatusCode());
    }
    
    /**
     * Test multiple levels of HTML encoding are cleaned.
     */
    public function test_multiple_html_encoding_levels_are_cleaned()
    {
        // Multiple levels of encoding: amp;amp;amp;placeLat
        $deeplyEncodedUrl = '/guidings?amp;amp;amp;placeLat=39.6952629&amp;amp;page=2';
        
        $response = $this->get($deeplyEncodedUrl);
        
        // Should redirect to clean URL
        $response->assertStatus(301);
        $response->assertRedirect('/guidings?placeLat=39.6952629&page=2');
    }
}
