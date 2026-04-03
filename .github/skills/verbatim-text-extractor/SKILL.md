---
name: "verbatim-text-extractor"
description: "Extract text from screenshots exactly as written, preserving typos and alignment, while applying smart line wrapping for responsive HTML."
---

# Skill 16: Verbatim Text Extractor

## 🚨 STACK CONTEXT (CRITICAL)
- **Target Output:** HTML using Bootstrap 4 utility classes (unless plain HTML is specifically requested).
- **Environment:** Legacy Laravel 5.7 / Vue 2 project environment.

## 📝 Text Extraction & Formatting Rules (CRITICAL)
- **Verbatim Copying:** You MUST transcribe all text exactly as it appears in the screenshot. Do NOT paraphrase, summarize, or rewrite. 
- **Preserve Typos:** Do NOT correct misspelled words or grammatical errors. If the original screenshot has a typo, your generated HTML must have the exact same typo.
- **Smart Line Wrapping (No Hard Returns):** Do NOT copy unnatural line breaks. If a sentence wraps to the next line in the screenshot simply because it ran out of horizontal space, combine it into a single, continuous sentence in your code. Let the browser/CSS handle the text wrapping naturally. Only insert `<br>` or new paragraphs (`<p>`) if there is a deliberate, semantic paragraph break in the original image.
- **Visual Alignment & Positioning:** You must replicate the text alignment exactly. If text is centered inside a table cell or div, use the appropriate Bootstrap 4 utility class (e.g., `text-center`, `align-middle`) to ensure it sits in the exact same position as the screenshot.

## ⚙️ Workflow 
- **Workflow:** Screenshot ➡️ Analyze Text & Layout ➡️ Transcribe Verbatim (applying smart wrapping) ➡️ Generate HTML snippet.
- **MCP Integration:** Use Vision AI capabilities to analyze the screenshot accurately.

## ⚠️ Escalation & Stop
- If the text in the screenshot is completely illegible or obscured: `🚨 ESCALATION REQUIRED. Text is illegible and cannot be transcribed accurately.`
- Output `WAITING_FOR_HUMAN_OK` when the HTML snippet is generated.
