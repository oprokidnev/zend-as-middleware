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
    protected $request = null;

    /**
     *
     * @var \Zend\Stratigility\Http\Response
     */
    protected $response = null;

    public function __invoke(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, callable $out = null)
    {
        /* @var $out \Zend\Stratigility\Next */
        $this->request  = $request;
        $this->response = $response;

        // Run the application!
        $app                = self::init($this->appConfig);
        $responseCollection = $app->getEventManager()->attach(\Zend\Mvc\MvcEvent::EVENT_FINISH, [$this, 'onFinish'], -1000);

        /**
         * Run zf2 app
         */
        $app->run();

        /**
         * Trigger next middleware
         */
        if ($out) {
            $out($this->request, $this->response);
        }
        return $this->response;
    }

    public function onFinish(\Zend\Mvc\MvcEvent $event)
    {
        $content    = $event->getResponse()->getContent();
        $zfResponse = $event->getResponse();
        /* @var $zfResponse \Zend\Http\PhpEnvironment\Response */
        $this->response = $this->response->withStatus($zfResponse->getStatusCode());
        foreach ($zfResponse->getHeaders()->toArray() as $header => $value) {
            $this->response = $this->response->withAddedHeader($header, $value);
        }
        $this->response->end();
    }

}
