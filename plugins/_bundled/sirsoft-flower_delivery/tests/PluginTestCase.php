<?php

namespace Plugins\Sirsoft\FlowerDelivery\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 꽃배달 플러그인 테스트 베이스 클래스
 */
abstract class PluginTestCase extends TestCase
{
    use RefreshDatabase;

    /**
     * 테스트 환경 설정
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->registerPluginAutoload();
        $this->app->register(\Plugins\Sirsoft\FlowerDelivery\Providers\FlowerDeliveryServiceProvider::class);
    }

    /**
     * 플러그인 오토로드를 등록합니다.
     */
    protected function registerPluginAutoload(): void
    {
        $pluginBasePath = dirname(__DIR__).'/src/';

        spl_autoload_register(function ($class) use ($pluginBasePath) {
            $prefix = 'Plugins\\Sirsoft\\FlowerDelivery\\';
            $len = strlen($prefix);

            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }

            $relativeClass = substr($class, $len);
            $file = $pluginBasePath.str_replace('\\', '/', $relativeClass).'.php';

            if (file_exists($file)
                && ! class_exists($class, false) && ! interface_exists($class, false)
                && ! trait_exists($class, false) && ! enum_exists($class, false)) {
                require_once $file;
            }
        });
    }
}
