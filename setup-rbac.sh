#!/bin/bash

echo "============================================"
echo "RBAC Setup Script"
echo "============================================"
echo ""
echo "This script will set up Role-Based Access Control"
echo "for your Runyakitara Hub application."
echo ""
read -p "Press Enter to continue or Ctrl+C to cancel..."
echo ""

echo "Running RBAC migration..."
php migrate-to-rbac.php

echo ""
echo "============================================"
echo "Setup Complete!"
echo "============================================"
echo ""
echo "Next steps:"
echo "1. Review RBAC-GUIDE.md for usage instructions"
echo "2. Access admin/roles-manage.php to manage roles"
echo "3. Update your admin pages to use RBAC permissions"
echo ""
echo "Default admin account has been assigned super_admin role."
echo ""
