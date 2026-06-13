<?php

declare(strict_types=1);

namespace Nietonchique\SofascoreApiBundle\Tests\DependencyInjection;

use Nietonchique\SofascoreApiBundle\SofascoreApiBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;

final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    /**
     * @param array<string, mixed> $bundleConfig
     */
    public function __construct(private readonly array $bundleConfig = [])
    {
        // debug=false so the kernel does not register (and leak) the debug
        // error/exception handlers, which PHPUnit flags as risky.
        parent::__construct('test', false);
    }

    public function registerBundles(): iterable
    {
        return [new FrameworkBundle(), new SofascoreApiBundle()];
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/sofascore-bundle-test/cache/'.spl_object_id($this);
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/sofascore-bundle-test/log/'.spl_object_id($this);
    }

    protected function configureContainer(ContainerConfigurator $container, LoaderInterface $loader): void
    {
        $container->extension('framework', [
            'secret' => 'test',
            'test' => true,
            'http_method_override' => false,
            'handle_all_throwables' => true,
            'php_errors' => ['log' => false],
        ]);
        $container->extension('sofascore_api', $this->bundleConfig);
    }
}
