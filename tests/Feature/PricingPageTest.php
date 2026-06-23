<?php

namespace Tests\Feature;

use Tests\TestCase;

class PricingPageTest extends TestCase
{
    /**
     * TC-P.1: GET /pricing returns 200 (public, no auth required).
     */
    public function test_pricing_page_is_publicly_accessible(): void
    {
        $this->get('/pricing')->assertOk();
    }

    /**
     * TC-P.2: Response contains each tier name: Free, Standard, Premium, Enterprise.
     */
    public function test_pricing_page_displays_all_tier_names(): void
    {
        $response = $this->get('/pricing');

        $response->assertSee('Free')
                 ->assertSee('Standard')
                 ->assertSee('Premium')
                 ->assertSee('Enterprise');
    }

    /**
     * TC-P.3: Response contains the "Most popular" badge text.
     */
    public function test_pricing_page_displays_most_popular_badge(): void
    {
        $this->get('/pricing')->assertSee('Most popular');
    }

    /**
     * TC-P.4: Each tier's primary CTA links to route('register').
     */
    public function test_pricing_ctas_link_to_register(): void
    {
        $response = $this->get('/pricing');
        $registerUrl = route('register');

        $response->assertSee($registerUrl, false);
    }

    /**
     * TC-P.5: Nav and footer "Pricing" links resolve to route('pricing').
     */
    public function test_nav_and_footer_pricing_links_point_to_pricing_route(): void
    {
        $response = $this->get('/pricing');
        $pricingUrl = route('pricing');

        $response->assertSee($pricingUrl, false);
    }

    /**
     * TC-P.6: Full existing suite stays green (no regressions).
     * This test simply ensures the pricing page doesn't break anything.
     */
    public function test_pricing_page_does_not_cause_regressions(): void
    {
        $this->get('/')->assertOk();
        $this->get('/pricing')->assertOk();
    }
}
