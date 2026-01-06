# Sportspack Testing & Validation Guide

## Plugin Structure Validation

All required files have been created:

### Core Files
- ✓ `sportspack.php` - Main plugin file with proper headers
- ✓ `includes/class-plugin.php` - Plugin bootstrap
- ✓ `includes/class-cpt.php` - CPT registration (4 post types)
- ✓ `includes/class-meta.php` - Meta fields and admin UI
- ✓ `includes/class-inheritance.php` - Inheritance resolver with caching
- ✓ `includes/class-template-loader.php` - Template system

### Provider System
- ✓ `includes/providers/interface-provider.php` - Provider interface
- ✓ `includes/providers/class-provider-statsperform.php` - StatsPerform provider stub
- ✓ `includes/providers/class-provider-heimspiel.php` - Heimspiel provider stub

### WP-CLI
- ✓ `includes/cli/class-cli.php` - CLI commands for event sync

### Templates
- ✓ `templates/single-sportspack_unit.php` - Hierarchical sports unit template
- ✓ `templates/single-sportspack_team.php` - Team template
- ✓ `templates/single-sportspack_person.php` - Person template
- ✓ `templates/single-sportspack_venue.php` - Venue template

## PHP Syntax Check

All PHP files pass syntax validation:
```bash
for file in $(find . -name "*.php"); do php -l "$file"; done
```
Result: ✓ No syntax errors detected

## Features Implemented

### 1. Hierarchical Custom Post Type
- **Post Type**: `sportspack_unit` (hierarchical)
- **Rewrite Base**: `/sports`
- **Hierarchy**: Sport (level 0) → Competition (level 1) → Event (level 2)
- **Supports**: Title, Editor (Gutenberg), Thumbnail, Page Attributes
- **Rest API**: Enabled for block editor support

### 2. Non-Hierarchical Custom Post Types
- **sportspack_team**: Team entities
- **sportspack_person**: Person entities (players, coaches)
- **sportspack_venue**: Venue entities
- All support: Title, Editor, Thumbnail, Custom Fields

### 3. Meta Fields with Inheritance
Three inheritable fields for `sportspack_unit`:
- `_sportspack_logo` - Logo attachment ID or URL
- `_sportspack_remote_provider` - Enum: statsperform, heimspiel, sportradar, custom
- `_sportspack_remote_id` - Remote provider ID

**Inheritance System**:
- Event inherits from Competition inherits from Sport
- Uses object cache to avoid recursive DB queries
- Cache automatically cleared when parent is updated
- Helper methods: `get_inherited_logo()`, `get_inherited_provider()`, `get_inherited_remote_id()`

### 4. Admin UI
- Meta box in sidebar for all post types
- Shows current values with inherited values as hints
- Validates provider enum values
- Nonce verification for security
- Auto-clears cache on save

### 5. Template System
- Template loader checks theme first, then plugin
- Templates can be overridden in theme: `{theme}/sportspack/single-{posttype}.php`
- Breadcrumb generation for hierarchical posts
- Logo display (supports attachment ID or URL)
- Hierarchy level labels (Sport/Competition/Event)
- Lists child items (Competitions under Sport, Events under Competition)

### 6. WP-CLI Commands
```bash
wp sportspack sync events --competition=<post_id> [--days=30] [--provider=<provider>]
```
- Fetches events from provider API
- Creates/updates Event posts under Competition
- Idempotent (won't duplicate events)
- Uses remote_id to match existing posts
- Inherits provider from competition if not specified

### 7. Provider Abstraction
- Interface: `Provider_Interface`
- Methods: `get_name()`, `fetch_events()`, `is_configured()`
- Implementations: StatsPerform, Heimspiel (stubs ready for API implementation)
- Extensible via filters: `sportspack_statsperform_fetch_events`

### 8. Performance Considerations
- Object cache for inheritance resolution
- Cache expiration: 1 hour
- No heavy meta queries on frontend
- Efficient parent traversal with early exit
- Child cache invalidation on parent update

### 9. WordPress Best Practices
- ✓ Namespace usage (`Sportspack\`)
- ✓ Singleton pattern for main classes
- ✓ Proper escaping (`esc_html`, `esc_url`, `esc_attr`)
- ✓ Sanitization on input (`sanitize_text_field`)
- ✓ Nonce verification for forms
- ✓ Capability checks (`current_user_can`)
- ✓ Translation ready (`__()`, `_x()`, `_e()`)
- ✓ Text domain: `sportspack`
- ✓ Activation/deactivation hooks with rewrite flush
- ✓ No hardcoded styling (relies on theme)

### 10. Internationalization
- Text domain: `sportspack`
- Domain path: `/languages`
- POT file stub created
- All strings wrapped in translation functions

## Activation Test Checklist

When activated in WordPress:
1. ✓ No fatal errors (all classes autoloaded correctly)
2. ✓ Rewrite rules flushed on activation
3. ✓ Post types registered: `sportspack_unit`, `sportspack_team`, `sportspack_person`, `sportspack_venue`
4. ✓ Meta fields registered for REST API
5. ✓ Meta boxes added to post editor
6. ✓ WP-CLI commands registered (if WP-CLI available)
7. ✓ Template loader hooked to `template_include`

## Usage Examples

### Creating a Hierarchy
```
Sport: Football (ID: 100)
  ├─ Competition: Premier League (ID: 101, parent: 100)
  │   ├─ Event: Match 1 (ID: 102, parent: 101)
  │   └─ Event: Match 2 (ID: 103, parent: 101)
  └─ Competition: Champions League (ID: 104, parent: 100)
      └─ Event: Match 3 (ID: 105, parent: 104)
```

### Setting Provider at Sport Level
- Set `_sportspack_remote_provider` = "statsperform" on Sport (ID: 100)
- Set `_sportspack_remote_id` = "football-001" on Sport
- All child Competitions and Events inherit these values unless overridden

### Syncing Events via CLI
```bash
# Sync next 14 days of events for Premier League
wp sportspack sync events --competition=101 --days=14

# Override provider for specific sync
wp sportspack sync events --competition=104 --provider=heimspiel
```

## Newspack Integration
- Graceful degradation if Newspack not active
- Check available: `Plugin::get_instance()->is_newspack_active()`
- No hard dependencies
- Templates work with standard WordPress themes

## Security Considerations
- ✓ Direct access checks (`ABSPATH` defined)
- ✓ Nonce verification on form submissions
- ✓ Capability checks before operations
- ✓ Input sanitization
- ✓ Output escaping
- ✓ No SQL injection vectors (using WordPress APIs)
- ✓ No XSS vectors (proper escaping)

## Extensibility

### Filters Available
- `sportspack_statsperform_fetch_events` - Mock/override StatsPerform data
- `sportspack_heimspiel_fetch_events` - Mock/override Heimspiel data

### Adding New Providers
1. Create class implementing `Provider_Interface`
2. Add to `CLI::get_provider()` switch
3. Add to provider enum in `Meta::sanitize_provider()`

### Template Override
Copy template to theme:
- `{theme}/sportspack/single-sportspack_unit.php`
- Or: `{theme}/single-sportspack_unit.php`

## Known Limitations
1. Provider implementations are stubs (require API credentials and implementation)
2. No built-in API key management UI (use constants or options)
3. Breadcrumbs use simple HTML (no schema.org markup)
4. Logo field is simple text (could be enhanced with media picker)
5. No import/export functionality
6. No bulk operations UI (use WP-CLI for bulk)

## Next Steps for Production
1. Implement actual API calls in providers
2. Add API key management UI
3. Add comprehensive unit tests
4. Add integration tests for WP-CLI commands
5. Add schema.org markup for breadcrumbs
6. Add media picker for logo field
7. Add admin columns for custom fields
8. Add quick edit support
9. Add REST API endpoints for frontend apps
10. Performance profiling and optimization
