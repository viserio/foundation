<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Foundation\DataCollector;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Profiler\DataCollector\AbstractDataCollector;
use Viserio\Contract\Profiler\PanelAware as PanelAwareContract;

class FilesLoadedCollector extends AbstractDataCollector implements PanelAwareContract
{
    /**
     * All compiled and included files.
     *
     * @var array
     */
    protected $files = [];

    /**
     * All included files.
     *
     * @var array
     */
    protected $included = [];

    /**
     * Base path.
     *
     * @var string
     */
    protected $basePath;

    /**
     * Create new files loaded collector instance.
     *
     * @param string $basePath
     */
    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response): void
    {
        // Get the files included on load.
        $included = [];

        foreach (\get_included_files() as $file) {
            // Skip the files from Profiler, they are only loaded for Debugging and confuse the output.
            if (\strpos($file, 'vendor/narrowspark/framework/src/Viserio/Profiler') !== false
                || \strpos($file, 'vendor/viserio/profiler') !== false
            ) {
                continue;
            }

            $included[] = $this->stripBasePath($file);
        }

        $this->included = $included;
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        return [
            'icon' => 'ic_insert_drive_file_white_24px.svg',
            'label' => '',
            'value' => (string) \count($this->included),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPanel(): string
    {
        return $this->createTable(
            $this->included,
            ['headers' => ['Files']]
        );
    }

    /**
     * Remove the base path from the paths, so they are relative to the base.
     *
     * @param string $path
     *
     * @return string
     */
    protected function stripBasePath(string $path): string
    {
        return \ltrim(\str_replace($this->basePath, '', $path), '/');
    }
}
