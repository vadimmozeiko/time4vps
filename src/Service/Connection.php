<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Connection implements ConnectionInterface
{

    private Client $client;


    public function __construct(private SessionInterface $session)
    {
        $this->session->start();
        $this->client = new Client([
            'base_uri' => 'https://billing.time4vps.com/api',
        ]);
    }

    public function encodeCredentials(Request $request): string
    {
        $username = $request->get('email');
        $password = $request->get('password');
        return base64_encode("$username:$password");

    }

    public function authorize(Request $request): void
    {
        $credentials = $this->encodeCredentials($request);
        try {
            $resp = $this->client->get('api/servers', [
                'headers' => [
                    'Authorization' => ['Basic ' . $credentials]
                ]
            ]);
            if ($resp->getStatusCode() === 200) {
                $this->session->set('credentials', $credentials);
                $this->session->set('authorized', true);
            }
        } catch (GuzzleException) {
            $this->session->set('authorized', false);
        }
    }


    public function isAuthorized(): bool|null
    {
        return $this->session->get('authorized');
    }

    public function logout()
    {
        $this->session->clear();
    }

    public function getResources(string $path): array
    {
        try {
            $resp = $this->client->get($path, [
                'headers' => [
                    'Authorization' => ['Basic ' . $this->session->get('credentials')]
                ]
            ]);
        } catch (GuzzleException) {
            return [];
        }
        $resp = $resp->getBody();
        return json_decode($resp, true);
    }

    public function postTask(string $path): void
    {
        try {
            $this->client->post($path, [
                'headers' => [
                    'Authorization' => ['Basic ' . $this->session->get('credentials')]
                ],
            ]);
        } catch (GuzzleException) {
        }
    }

    public function postTaskWithArgs(string $path, array $args): void
    {
        try {
            $this->client->post($path, [
                'headers' => [
                    'Authorization' => ['Basic ' . $this->session->get('credentials')]
                ],
                'json' => $args
            ]);
        } catch (GuzzleException) {
        }
    }
}