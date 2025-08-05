<?php

namespace Mhmiton\LaravelModulesLivewire\Tests;

use Mhmiton\LaravelModulesLivewire\LaravelModulesLivewireServiceProvider;
use Mhmiton\LaravelModulesLivewire\Commands\LivewireMakeCommand;
use Mhmiton\LaravelModulesLivewire\Commands\LivewireMakeFormCommand;
use Mhmiton\LaravelModulesLivewire\Commands\VoltMakeCommand;
use Orchestra\Testbench\TestCase as TestbenchTestCase;

class TestCase extends TestbenchTestCase
{
    /**
     * Automatically enables package discoveries.
     *
     * @var bool
     */
    protected $enablesPackageDiscoveries = true;

    public function setUp(): void
    {
        parent::setUp();

        // Mock the Modules facade to prevent binding resolution errors
        $this->app->singleton('modules', function () {
            return new class {
                public function toCollection()
                {
                    return collect([]);
                }

                public function find($name)
                {
                    // Return a mock module object for testing
                    return new class($name) {
                        private $name;

                        public function __construct($name) {
                            $this->name = $name;
                        }

                        public function getName()
                        {
                            return $this->name;
                        }

                        public function getLowerName()
                        {
                            return strtolower($this->name);
                        }

                        public function getPath()
                        {
                            return base_path('Modules/' . $this->name);
                        }

                        public function getAppPath()
                        {
                            return base_path('Modules/' . $this->name . '/app');
                        }

                        public function getNamespace()
                        {
                            return 'Modules\\' . $this->name;
                        }
                    };
                }

                public function all()
                {
                    return [];
                }
            };
        });

        // Register the Modules facade alias
        if (!class_exists('Module')) {
            class_alias('Illuminate\Support\Facades\Facade', 'Module');
        }

        // Manually register commands for testing
        $this->app->make('Illuminate\Contracts\Console\Kernel')->registerCommand(new LivewireMakeCommand());
        $this->app->make('Illuminate\Contracts\Console\Kernel')->registerCommand(new LivewireMakeFormCommand());
        $this->app->make('Illuminate\Contracts\Console\Kernel')->registerCommand(new VoltMakeCommand());
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelModulesLivewireServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $modulesLivewireConfig = require __DIR__.'/../config/modules-livewire.php';

        $app['config']->set('modules-livewire', $modulesLivewireConfig);
    }
}
