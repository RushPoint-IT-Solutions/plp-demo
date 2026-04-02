---
name: "route-mapper"
description: "Map all routes, controllers, views, relationships."


---

# Skill 20: Route Mapper

##  LEGACY STACK CONTEXT (CRITICAL)
- **Framework:** Laravel 5.7 ONLY (Requires PHP 7.1+ syntax).
- **Frontend:** Bootstrap 4, Vue 2, jQuery.
- **Build Tool:** Laravel Mix (`webpack.mix.js`). Run via `npm run dev`. NO Vite.

##  Explicitly Forbidden PHP 8+ Features
- `match` expressions
- Union types (e.g., `string|int`)
- Nullsafe operator (`?->`)
- Named arguments
- Constructor property promotion
- Arrow functions (`fn() =>`)
- Typed properties
- Null-coalescing assignment (`??=`)

##  Resource Controller Standard (7 Routes)
| Method | URI | Action | Route Name |
|--------|-----|--------|------------|
| GET | /students | index | students.index |
| GET | /students/create | create | students.create |
| POST | /students | store | students.store |
| GET | /students/{id} | show | students.show |
| GET | /students/{id}/edit | edit | students.edit |
| PUT | /students/{id} | update | students.update |
| DELETE | /students/{id} | destroy | students.destroy |

##  File Connection Map
```
routes/web.php
  -> app/Http/Controllers/StudentController.php
  -> resources/views/students/index.blade.php
  -> app/Models/Student.php
```

##  MCP Integration
- **Filesystem MCP:** Run `php artisan route:list` and verify all controllers/methods exist.
- **Code MCP:** Verify controller methods return the expected views and view data keys match templates.
- **MySQL MCP:** Ensure related tables and schema exist for resource routes.

##  Escalation Protocol
- If a route references a missing controller/method or view, or route names are duplicated/mismatched, STOP and output:
  ` ESCALATION REQUIRED. Route/view/controller mismatch: <detail>.`

##  STOP COMMAND
Output `WAITING_FOR_HUMAN_OK` when the file is generated.
