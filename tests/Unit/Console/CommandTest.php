<?php

namespace Orchestra\Tenanti\Tests\Unit\Console;

use Illuminate\Filesystem\Filesystem;
use Mockery as m;
use Orchestra\Tenanti\CommandServiceProvider;
use Orchestra\Tenanti\Contracts\Factory;
use Orchestra\Tenanti\Contracts\Notice;
use Orchestra\Tenanti\Migrator\Creator;
use Orchestra\Tenanti\Migrator\MigrationWriter;
use Orchestra\Tenanti\TenantiManager;
use Orchestra\Tenanti\Tests\Kernel;
use Orchestra\Testbench\TestCase;

abstract class CommandTest extends TestCase
{
    protected function getMockDriverFactory()
    {
        $factory = m::mock(Factory::class);
        $notice = m::mock(Notice::class);

        $factory->shouldReceive('install');

        $factory->shouldReceive('setNotice');

        $factory->shouldReceive('run');

        return $factory;
    }

    protected function getPackageProviders($app)
    {
        return [
            CommandServiceProvider::class,
        ];
    }

    protected function resolveApplicationConsoleKernel($app)
    {
        $app->singleton('artisan', function ($app) {
            return new \Illuminate\Console\Application($app, $app['events'], $app->version());
        });

        $app->singleton('Illuminate\Contracts\Console\Kernel', Kernel::class);

        $app['orchestra.tenanti.creator'] = $app[Creator::class] = $creator = m::mock(Creator::class);
        $app['orchestra.tenanti'] = $app[TenantiManager::class] = $tenanti = m::mock(TenantiManager::class);

        $app[MigrationWriter::class] = m::mock(MigrationWriter::class, [new Filesystem(), $tenanti]);
    }

    public function artisan($command, $parameters = [])
    {
        return parent::artisan($command, array_merge($parameters, ['--no-interaction' => true]));
    }
}
