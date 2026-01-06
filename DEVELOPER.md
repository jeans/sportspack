# Sportspack Developer Documentation

## Overview

Sportspack is a high-performance WordPress plugin designed for managing sports data hierarchies and entities. It provides a flexible system for organizing Sports → Competitions → Events with intelligent field inheritance and provider integration.

## Architecture

### Core Components

1. **Hierarchical CPT System** (`class-cpt.php`)
   - Single hierarchical post type: `sportspack_unit`
   - Three non-hierarchical post types: `sportspack_team`, `sportspack_person`, `sportspack_venue`

2. **Inheritance Engine** (`class-inheritance.php`)
   - Smart field inheritance from parent to child
   - Object cache integration for performance
   - Automatic cache invalidation

3. **Provider System** (`providers/`)
   - Pluggable architecture for data sources
   - Built-in support for StatsPerform and Heimspiel
   - Easy to extend with new providers

4. **Template System** (`class-template-loader.php`)
   - Theme-aware template loading
   - Breadcrumb generation
   - Hierarchical content display

5. **WP-CLI Integration** (`cli/class-cli.php`)
   - Automated event synchronization
   - Bulk operations support

## Installation

### Standard Installation

1. Upload the plugin to `/wp-content/plugins/sportspack/`
2. Activate via WordPress admin or WP-CLI:
   ```bash
   wp plugin activate sportspack
   ```
3. Flush permalinks (automatic on activation)

### Development Setup

```bash
# Install development dependencies
composer install

# Run code standards check (note: requires proper PHPCS setup)
composer phpcs
```

## Basic Usage

### Creating a Sports Hierarchy

#### Via WordPress Admin

1. **Create a Sport** (Top Level)
   - Navigate to Sports → Add New
   - Title: "Football"
   - Set Parent: (None) - this makes it a top-level Sport
   - In Sportspack Settings box:
     - Logo: Enter attachment ID or image URL
     - Remote Provider: Select "StatsPerform"
     - Remote ID: "football-001"

2. **Create a Competition** (Child of Sport)
   - Navigate to Sports → Add New
   - Title: "Premier League"
   - Set Parent: "Football"
   - Sportspack Settings: Leave empty to inherit from Sport, or override

3. **Create Events** (Children of Competition)
   - Navigate to Sports → Add New
   - Title: "Manchester United vs Liverpool"
   - Set Parent: "Premier League"
   - Settings inherit from Competition/Sport unless overridden

#### Via WP-CLI

```bash
# Create Sport
wp post create --post_type=sportspack_unit --post_title="Football" --post_status=publish

# Get the ID from output (e.g., 100), then create Competition
wp post create --post_type=sportspack_unit --post_title="Premier League" --post_parent=100 --post_status=publish

# Set meta on Sport
wp post meta update 100 _sportspack_remote_provider statsperform
wp post meta update 100 _sportspack_remote_id football-001
```

### Syncing Events from Provider

```bash
# Sync next 30 days of events (default)
wp sportspack sync events --competition=101

# Sync next 14 days
wp sportspack sync events --competition=101 --days=14

# Override provider for this sync
wp sportspack sync events --competition=101 --provider=heimspiel --days=7
```

## API Reference

### Inheritance Class

```php
use Sportspack\Inheritance;

$inheritance = Inheritance::get_instance();

// Get inherited values
$logo = $inheritance->get_inherited_logo( $post_id );
$provider = $inheritance->get_inherited_provider( $post_id );
$remote_id = $inheritance->get_inherited_remote_id( $post_id );

// Get hierarchy information
$level = $inheritance->get_hierarchy_level( $post_id ); // 0, 1, or 2
$label = $inheritance->get_hierarchy_label( $post_id ); // "Sport", "Competition", or "Event"

// Clear cache
$inheritance->clear_cache( $post_id );
```

### Template Loader Class

```php
use Sportspack\Template_Loader;

$loader = Template_Loader::get_instance();

// Get breadcrumbs array
$breadcrumbs = $loader->get_breadcrumbs( $post_id );
// Returns: [['id' => 100, 'title' => 'Football', 'url' => '...'], ...]

// Render breadcrumbs HTML
$loader->render_breadcrumbs( $post_id );
```

### Meta Class

```php
use Sportspack\Meta;

$meta = Meta::get_instance();

// Sanitize provider value
$clean_provider = $meta->sanitize_provider( $input );
// Returns: 'statsperform', 'heimspiel', 'sportradar', 'custom', or ''
```

## Custom Provider Implementation

### Creating a New Provider

1. Create a new class implementing `Provider_Interface`:

```php
<?php
namespace Sportspack\Providers;

class Provider_CustomAPI implements Provider_Interface {
    
    private $api_key;
    
    public function __construct( $api_key = '' ) {
        $this->api_key = $api_key;
    }
    
    public function get_name() {
        return 'customapi';
    }
    
    public function fetch_events( $remote_id, $days = 30 ) {
        // Make API call
        $response = wp_remote_get(
            "https://api.example.com/events?competition={$remote_id}&days={$days}",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->api_key,
                ],
            ]
        );
        
        if ( is_wp_error( $response ) ) {
            return [];
        }
        
        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        
        // Transform to expected format
        $events = [];
        foreach ( $body['events'] as $event ) {
            $events[] = [
                'remote_id' => $event['id'],
                'title'     => $event['name'],
                'content'   => $event['description'],
            ];
        }
        
        return $events;
    }
    
    public function is_configured() {
        return ! empty( $this->api_key );
    }
}
```

2. Register the provider in `CLI::get_provider()`:

```php
private static function get_provider( $provider_name ) {
    switch ( $provider_name ) {
        case 'statsperform':
            return new Provider_StatsPerform();
        case 'heimspiel':
            return new Provider_Heimspiel();
        case 'customapi':
            $api_key = get_option( 'sportspack_customapi_key' );
            return new Provider_CustomAPI( $api_key );
        default:
            return null;
    }
}
```

3. Add to provider enum in `Meta::sanitize_provider()`:

```php
$allowed_providers = [ 'statsperform', 'heimspiel', 'sportradar', 'custom', 'customapi', '' ];
```

## Template Customization

### Overriding Plugin Templates

Copy template from plugin to your theme:

```
From: wp-content/plugins/sportspack/templates/single-sportspack_unit.php
To:   wp-content/themes/your-theme/sportspack/single-sportspack_unit.php
```

Or directly:

```
To:   wp-content/themes/your-theme/single-sportspack_unit.php
```

### Template Data Available

In templates, you have access to:

```php
// Inheritance helper
$inheritance = Sportspack\Inheritance::get_instance();
$level = $inheritance->get_hierarchy_level( get_the_ID() );
$label = $inheritance->get_hierarchy_label( get_the_ID() );
$logo = $inheritance->get_inherited_logo( get_the_ID() );

// Template loader helper
$loader = Sportspack\Template_Loader::get_instance();
$loader->render_breadcrumbs( get_the_ID() );

// Standard WordPress functions
the_title();
the_content();
get_permalink();
// etc.
```

### Example Custom Template

```php
<?php
// wp-content/themes/your-theme/sportspack/single-sportspack_unit.php

use Sportspack\Inheritance;
use Sportspack\Template_Loader;

get_header();

$inheritance = Inheritance::get_instance();
$loader = Template_Loader::get_instance();
$level = $inheritance->get_hierarchy_level( get_the_ID() );
?>

<div class="sportspack-wrapper custom-design">
    <?php if ( $level > 0 ) : ?>
        <?php $loader->render_breadcrumbs( get_the_ID() ); ?>
    <?php endif; ?>
    
    <h1><?php the_title(); ?></h1>
    
    <div class="sportspack-content">
        <?php the_content(); ?>
    </div>
    
    <?php
    // Your custom code here
    // e.g., display related teams, upcoming events, etc.
    ?>
</div>

<?php get_footer(); ?>
```

## Filters and Hooks

### Available Filters

```php
// Mock or override provider event data
add_filter( 'sportspack_statsperform_fetch_events', function( $events, $remote_id, $days ) {
    // Return custom event array
    return $events;
}, 10, 3 );

add_filter( 'sportspack_heimspiel_fetch_events', function( $events, $remote_id, $days ) {
    // Return custom event array
    return $events;
}, 10, 3 );
```

### Example: Mock Provider Data for Testing

```php
// In your theme's functions.php or a custom plugin

add_filter( 'sportspack_statsperform_fetch_events', function( $events, $remote_id, $days ) {
    return [
        [
            'remote_id' => 'evt-001',
            'title'     => 'Team A vs Team B',
            'content'   => 'Match description here',
        ],
        [
            'remote_id' => 'evt-002',
            'title'     => 'Team C vs Team D',
            'content'   => 'Another match description',
        ],
    ];
}, 10, 3 );
```

## Performance Optimization

### Caching Strategy

The plugin uses WordPress Object Cache for inheritance resolution:

```php
// Cache key format: inheritance_{post_id}_{field}
// Example: inheritance_123_logo

// Cache duration: 1 hour (3600 seconds)
// Cache group: 'sportspack_inheritance'
```

### Recommendations

1. **Use a persistent object cache** (Redis, Memcached) for production
2. **Clear cache after bulk operations**:
   ```bash
   wp cache flush
   ```
3. **Limit recursion depth** - Plugin already limits to 3 levels (Sport → Competition → Event)

## Security Best Practices

### Input Validation

The plugin follows WordPress security best practices:

1. **Nonce verification** on all form submissions
2. **Capability checks** before operations
3. **Sanitization** on all inputs
4. **Escaping** on all outputs
5. **Prepared statements** via WordPress APIs

### API Key Management

For production providers, store API keys securely:

```php
// In wp-config.php
define( 'SPORTSPACK_STATSPERFORM_KEY', 'your-api-key-here' );

// In your provider class
$api_key = defined( 'SPORTSPACK_STATSPERFORM_KEY' ) 
    ? SPORTSPACK_STATSPERFORM_KEY 
    : get_option( 'sportspack_statsperform_key' );
```

## Troubleshooting

### Events Not Syncing

1. Check provider configuration:
   ```bash
   wp post meta get {competition_id} _sportspack_remote_provider
   wp post meta get {competition_id} _sportspack_remote_id
   ```

2. Test provider directly:
   ```php
   $provider = new \Sportspack\Providers\Provider_StatsPerform();
   $events = $provider->fetch_events( 'competition-id', 30 );
   var_dump( $events );
   ```

3. Check WP-CLI output for errors:
   ```bash
   wp sportspack sync events --competition=101 --debug
   ```

### Inheritance Not Working

1. Clear cache:
   ```bash
   wp cache flush
   ```

2. Verify parent-child relationship:
   ```bash
   wp post get {event_id} --field=post_parent
   ```

3. Check meta values:
   ```bash
   wp post meta list {post_id}
   ```

### Permalinks Not Working

Flush rewrite rules:
```bash
wp rewrite flush
```

Or via admin:
Settings → Permalinks → Save Changes

## Contributing

### Code Standards

- Follow WordPress Coding Standards
- Use namespaces for all classes
- Add docblocks to all functions
- Escape output, sanitize input
- Use translation functions for all strings

### Testing

1. Create test data:
   ```bash
   wp sportspack create-test-data
   ```

2. Run syntax checks:
   ```bash
   for file in $(find . -name "*.php"); do php -l "$file"; done
   ```

## Support

- GitHub Issues: https://github.com/jeans/sportspack/issues
- Documentation: See README.md and TESTING.md

## License

GPL v2 or later
