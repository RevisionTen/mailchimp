<?php

declare(strict_types=1);

namespace RevisionTen\Mailchimp\Services;

use GuzzleHttp\Client;

class MailchimpService
{
    /** @var array */
    private $config;

    /** @var Client */
    private $client;

    public function __construct(array $config)
    {
        $this->config = $config;

        $apiKey = $this->config['api_key'] ?? '';
        $apiKeyParts = explode('-', $apiKey);
        $server = end($apiKeyParts);

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

    /**
     * Subscribes a user to a list.
     *
     * @param string      $campaign
     * @param string      $email
     * @param string|NULL $source
     * @param array       $mergeFields
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function subscribe(string $campaign, string $email, string $source = null, array $mergeFields = []): bool
    {
        if (!isset($this->config['campaigns'][$campaign])) {
            return false;
        }

        if (null === $source) {
            $source = 'symfony';
        }

        $requestData = [
            'email_address' => $email,
            'status' => 'pending',
        ];

        if (!empty($mergeFields)) {
            $requestData['merge_fields'] = $mergeFields;
        }

        $requestBody = json_encode($requestData);

        $subscriberHash = md5(strtolower($email));

        // Add subscriber to recipient list or update If already exists.
        $response = $this->client->request('PUT', 'lists/'.$this->config['campaigns'][$campaign]['list_id'].'/members/'.$subscriberHash, [
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
     * @throws \GuzzleHttp\Exception\GuzzleException
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
