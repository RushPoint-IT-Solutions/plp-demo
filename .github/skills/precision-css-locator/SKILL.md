---
name: precision-css-locator
description: Emulates Chrome DevTools workflow. Uses Playwright to inspect an element, extracts its classes, uses CLI grep to find the exact SassCSS file and line, edits it, and recompiles.
---

# Precision CSSSass Locator & Editor

## Purpose
Fix UITheme issues without wasting time or context window reading entire stylesheets. The AI MUST locate styling logic exactly like a human developer Inspect - Search - Edit - Compile.

## Required MCP Tools
- Playwright (`browser_navigate`, `browser_evaluate`)
- CLI  Terminal (for `grep` searching and npm compilation)
- Filesystem (`edit_file`)

## Workflow (Steps 1–6)

1) Get Inputs
- Prompt the user for Target URL and visual description of the broken element (e.g., The blue login button is too small).

2) Step 1 Inspect Element (Playwright)
- Navigate to the Target URL.
- Use `browser_evaluate` to find the described element and extract its exact ID and Class List.
  
    (description) = {
       Logic to find element based on textcontext, then return
       return el.className;
    }

3) Step 2 Precision Search (CLI)
- Do NOT use read_file on `app.css`.
- Use the CLI MCP to search the source directories (usually `resourcescss` or `resourcessass` in Laravel) for the specific classes extracted in Step 1. 
- Use grep to find the exact file and line number `grep -rnw resources -e specific-class-name`

4) Step 3 Targeted Edit (Filesystem)
- Using the file path and line number discovered via CLI, read ONLY that specific file (or use targeted file editing to modify just that block).
- Apply the requested visual fix (e.g., fixing padding, changing colors to match theme).

5) Step 4 Recompile Assets (CLI)
- If modifying SassSCSS, use the CLI MCP to run the project's build tool to compile the changes into the public directory
  - `npm run build` or `npm run dev`

6) Step 5 Verify (Playwright)
- Reload the page in Playwright.
- Ensure the visual issue is fixed (no overlapping, correct computed styles).

## STOP COMMAND
When the CSS is compiled and verified, output exactly
WAITING_FOR_HUMAN_OK