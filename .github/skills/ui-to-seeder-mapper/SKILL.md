---
name: "ui-to-seeder-mapper"
description: "Scans the existing web pages to identify data requirements. Extracts form labels and input names to generate a JSON contract that ensures the Seeder matches the UI perfectly."
---

# UI-to-Seeder Mapper

## Purpose
Prevent "Missing Data" errors by scanning the frontend UI to see exactly what fields are required by the forms and tables before generating the Seeder.

## Workflow
1) **Navigate:** Use Playwright to visit key pages (Applicant Form, Student Profile, Registrar Dashboard, etc.).
2) **Extract:** Run a script in the browser context to find all `<input>`, `<select>`, `<textarea>`, and `<table>` headers.
3) **Map:** Generate a JSON "Data Contract" (`ui-data-contract.json`) that lists every field the Seeder must provide to make the page look complete.
4) **Handoff:** Save this contract to `.security-audits/ui-data-contract.json` for use by `high-volume-trash-seeder` and `db-retrofit-normalizer`.

## Output Format Example
```json
{
  "models": {
    "Student": {
      "fields": ["student_id", "first_name", "last_name", "email", "grade_level", "section"],
      "relationships": {
        "user": "belongsTo",
        "grades": "hasMany"
      }
    },
    "Applicant": {
      "fields": ["application_no", "full_name", "contact_number", "desired_strand", "status"]
    }
  }
}
```

## STOP COMMAND
WAITING_FOR_HUMAN_OK