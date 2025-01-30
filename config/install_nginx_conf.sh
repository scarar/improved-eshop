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

print_status "Installing Nginx configuration..."

# Backup existing configuration
if [ -f /etc/nginx/nginx.conf ]; then
    print_status "Backing up existing configuration..."
    cp /etc/nginx/nginx.conf /etc/nginx/nginx.conf.bak
    print_success "Backup created at /etc/nginx/nginx.conf.bak"
fi

# Copy configuration file
print_status "Copying nginx.conf..."
cp nginx.conf /etc/nginx/nginx.conf
chmod 644 /etc/nginx/nginx.conf
print_success "Configuration copied"

# Create required directories
print_status "Creating required directories..."
mkdir -p /tmp/nginx/{client_temp,proxy_temp,fastcgi_temp,uwsgi_temp,scgi_temp}
chown -R www-data:www-data /tmp/nginx
chmod -R 755 /tmp/nginx
print_success "Directories created"

# Set permissions for web root
print_status "Setting web root permissions..."
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
systemctl restart php8.2-fpm

print_success "Installation complete!"
echo -e "${YELLOW}Please check the following:${NC}"
echo "1. Verify logs for any errors:"
echo "   - /var/log/nginx/error.log"
echo "   - /var/log/nginx/access.log"
echo "2. Check PHP-FPM socket:"
echo "   ls -l /var/run/php/php8.2-fpm.sock"
echo "3. Monitor resource usage:"
echo "   htop"
echo "4. Test the website through Tor"