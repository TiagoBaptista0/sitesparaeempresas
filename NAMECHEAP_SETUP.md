# 🧱 Namecheap Automation Setup (Sandbox Mode)

## 📁 File Structure

```
/api/
├── namecheap_helper.php      → Central API configuration
├── check_domain.php          → Check domain availability
├── register_domain.php       → Register domain automatically
├── set_dns.php               → Configure DNS nameservers
└── namecheap_webhook.php     → Automate entire process after payment
```

## 🔧 Configuration

All files use credentials from `namecheap_helper.php`:

- **API User**: TiagoBaptista13
- **API Key**: d528fc44618a47e789db98b20c772872
- **Username**: TiagoBaptista13
- **Client IP**: 167.250.106.18
- **Environment**: Sandbox (https://api.sandbox.namecheap.com/xml.response)

## 🧪 Testing Endpoints

### 1. Check Domain Availability
```
GET /api/check_domain.php?domain=meutestedominiotiago.com
```

**Response:**
```json
{
  "success": true,
  "domain": "meutestedominiotiago.com",
  "available": true,
  "isPremium": false,
  "premiumPrice": null
}
```

### 2. Register Domain
```
GET /api/register_domain.php?domain=meutestedominiotiago.com&firstName=Tiago&lastName=Baptista&email=contato@sitesparaempresas.com
```

**Optional Parameters:**
- `firstName` (default: Tiago)
- `lastName` (default: Baptista)
- `email` (default: contato@sitesparaempresas.com)
- `phone` (default: +55.19999999999)
- `address` (default: Rua Exemplo, 123)
- `city` (default: São José do Rio Pardo)
- `state` (default: SP)
- `postalCode` (default: 13720000)
- `country` (default: BR)

**Response:**
```json
{
  "success": true,
  "message": "Domain registered successfully",
  "domainId": "12345",
  "domain": "meutestedominiotiago.com",
  "orderId": "67890"
}
```

### 3. Configure DNS
```
GET /api/set_dns.php?domain=meutestedominiotiago.com&nameservers=ns1.testeempresa.com,ns2.testeempresa.com
```

**Response:**
```json
{
  "success": true,
  "message": "DNS configured successfully",
  "domain": "meutestedominiotiago.com",
  "nameservers": "ns1.testeempresa.com,ns2.testeempresa.com"
}
```

## 🚀 Webhook Integration (Automated Process)

### Trigger Domain Registration After Payment

**POST** `/api/namecheap_webhook.php`

```json
{
  "action": "register_domain",
  "orderId": "12345",
  "userId": "1",
  "domain": "meutestedominiotiago.com"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Domain registered successfully",
  "domainId": "12345",
  "domain": "meutestedominiotiago.com"
}
```

### Configure DNS After Registration

**POST** `/api/namecheap_webhook.php`

```json
{
  "action": "set_dns",
  "orderId": "12345",
  "domain": "meutestedominiotiago.com",
  "nameservers": "ns1.hostinger.com,ns2.hostinger.com"
}
```

## 🔄 Complete Automation Flow

1. **User purchases plan** → Payment confirmed
2. **Webhook triggered** → `register_domain` action
3. **Domain registered** → Namecheap creates domain
4. **DNS configured** → Nameservers set to Hostinger
5. **Site deployed** → Automatic site creation
6. **Email sent** → Confirmation to customer

## 🌐 Switching to Production

Change one line in `namecheap_helper.php`:

```php
// From:
define('NAMECHEAP_API_URL', 'https://api.sandbox.namecheap.com/xml.response');

// To:
define('NAMECHEAP_API_URL', 'https://api.namecheap.com/xml.response');
```

And update nameservers in `set_dns.php`:

```php
// From:
$nameservers = 'ns1.testeempresa.com,ns2.testeempresa.com';

// To:
$nameservers = 'dns1.hostinger.com,dns2.hostinger.com';
```

## 📝 Database Updates

The webhook automatically updates the `orders` table:

- After domain registration: `status = 'domain_registered'`, `domain_id = <namecheap_id>`
- After DNS configuration: `status = 'dns_configured'`

## ⚠️ Important Notes

- **Sandbox Mode**: Domains are not actually registered, only simulated
- **Testing**: Use any domain name in sandbox (e.g., `meutestedominiotiago.com`)
- **Production**: Real domains will be registered and charged
- **DNS**: In sandbox, use fictional nameservers; in production, use real ones
- **Contact Info**: Required for domain registration (Registrant, Tech, Admin, AuxBilling)

## 🔐 Security

- API credentials are centralized in `namecheap_helper.php`
- All endpoints return JSON responses
- Error handling with try-catch blocks
- Database integration for order tracking
