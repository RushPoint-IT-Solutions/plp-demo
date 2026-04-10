---
name: "memory-consolidation-dreamer"
description: "Mimics a sleep cycle for AI. Scans raw session logs and project files, extracts user preferences, prunes contradictory data, and consolidates knowledge into a durable, indexed MEMORY.md file."
---

# AI Memory Consolidator (The 'Dream' Cycle)

## Purpose
Solve the "Context Bloat" problem. As you interact with the AI, scattered notes, corrections, and preferences accumulate. This skill acts as a garbage collector and defragmenter: it reads recent activity, extracts the "true signal" (your current architecture decisions and coding style), deletes stale memories, and writes everything into a clean, searchable index.

## Required MCP Tools
- Filesystem (`read_file`, `write_file`, `edit_file`, `list_directory`, `delete_file`)

## Workflow (Steps 1–5)

1) **Phase 1: Orient (Read the Current Index)**
- Check if the directory `.ai-memory/` and the file `.ai-memory/MEMORY.md` exist in the project root. If not, use the CLI/Filesystem to create them.
- Read `MEMORY.md` to understand the currently established facts about the project and the user's preferences.

2) **Phase 2: Gather Signal (Scan Recent Activity)**
- Use `list_directory` to look for raw session logs, recent git commits, or unstructured notes.
- Actively scan the codebase to identify implicit preferences (e.g., "The user strictly uses Tailwind CSS for styling," "The user prefers async/await over promises").

3) **Phase 3: Consolidate & Resolve Contradictions**
- Compare the new signals against the existing `MEMORY.md`.
- **CRITICAL (Conflict Resolution):** If a contradiction is found (e.g., Old Memory: "Uses Vue", New Signal: "Migrated to React"), the new signal overwrites the old. 
- Prune redundant data. Remove vague relative timeframes (replace words like "yesterday" with actual dates or version numbers).

4) **Phase 4: Distill into Core Files**
- Do not dump everything into one massive file. Split the consolidated knowledge into distinct, tightly scoped files inside `.ai-memory/`:
  - `tech-stack-and-architecture.md` (Current database structure, core libraries).
  - `coding-style-preferences.md` (Linting rules, variable naming conventions, UI themes).
  - `active-goals.md` (What the user is currently trying to build or fix).
- Overwrite `MEMORY.md` so it acts purely as a Table of Contents with one-line descriptions linking to these specific files.

5) **Phase 5: The Wake Up (Cleanup & Gitignore)**
- Ensure the `.ai-memory/` folder is added to `.gitignore` so personal AI context doesn't clutter the team repository.
- Delete any raw, unstructured session logs that were processed in Step 2 to save disk space.

## STOP COMMAND
When the memory has been successfully consolidated, contradictions resolved, and old logs pruned, output exactly:
WAITING_FOR_HUMAN_OK