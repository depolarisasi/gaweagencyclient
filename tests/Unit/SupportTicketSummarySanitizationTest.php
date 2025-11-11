<?php

namespace Tests\Unit;

use Illuminate\Support\Str;
use Tests\TestCase;

class SupportTicketSummarySanitizationTest extends TestCase
{
    public function test_strip_tags_and_limit_produces_plain_text_summary(): void
    {
        $html = '<p><strong>Hello</strong> world &amp; <em>friends</em> with a very long text that should be truncated at some point because we do not want to show too much content in the list view.</p>';

        $summary = Str::limit(strip_tags($html), 150);

        $this->assertStringContainsString('Hello world &amp; friends', $summary);
        $this->assertStringNotContainsString('<strong>', $summary);
        $this->assertStringNotContainsString('</strong>', $summary);
        $this->assertStringNotContainsString('<em>', $summary);
        $this->assertStringNotContainsString('</em>', $summary);
        $this->assertTrue(strlen($summary) <= 153); // termasuk '...'
    }
}