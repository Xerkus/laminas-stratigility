# Installation and Requirements

Install this library using composer:

```console
$ composer require laminas/laminas-diactoros laminas/laminas-stratigility:^1.0
```

Stratigility has the following dependencies (which are managed by Composer):

- `psr/http-message`, which provides the interfaces specified in [PSR-7](http://www.php-fig.org/psr/psr-7),
  and type-hinted against in this package. In order to use Stratigility, you
  will need an implementation of PSR-7; one such package is
  [Diactoros](https://docs.laminas.dev/laminas-diactoros/).

- [`http-interop/http-middleware`](https://github.com/http-interop/http-middleware),
  which provides the interfaces that will become PSR-15. This is pinned to the
  0.2 series.

- `laminas/laminas-escaper`, used by the `ErrorHandler` middleware and the
  (legacy) `FinalHandler` implementation for escaping error messages prior to
  passing them to the response.

You can provide your own request and response implementations if desired as
long as they implement the PSR-7 HTTP message interfaces.

## Later versions

- [Version 2 documentation](../v2/install.md)
- [Version 3 (current) documentation](../v3/install.md)
