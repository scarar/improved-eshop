#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Function to print status
print_status() {
    echo -e "${YELLOW}[*] $1${NC}"
}

# Function to print success
print_success() {
    echo -e "${GREEN}[+] $1${NC}"
}

# Function to print error
print_error() {
    echo -e "${RED}[-] $1${NC}"
}

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    print_error "Please run as root"
    exit 1
fi

print_status "Installing Tor site configuration..."

# Backup existing configuration if it exists
if [ -f /etc/nginx/sites-available/tor-site ]; then
    print_status "Backing up existing configuration..."
    cp /etc/nginx/sites-available/tor-site /etc/nginx/sites-available/tor-site.bak
    print_success "Backup created at /etc/nginx/sites-available/tor-site.bak"
fi

# Copy configuration file
print_status "Copying tor-site configuration..."
cp tor-site /etc/nginx/sites-available/
chmod 644 /etc/nginx/sites-available/tor-site
print_success "Configuration copied"

# Remove default site if it exists
if [ -f /etc/nginx/sites-enabled/default ]; then
    print_status "Removing default site..."
    rm /etc/nginx/sites-enabled/default
    print_success "Default site removed"
fi

# Create symlink if it doesn't exist
if [ ! -f /etc/nginx/sites-enabled/tor-site ]; then
    print_status "Creating symlink..."
    ln -s /etc/nginx/sites-available/tor-site /etc/nginx/sites-enabled/
    print_success "Symlink created"
fi

# Check if document root exists
if [ ! -d /var/www/html ]; then
    print_status "Creating document root..."
    mkdir -p /var/www/html
    print_success "Document root created"
fi

# Set permissions
print_status "Setting permissions..."
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
print_success "Permissions set"

# Test nginx configuration
print_status "Testing Nginx configuration..."
nginx -t
if [ $? -eq 0 ]; then
    print_success "Nginx configuration test passed"
else
    print_error "Nginx configuration test failed"
    exit 1
fi

# Reload services
print_status "Reloading services..."
systemctl reload nginx
systemctl restart tor
systemctl restart php8.2-fpm

print_success "Installation complete!"
echo -e "${YELLOW}Please check the following:${NC}"
echo "1. Update /etc/tor/torrc with:"
echo "   HiddenServiceDir /var/lib/tor/ai-test"
echo "   HiddenServicePort 80 127.0.0.1:8484"
echo "2. Update your .onion address in /etc/nginx/sites-available/tor-site"
echo "3. Verify PHP-FPM socket path in the configuration"
echo "4. Check the logs if you encounter any issues:"
echo "   - /var/log/nginx/error.log"
echo "   - /var/log/tor/notices.log"
echo "   - /var/log/php8.2-fpm.log"