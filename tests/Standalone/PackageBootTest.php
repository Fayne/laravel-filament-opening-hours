<?php

namespace Fayne\OpeningHours\Tests\Standalone;

use Fayne\OpeningHours\OpeningHoursServiceProvider;
use Orchestra\Testbench\TestCase;

class PackageBootTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [OpeningHoursServiceProvider::class];
    }

    public function test_package_registers_its_resources_without_a_host_panel(): void
    {
        $this->assertTrue(view()->exists('filament-opening-hours::opening-hours'));
    }
}
