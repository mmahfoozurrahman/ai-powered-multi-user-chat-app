<?php

namespace Tests\Unit;

use App\Support\AssistantMessageFormatter;
use PHPUnit\Framework\TestCase;

class AssistantMessageFormatterTest extends TestCase
{
    public function test_it_removes_think_tags_and_extracts_follow_ups(): void
    {
        $parsed = AssistantMessageFormatter::parse(
            "<think>private reasoning</think>Here is the final answer.\n[FOLLOWUP_1]: First question?\n[FOLLOWUP_2]: Second question?"
        );

        $this->assertSame('Here is the final answer.', $parsed['content']);
        $this->assertSame(['First question?', 'Second question?'], $parsed['follow_ups']);
    }
}
