<?php

/**
 * @see       https://github.com/laminas/laminas-stratigility for the canonical source repository
 * @copyright https://github.com/laminas/laminas-stratigility/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-stratigility/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Stratigility;

use InvalidArgumentException;
use Laminas\Stratigility\Middleware\PathMiddlewareDecorator;
use Laminas\Stratigility\Route;
use OutOfRangeException;
use PHPUnit\Framework\TestCase;
use Webimpress\HttpMiddlewareCompatibility\MiddlewareInterface as ServerMiddlewareInterface;

class RouteTest extends TestCase
{
    public function createEmptyMiddleware($path = '/')
    {
        return new PathMiddlewareDecorator($path, $this->prophesize(ServerMiddlewareInterface::class)->reveal());
    }

    public function testPathAndHandlerAreAccessibleAfterInstantiation()
    {
        $path = '/foo';
        $handler = $this->createEmptyMiddleware($path);

        $route = new Route($path, $handler);
        $this->assertSame($path, $route->path);
        $this->assertSame($handler, $route->handler);
    }

    public function nonStringPaths()
    {
        return [
            'null' => [null],
            'int' => [1],
            'float' => [1.1],
            'bool' => [true],
            'array' => [[]],
            'object' => [(object) []],
        ];
    }

    /**
     * @dataProvider nonStringPaths
     *
     * @param mixed $path
     */
    public function testDoesNotAllowNonStringPaths($path)
    {
        $this->expectException(InvalidArgumentException::class);
        new Route($path, $this->createEmptyMiddleware($path));
    }

    public function testExceptionIsRaisedIfUndefinedPropertyIsAccessed()
    {
        $route = new Route('/foo', $this->createEmptyMiddleware('/foo'));

        $this->expectException(OutOfRangeException::class);
        $route->foo;
    }

    public function testConstructorTriggersDeprecationErrorWhenNonEmptyPathProvidedWithoutPathMiddleware()
    {
        $error = (object) [];
        set_error_handler(function ($errno, $errstr) use ($error) {
            $error->type = $errno;
            $error->message = $errstr;
        }, E_USER_DEPRECATED);
        new Route('/foo', $this->prophesize(ServerMiddlewareInterface::class)->reveal());
        restore_error_handler();

        $this->assertObjectHasAttribute('type', $error);
        $this->assertSame(E_USER_DEPRECATED, $error->type);
        $this->assertObjectHasAttribute('message', $error);
        $this->assertContains(PathMiddlewareDecorator::class, $error->message);
    }

    public function invalidPathArguments()
    {
        return [
            'null'       => [null],
            'true'       => [true],
            'false'      => [false],
            'zero'       => [0],
            'int'        => [1],
            'zero-float' => [0.0],
            'float'      => [1.1],
            'array'      => [['string']],
            'object'     => [(object) ['string' => 'string']],
        ];
    }

    /**
     * @dataProvider invalidPathArguments
     */
    public function testConstructorRaisesExceptionIfPathIsNotAString($path)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Path must be a string');
        new Route($path, $this->createEmptyMiddleware());
    }
}
