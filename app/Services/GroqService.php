<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GroqService
{
    public function chat(array $messages): array
    {
        $settings = app(AiSettingsService::class);
        $apiKey = $settings->apiKey();
        $baseUrl = rtrim((string) config('services.groq.base_url'), '/');
        $model = $settings->current()->active_model;

        if (! $apiKey) {
            throw new RuntimeException('The Groq API key is missing. Add it from the admin AI settings page to enable chat responses.');
        }

        try {
            $response = Http::baseUrl($baseUrl)
                ->withToken($apiKey)
                ->acceptJson()
                ->asJson()
                ->timeout(60)
                ->post('/chat/completions', [
                    ...$this->payload($model, $messages),
                ])
                ->throw();
        } catch (ConnectionException $exception) {
            throw new RuntimeException('Groq could not be reached right now. Please try again in a moment.', previous: $exception);
        } catch (RequestException $exception) {
            $message = data_get($exception->response?->json(), 'error.message')
                ?? 'Groq returned an unexpected error while generating the response.';

            throw new RuntimeException($message, previous: $exception);
        }

        $content = $this->normalizeContent(
            trim((string) data_get($response->json(), 'choices.0.message.content', ''))
        );

        if ($content === '') {
            throw new RuntimeException('Groq returned an empty response. Please try again.');
        }

        return [
            'content' => $content,
            'prompt_tokens' => (int) data_get($response->json(), 'usage.prompt_tokens', 0),
            'completion_tokens' => (int) data_get($response->json(), 'usage.completion_tokens', 0),
            'total_tokens' => (int) data_get($response->json(), 'usage.total_tokens', 0),
        ];
    }

    public function stream(array $messages, callable $onChunk): array
    {
        $settings = app(AiSettingsService::class);
        $apiKey = $settings->apiKey();
        $baseUrl = rtrim((string) config('services.groq.base_url'), '/');
        $model = $settings->current()->active_model;

        if (! $apiKey) {
            throw new RuntimeException('The Groq API key is missing. Add it from the admin AI settings page to enable chat responses.');
        }

        $client = new Client([
            'base_uri' => $baseUrl.'/',
            'headers' => [
                'Authorization' => "Bearer {$apiKey}",
                'Accept' => 'text/event-stream',
                'Content-Type' => 'application/json',
            ],
            'http_errors' => false,
            'timeout' => 60,
            'read_timeout' => 60,
        ]);

        try {
            $response = $client->request('POST', 'chat/completions', [
                'json' => [
                    ...$this->payload($model, $messages),
                    'stream' => true,
                ],
                'stream' => true,
            ]);
        } catch (GuzzleException $exception) {
            throw new RuntimeException('Groq could not be reached right now. Please try again in a moment.', previous: $exception);
        }

        if ($response->getStatusCode() >= 400) {
            $payload = json_decode((string) $response->getBody(), true);
            $message = data_get($payload, 'error.message')
                ?? 'Groq returned an unexpected error while starting the stream.';

            throw new RuntimeException($message);
        }

        $body = $response->getBody();
        $buffer = '';
        $content = '';

        while (! $body->eof()) {
            $chunk = $body->read(1);

            if ($chunk === '') {
                usleep(10000);
                continue;
            }

            $buffer .= $chunk;

            while (($position = strpos($buffer, "\n")) !== false) {
                $line = trim(substr($buffer, 0, $position));
                $buffer = substr($buffer, $position + 1);

                if ($line === '' || ! str_starts_with($line, 'data: ')) {
                    continue;
                }

                $payload = substr($line, 6);

                if ($payload === '[DONE]') {
                    break 2;
                }

                $decoded = json_decode($payload, true);
                $delta = data_get($decoded, 'choices.0.delta.content');

                if (! is_string($delta) || $delta === '') {
                    continue;
                }

                $content .= $delta;
                $onChunk($delta);
            }
        }

        if (trim($buffer) !== '' && str_starts_with(trim($buffer), 'data: ')) {
            $payload = substr(trim($buffer), 6);

            if ($payload !== '[DONE]') {
                $decoded = json_decode($payload, true);
                $delta = data_get($decoded, 'choices.0.delta.content');

                if (is_string($delta) && $delta !== '') {
                    $content .= $delta;
                    $onChunk($delta);
                }
            }
        }

        $content = $this->normalizeContent(trim($content));

        if ($content === '') {
            throw new RuntimeException('Groq returned an empty response. Please try again.');
        }

        return [
            'content' => $content,
            'prompt_tokens' => 0,
            'completion_tokens' => 0,
            'total_tokens' => 0,
        ];
    }

    private function payload(string $model, array $messages): array
    {
        $settings = app(AiSettingsService::class)->current();

        $payload = [
            'model' => $model,
            'reasoning_format' => 'hidden',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => app(AiSettingsService::class)->systemPrompt(),
                ],
                ...$messages,
            ],
        ];

        if ($this->supportsReasoningEffort($model)) {
            $payload['reasoning_effort'] = $settings->reasoning_effort ?: 'medium';
        }

        return $payload;
    }

    private function supportsReasoningEffort(string $model): bool
    {
        return str_starts_with($model, 'openai/gpt-oss-');
    }

    private function normalizeContent(string $content): string
    {
        return trim(preg_replace('/<think>[\s\S]*?<\/think>/i', '', $content) ?? $content);
    }
}
