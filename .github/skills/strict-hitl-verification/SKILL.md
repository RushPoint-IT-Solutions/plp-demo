---
name: "strict-hitl-verification"
description: "Human-in-the-loop approval gate for UI and core backend changes. Stops after implementation, requests manual browser testing, and loops on feedback until approved."
---

# Strict Human-in-the-Loop Verification

## Purpose
Use this skill after writing, updating, or refactoring code for a UI component or a core backend function. It pauses the active plan immediately after implementation and waits for human browser verification instead of assuming the code works.

## Trigger Condition
- The change touches a UI component or a core backend function.
- The implementation is complete and ready for manual browser testing.

## Agent Plan Mode Checkpoint (Native UI Pause)
- Plan Interruption: Whenever you complete the code edits for a UI component or backend function, immediately pause the active execution plan. Do not finish the run or close the request.
- Native Confirmation Tool: Do not output a standard text question in chat. Invoke the built-in confirmation dialog or AskUser-style prompt so the user sees interactive Yes / No / Other options without ending the request.
- Dialog Content: "I have implemented the changes. Please manually test the UI in your browser. Is it okay now?"
- Evaluation Loop:
  - If the user clicks Yes:
    - resolve the pause,
    - mark the task complete in the internal plan,
    - finish the run gracefully.
  - If the user clicks No or Other and provides feedback:
    - stay inside the current request,
    - analyze the text feedback,
    - apply only the necessary fix,
    - trigger the exact same native confirmation dialog again.

## Required Workflow
1. Finish the code change.
2. Stop immediately. Do not start another ticket, refactor, or optimization.
3. Present the native confirmation dialog described above.
4. Enter a paused waiting state and do not move on to any other task.
5. If the user replies "Yes" or "Y":
   - acknowledge approval briefly,
   - update project_memory.md with the completed state,
   - ask for the next assignment.
6. If the user replies with anything else:
   - analyze the specific issue,
   - modify the workspace code to fix only that issue,
   - restart this exact checkpoint from Step 1.

## Non-Negotiables
- Do not continue to another task while waiting for approval.
- Do not infer approval from silence, screenshots, or partial comments.
- Do not mark the task complete until the user explicitly approves.
- Do not skip the project_memory.md update after approval.
- Do not broaden the fix beyond the reported issue when feedback is given.

## Completion Criteria
- The user explicitly approves.
- project_memory.md is updated to reflect the completed task.
- The next assignment has been requested from the user.
