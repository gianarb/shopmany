# Error Handlers

In your application, you may need to handle error conditions:

- Errors raised by PHP itself (e.g., inability to open a file or database
  connection).
- Exceptions/throwables raised by PHP and/or code you write or consume.
- Inability of any middleware to handle a request.

You can typically handle these conditions via middleware itself.

## Handling 404 conditions

If no middleware is able to handle the incoming request, this is typically
representative of an HTTP 404 status. Stratigility provides a barebones
middleware that you may register in an innermost layer that will return a 404
condition, `Zend\Stratigility\Middleware\NotFoundHandler`. The class requires a
response prototype instance that it will use to provide the 404 status and a
message indicating the request method and URI used:

```php
// setup layers
$app->pipe(/* ... */);
$app->pipe(/* ... */);
$app->pipe(new NotFoundHandler(new Response());

// execute application
```

Note that it is the last middleware piped into the application! Since it returns
a response, no deeper nested layers will execute once it has been invoked.

If you would like a templated response, you will need to write your own
middleware; such middleware might look like the following:

```php
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class NotFoundMiddleware implements ServerMiddlewareInterface
{
    private $renderer;

    public function __construct(
        TemplateRendererInterface $renderer,
        ResponseInterface $response
    ) {
        $this->renderer = $renderer;
        $this->response = $response;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $response = $this->response->withStatus(404);
        $response->getBody()->write(
            $this->renderer->render('error::404')
        );
        return $response;
    }
}
```

## Handling PHP errors and exceptions

`Zend\Stratigility\Middleware\ErrorHandler` is a middleware implementation to
register as the *outermost layer* of your application (or close to the outermost
layer). It does the following:

- Creates a PHP error handler that catches any errors in the `error_handling()`
  mask and throws them as `ErrorException` instances.
- Wraps the invocation of the delegate in a try/catch block:
  - if no exception is caught, and the result is a response, it returns it.
  - if no exception is caught, it raises an exception, which will be caught.
  - any caught exception is transformed into an error response.

To generate the error response, we provide the ability to inject a callable with
the following signature into the `ErrorHandler` during instantiation:

```php
Psr\Http\Message\ResponseInterface;
Psr\Http\Message\ServerRequestInterface;

function (
    Throwable|Exception $e,
    ServerRequestInterface $request,
    ResponseInterface $response
) : ResponseInterface
```

We provide a default implementation, `Zend\Stratigility\Middleware\ErrorResponseGenerator`,
which generates an error response with a `5XX` series status code and a message
derived from the reason phrase, if any is present. You may pass a boolean flag
to its constructor indicating the application is in development mode; if so, the
response will have the stack trace included in the body.

In order to work, the `ErrorHandler` needs a prototype response instance, and,
optionally, an error response generator (if none is provided,
`ErrorResponseGenerator` is used, in production mode):

```php
// setup error handling
$app->pipe(new ErrorHandler(new Response(), new ErrorResponseGenerator($isDevelopmentMode));

// setup layers
$app->pipe(/* ... */);
$app->pipe(/* ... */);
```

As a full example, you can combine the two middleware into the same application
as separate layers:

```php
// setup error handling
$app->pipe(new ErrorHandler(new Response(), new ErrorResponseGenerator($isDevelopmentMode));

// setup layers
$app->pipe(/* ... */);
$app->pipe(/* ... */);

// setup 404 handling
$app->pipe(new NotFoundHandler(new Response());

// execute application
```

The `ErrorResponseGenerator` provides no templating facilities, and only
responds as `text/html`. If you want to provide a templated response, or a
different serialization and/or markup format, you will need to write your own
error response generator.

As an example:

```php
use ErrorException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Zend\Stratigility\Exception\MissingResponseException;
use Zend\Stratigility\Middleware\ErrorHandler;

class TemplatedErrorResponseGenerator
{
    private $isDevelopmentMode;
    private $renderer;

    public function __construct(
        TemplateRendererInterface $renderer,
        $isDevelopmentMode = false
    ) {
        $this->renderer = $renderer;
        $this->isDevelopmentMode = $isDevelopmentMode;
    }

    public function __invoke($e, ServerRequestInterface $request, ResponseInterface $response)
    {
        $response = $response->withStatus(500);
        $response->getBody()->write($this->renderer->render('error::error', [
            'exception'        => $e,
            'development_mode' => $this->isDevelopmentMode,
        ]));
        return $response;
    }
}
```

You would then pass this to the `ErrorHandler`:

```php
$app->pipe(new ErrorHandler(
    new Response(),
    new TemplatedErrorResponseGenerator($renderer, $isDevelopmentMode)
));
```

### ErrorHandler Listeners

`Zend\Stratigility\Middleware\ErrorHandler` provides the ability to attach
*listeners*; these are triggered when an error or exception is caught, and
provided with the exception/throwable raised, the original request, and the
final response. These instances are considered immutable, so listeners are for
purposes of logging/monitoring only.

Listeners must implement the following signature:

```php
Psr\Http\Message\ResponseInterface;
Psr\Http\Message\ServerRequestInterface;

function (
    Throwable|Exception $e,
    ServerRequestInterface $request,
    ResponseInterface $response
) : void
```

Attach listeners using `ErrorHandler::attachListener()`:

```php
$errorHandler->attachListener(function ($throwable, $request, $response) use ($logger) {
    $message = sprintf(
        '[%s] %s %s: %s',
        date('Y-m-d H:i:s'),
        $request->getMethod(),
        (string) $request->getUri(),
        $throwable->getMessage()
    );
    $logger->error($message);
});
```
