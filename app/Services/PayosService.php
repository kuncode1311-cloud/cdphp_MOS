<?php

namespace App\Services;

use App\Models\PackageOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service tích hợp Cổng thanh toán trực tuyến PayOS (Chuẩn HMAC-SHA256)
 * 
 * Tạo mã QR thanh toán động và xác thực Webhook tự động kích hoạt gói bản quyền.
 */
class PayosService
{
    protected string $clientId;
    protected string $apiKey;
    protected string $checksumKey;
    protected string $endpoint;

    public function __construct()
    {
        $this->clientId = config('services.payos.client_id', '');
        $this->apiKey = config('services.payos.api_key', '');
        $this->checksumKey = config('services.payos.checksum_key', '');
        $this->endpoint = rtrim(config('services.payos.endpoint', 'https://api-merchant.payos.vn'), '/');
    }

    /**
     * Kiểm tra cấu hình PayOS đã đầy đủ chưa
     */
    public function isConfigured(): bool
    {
        return ! empty($this->clientId) && ! empty($this->apiKey) && ! empty($this->checksumKey);
    }

    /**
     * Tạo Link thanh toán PayOS cho đơn hàng
     * 
     * @return array{ok: bool, checkoutUrl?: string, qrCode?: string, message?: string}
     */
    public function createPaymentLink(PackageOrder $order, string $returnUrl, string $cancelUrl): array
    {
        if (! $this->isConfigured()) {
            return [
                'ok' => false,
                'message' => 'Hệ thống chưa cấu hình thông tin kết nối PayOS.',
            ];
        }

        // PayOS yêu cầu orderCode là số nguyên dương <= 9007199254740991
        // Tạo numeric code từ ID hoặc timestamp
        $numericCode = (int) ($order->id . date('dHi'));
        $amount = (int) $order->price;

        if ($amount <= 0) {
            return [
                'ok' => false,
                'message' => 'Số tiền thanh toán không hợp lệ.',
            ];
        }

        // Description tối đa 25 ký tự, không dấu
        $description = 'MOS ' . substr(preg_replace('/[^a-zA-Z0-9]/', '', $order->code), -15);

        $payload = [
            'orderCode' => $numericCode,
            'amount' => $amount,
            'description' => $description,
            'returnUrl' => $returnUrl,
            'cancelUrl' => $cancelUrl,
        ];

        $payload['signature'] = $this->generateSignature($payload);

        try {
            $response = Http::withHeaders([
                'x-client-id' => $this->clientId,
                'x-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(12)->post("{$this->endpoint}/v2/payment-requests", $payload);

            if (! $response->successful()) {
                Log::error('PayOS Create Payment Failed: ' . $response->body());
                return [
                    'ok' => false,
                    'message' => 'Cổng PayOS từ chối yêu cầu: ' . ($response->json('desc') ?? 'Lỗi không xác định'),
                ];
            }

            $resData = $response->json();
            if (($resData['code'] ?? '') !== '00') {
                return [
                    'ok' => false,
                    'message' => $resData['desc'] ?? 'Không thể tạo link thanh toán PayOS.',
                ];
            }

            $data = $resData['data'] ?? [];

            return [
                'ok' => true,
                'checkoutUrl' => $data['checkoutUrl'] ?? '',
                'qrCode' => $data['qrCode'] ?? '',
                'bin' => $data['bin'] ?? '',
                'accountNumber' => $data['accountNumber'] ?? '',
                'accountName' => $data['accountName'] ?? '',
                'orderCode' => $numericCode,
            ];
        } catch (\Throwable $e) {
            Log::error('PayOS Exception: ' . $e->getMessage());
            return [
                'ok' => false,
                'message' => 'Lỗi kết nối tới máy chủ PayOS: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Xác thực dữ liệu Webhook từ PayOS gửi sang
     */
    public function verifyWebhookData(array $webhookPayload): ?array
    {
        $signature = $webhookPayload['signature'] ?? null;
        $data = $webhookPayload['data'] ?? null;

        if (! $signature || ! is_array($data)) {
            return null;
        }

        $calculatedSignature = $this->generateSignature($data);

        if (! hash_equals($calculatedSignature, $signature)) {
            Log::warning('PayOS Webhook Invalid Signature', [
                'expected' => $calculatedSignature,
                'received' => $signature,
            ]);
            return null;
        }

        return $data;
    }

    /**
     * Tạo chữ ký HMAC-SHA256 theo thuật toán của PayOS
     * Sắp xếp các khóa theo alphabet, nối thành key=value&...
     */
    protected function generateSignature(array $data): string
    {
        ksort($data);
        $parts = [];
        foreach ($data as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            if (is_array($value) || is_object($value)) {
                continue;
            }
            $parts[] = "{$key}={$value}";
        }

        $queryString = implode('&', $parts);

        return hash_hmac('sha256', $queryString, $this->checksumKey);
    }
}
