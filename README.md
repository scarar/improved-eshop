# Improved E-Shop

A secure e-commerce platform with Bitcoin integration, running as a Tor hidden service.

## Current Status

- **PHP Version**: Updated to PHP 8.2 (from PHP 7.4)
- **Bitcoin Integration**: Mainnet support with RPC integration
- **Tor Integration**: Running as hidden service on port 8484
- **Security**: Enhanced with modern security practices

## Features

- Bitcoin payments (mainnet)
- Tor hidden service support
- Secure user authentication
- PGP 2FA support
- CSRF protection
- Rate limiting
- Input validation
- Secure session handling

## Requirements

- PHP 8.2 or higher
- Bitcoin Core (mainnet)
- Tor
- MySQL/MariaDB
- Composer

## Installation

1. Clone the repository:
```bash
git clone https://github.com/scarar/improved-eshop.git
cd improved-eshop
```

2. Install dependencies:
```bash
composer install
```

3. Configure environment:
```bash
cp .env.example .env
# Edit .env with your settings
```

4. Configure Tor hidden service:
```bash
# Add to /etc/tor/torrc:
HiddenServiceDir /var/lib/tor/ai-test
HiddenServicePort 80 127.0.0.1:8484
```

5. Configure Bitcoin Core:
```bash
# Update bitcoin.conf with provided settings
```

6. Import database:
```bash
mysql -u your_user -p your_database < sql_file/eshop.sql
```

## Configuration

### Bitcoin Core
- RPC enabled on port 8332
- Tor integration enabled
- Mainnet configuration

### Tor Hidden Service
- Running on port 8484
- Strict security headers
- CSP configuration
- Session handling for .onion

### Security Features
- CSRF protection
- Input validation
- Rate limiting
- Secure headers
- PGP 2FA
- Session security

## Development Status

### Completed
- [x] PHP 8.2 compatibility
- [x] Bitcoin Core integration
- [x] Tor hidden service
- [x] Security improvements
- [x] Session handling
- [x] User authentication
- [x] PGP 2FA

### In Progress
- [ ] Withdrawal system implementation
- [ ] Additional payment features
- [ ] Admin dashboard improvements
- [ ] Order management system

## Security

- All traffic through Tor
- No external resources
- Strict CSP
- Input validation
- CSRF protection
- Rate limiting
- PGP 2FA support

## Testing

1. Check Tor service:
```bash
php check_tor.php
```

2. Verify Bitcoin connection:
```bash
php check_bitcoin.php
```

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgments

- Original E-SHOP-Main project
- Bitcoin Core team
- Tor Project
