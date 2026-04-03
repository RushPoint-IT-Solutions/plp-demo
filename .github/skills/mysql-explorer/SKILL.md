---
name: "mysql-explorer"
description: "Safely explore and query MySQL databases via MCP, strictly adhering to parser-safe syntax for schema inspection and data retrieval."
---

# MySQL Explorer

## Purpose

Use the MySQL MCP server to explore database schemas, inspect table structures, and retrieve data without triggering strict SQL parser errors (specifically the unquoted dot notation error). Produce formatted, easily readable data reports.

## Core Parsing Rules (CRITICAL)

The built-in SQL parser for this MCP server will FAIL on unquoted dot notation (e.g., `DESCRIBE plp_demo.courses`). You MUST use one of the following safe syntaxes for schema exploration:

- **Safe Syntax 1 (Backticks):** Wrap database and table names in backticks.
  `DESCRIBE \`plp_demo\`.\`courses\``
- **Safe Syntax 2 (Native FROM/IN):** Avoid the dot entirely using MySQL native keywords.
  `SHOW COLUMNS FROM courses IN plp_demo`
- **Safe Syntax 3 (Information Schema):** Use a standard SELECT statement for bulletproof execution.
  `SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT FROM information_schema.columns WHERE table_schema = 'plp_demo' AND table_name = 'courses'`

## Required MCP Tools

- `query` (or the equivalent SQL execution tool provided by your specific MySQL MCP server)

## Preconditions

- The MySQL MCP server is reachable and connected.
- Confirm the target database and table names.
- Confirm whether the user wants to inspect the schema or retrieve actual rows of data.
- Read-only operations are the default. Destructive operations require explicit consent.

## Workflow (steps 1–5)

1) Get inputs  
- Prompt the user for:
  - Target database name.
  - Target table name (or specific SQL query intent).
  - Action required: "Inspect Schema" or "Query Data"?
  - Allow destructive actions (INSERT, UPDATE, DELETE, DROP)? (yes/no) — default: no.

2) Inspect Schema (If requested)  
- NEVER use `DESCRIBE db.table`. Use Safe Syntax 2 (Native FROM/IN) to fetch the table structure.

```json
{
  "tool": "query",
  "params": { 
    "sql": "SHOW COLUMNS FROM courses IN plp_demo" 
  }
}