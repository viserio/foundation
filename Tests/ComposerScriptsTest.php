<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests;

use Composer\IO\NullIO;
use Composer\Script\Event;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Nyholm\NSA;
use Symfony\Component\Filesystem\Filesystem;
use Viserio\Component\Foundation\ComposerScripts;

class ComposerScriptsTest extends MockeryTestCase
{
    /**
     * @var string
     */
    private $path;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->path = __DIR__ . '/ComposerScriptsTest';

        @\mkdir($this->path);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        (new Filesystem())->remove($this->path);
    }

    public function testOnPostCreateProject(): void
    {
        $config     = $this->arrangeConfig();
        $eventMock  = $this->mock(Event::class);
        $eventMock->shouldReceive('getIO')
            ->once()
            ->andReturn(new NullIO());

        ComposerScripts::onPostCreateProject($eventMock, __DIR__ . '/Fixtures');

        $this->arrangeAssertDirectoryExists($config, ['resources-dir', 'public-dir']);

        self::assertDirectoryExists($config['app-dir'] . '/Console');
        self::assertDirectoryExists($config['app-dir'] . '/Provider');
        self::assertDirectoryNotExists($config['app-dir'] . '/Http/Middleware');
        self::assertFileNotExists($config['app-dir'] . '/Http/Controller/Controller.php');

        self::assertFileNotExists($config['routes-dir'] . '/api.php');
        self::assertFileExists($config['routes-dir'] . '/console.php');
        self::assertFileNotExists($config['routes-dir'] . '/web.php');

        self::assertDirectoryNotExists($this->path . '/resources/lang');
        self::assertDirectoryNotExists($this->path . '/resources/views');

        self::assertFileExists($config['storage-dir'] . '/framework/.gitignore');
        self::assertFileExists($config['storage-dir'] . '/logs/.gitignore');

        self::assertDirectoryNotExists($config['tests-dir'] . '/Feature');
        self::assertDirectoryExists($config['tests-dir'] . '/Unit');
        self::assertFileExists($config['tests-dir'] . '/AbstractTestCase.php');
    }

    public function testOnPostCreateProjectWithoutComposerAndDiscovery(): void
    {
        $config    = $this->arrangeConfig();
        $eventMock = $this->mock(Event::class);
        $eventMock->shouldReceive('getIO')
            ->never()
            ->andReturn(new NullIO());

        ComposerScripts::onPostCreateProject($eventMock, __DIR__);

        self::assertDirectoryNotExists($config['app-dir'] . '/Console');
        self::assertDirectoryNotExists($config['app-dir'] . '/Provider');
        self::assertDirectoryNotExists($config['app-dir'] . '/Http/Middleware');
        self::assertFileNotExists($config['app-dir'] . '/Http/Controller/Controller.php');

        self::assertFileNotExists($config['routes-dir'] . '/api.php');
        self::assertFileNotExists($config['routes-dir'] . '/console.php');
        self::assertFileNotExists($config['routes-dir'] . '/web.php');

        self::assertDirectoryNotExists($this->path . '/resources/lang');
        self::assertDirectoryNotExists($this->path . '/resources/views');

        self::assertFileNotExists($config['storage-dir'] . '/framework/.gitignore');
        self::assertFileNotExists($config['storage-dir'] . '/logs/.gitignore');

        self::assertDirectoryNotExists($config['tests-dir'] . '/Feature');
        self::assertDirectoryNotExists($config['tests-dir'] . '/Unit');
        self::assertFileNotExists($config['tests-dir'] . '/AbstractTestCase.php');
    }

    /**
     * @param array $config
     * @param array $skip
     */
    protected function arrangeAssertDirectoryExists(array $config, array $skip = []): void
    {
        foreach ($config as $key => $dir) {
            if (\in_array($key, \array_merge(['discovery_test'], $skip), true)) {
                continue;
            }

            self::assertDirectoryExists($dir);
        }
    }

    /**
     * @return array
     */
    protected function arrangeConfig(): array
    {
        return [
            'app-dir'        => $this->path . '/app',
            'public-dir'     => $this->path . '/public',
            'config-dir'     => $this->path . '/config',
            'resources-dir'  => $this->path . '/resources',
            'routes-dir'     => $this->path . '/routes',
            'tests-dir'      => $this->path . '/tests',
            'storage-dir'    => $this->path . '/storage',
            'discovery_test' => true,
        ];
    }
}
