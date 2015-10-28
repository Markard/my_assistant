<?php namespace MyAssistant\CoreBundle\Tests;


class ApiTestCase extends TestCase
{
    protected function getResponse($uri, $method = 'GET', $content = null, $parameters = [])
    {
        $this->client->request($method, $uri, $parameters, [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ], $content);

        return $this->client->getResponse();
    }
}