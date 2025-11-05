# PayMongo Integration Setup Guide

## 📋 Prerequisites

1. PayMongo Account (Sign up at https://dashboard.paymongo.com)
2. Verified Business Account
3. API Keys from PayMongo Dashboard

---

## 🚀 Step-by-Step Setup

### Step 1: Get PayMongo API Keys

1. Go to https://dashboard.paymongo.com
2. Navigate to **Developers** → **API Keys**
3. Copy your:
   - **Secret Key** (starts with `sk_test_` for testing)
   - **Public Key** (starts with `pk_test_` for testing)

### Step 2: Update .env File

```properties
# PayMongo API Keys
PAYMONGO_SECRET_KEY=sk_test_your_actual_secret_key_here
PAYMONGO_PUBLIC_KEY=pk_test_your_actual_public_key_here
```

⚠️ **Important:** Never commit your production keys to version control!

### Step 3: Database Migration

Run the SQL migration to update your database:

```sql
-- Add payment_intent_id column
ALTER TABLE orders 
ADD COLUMN payment_intent_id VARCHAR(255) NULL AFTER payment_status,
ADD INDEX idx_payment_intent (payment_intent_id);

-- Update payment methods
ALTER TABLE orders 
MODIFY COLUMN payment_method ENUM('cod','gcash','paymaya','card','grab_pay','bank') NOT NULL;
```

### Step 4: Create Directory Structure

```
STARROOFING/
├── user/
│   ├── payment/
│   │   └── paymongo-helper.php  (NEW)
│   ├── pages/
│   │   ├── checkout.php  (UPDATED)
│   │   ├── payment-return.php  (NEW)
│   │   ├── payment-failed.php  (NEW)
│   │   └── order-success.php  (EXISTING)
│   └── process/
│       └── process-order.php  (UPDATED)
└── .env  (UPDATED)
```

### Step 5: File Placement

1. **paymongo-helper.php** → Save to `user/payment/paymongo-helper.php`
2. **checkout.php (updated)** → Replace existing `user/pages/checkout.php`
3. **process-order.php (updated)** → Replace existing `user/process/process-order.php`
4. **payment-return.php** → Save to `user/pages/payment-return.php`
5. **payment-failed.php** → Save to `user/pages/payment-failed.php`

---

## 🧪 Testing

### Test Mode

PayMongo provides test cards for development:

#### Test Card Numbers:
- **Success:** `4343434343434345`
- **Failed:** `4571736000000075`
- **3D Secure:** `4120000000000007`

#### Test Details:
- **CVV:** Any 3 digits (e.g., 123)
- **Expiry:** Any future date (e.g., 12/25)
- **Name:** Any name

### GCash Test Account:
- **Mobile:** `09123456789`
- **OTP:** `123456`

### PayMaya Test Account:
- **Card:** `5453010000064154`
- **CVV:** `123`
- **Expiry:** Any future date

---

## 🔄 Payment Flow

```
1. Customer selects items → Checkout
2. Customer chooses payment method
3. System creates PayMongo payment intent/source
4. Customer redirected to PayMongo checkout
5. Customer completes payment
6. PayMongo redirects back to payment-return.php
7. System verifies payment status
8. Updates order status in database
9. Shows success/failed page
```

---

## 📊 Supported Payment Methods

| Method | Type | Description |
|--------|------|-------------|
| COD | Cash | Cash on Delivery |
| GCash | E-Wallet | Mobile wallet payment |
| PayMaya | E-Wallet | PayMaya account |
| Card | Card | Visa, Mastercard, JCB |
| GrabPay | E-Wallet | GrabPay wallet |

---

## 🔒 Security Best Practices

1. **Never expose secret keys** in client-side code
2. **Use HTTPS** in production
3. **Validate webhook signatures** (implement webhook handler)
4. **Store sensitive data encrypted**
5. **Implement rate limiting** on payment endpoints
6. **Log all transactions** for audit trail

---

## 🐛 Troubleshooting

### Error: "PayMongo API Error"
- Check if API keys are correct in .env
- Verify internet connection
- Check PayMongo status page

### Error: "Payment URL not generated"
- Ensure payment amount is at least ₱100
- Check if payment method is supported
- Verify PayMongo account is verified

### Payment stuck in "pending"
- Implement webhook handler for async updates
- Check PayMongo dashboard for payment status
- Verify return URL is accessible

---

## 📝 Next Steps

1. **Implement Webhooks** for real-time payment updates
2. **Add refund functionality** for cancelled orders
3. **Setup email notifications** for payment confirmations
4. **Add payment receipts** generation
5. **Implement installment payments** (if needed)

---

## 🔗 Useful Links

- [PayMongo Documentation](https://developers.paymongo.com/docs)
- [API Reference](https://developers.paymongo.com/reference)
- [Test Cards](https://developers.paymongo.com/docs/testing)
- [Webhook Guide](https://developers.paymongo.com/docs/webhooks)

---

## ⚠️ Important Notes

### Before Going Live:

1. ✅ Replace test API keys with production keys
2. ✅ Test all payment methods thoroughly
3. ✅ Setup webhook handler
4. ✅ Configure proper error logging
5. ✅ Add monitoring and alerts
6. ✅ Backup database regularly
7. ✅ Review and accept PayMongo Terms of Service
8. ✅ Complete business verification
9. ✅ Setup proper SSL certificate
10. ✅ Test refund process

### Webhook Implementation (Recommended):

Create `user/webhooks/paymongo-webhook.php`:

```php
<?php
require_once '../../database/starroofing_db.php';

// Get webhook payload
$payload = file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_PAYMONGO_SIGNATURE'] ?? '';

// Verify signature
// ... implement signature verification

$event = json_decode($payload, true);

if ($event['data']['attributes']['type'] === 'payment.paid') {
    $payment_intent_id = $event['data']['attributes']['data']['id'];
    
    // Update order status
    $query = "UPDATE orders SET payment_status = 'paid' WHERE payment_intent_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $payment_intent_id);
    $stmt->execute();
}

http_response_code(200);
?>
```

---

## 💡 Tips

- Start with test mode and small amounts
- Monitor PayMongo dashboard regularly
- Keep transaction logs for reconciliation
- Implement proper error handling
- Add user-friendly error messages
- Test on mobile devices
- Handle network timeouts gracefully

---

## 📞 Support

For PayMongo issues:
- Email: support@paymongo.com
- Dashboard: https://dashboard.paymongo.com
- Documentation: https://developers.paymongo.com

For integration issues:
- Check error logs in your application
- Review PayMongo API response errors
- Test with PayMongo test environment first