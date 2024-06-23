## Notification Publisher

The Notification Publisher service provides a mechanism to send notifications to users via email or SMS. It supports multiple providers: AWS SES and SMTP for email, and Twilio for SMS.

### Key Features

- Send notifications through various channels and providers.
- Automatically switches to a backup provider in case of fail.
- Ability to configure several providers for each channel.
- If all providers fail, notifications are delayed and resent at a later configured time.
- Enable or disable communication channels through configuration settings.
- Send one notification across multiple channels simultaneously.
- Limit the number of notifications sent over a specified period.
- Keeping a log of user notifications.

### Installation

Ensure the following ports are available on your system: 80, 443, 3306, 6379.

1) Clone the repository:``git clone git@github.com:kuzmicz/notification-publisher.git``
2) Enter ``notification-publisher``
2) Set up proper credentials in ``.env`` file for defined providers.
3) Run ``docker-compose up --build -d``
4) Open ``https://localhost`` in your browser and accept certificate.


### Create user

```
curl -k -X POST https://localhost/api/create-user \
-H "Content-Type: application/json" \
-d '{"email": "example@example.com", "phoneNumber": "48321123321"}'
```

### Retrieve notifications sent to user:
Simply paste the following URL into your browser, replacing ``{userId}`` with the one retrieved during user creation.
```
https://localhost/api/notifications/user/{userId}
```
The User ID should be obtained from the response of the create user request.

### Sending notifications
First, ensure that you have already created the user and have obtained the User ID from the response of the user creation request.

Please adjust the following examples to your needs:
```
userId - user ID returned during user creation
message - message you want to send
subject - subject of the message (optional for email channel)
channels - array of channels you want to send notification to (email, sms)
```
#### Notification by email
```
curl -k -X POST https://localhost/api/notify \
-H "Content-Type: application/json" \
-d '{"userId": "1", "message": "Test message", "subject": "test", "channels": ["email"]}' \
-w "\nHTTP Status: %{http_code}\n" \
-s
```

#### Notification by SMS
```
curl -k -X POST https://localhost/api/notify \
-H "Content-Type: application/json" \
-d '{"userId": "1", "message": "Test message", "subject": "test", "channels": ["sms"]}' \
-w "\nHTTP Status: %{http_code}\n" \
-s
```

#### Notification by email and SMS
```
curl -k -X POST https://localhost/api/notify \
-H "Content-Type: application/json" \
-d '{"userId": "1", "message": "Test message", "subject": "test", "channels": ["email", "sms"]}' \
-w "\nHTTP Status: %{http_code}\n" \
-s
```

### Managing providers
Providers can be managed in the ``config/services.yaml`` file. You can add or remove providers for each channel. If one provider fails, the system will automatically switch to the next one in the list. Below is an example from the ``config/services.yaml`` file:
```
parameters:
    notification:
        channels:
            email:
                providers:
                    - 'smtp'
                    - 'aws_ses'
            sms:
                providers:
                    - 'twilio'
```
In this example, the ``smtp`` provider is prioritized over ``aws_ses`` for the ``email`` channel. If ``smtp`` fails, the system will automatically switch to the ``aws_ses`` provider. You can adjust the position of the providers in the list to change the priority.

### Messenger Strategy, Thottling and Redispatching
You can limit the number of notifications sent over a specified period. This can be configured in the `config/messenger.yaml` and `config/packages/rate_limiter.yaml` files.

Current configuration in ``config/messenger.yaml`` means:
- The async transport has a retry strategy that will attempt to resend a message up to 3 times if it fails.
- The retry strategy for the async transport starts with an initial delay of 1000 milliseconds between attempts.
- The delay between attempts for the async transport will double after each attempt due to a multiplier of 2.
- The maximum delay between attempts for the async transport is 10000 milliseconds.
- The failed transport has a retry strategy similar to the async transport.
- The retry strategy for the failed transport starts with a delay of 5000 milliseconds.
- The maximum delay between attempts for the failed transport is 60000 milliseconds.

Current configuration in ``config/rate_limiter.yaml`` means:
- The rate limiting policy is set to 'token_bucket'. This policy allows a certain number of requests to be made in a given interval, and refills the "bucket" of allowable requests at a specified rate.  
- The 'limit' parameter is set to 50. This means that the maximum number of requests allowed in the bucket for the interval is 50.  
- The 'rate' parameter is set to refill the bucket every 15 minutes with 1 token. This means that every 15 minutes, the number of allowable requests increases by 1, up to the maximum limit of 50.

If a command implements `ThrottleInterface`, the rate limiter will be applied, and messages will be delayed according to the rate limiter's configuration.

If a message fails to be processed by all providers, it will be moved to the failed transport. Subsequently, it will be managed according to the respective policies defined in Symfony Messenger. If the consumer is unable to process the message on the failed transport, the message will not be retried automatically and will require manual intervention.

If message will be rate limited the proper logs will appear in the console: ``docker-compose logs -f php``

### Supervisor
The application uses Supervisor to manage the queue workers. The configuration file is located in ``supervisor/supervisord.conf``.  The worker command is responsible for consuming messages from the async transport and processing them. The worker command is set to run with 2 processes, but this can be adjusted according to the system's capabilities.

## Tests

Run: ``composer test`` inside the directory root.

#### Note
To make the application production-ready, more tests need to be added beyond the basic primary tests for application classes. Specifically, integration tests for the notification sending process should be implemented. However, I opted not to write these tests due to the time-consuming nature of the task, as my goal was to present a general concept rather than a fully production developed feature set.

## Notes
### Architecture
The application is based on Symfony 7.1 and uses the Messenger component for handling notifications. The default transport is Redis, and the failure transport is Doctrine, but it can be easily changed to any other transport supported by the Messenger component. The project structure follows DDD and CQRS patterns.

### Endpoints

Current endpoints was created for testing purposes to facilitate the testing of notification sending. For production use, it should be redesigned and replaced with proper authentication and authorization mechanisms.

### Infrastructure
Based on ``https://github.com/dunglas/symfony-docker``.
The changes include adding Redis, supervisor, and a few custom modifications.

## Useful commands

#### Codestyle
```
vendor/bin/php-cs-fixer fix
vendor/bin/phpcs --standard=PSR12 src
vendor/bin/phpcbf src
```

#### Migrations
```
bin/console make:migration
bin/console doctrine:migrations:migrate
```

#### Queue (if supervisor is not enabled)
```
php bin/console messenger:consume async -vvv
php bin/console messenger:consume --all -vvv
php bin/console messenger:consume failed -vvv
php bin/console messenger:failed:show
```

### Troubleshooting

If you encounter any issues with starting the application due to migrations on the first start, please ensure that ``frankenphp/docker-entrypoint.sh`` is running properly. Sometimes, there is a problem with line endings that prevents the entrypoint script from executing. To fix this, run the following commands:


```
git config --global core.autocrlf input
git rm --cached -r .
git reset --hard
```
Then try to run ``docker-compose up --build`` again.







