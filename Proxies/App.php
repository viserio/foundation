<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Proxies;

use Viserio\Component\StaticalProxy\StaticalProxy;

class App extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return self::$container;
    }
}
