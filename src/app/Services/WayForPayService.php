<?php

namespace App\Services;

final class WayForPayService
{
    public function __construct(
        private readonly string $merchantAccount,
        private readonly string $secretKey,
        private readonly string $merchantDomainName,
    ) {}

    /**
     * @param array<int, string> $productNames
     * @param array<int, int|float|string> $productCounts
     * @param array<int, int|float|string> $productPrices
     * @return array<string, mixed>
     */
    public function buildPurchaseRequest(
        string $orderReference,
        int $orderDateUnix,
        float $amount,
        string $currency,
        array $productNames,
        array $productCounts,
        array $productPrices,
        ?string $returnUrl = null,
        ?string $serviceUrl = null,
        string $language = 'UA',
    ): array {
        $signatureStringParts = [
            $this->merchantAccount,
            $this->merchantDomainName,
            $orderReference,
            (string) $orderDateUnix,
            $this->formatAmount($amount),
            $currency,
            ...$productNames,
            ...array_map(fn ($v) => (string) $v, $productCounts),
            ...array_map(fn ($v) => (string) $v, $productPrices),
        ];

        $payload = [
            'merchantAccount' => $this->merchantAccount,
            'merchantAuthType' => 'SimpleSignature',
            'merchantDomainName' => $this->merchantDomainName,
            'merchantTransactionSecureType' => 'AUTO',
            'merchantSignature' => $this->hmacMd5(implode(';', $signatureStringParts)),
            'orderReference' => $orderReference,
            'orderDate' => $orderDateUnix,
            'amount' => $this->formatAmount($amount),
            'currency' => $currency,
            'productName' => $productNames,
            'productPrice' => array_map(fn ($v) => (string) $v, $productPrices),
            'productCount' => array_map(fn ($v) => (string) $v, $productCounts),
            'language' => $language,
        ];

        if ($returnUrl) {
            $payload['returnUrl'] = $returnUrl;
        }
        if ($serviceUrl) {
            $payload['serviceUrl'] = $serviceUrl;
        }

        return $payload;
    }

    /**
     * Проверка подписи callback (serviceUrl) от WayForPay.
     *
     * По документации строка: merchantAccount;orderReference;amount;currency;authCode;cardPan;transactionStatus;reasonCode
     *
     * @param array<string, mixed> $callback
     */
    public function isValidCallbackSignature(array $callback): bool
    {
        $merchantSignature = (string) ($callback['merchantSignature'] ?? '');
        if ($merchantSignature === '') {
            return false;
        }

        $parts = [
            (string) ($callback['merchantAccount'] ?? ''),
            (string) ($callback['orderReference'] ?? ''),
            (string) ($callback['amount'] ?? ''),
            (string) ($callback['currency'] ?? ''),
            (string) ($callback['authCode'] ?? ''),
            (string) ($callback['cardPan'] ?? ''),
            (string) ($callback['transactionStatus'] ?? ''),
            (string) ($callback['reasonCode'] ?? ''),
        ];

        $expected = $this->hmacMd5(implode(';', $parts));

        return hash_equals($expected, $merchantSignature);
    }

    /**
     * Подпись ответа мерчанта на callback: orderReference;status;time
     */
    public function signCallbackResponse(string $orderReference, string $status, int $timeUnix): string
    {
        return $this->hmacMd5($orderReference.';'.$status.';'.$timeUnix);
    }

    private function hmacMd5(string $string): string
    {
        return hash_hmac('md5', $string, $this->secretKey);
    }

    private function formatAmount(float $amount): string
    {
        // WayForPay ожидает строку суммы, обычно с 2 знаками после запятой
        return number_format($amount, 2, '.', '');
    }
}

