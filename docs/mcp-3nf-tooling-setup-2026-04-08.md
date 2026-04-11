# MCP Tooling Setup for 3NF Work (2026-04-08)

## Installed Node Packages
Installed as dev dependencies for local MCP usage:
- `@modelcontextprotocol/server-filesystem`
- `@playwright/mcp`
- `@benborla29/mcp-server-mysql`
- `@berthojoris/mcp-mysql-server`

Files updated:
- `package.json`
- `package-lock.json`

## Workspace MCP Configuration
Updated `.vscode/mcp.json` to include MySQL endpoints used for schema discovery and 3NF analysis.

Configured server entries:
- `mysql`
- `mysql_3nf_graph`

Also corrected JSON validity in the Figma entry (`FIGMA_API_TOKEN` env field).

## Practical 3NF Query Patterns Used
For reliable schema reads in this environment, the following patterns were used:
- Base tables:
  - `SELECT table_name FROM information_schema.tables WHERE table_schema = 'plp_demo' AND table_type = 'BASE TABLE';`
- Columns:
  - `SELECT table_name, column_name, data_type FROM information_schema.columns WHERE table_schema = 'plp_demo';`
- Foreign keys:
  - `SELECT * FROM information_schema.referential_constraints WHERE constraint_schema = 'plp_demo';`

## Notes
- In this environment, MCP MySQL may require explicit schema-qualified table names in some queries.
- Example: use `plp_demo.notification_types` instead of unqualified `notification_types` when needed.
