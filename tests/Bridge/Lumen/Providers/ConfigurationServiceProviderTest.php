<?php
declare(strict_types=1);

namespace Tests\EoneoPay\Utils\Bridge\Lumen\Providers;

use EoneoPay\Utils\Bridge\Lumen\Providers\ConfigurationServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use Mockery;
use Tests\EoneoPay\Utils\TestCase;

/**
 * @covers \EoneoPay\Utils\Bridge\Lumen\Providers\ConfigurationServiceProvider
 */
class ConfigurationServiceProviderTest extends TestCase
{
    /**
     * @var string[]
     */
    private static $configFiles = [
        'config-one',
        'config-two'
    ];

    /**
     * Service provider should call configure on application for all php config files and ignore others.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.StaticAccess) Mockery is working this way
     */
    public function testRegisterCallsConfigureForFilesInConfig(): void
    {
        $app = Mockery::mock(Application::class);
        $app->shouldReceive('basePath')->once()->withNoArgs()->andReturn(\realpath(__DIR__));

        foreach (static::$configFiles as $configFile) {
            $app->shouldReceive('configure')
                ->once()
                ->with(\basename($configFile))
                ->andReturnSelf();
        }

        $app->shouldNotReceive('configure')->once()->with('no-config.txt');

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        $serviceProvider = new ConfigurationServiceProvider($app);
        $serviceProvider->register();

        self::assertInstanceOf(ConfigurationServiceProvider::class, $serviceProvider);
    }

    /**
     * Service provider should return null if config path isn't a directory.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.StaticAccess) Mockery is working this way
     */
    public function testRegisterReturnNullIfConfigPathNotDirectory(): void
    {
        $app = Mockery::mock(Application::class);
        $app->shouldReceive('basePath')->once()->withNoArgs()->andReturn(null);

        /** @var \Illuminate\Contracts\Foundation\Application $app */
        (new ConfigurationServiceProvider($app))->register();

        self::assertTrue(true);
    }
}
