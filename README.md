# Web HTTP Router

[![GitHub release](https://img.shields.io/github/release/raylin666/routing.svg)](https://github.com/raylin666/routing/releases)
[![PHP version](https://img.shields.io/badge/php-%3E%207.0-orange.svg)](https://github.com/php/php-src)

### 环境要求

* PHP >=7.3

### 安装说明

```
composer require "raylin666/routing"
```

### 使用方式

可以通过 `RouteCollection` 对象设置路由，也可以通过路由列表创建路由. 


### 静态路由

```php
<?php 

use Raylin666\Http\Request;
use Raylin666\Router\RouteCollection;

$collection = new RouteCollection();

$collection->addRoute('GET', '/', function () {
    return 'hello world';
});

$route = $collection->match(new Request('GET', '/')); // \Raylin666\Router\Route

echo call_user_func_array($route->getCallback(), []);
```

路由匹配并不会将路由的回调进行调用，但会返回整个 Route，方便回调处理，因此 `match` 仅返回匹配成功的路由对象

### 动态路由

```php
<?php

use Raylin666\Http\Request;
use Raylin666\Router\RouteCollection;

$collection = new RouteCollection();

$collection->addRoute('GET', '/{name}', function ($name) {
    return 'hello ' . $name;
});

$route = $collection->match(new Request('GET', '/foo')); // \Raylin666\Router\Route

echo call_user_func_array($route->getCallback(), $route->getParameters());
```

在动态路由下，成功匹配的路由会将匹配成功的参数更新到 `getParameters` 中，通过 `getParameters` 获取成功匹配的参数信息。

### 同个路由, 多个方法

```php
<?php

use Raylin666\Router\RouteCollection;
use Raylin666\Router\RouteDispatcher;
use Raylin666\Http\Request;

$collection = new RouteCollection();
$collection->get('/', function () {
    return 'hello GET';
});
$collection->post('/', function () {
    return 'hello POST';
});
$dispatcher = new RouteDispatcher($collection);

$response = $dispatcher->dispatch(new Request('GET', '/')); // hello GET
dump($response);
$response = $dispatcher->dispatch(new Request('POST', '/')); // hello POST
dump($response);
```

### 混合路由

在很多情况下，我们路由可能只差一个参数，以下做个例子。

```php
<?php

use Raylin666\Http\Request;
use Raylin666\Router\RouteCollection;

$collection = new RouteCollection();
$collection->addRoute('GET', '/{name}', function () {
    return 'get1';
});
$collection->addRoute('GET', '/', function () {
    return 'get2';
});

$route = $collection->match(new Request('GET', '/abc')); // \Raylin666\Router\Route
$route2 = $collection->match(new Request('GET', '/')); // \Raylin666\Router\Route
echo call_user_func_array($route->getCallback(), $route->getParameters());      //  get1
echo call_user_func_array($route2->getCallback(), $route2->getParameters());    //  get2
```

### 路由组

路由组会在你每个子路由钱添加自己的路由前缀，支持多层嵌套。

```php
<?php 

use Raylin666\Http\Request;
use Raylin666\Router\RouteCollection;

$collection = new RouteCollection();

$collection->group('/v1', function (RouteCollection $collection) {
    $collection->addRoute('GET', '/{name}', function () {
        return 'get1';
    });
});

$route = $collection->match(new Request('GET', '/v1/abc'));

echo call_user_func_array($route->getCallback(), $route->getParameters());  // get1
```

### 模糊路由

模糊路由的灵感来自于 Swoole http server 的 onRequest 回调中，因为每个路由入口都经过 onRequest，那么自己造的时候，可能会有一些根据 pathinfo 进行处理的特殊路由，那么此时模糊路由就可以派上用场了。

```php
<?php

use Raylin666\Http\Request;
use Raylin666\Router\RouteCollection;

$collection = new RouteCollection();

$collection->addRoute('GET', '/api/*', function ($path) {
    return $path;
});

$route = $collection->match(new Request('GET', '/api/abc'));
echo call_user_func_array($route->getCallback(), $route->getParameters()); // /abc

$route = $collection->match(new Request('GET', '/api/cba'));
echo call_user_func_array($route->getCallback(), $route->getParameters()); // /cba
```

匹配凡是以 `/api` 开头的所有合法路由，然后进行回调

### 路由中间件

路由组件实现了路由中间件，基于 [Http](https://github.com/raylin666/php-http) 和 [HTTP Middlewares](https://github.com/raylin666/php-middleware) 实现。

> 路由中间件回调会自动回调 `Psr\Http\Message\ServerRequestInterface` 和 `Raylin666\Middleware\DelegateInterface` 两个对象作为参数。

中间件调用完成后，会返回 `\Psr\Http\Message\ResponseInterface` 对象，用于程序最终处理输出。

```php
<?php

use Raylin666\Http\Request;
use Raylin666\Router\RouteCollection;
use Raylin666\Router\RouteDispatcher;
use Raylin666\Middleware\Middleware;
use Psr\Http\Message\ServerRequestInterface;
use Raylin666\Middleware\DelegateInterface;

class HttpMiddleware extends Middleware
{
    public function handler(ServerRequestInterface $request, DelegateInterface $next)
    {
        // TODO: Implement handle() method.

        return $next->process($request);
    }
}

$router = new RouteCollection('\\App\\Http\\Controllers\\');

$dispatcher = new RouteDispatcher($router, [
    'httpMiddleware'   =>  new HttpMiddleware()
]);

$router->group(['prefix' => '', 'middleware' => 'httpMiddleware'], function () use ($router) {
    $router->group(['prefix' => 'v1'], function() use ($router) {
        $router->get('/hello', function () {
            return 'hello world';
        } /*'v1\CommonController@Hello'*/);
    });
});

$response = $dispatcher->dispatch(new Request('GET', '/v1/hello'));
dump($response);        //  hello world
```

## 更新日志

请查看 [CHANGELOG.md](CHANGELOG.md)

### 联系

如果你在使用中遇到问题，请联系: [1099013371@qq.com](mailto:1099013371@qq.com). 博客: [kaka 梦很美](http://www.ls331.com)

