<?php

/*
 * This file is part of Psr-7 Service Provider.
 *
 * (c) 2015 Marvin Butkereit
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Silex\Provider;


use Silex\Application;
use Silex\ServiceProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Psr-7 integration for Silex.
 *
 * @author Marvin Butkereit
 */
class Psr7ServiceProvider implements ServiceProviderInterface
{
    /**
     *  Supported Interfaces.
     *
     * @var array
     */
    private static $supportedTypes = array(
        'Psr\Http\Message\ServerRequestInterface' => true,
        'Psr\Http\Message\RequestInterface' => true,
        'Psr\Http\Message\MessageInterface' => true,
    );

    public function register(Application $app)
    {
        $app['psr7.http_foundation_factory'] = function () {
            return new HttpFoundationFactory();
        };

        $app['psr7.http_message_factory'] = function () {
            return new DiactorosFactory();
        };


        $app['dispatcher']->addListener(
            KernelEvents::VIEW,
            function (GetResponseForControllerResultEvent $event) use ($app) {
                $controllerResult = $event->getControllerResult();

                if (!$controllerResult instanceof ResponseInterface) {
                    return;
                }

                $event->setResponse($app['psr7.http_foundation_factory']->createResponse($controllerResult));
            }
        );


        $app['dispatcher']->addListener(
            KernelEvents::CONTROLLER,
            function (FilterControllerEvent $event) use ($app) {

                $controller = $event->getController();
                $request = $event->getRequest();

                if (is_array($controller)) {
                    $r = new \ReflectionMethod($controller[0], $controller[1]);
                } elseif (is_object($controller) && is_callable($controller, '__invoke')) {
                    $r = new \ReflectionMethod($controller, '__invoke');
                } else {
                    $r = new \ReflectionFunction($controller);
                }

                foreach ($r->getParameters() as $param) {
                    if ($param->getClass() && isset(self::$supportedTypes[$param->getClass()->name])) {
                        $request->attributes->set(
                            $param->name,
                            $app['psr7.http_message_factory']->createRequest($request)
                        );
                    }
                }
            }
        );
    }

    public function boot(Application $app)
    {
    }
}
