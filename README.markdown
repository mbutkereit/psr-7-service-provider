# Psr7ServiceProvider

`Psr7ServiceProvider` provides [Psr-7][psr-7] integration for the
[Silex][silex] application micro-framework.

 [psr-7]: http://www.php-fig.org/psr/psr-7/
 [silex]:    http://silex.sensiolabs.org


## Installation

Add `silex/psr-7-service-provider` to your project's `composer.json`:

```json
{
    "require": {
         "marvin_b8/psr-7-service-provider": "1.0.x-dev"
    }
}
```

And install:

```
php composer.phar install
```

## Registering

```php
<?php

$app->register(new Silex\Provider\Psr7ServiceProvider());
```


## Usage

The Psr-7 provider provides a automatic Psr-7 injection:

```php
<?php

$app->get('/hello', function (\Psr\Http\Message\ServerRequestInterface $request) use($app) {
    $request = new \Zend\Diactoros\Response();
    $request->getBody()->write(json_encode(array('foo'=>'bar')));
    return $request;
    ));
});

$app->get('/hello', function (\Psr\Http\Message\RequestInterface $request) use($app) {
    $request = new \Zend\Diactoros\Response();
    $request->getBody()->write(json_encode(array('foo'=>'bar')));
    return $request;
    ));
});

$app->get('/hello', function (\Psr\Http\Message\MessageInterface $request) use($app) {
    $request = new \Zend\Diactoros\Response();
    $request->getBody()->write(json_encode(array('foo'=>'bar')));
    return $request;
    ));
});

```
