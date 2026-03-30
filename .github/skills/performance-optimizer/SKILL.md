---
name: "performance-optimizer"
description: "Prevent slow queries and optimize page loads."
applyTo:
    - "app/Models/*"
    - "app/Http/Controllers/*"
version: "1.0-legacy"

---

# Skill 17: Performance Optimizer

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

##  N+1 Query Prevention
Avoid accessing relationships inside loops in views/controllers. Example:

```php
//  WRONG - N+1 queries in Blade
@foreach($students as $student)
        {{ $student->course->name }}
@endforeach
```

```php
//  CORRECT - Eager load in controller
$students = Student::with('course')->get();
```

##  Caching Expensive Queries
Cache results of expensive queries when appropriate:

```php
$grades = Cache::remember(
        'grades_'.$student_id,
        300,
        function () use ($student_id) {
                return Grade::where('student_id', $student_id)->get();
        }
);
```

##  Database Indexing
- Add indexes for foreign keys (`student_id`, `course_id`).
- Index search columns (`email`, `student_number`).
- Consider composite indexes for common query patterns.

##  MCP Integration
- **MySQL MCP:** Run `EXPLAIN` on slow queries; capture slow query log.
- **Filesystem MCP:** Verify models use eager loading (`with`) and avoid large in-memory collections.
- **Performance MCP:** Run Lighthouse, XHProf/Blackfire, or CPU profiling to measure regressions.

##  Escalation Protocol
- If page load time consistently exceeds 2 seconds, or slow queries are found that cannot be optimized automatically, STOP and output:
    ` ESCALATION REQUIRED. Performance issue: <detail>.`

##  STOP COMMAND
Output `WAITING_FOR_HUMAN_OK` when the file is generated.
