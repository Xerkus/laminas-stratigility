<?php

/**
 * @see       https://github.com/laminas/laminas-stratigility for the canonical source repository
 * @copyright https://github.com/laminas/laminas-stratigility/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-stratigility/blob/master/LICENSE.md New BSD License
 */
declare(strict_types=1);

namespace Laminas\Stratigility;

use Interop\Http\Server\MiddlewareInterface;
use OutOfRangeException;

/**
 * Value object representing route-based middleware
 *
 * Details the subpath on which the middleware is active, and the
 * handler for the middleware itself.
 *
 * @property-read MiddlewareInterface $handler Handler for this route
 * @property-read string $path Path for this route
 */
class Route
{
    /**
     * @var MiddlewareInterface
     */
    protected $handler;

    /**
     * @var string
     */
    protected $path;

    public function __construct(string $path, MiddlewareInterface $handler)
    {
        $this->path    = $path;
        $this->handler = $handler;
    }

    /**
     * @return mixed
     * @throws OutOfRangeException for invalid properties
     */
    public function __get(string $name)
    {
        if (! property_exists($this, $name)) {
            throw new OutOfRangeException('Only the path and handler may be accessed from a Route instance');
        }
        return $this->{$name};
    }
}
