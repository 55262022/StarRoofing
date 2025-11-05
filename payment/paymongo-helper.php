<?php
class PayMongoHelper {
    private $secretKey;
    private $publicKey;
    private $baseUrl = 'https://api.paymongo.com/v1';

    public function __construct() {
        // Load environment variables
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                list($key, $value) = explode('=', $line, 2);
                putenv(trim($key) . '=' . trim($value));
            }
        }

        $this->secretKey = getenv('PAYMONGO_SECRET_KEY');
        $this->publicKey = getenv('PAYMONGO_PUBLIC_KEY');
    }

    /**
     * Create a Payment Intent for GCash, PayMaya, Cards
     */
    public function createPaymentIntent($amount, $description, $metadata = []) {
        $url = $this->baseUrl . '/payment_intents';
        
        $data = [
            'data' => [
                'attributes' => [
                    'amount' => (int)($amount * 100), // Convert to centavos
                    'payment_method_allowed' => ['gcash', 'paymaya', 'card'],
                    'payment_method_options' => [
                        'card' => [
                            'request_three_d_secure' => 'any'
                        ]
                    ],
                    'currency' => 'PHP',
                    'description' => $description,
                    'statement_descriptor' => 'Star Roofing',
                    'metadata' => $metadata
                ]
            ]
        ];

        return $this->makeRequest('POST', $url, $data);
    }

    /**
     * Create Payment Method for the intent
     */
    public function createPaymentMethod($type, $details = []) {
        $url = $this->baseUrl . '/payment_methods';
        
        $data = [
            'data' => [
                'attributes' => [
                    'type' => $type,
                    'details' => $details
                ]
            ]
        ];

        return $this->makeRequest('POST', $url, $data);
    }

    /**
     * Attach Payment Method to Payment Intent
     */
    public function attachPaymentIntent($intentId, $paymentMethodId, $returnUrl) {
        $url = $this->baseUrl . '/payment_intents/' . $intentId . '/attach';
        
        $data = [
            'data' => [
                'attributes' => [
                    'payment_method' => $paymentMethodId,
                    'return_url' => $returnUrl
                ]
            ]
        ];

        return $this->makeRequest('POST', $url, $data);
    }

    /**
     * Retrieve Payment Intent
     */
    public function getPaymentIntent($intentId) {
        $url = $this->baseUrl . '/payment_intents/' . $intentId;
        return $this->makeRequest('GET', $url);
    }

    /**
     * Create Source for Online Banking
     */
    public function createSource($amount, $type, $redirectUrl, $metadata = []) {
        $url = $this->baseUrl . '/sources';
        
        $data = [
            'data' => [
                'attributes' => [
                    'amount' => (int)($amount * 100),
                    'redirect' => [
                        'success' => $redirectUrl . '?status=success',
                        'failed' => $redirectUrl . '?status=failed'
                    ],
                    'type' => $type, // grab_pay, billease, etc
                    'currency' => 'PHP',
                    'metadata' => $metadata
                ]
            ]
        ];

        return $this->makeRequest('POST', $url, $data);
    }

    /**
     * Make API Request
     */
    private function makeRequest($method, $url, $data = null) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($this->secretKey . ':')
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('PayMongo API Error: ' . $error);
        }

        curl_close($ch);

        $result = json_decode($response, true);

        if ($httpCode >= 400) {
            $errorMessage = $result['errors'][0]['detail'] ?? 'Unknown error occurred';
            throw new Exception('PayMongo Error: ' . $errorMessage);
        }

        return $result;
    }

    /**
     * Get supported payment methods
     */
    public function getSupportedMethods() {
        return [
            'gcash' => [
                'name' => 'GCash',
                'icon' => 'fa-mobile-alt',
                'description' => 'Pay using your GCash wallet'
            ],
            'paymaya' => [
                'name' => 'PayMaya',
                'icon' => 'fa-wallet',
                'description' => 'Pay using your PayMaya account'
            ],
            'card' => [
                'name' => 'Credit/Debit Card',
                'icon' => 'fa-credit-card',
                'description' => 'Visa, Mastercard, JCB'
            ],
            'grab_pay' => [
                'name' => 'GrabPay',
                'icon' => 'fa-car',
                'description' => 'Pay using GrabPay'
            ]
        ];
    }
}
?>