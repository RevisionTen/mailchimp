<?php

declare(strict_types=1);

namespace RevisionTen\Mailchimp\Services;

use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\RequestStack;

class MailchimpService
{
    /** @var RequestStack */
    private $requestStack;

    /** @var array */
    private $config;

    /** @var Client */
    private $client;

    public function __construct(RequestStack $requestStack, array $config)
    {
        $this->requestStack = $requestStack;
        $this->config = $config;

        $apiKey = $this->config['api_key'] ?? '';
        $server = end(explode('-', $apiKey));

        if (!$server) {
            throw new \Exception('Mailchimp server could not be read from api_key');
        }

        $this->client = new Client([
            'auth' => [
                'noUserName',
                $this->config['api_key'],
            ],
            'base_uri' => 'https://'.$server.'.api.mailchimp.com/3.0/',
            'timeout' => 60,
            'allow_redirects' => true,
        ]);
    }

    public function subscribe(string $campaign, string $email, string $source = null, array $mergeFields = []): bool
    {
        if (!isset($this->config['campaigns'][$campaign])) {
            return false;
        }

        if (null === $source) {
            $source = 'symfony';
        }

        $requestBody = json_encode([
            'email_address' => $email,
            'status' => 'pending',
            'merge_fields' => $mergeFields,
        ]);

        // Add subscriber to recipient list.
        $response = $this->client->request('POST', 'lists/'.$this->config['campaigns'][$campaign]['list_id'].'/members/', [
            'body' => $requestBody,
            'http_errors' => false,
        ]);

        if (200 !== $response->getStatusCode()) {
            return false;
        }

        return true;
    }

    /**
     * Unsubscribes a user from a list.
     *
     * @param string $campaign
     * @param string $email
     *
     * @return bool
     */
    public function unsubscribe(string $campaign, string $email): bool
    {
        if (!isset($this->config['campaigns'][$campaign])) {
            return false;
        }

        $subscriberHash = md5(strtolower($email));

        $requestBody = json_encode([
            'email_address' => $email,
            'status' => 'unsubscribed',
        ]);

        // Unsubscribe email.
        $response = $this->client->request('PATCH', '/lists/'.$this->config['campaigns'][$campaign]['list_id'].'/members/'.$subscriberHash, [
            'body' => $requestBody,
            'http_errors' => false,
        ]);

        return 200 === $response->getStatusCode();
    }
}
