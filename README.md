# MoneyHash Payment Gateway for Magento 2

A secure and unified payment gateway integration for Magento 2, enabling merchants across Africa and the Middle East to accept and process payments seamlessly.

## 📦 Module Information

- Module Name: `MoneyHash_Payment`
- Composer Package: `moneyhash/magento2-payment`
- Version: `1.0.0`
- Magento Compatibility: `2.4.2` and above
- PHP Compatibility: `8.1` – `8.4`
- Website: https://moneyhash.io
---

## 🚀 Installation Instructions

### 1. Install via Composer (Recommended)

```
composer require moneyhash/magento2-payment
```

If you are using a private repository or downloaded the package manually, make sure the module is placed under:
```
app/code/MoneyHash/Payment
```

---

### 2. Enable the Module

```
php bin/magento module:enable MoneyHash_Payment
```

---

### 3. Run Setup Upgrade

```
php bin/magento setup:upgrade
```

---

### 4. Compile and Deploy Static Content (Production Mode Only)

```
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
```

---

### 5. Clear Cache

```
php bin/magento cache:flush
```

---

## 🧭 Configuration Guide

1. Log in to Magento Admin.
2. Go to **Stores → Configuration → Sales → Payment Methods**.
3. Find **MoneyHash Payment**.
4. Enter your API Keys and configure the desired payment settings.
5. Save Config and Clear Cache.

---

## 🔐 Features

- Secure payment processing with MoneyHash
- Unified checkout experience for multiple payment methods
- Supports refunds, order synchronization, and real-time transaction status
- Compatible with Magento’s native checkout and sales workflows
- PCI-DSS compliant

---

## 🆘 Support

- Email: support@moneyhash.io
- Issues: https://github.com/moneyhash/magento2-payment/issues
- Docs: https://docs.moneyhash.io/

---

## 📝 License

This extension is proprietary and licensed to MoneyHash.  
Unauthorized redistribution or modification is prohibited.

---

© 2025 MoneyHash — All rights reserved.
