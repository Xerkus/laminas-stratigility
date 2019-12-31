# Middleware

What is middleware?

Middleware is code that exists between the request and response, and which can
take the incoming request, perform actions based on it, and either complete the
response or pass delegation on to the next middleware in the queue.

```php
use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Server;
use Laminas\Stratigility\MiddlewarePipe;

require __DIR__ . '/../vendor/autoload.php';

$app = new MiddlewarePipe();

$server = Server::createServer($app, $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);

// Landing page
$app->pipe('/', function ($req, RequestHandlerInterface $handler) {
    if (! in_array($req->getUri()->getPath(), ['/', ''], true)) {
        return $handler->handle($req);
    }

    $response = new Response();
    $response->getBody()->write('Hello world!');
    return $response;
});

// Another page
$app->pipe('/foo', function ($req, RequestHandlerInterface $handler) {
    $response = new Response();
    $response->getBody()->write('FOO!');
    return $response;
});

// 404 handler
$app->pipe(new NotFoundHandler(new Response());

$server->listen(function ($req, $res) {
  return $res;
});
```

In the above example, we have two examples of middleware. The first is a
landing page, and listens at the root path. If the request path is empty or
`/`, it completes the response. If it is not, it delegates to the next
middleware in the stack. The second middleware matches on the path `/foo`
&mdash; meaning it will match `/foo`, `/foo/`, and any path beneath. In that
case, it will complete the response with its own message. If no paths match at
this point, a "final handler" is composed by default to report 404 status.

So, concisely put, _middleware are PHP callables that accept a request object,
and do something with it_.

> ### http-interop middleware
>
> The above example demonstrates the using the interfaces from the http-interop
> project. http-interop is a project attempting to standardize middleware signatures.
> The signature of the http-interop/http-server-middleware 1.0 series, on which
> Stratigility 3.X is based, is:
>
> ```php
> namespace Interop\Http\Server;
>
> use Psr\Http\Message\ResponseInterface;
> use Psr\Http\Message\ServerRequestInterface;
>
> interface MiddlewareInterface
> {
>     public function process(
>         ServerRequestInterface $request,
>         RequestHandlerInterface $handler
>     ) : ResponseInterface;
> }
>
> interface RequestHandlerInterface
> {
>     public function process(
>         ServerRequestInterface $request
>     ) : ResponseInterface;
> }
> ```
>
> Stratigility allows you to implement the http-interop/http-server-middleware
> interface to provide middleware.

Middleware can decide more processing can be performed by calling the `$next`
callable (or, when defining http-interop middleware, `$delegate`/`$handler`)
passed during invocation. With this paradigm, you can build a workflow engine
for handling requests &mdash; for instance, you could have middleware perform
the following:

- Handle authentication details
- Perform content negotiation
- Perform HTTP negotiation
- Route the path to a more appropriate, specific handler

Each middleware can itself be middleware, and can attach to specific paths,
allowing you to mix and match applications under a common domain. As an
example, you could put API middleware next to middleware that serves its
documentation, next to middleware that serves files, and segregate each by URI:

```php
$app->pipe('/api', $apiMiddleware);
$app->pipe('/docs', $apiDocMiddleware);
$app->pipe('/files', $filesMiddleware);
```

The handlers in each middleware attached this way will see a URI with that path
segment stripped, allowing them to be developed separately and re-used under
any path you wish.

Within Stratigility, middleware can be any
[http-interop/http-server-middleware](https://github.com/http-interop/http-server-middleware).
`Laminas\Stratigility\MiddlewarePipe` implements
`Interop\Http\Server\MiddlewareInterface`.
