<?php

/*
 * This file is part of Psr-7 Service Provider.
 *
 * (c) 2015 Marvin Butkereit
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Silex\Tests\Provider;

use Silex\Application;
use Silex\Provider\Psr7ServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Psr7ServiceProvider.
 *
 * @author Marvin Butkereit
 */
class Psr7ServiceProviderTest extends \PHPUnit_Framework_TestCase
{

    public function testPsr7HttpFoundationFactoryServiceIsHttpFoundationFactoryInterface()
    {
        $app = new Application();
        $app->register(new Psr7ServiceProvider());
        $this->assertInstanceOf('\Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface', $app['psr7.http_foundation_factory']);
    }

    public function testPsr7HttpMessageFactoryServiceIsHttpMessageFactoryInterface()
    {
        $app = new Application();
        $app->register(new Psr7ServiceProvider());
        $this->assertInstanceOf('\Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface', $app['psr7.http_message_factory']);
    }

    public function testServerRequestInterfaceInjection()
    {
        $app = new Application();
        $phpunit =$this;
        $app->register(new Psr7ServiceProvider());
        $app->get('/', function (\Psr\Http\Message\ServerRequestInterface $serverRequest) use($phpunit)
        {
            $phpunit->assertInstanceOf('\Psr\Http\Message\ServerRequestInterface',$serverRequest);
            return new Response();
        });
        $response = $app->handle(Request::create('/'));
        $this->assertEquals($response->getStatusCode(),200);
    }


    public function testRequestInterfaceInjection()
    {
        $app = new Application();
        $phpunit =$this;
        $app->register(new Psr7ServiceProvider());
        $app->get('/', function (\Psr\Http\Message\RequestInterface $serverRequest) use($phpunit)
        {
            $phpunit->assertInstanceOf('\Psr\Http\Message\RequestInterface',$serverRequest);
            return new Response();
        });
        $response = $app->handle(Request::create('/'));
        $this->assertEquals($response->getStatusCode(),200);
    }

    public function testMessageInterfaceInjection()
    {
        $app = new Application();
        $phpunit =$this;
        $app->register(new Psr7ServiceProvider());
        $app->get('/', function (\Psr\Http\Message\MessageInterface $serverRequest) use($phpunit)
        {
            $phpunit->assertInstanceOf('\Psr\Http\Message\MessageInterface',$serverRequest);
            return new Response();
        });
        $response = $app->handle(Request::create('/'));
        $this->assertEquals($response->getStatusCode(),200);
    }

    public function testResponseInterfaceResponse()
    {
        $app = new Application();
        $app->register(new Psr7ServiceProvider());
        $app->get('/', function ()
        {
            $response = new \Zend\Diactoros\Response();
            $response->getBody()->write(json_encode(array('foo'=>'bar')));
            return $response;
        });
        $response = $app->handle(Request::create('/'));
        $this->assertEquals($response->getContent(),json_encode(array('foo'=>'bar')));
    }
}
