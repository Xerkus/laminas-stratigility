<?php

/**
 * @see       https://github.com/laminas/laminas-stratigility for the canonical source repository
 * @copyright https://github.com/laminas/laminas-stratigility/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-stratigility/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Stratigility;

use Exception;

/**
 * Dispatch middleware
 *
 * This class is an implementation detail of Next.
 *
 * @internal
 */
class Dispatch
{
    /**
     * Dispatch middleware
     *
     * Given a route (which contains the handler for given middleware),
     * the $err value passed to $next, $next, and the request and response
     * objects, dispatch a middleware handler.
     *
     * If $err is non-falsy, and the current handler has an arity of 4,
     * it will be dispatched.
     *
     * If $err is falsy, and the current handler has an arity of < 4,
     * it will be dispatched.
     *
     * In all other cases, the handler will be ignored, and $next will be
     * invoked with the current $err value.
     *
     * If an exception is raised when executing the handler, the exception
     * will be assigned as the value of $err, and $next will be invoked
     * with it.
     *
     * @param Route $route
     * @param mixed $err
     * @param Http\Request $request
     * @param Http\Response $response
     * @param callable $next
     */
    public function __invoke(
        Route $route,
        $err,
        Http\Request $request,
        Http\Response $response,
        callable $next
    ) {
        $handler  = $route->handler;
        $hasError = (null !== $err);

        switch (true) {
            case ($handler instanceof ErrorMiddlewareInterface):
                $arity = 4;
                break;
            case ($handler instanceof MiddlewareInterface):
                $arity = 3;
                break;
            default:
                $arity = Utils::getArity($handler);
                break;
        }

        // @todo Trigger event with Route, original URL from request?

        try {
            if ($hasError && $arity === 4) {
                return call_user_func($handler, $err, $request, $response, $next);
            }

            if (! $hasError && $arity < 4) {
                return call_user_func($handler, $request, $response, $next);
            }
        } catch (Exception $e) {
            $err = $e;
        }

        return $next($request, $response, $err);
    }
}
