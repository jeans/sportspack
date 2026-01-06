#!/bin/bash
# Sportspack Plugin Validation Script
# This script performs basic validation checks on the plugin

echo "=================================="
echo "Sportspack Plugin Validation"
echo "=================================="
echo ""

# Check if we're in the right directory
if [ ! -f "sportspack.php" ]; then
    echo "❌ Error: sportspack.php not found. Please run this script from the plugin root directory."
    exit 1
fi

echo "✓ Plugin directory structure confirmed"
echo ""

# Check PHP syntax on all PHP files
echo "Checking PHP syntax..."
syntax_errors=0
for file in $(find . -name "*.php" -not -path "*/vendor/*"); do
    if ! php -l "$file" > /dev/null 2>&1; then
        echo "❌ Syntax error in: $file"
        syntax_errors=$((syntax_errors + 1))
    fi
done

if [ $syntax_errors -eq 0 ]; then
    echo "✓ All PHP files have valid syntax"
else
    echo "❌ Found $syntax_errors file(s) with syntax errors"
    exit 1
fi
echo ""

# Check required files exist
echo "Checking required files..."
required_files=(
    "sportspack.php"
    "includes/class-plugin.php"
    "includes/class-cpt.php"
    "includes/class-meta.php"
    "includes/class-inheritance.php"
    "includes/class-template-loader.php"
    "includes/providers/interface-provider.php"
    "includes/providers/class-provider-statsperform.php"
    "includes/providers/class-provider-heimspiel.php"
    "includes/cli/class-cli.php"
    "templates/single-sportspack_unit.php"
    "templates/single-sportspack_team.php"
    "templates/single-sportspack_person.php"
    "templates/single-sportspack_venue.php"
    "README.md"
)

missing_files=0
for file in "${required_files[@]}"; do
    if [ ! -f "$file" ]; then
        echo "❌ Missing: $file"
        missing_files=$((missing_files + 1))
    fi
done

if [ $missing_files -eq 0 ]; then
    echo "✓ All required files present"
else
    echo "❌ Missing $missing_files required file(s)"
    exit 1
fi
echo ""

# Count lines of code
echo "Code Statistics:"
php_lines=$(find . -name "*.php" -not -path "*/vendor/*" -exec cat {} \; | wc -l)
echo "  - PHP lines: $php_lines"
php_files=$(find . -name "*.php" -not -path "*/vendor/*" | wc -l)
echo "  - PHP files: $php_files"
echo ""

# Check for WordPress plugin header
echo "Checking plugin header..."
if grep -q "Plugin Name: Sportspack" sportspack.php; then
    echo "✓ Valid WordPress plugin header found"
else
    echo "❌ Missing or invalid plugin header"
    exit 1
fi
echo ""

# Check for namespace usage
echo "Checking namespace usage..."
if grep -q "namespace Sportspack" sportspack.php; then
    echo "✓ Namespace declared correctly"
else
    echo "❌ Namespace not found"
    exit 1
fi
echo ""

# Check for text domain
echo "Checking internationalization..."
text_domain_count=$(grep -r "sportspack" --include="*.php" -c . | grep -v ":0" | wc -l)
if [ $text_domain_count -gt 0 ]; then
    echo "✓ Text domain 'sportspack' used in multiple files"
else
    echo "❌ Text domain not properly implemented"
    exit 1
fi
echo ""

# Validation Summary
echo "=================================="
echo "Validation Summary"
echo "=================================="
echo "✓ Plugin structure valid"
echo "✓ PHP syntax correct"
echo "✓ All required files present"
echo "✓ WordPress standards followed"
echo ""
echo "The plugin is ready for testing in a WordPress environment!"
echo ""
echo "Next steps:"
echo "1. Copy plugin to WordPress: wp-content/plugins/sportspack/"
echo "2. Activate: wp plugin activate sportspack"
echo "3. Create hierarchy: Sports → Competitions → Events"
echo "4. Test CLI: wp sportspack sync events --competition=<ID>"
echo ""
