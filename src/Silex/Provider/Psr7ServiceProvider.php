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

use Pimple\Container;
use Pimple\ServiceProviderInterface;
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

    public function register(Container $container)
    {
        $container['psr7.http_foundation_factory'] = function () {
            return new HttpFoundationFactory();
        };

        $container['psr7.http_message_factory'] = function () {
            return new DiactorosFactory();
        };


        $container['dispatcher']->addListener(
            KernelEvents::VIEW,
            function (GetResponseForControllerResultEvent $event) use ($container) {
                $controllerResult = $event->getControllerResult();

                if (!$controllerResult instanceof ResponseInterface) {
                    return;
                }

                $event->setResponse($container['psr7.http_foundation_factory']->createResponse($controllerResult));
            }
        );


        $container['dispatcher']->addListener(
            KernelEvents::CONTROLLER,
            function (FilterControllerEvent $event) use ($container) {

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
                            $container['psr7.http_message_factory']->createRequest($request)
                        );
                    }
                }
            }
        );
    }
}
