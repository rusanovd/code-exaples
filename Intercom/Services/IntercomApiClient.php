<?php

declare(strict_types=1);

namespace More\Integration\Intercom\Services;

use Http\Client\Exception;
use Infrastructure\Metrics\Facade\Metrics;
use Intercom\IntercomClient;
use More\Integration\Intercom\Exceptions\IntercomApiException;
use More\Integration\Intercom\Exceptions\IntercomConfigDisabledException;
use More\Integration\Intercom\Exceptions\IntercomEmptyOptionsException;
use More\Integration\Intercom\Log\IntercomLoggerFactory;
use Psr\Log\LoggerInterface;

class IntercomApiClient
{
    private const CODE_NOT_FOUND = 'not_found';
    private const CODE_COMPANY_NOT_FOUND = 'company_not_found';
    private const CODE_BAD_REQUEST = 'bad_request';

    private IntercomClient $intercomClient;
    private IntercomConfig $intercomConfig;
    private LoggerInterface $logger;

    /**
     * IntercomApiClient constructor.
     * @param IntercomClient $intercomClient
     * @param IntercomConfig $intercomConfig
     * @param IntercomLoggerFactory $intercomLoggerFactory
     * @throws \Exception
     */
    public function __construct(IntercomClient $intercomClient, IntercomConfig $intercomConfig, IntercomLoggerFactory $intercomLoggerFactory)
    {
        $this->intercomClient = $intercomClient;
        $this->intercomConfig = $intercomConfig;
        $this->logger = $intercomLoggerFactory->getIntercomLogger();
    }

    public function isEnabled(): bool
    {
        return $this->intercomConfig->isEnabled();
    }

    /**
     * @throws IntercomConfigDisabledException
     */
    public function checkIsEnabled(): void
    {
        if (! $this->intercomConfig->isEnabled()) {
            throw new IntercomConfigDisabledException();
        }
    }

    /**
     * @param array $options
     * @throws IntercomEmptyOptionsException
     */
    public function checkOptions(array $options): void
    {
        if (empty($options)) {
            throw new IntercomEmptyOptionsException();
        }
    }

    /**
     * @param array $options
     * @throws IntercomConfigDisabledException
     * @throws IntercomEmptyOptionsException
     */
    public function checkParams(array $options): void
    {
        $this->checkIsEnabled();
        $this->checkOptions($options);
    }

    private function getErrorCodeFromException(Exception $e): string
    {
        if ($e instanceof Exception\HttpException) {
            $bodyContent = (string) $e->getResponse()->getBody()->getContents();
            $response = json_decode($bodyContent, true);

            if (isset($response['errors'][0]['code'])) {
                return (string) $response['errors'][0]['code'];
            }
        }

        return '';
    }

    /**
     * @param Exception $exception
     * @param string $errorText
     * @param array $options
     * @return void
     * @throws IntercomApiException
     */
    private function processException(Exception $exception, string $errorText, array $options = []): void
    {
        $code = $this->getErrorCodeFromException($exception);
        // могут возвращаться если наш id не найден
        if (! in_array($code, [self::CODE_NOT_FOUND, self::CODE_COMPANY_NOT_FOUND, self::CODE_BAD_REQUEST])) {
            $errorString = $errorText . ': ' . $code;
            $this->logger->info('Intercom integration error' . "\n\n" . $errorString, [
                'exception' => $exception,
                'options'   => $options,
            ]);
            throw new IntercomApiException($errorString);
        }
    }

    /**
     * @param array $options
     * @return \stdClass|null
     * @throws IntercomApiException
     * @throws IntercomConfigDisabledException
     * @throws IntercomEmptyOptionsException
     */
    public function updateCompany(array $options): ?\stdClass
    {
        $this->checkParams($options);
        $response = null;

        try {
            $response = $this->intercomClient->companies->update($options);
            $this->logResponse(IntercomFieldsMapper::FIELD_COMPANY, $options, $response);
            Metrics::increment(IntercomMetric::createRequestSuccessMetric(IntercomMetric::METRIC_REQUEST_COMPANY));
        } catch (Exception $e) {
            $this->processException($e, 'Error occurred while updating Company from Intercom', $options);
            Metrics::increment(IntercomMetric::createRequestErrorMetric(IntercomMetric::METRIC_REQUEST_COMPANY));
        }

        return $response;
    }

    /**
     * @param array $options
     * @return \stdClass|null
     * @throws IntercomApiException
     * @throws IntercomConfigDisabledException
     * @throws IntercomEmptyOptionsException
     */
    public function updateContact(array $options): ?\stdClass
    {
        $this->checkParams($options);
        $response = null;

        try {
            $response = $this->intercomClient->users->update($options);
            $this->logResponse(IntercomFieldsMapper::FIELD_CONTACT, $options, $response);
            Metrics::increment(IntercomMetric::createRequestSuccessMetric(IntercomMetric::METRIC_REQUEST_CONTACT));
        } catch (Exception $e) {
            $this->processException($e, 'Error occurred while updating Contact from Intercom', $options);
            Metrics::increment(IntercomMetric::createRequestErrorMetric(IntercomMetric::METRIC_REQUEST_CONTACT));
        }

        return $response;
    }

    /**
     * @param string $type
     * @param array $options
     * @param \stdClass|null $response
     */
    private function logResponse(string $type, array $options, ?\stdClass $response): void
    {
        $logData['response'] = (array) json_decode(json_encode($response), true);
        $logData['options'] = $options;
        $this->logger->info('update intercom ' . $type, $logData);
    }
}
