---
name: "school-rules"
description: "Philippine University grading system (1.00-5.00)."
applyTo:
  - "app/Models/Grade.php"
  - "app/Http/Controllers/GradeController.php"
version: "1.0-legacy"

---

# Skill 22: School Rules

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

##  Grading System: Philippine University Standard
| Grade | Meaning |
|-------|---------|
| 1.00 | Excellent |
| 1.25 | Very Good |
| 1.50 | Good |
| 1.75 | Fair |
| 2.00 - 3.00 | Passing |
| > 3.00 | Failing |
| 5.00 | Failed |
| INC | Incomplete |

##  CRITICAL: Any numerical grade higher than 3.00 is a FAILING grade.

##  Attendance Rule
- 3 unexcused absences in a single subject per semester = Automatic 5.00 (Failed)

##  Implementation
- Implement in Grade Model/Controller
- Validate before saving grades
- Display warnings when approaching 3 absences

##  MCP Integration
- **MySQL MCP:** Verify grades table has `absences` column.
- **Filesystem MCP:** Verify Grade model has attendance logic.

##  Escalation Protocol
- If grading logic incorrect, STOP and output:
` ESCALATION REQUIRED. Grading logic error: <detail>.`

##  STOP COMMAND
Output `WAITING_FOR_HUMAN_OK` when the file is generated.
