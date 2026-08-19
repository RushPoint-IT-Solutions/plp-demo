<?php

namespace App\Http\Controllers\Registrar\Services;

use App\DocumentTemplate;
use App\Http\Controllers\Controller;
use App\Student;
use App\Support\DocumentTemplateTokens;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * Shared, slug-driven template editor engine. COR, TOR, and Honorable
 * Dismissal each keep their own dedicated get/save methods on
 * RegistrarController (already shipped and in active use) — this generic
 * controller is for every document type added after them, so adding one
 * only needs a token resolver + default layout in DocumentTemplateTokens,
 * not a whole new pair of controller methods.
 */
class DocumentTemplateController extends Controller
{
    /**
     * Slugs allowed through the generic editor. A document must be listed
     * here before its layout can be loaded/saved via these routes.
     */
    private static $allowedSlugs = [
        'f137a', 'certificate-gwa', 'diploma', 'clearance-2', 'graduation-clearance',
        'leave-of-absence', 'deans-honors', 'presidents-honors', 'form-8c2', 'form-8d2',
        'official-grade-report', 'cross-enroll-permit',
    ];

    private static $labels = [
        'f137a' => 'Request Form F137A',
        'certificate-gwa' => 'Certificate of GWA',
        'diploma' => 'Diploma',
        'clearance-2' => 'Clearance 2',
        'graduation-clearance' => 'Graduation Clearance',
        'leave-of-absence' => 'Leave of Absence',
        'deans-honors' => "Dean's Honors",
        'presidents-honors' => "President's Honors",
        'form-8c2' => 'Form 8C-2 (Graduation)',
        'form-8d2' => 'Form 8D-2 (Honor)',
        'official-grade-report' => 'Official Grade Report',
        'cross-enroll-permit' => 'Cross-Enroll Permit',
    ];

    public function index()
    {
        return view('registrar.admin-tools.system-config.document-templates', [
            'documents' => \App\Support\StudentDocumentRegistry::all(),
            'genericSlugs' => self::$allowedSlugs,
        ]);
    }

    public function getLayout(Request $request, $slug, $studentId = null): JsonResponse
    {
        $this->assertAllowedSlug($slug);

        if (!Schema::hasTable('document_templates')) {
            return response()->json(['success' => false, 'message' => 'Document templates table is not available. Run migrations first.'], 500);
        }

        $template = $this->template($slug);
        $layout = $template->content_json ?: DocumentTemplateTokens::defaultLayout($slug);
        $student = $studentId ? Student::find($studentId) : null;

        $tokens = DocumentTemplateTokens::resolve($slug, $student);
        foreach (($layout['elements'] ?? []) as $index => $element) {
            $layout['elements'][$index]['resolved_text'] = strtr((string) ($element['text'] ?? ''), $tokens);
        }

        return response()->json([
            'success' => true,
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'slug' => $template->slug,
                'content_json' => $layout,
            ],
        ]);
    }

    public function saveLayout(Request $request, $slug): JsonResponse
    {
        $this->assertAllowedSlug($slug);

        if (!Schema::hasTable('document_templates')) {
            return response()->json(['success' => false, 'message' => 'Document templates table is not available. Run migrations first.'], 500);
        }

        $payload = $request->validate([
            'content_json' => 'required|array',
            'content_json.page' => 'nullable|array',
            'content_json.elements' => 'required|array|min:1',
            'content_json.elements.*.id' => 'required|string|max:80',
            'content_json.elements.*.type' => 'required|string|in:text',
            'content_json.elements.*.text' => 'nullable|string',
            'content_json.elements.*.top' => 'required|numeric|min:0|max:100',
            'content_json.elements.*.left' => 'required|numeric|min:0|max:100',
            'content_json.elements.*.width' => 'nullable|numeric|min:1|max:100',
            'content_json.elements.*.font_family' => 'nullable|string|max:80',
            'content_json.elements.*.font_size' => 'nullable|numeric|min:6|max:96',
            'content_json.elements.*.font_weight' => 'nullable|string|in:normal,bold',
            'content_json.elements.*.font_style' => 'nullable|string|in:normal,italic',
            'content_json.elements.*.text_decoration' => 'nullable|string|in:none,underline',
            'content_json.elements.*.text_align' => 'nullable|string|in:left,center,right,justify',
            'content_json.elements.*.line_height' => 'nullable|numeric|min:0.8|max:3',
        ]);

        $layout = $this->sanitizeLayout($payload['content_json']);
        $template = $this->template($slug);
        $template->content_json = $layout;
        $template->save();

        return response()->json([
            'success' => true,
            'message' => (self::$labels[$slug] ?? $slug) . ' layout saved.',
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'slug' => $template->slug,
                'content_json' => $template->content_json,
            ],
        ]);
    }

    private function template($slug): DocumentTemplate
    {
        return DocumentTemplate::firstOrCreate(
            ['slug' => $slug],
            ['name' => self::$labels[$slug] ?? $slug, 'content_json' => DocumentTemplateTokens::defaultLayout($slug)]
        );
    }

    private function assertAllowedSlug($slug): void
    {
        if (!in_array($slug, self::$allowedSlugs, true)) {
            abort(404, 'This document does not have an editable template.');
        }
    }

    private function sanitizeLayout(array $layout): array
    {
        $allowedFonts = ['Arial', 'Times New Roman', 'Courier New', 'Georgia'];
        $clean = [
            'page' => [
                'width_mm' => (float) ($layout['page']['width_mm'] ?? 215.9),
                'height_mm' => (float) ($layout['page']['height_mm'] ?? 279.4),
                'orientation' => 'portrait',
                'background' => '#ffffff',
            ],
            'elements' => [],
        ];

        foreach ($layout['elements'] as $element) {
            $font = in_array(($element['font_family'] ?? 'Arial'), $allowedFonts, true) ? $element['font_family'] : 'Arial';
            $clean['elements'][] = [
                'id' => (string) $element['id'],
                'type' => 'text',
                'text' => (string) $element['text'],
                'top' => round((float) $element['top'], 3),
                'left' => round((float) $element['left'], 3),
                'width' => round((float) ($element['width'] ?? 30), 3),
                'font_family' => $font,
                'font_size' => round((float) ($element['font_size'] ?? 12), 2),
                'font_weight' => ($element['font_weight'] ?? 'normal') === 'bold' ? 'bold' : 'normal',
                'font_style' => ($element['font_style'] ?? 'normal') === 'italic' ? 'italic' : 'normal',
                'text_decoration' => ($element['text_decoration'] ?? 'none') === 'underline' ? 'underline' : 'none',
                'text_align' => in_array(($element['text_align'] ?? 'left'), ['left', 'center', 'right', 'justify'], true) ? $element['text_align'] : 'left',
                'line_height' => round((float) ($element['line_height'] ?? 1.3), 2),
            ];
        }

        return $clean;
    }
}
