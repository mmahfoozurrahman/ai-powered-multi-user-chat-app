<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $compiledPath = storage_path('framework/testing/views/'.sha1(static::class.'|'.$this->name().'|'.spl_object_id($this)));

        if (! is_dir($compiledPath)) {
            mkdir($compiledPath, 0777, true);
        }

        config()->set('view.compiled', $compiledPath);

        $inertiaVersion = app(\App\Http\Middleware\HandleInertiaRequests::class)->version(request());

        $this->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Version' => (string) $inertiaVersion,
            'X-Requested-With' => 'XMLHttpRequest',
        ]);
    }
}
