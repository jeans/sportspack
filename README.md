# Sportspack

A high-performance WordPress plugin for managing sports data hierarchies (Sport -> Competition -> Event) and entities (Teams, Persons, Venues). Designed for WordPress VIP and Newspack.

## Features

- **Hierarchical CPT**: `sportspack_unit` handles Sports, Competitions, and Events.
- **Smart Inheritance**: Logos and Remote Provider settings inherit from Sport -> Competition -> Event.
- **Performance**: Heavy use of Object Cache to avoid recursive DB queries on frontend.
- **WP-CLI Integration**: Sync events from external providers like StatsPerform.
- **Newspack Ready**: Clean template integration.

## Installation

1. Upload `sportspack` folder to `wp-content/plugins/`.
2. Activate via Admin or WP-CLI: `wp plugin activate sportspack`.
3. Flush permalinks.

## CLI Usage

Sync events for a specific competition:

```bash
# Find the Post ID of your Competition (e.g., Bundesliga)
wp sportspack sync events --competition=123 --days=14
```

## Developer Notes

- **Providers**: Implement `Sportspack\Providers\Provider_Interface` to add new data sources.
- **Templates**: Override templates by copying files from `templates/` to your theme folder.