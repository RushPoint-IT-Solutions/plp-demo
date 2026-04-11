---
name: "scalability-performance-auditor"
description: "Prevents server crashes by identifying N+1 query loops, missing database indexes, and suggesting Redis caching for high-traffic school portal routes."
---

# Scalability & Performance Auditor

## Purpose
Ensure the application can handle thousands of concurrent users by optimizing how the backend talks to the database.

## Required MCP Tools
- Filesystem (`read_file`, `edit_file`)
- Database/SQL MCP

## Workflow
1) **N+1 Query Hunt:** Scan Controllers for loops that call the database inside a `foreach`. 
   - *Fix:* Force "Eager Loading" (e.g., change `User::all()` to `User::with('grades')->get()`).

2) **Indexing Check:** Scan migrations and the database for frequently searched columns (e.g., `student_id`, `email`, `status`). 
   - *Fix:* If a column is used in a `where()` clause but isn't indexed, generate a migration to add `$table->index('column_name');`.

3) **Caching Suggestion:** Identify static/heavy data (e.g., School Calendar, Announcements).
   - *Fix:* Wrap the query in `Cache::remember()`.

4) **Pagination Check:** Ensure no controller is using `->get()` on tables like "Applicants" or "Logs". 
   - *Fix:* Change to `->paginate(15)`.

5) **Large Dataset UI Guardrail:** Verify frontend tables do not render thousands of rows from a single API response.
   - *Fix:* Add page controls (`page`, `per_page`), keep max `per_page` bounded (<=100), and move search/sort to backend.

6) **Query Path Evidence:** For each high-traffic list endpoint, run query analysis and confirm index coverage for filter/order columns.
   - *Fix:* Generate additive index migration for missing indexes and document in audit report.

7) **Save Audit Report:** Write findings and applied fixes to `.security-audits/performance-audit.txt`.

## STOP COMMAND
WAITING_FOR_HUMAN_OK