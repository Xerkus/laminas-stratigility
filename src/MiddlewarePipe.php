<?php

/**
 * @see       https://github.com/laminas/laminas-stratigility for the canonical source repository
 * @copyright https://github.com/laminas/laminas-stratigility/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-stratigility/blob/master/LICENSE.md New BSD License
 */
declare(strict_types=1);

namespace Laminas\Stratigility;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Laminas\Stratigility\Exception\InvalidMiddlewareException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SplQueue;

/**
 * Pipe middleware like unix pipes.
 *
 * This class implements a pipeline of middleware, which can be attached using
 * the `pipe()` method, and is itself middleware.
 *
 * It creates an instance of `Next` internally, invoking it with the provided
 * request and response instances, passing the original request and the returned
 * response to the `$next` argument when complete.
 *
 * Inspired by Sencha Connect.
 *
 * @see https://github.com/sencha/connect
 */
class MiddlewarePipe implements MiddlewareInterface
{
    /**
     * @var SplQueue
     */
    protected $pipeline;

    /**
     * Constructor
     *
     * Initializes the queue.
     */
    public function __construct()
    {
        $this->pipeline = new SplQueue();
    }

    /**
     * PSR-15 middleware invocation.
     *
     * Executes the internal pipeline, passing $handler as the "final
     * handler" in cases when the pipeline exhausts itself.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $next = new Next($this->pipeline, $handler);

        return $next->handle($request);
    }

    /**
     * Attach middleware to the pipeline.
     *
     * Each middleware can be associated with a particular path; if that
     * path is matched when that middleware is invoked, it will be processed;
     * otherwise it is skipped.
     *
     * No path means it should be executed every request cycle.
     *
     * @see Next
     * @param string|MiddlewareInterface $path Either a URI path prefix, or middleware.
     * @param null|MiddlewareInterface $middleware Middleware (callback or PSR-15 middleware)
     * @throws InvalidMiddlewareException If provided middleware is invalid (not instance of MiddlewareInterface)
     * @todo: swap param order
     * @todo: add type hint on $middleware?
     */
    public function pipe($path, $middleware = null) : self
    {
        if (null === $middleware
            && $path instanceof MiddlewareInterface
        ) {
            $middleware = $path;
            $path       = '/';
        }

        if (! $middleware instanceof MiddlewareInterface) {
            throw InvalidMiddlewareException::fromValue($middleware);
        }

        $this->pipeline->enqueue(new Route(
            $this->normalizePipePath($path),
            $middleware
        ));

        // @todo Trigger event here with route details?
        return $this;
    }

    /**
     * Normalize a path used when defining a pipe
     *
     * Strips trailing slashes, and prepends a slash.
     */
    private function normalizePipePath(string $path) : string
    {
        // Prepend slash if missing
        if (empty($path) || $path[0] !== '/') {
            $path = '/' . $path;
        }

        // Trim trailing slash if present
        if (strlen($path) > 1 && '/' === substr($path, -1)) {
            $path = rtrim($path, '/');
        }

        return $path;
    }
}
