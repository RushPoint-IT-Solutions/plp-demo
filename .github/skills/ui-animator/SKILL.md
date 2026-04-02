---
name: "ui-animator"
description: "CSS transitions and jQuery animations for UI elements."


---

# Skill 11: UI Animator

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

##  Animation Standards

### Performance Guidance
- Animate only `transform` and `opacity` where possible to avoid layout thrashing.
- Avoid animating `width`, `height`, `top`, `left`, `margin`.
- Keep durations concise (<= 400ms) and minimize simultaneous animations.
- Use `will-change` sparingly and remove it when not needed.

### Transitions (SCSS example)
```scss
/* Example: subtle button lift */
.btn {
  transition: transform 0.25s ease, box-shadow 0.25s ease;
  will-change: transform;
}

.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
```

### jQuery Animations (JS example)
```javascript
// Use jQuery for simple interactions when necessary
$('.element').fadeIn(300);
$('.element').slideUp(300);

// Prefer CSS transitions for performance-critical animations
```

##  NO Heavy Animations
- Avoid endless loops, repeated `setInterval`, and animating layout properties on scroll.

##  MCP Integration
- **Filesystem MCP:** Verify SCSS/JS files exist under `resources/assets/sass/components/` and `resources/assets/js/`.
- **Playwright MCP:** Record animation playback and check dropped frames / visual regressions.
- **Performance MCP:** Run Lighthouse/CPU profiling to measure impact.

##  Escalation Protocol
- If animations cause performance regressions (high CPU, jank, low FPS), STOP and output:
  ` ESCALATION REQUIRED. Animation performance issue: <detail>.`

##  STOP COMMAND
- Output `WAITING_FOR_HUMAN_OK` when the file is generated.
