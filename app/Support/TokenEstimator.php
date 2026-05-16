<?php

namespace App\Support;

class TokenEstimator
{
    public static function estimateString(?string $content): int
    {
        $content = trim((string) $content);

        if ($content === '') {
            return 0;
        }

        return max(1, (int) ceil(mb_strlen($content) / 4));
    }

    public static function estimateMessages(iterable $messages): int
    {
        $total = 0;

        foreach ($messages as $message) {
            $content = is_array($message) ? ($message['content'] ?? '') : ($message->content ?? '');
            $total += self::estimateString($content);
        }

        return $total;
    }
}
