# revision-ten/mailchimp

## Installation

#### Install via composer

Run `composer req revision-ten/mailchimp`.

### Add the Bundle

Add the bundle to your AppKernel (Symfony 3.4.\*) or your Bundles.php (Symfony 4.\*).

Symfony 3.4.\* /app/AppKernel.php:
```PHP
new \RevisionTen\Mailchimp\MailchimpBundle(),
```

Symfony 4.\* /config/bundles.php:
```PHP
RevisionTen\Mailchimp\MailchimpBundle::class => ['all' => true],
```

### Configuration

Configure the bundle:

```YAML
# Mailchimp example config.
mailchimp:
    api_key: 'XXXXXXXXXXXXXXXXXXXXXXX-us5' # Your mailchimp api key.
    campaigns:
        dailyNewsletterCampagin:
            list_id: '123456' # Id of your newsletter list.
```

### Usage

Use the MailchimpService to subscribe users.

Symfony 3.4.\* example:
```PHP
$mailchimpService = $this->container->get(MailchimpService::class);

$subscribed = $mailchimpService->subscribe('dailyNewsletterCampagin', 'visitor.email@domain.tld', 'My Website', [
    'FNAME' => 'John',
    'LNAME' => 'Doe',
]);
```

Or unsubscribe users:
```PHP
$mailchimpService = $this->container->get(MailchimpService::class);

$unsubscribed = $mailchimpService->unsubscribe('dailyNewsletterCampagin', 'visitor.email@domain.tld');
```
