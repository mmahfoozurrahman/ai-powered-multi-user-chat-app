<?php

namespace App\Support;

class AssistantMessageFormatter
{
    public static function parse(string $content): array
    {
        $sanitized = preg_replace('/<think>[\s\S]*?<\/think>/i', '', $content) ?? $content;

        preg_match('/\[FOLLOWUP_1\]:\s*(.+)/i', $sanitized, $followUpOne);
        preg_match('/\[FOLLOWUP_2\]:\s*(.+)/i', $sanitized, $followUpTwo);

        $cleanedContent = preg_replace('/\[FOLLOWUP_[12]\]:.*$/mi', '', $sanitized) ?? $sanitized;

        return [
            'content' => trim($cleanedContent),
            'follow_ups' => array_values(array_filter([
                isset($followUpOne[1]) ? trim($followUpOne[1]) : null,
                isset($followUpTwo[1]) ? trim($followUpTwo[1]) : null,
            ])),
        ];
    }
}
