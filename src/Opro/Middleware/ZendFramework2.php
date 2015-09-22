<?php

namespace Opro\Middleware;

class ZendFramework2 extends \Zend\Mvc\Application implements \Zend\Stratigility\MiddlewareInterface
{

    protected $appConfig = [];

    public function __construct($appConfig)
    {
        $this->appConfig = $appConfig;
    }

    /**
     *
     * @var \Psr\Http\Message\ServerRequestInterface 
     */
    protected $request  = null;

    /**
     *
     * @var \Zend\Stratigility\Http\Response
     */
    protected $response = null;

    public function __invoke(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, callable $out = null)
    {
        $this->request      = $request;
        $this->response     = $response;
        
        // Run the application!
        $app                = self::init($this->appConfig);
        $responseCollection = $app->getEventManager()->attach(\Zend\Mvc\MvcEvent::EVENT_FINISH, [$this, 'onFinish'], -1000);

        $app->run();
        if ($out) {
            $out();
        }
        return $this->response;
    }

    public function onFinish(\Zend\Mvc\MvcEvent $event)
    {
        $content = $event->getResponse()->getContent();
        
        $this->response->write($content);
        $this->response->end();
    }

}
