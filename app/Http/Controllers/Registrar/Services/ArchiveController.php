<?php

namespace App\Http\Controllers\Registrar\Services;

use App\Archive;
use App\ArchiveAccessLog;
use App\ArchiveCategory;
use App\ArchiveDisposalQueue;
use App\ArchiveRetrievalRequest;
use App\ArchiveSetting;
use App\DisposalCertificate;
use App\Http\Controllers\Controller;
use App\RetentionPolicy;
use App\Services\ArchiveService;
use App\Support\AuditTrailRecorder;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * Archive Controller for Document Control and Record Management
 * 
 * Handles archiving, retrieval, disposal, and management of archived records.
 * Access restricted to Registrar and Admin roles.
 */
class ArchiveController extends Controller
{
    protected ArchiveService $archiveService;

    public function __construct(ArchiveService $archiveService)
    {
        $this->archiveService = $archiveService;
    }

    /**
     * Display archive dashboard
     */
    public function index()
    {
    $archives = Archive::with(['archivedBy', 'retentionPolicy'])
    ->orderByDesc('archived_at')
    ->paginate(20);
    
    $retrievalRequests = ArchiveRetrievalRequest::with(['archive', 'requestedBy', 'reviewer'])
    ->orderByDesc('created_at')
    ->limit(50)
    ->get();
    
    $disposalQueue = ArchiveDisposalQueue::with(['archive'])
    ->where('status', 'pending')
    ->orderByDesc('scheduled_date')
    ->get();
    
    $retentionPolicies = RetentionPolicy::all();
    
    $accessLogs = ArchiveAccessLog::with(['user', 'archive'])
    ->orderByDesc('created_at')
    ->limit(50)
    ->get();
    
    return view('registrar.services.archive.index', compact(
    'archives',
    'retrievalRequests',
    'disposalQueue',
    'retentionPolicies',
    'accessLogs'
    ));
    }

    /**
     * List all archives with filtering
     */
    public function listArchives(Request $request)
    {
        $query = Archive::with(['archiver', 'retentionPolicy']);

        // Apply filters
        if ($request->has('category') && $request->category) {
            $query->where('archive_category', $request->category);
        }

        if ($request->has('record_type') && $request->record_type) {
            $query->where('record_type', $request->record_type);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('record_type', 'like', "%{$search}%")
                    ->orWhere('archive_reason', 'like', "%{$search}%");
            });
        }

        // Date range filter
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('archived_at', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('archived_at', '<=', $request->to_date);
        }

        // Status filter
        if ($request->has('status')) {
            if ($request->status === 'restored') {
                $query->where('is_restored', true);
            } elseif ($request->status === 'pending_disposal') {
                $query->pendingDisposal();
            }
        }

        $archives = $query->orderByDesc('archived_at')->paginate(20);

        $categories = ArchiveCategory::all();
        $recordTypes = Archive::distinct()->pluck('record_type')->filter()->values();

        return view('registrar.services.archive.list', compact(
            'archives',
            'categories',
            'recordTypes'
        ));
    }

    /**
     * View archive details
     */
    public function viewArchive(Archive $archive)
    {
        $archive->load(['archiver', 'retentionPolicy', 'retrievalRequests.requester', 'disposalQueue']);

        $accessLogs = ArchiveAccessLog::forArchive($archive->id)
            ->with('performer')
            ->orderByDesc('performed_at')
            ->limit(50)
            ->get();

        return view('registrar.services.archive.view', compact('archive', 'accessLogs'));
    }

    /**
     * Show archive retrieval requests
     */
    public function retrievalRequests(Request $request)
    {
        $query = ArchiveRetrievalRequest::with(['archive', 'requester', 'reviewer']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        } else {
            // Default to pending
            $query->pending();
        }

        $requests = $query->orderByDesc('created_at')->paginate(20);

        return view('registrar.services.archive.retrieval-requests', compact('requests'));
    }

    /**
     * Request retrieval of an archive
     */
    public function requestRetrieval(Request $request, Archive $archive)
    {
        $this->validate($request, [
            'reason' => 'required|string|max:500',
            'access_level' => 'required|in:view,download,restore_temporary',
        ]);

        try {
            $retrievalRequest = $this->archiveService->requestRetrieval(
                $archive,
                auth()->id(),
                $request->reason,
                $request->access_level
            );

            return redirect()->back()->with('success', 'Retrieval request submitted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Approve retrieval request
     */
    public function approveRetrieval(Request $request, ArchiveRetrievalRequest $retrievalRequest)
    {
        $this->validate($request, [
            'notes' => 'nullable|string|max:500',
            'restoration_days' => 'nullable|integer|min:1|max:90',
        ]);

        try {
            $reviewer = auth()->user();
            $this->archiveService->approveRetrieval(
                $retrievalRequest,
                $reviewer,
                $request->notes,
                $request->restoration_days
            );

            return redirect()->back()->with('success', 'Retrieval request approved.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject retrieval request
     */
    public function rejectRetrieval(Request $request, ArchiveRetrievalRequest $retrievalRequest)
    {
        $this->validate($request, [
            'notes' => 'required|string|max:500',
        ]);

        try {
            $reviewer = auth()->user();
            $this->archiveService->rejectRetrieval(
                $retrievalRequest,
                $reviewer,
                $request->notes
            );

            return redirect()->back()->with('success', 'Retrieval request rejected.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Fulfill retrieval request
     */
    public function fulfillRetrieval(ArchiveRetrievalRequest $retrievalRequest)
    {
        try {
            $result = $this->archiveService->fulfillRetrieval($retrievalRequest);

            if ($result['type'] === 'view') {
                return view('registrar.services.archive.retrieved-data', [
                    'data' => $result['data'],
                    'archive' => $retrievalRequest->archive,
                ]);
            }

            if ($result['type'] === 'download') {
                // Return download path or trigger download
                return redirect()->back()->with('success', 'File ready for download.');
            }

            return redirect()->back()->with('success', 'Record temporarily restored.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show disposal queue
     */
    public function disposalQueue(Request $request)
    {
        $query = ArchiveDisposalQueue::with(['archive', 'requester', 'reviewer', 'disposer']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $queue = $query->orderByDesc('scheduled_date')->paginate(20);

        return view('registrar.services.archive.disposal-queue', compact('queue'));
    }

    /**
     * Approve disposal
     */
    public function approveDisposal(Request $request, ArchiveDisposalQueue $disposalQueue)
    {
        $this->validate($request, [
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $reviewer = auth()->user();
            $this->archiveService->approveDisposal($disposalQueue, $reviewer, $request->notes);

            return redirect()->back()->with('success', 'Disposal approved.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject disposal
     */
    public function rejectDisposal(Request $request, ArchiveDisposalQueue $disposalQueue)
    {
        $this->validate($request, [
            'notes' => 'required|string|max:500',
        ]);

        try {
            $reviewer = auth()->user();
            $this->archiveService->rejectDisposal($disposalQueue, $reviewer, $request->notes);

            return redirect()->back()->with('success', 'Disposal rejected.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Execute disposal
     */
    public function executeDisposal(ArchiveDisposalQueue $disposalQueue)
    {
        try {
            $executor = auth()->user();
            $certificate = $this->archiveService->executeDisposal($disposalQueue, $executor);

            return redirect()->back()->with('success', "Disposal executed. Certificate: {$certificate->certificate_number}");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * View disposal certificate
     */
    public function viewCertificate(DisposalCertificate $certificate)
    {
        $certificate->load(['disposalQueue.archive', 'disposer']);

        return view('registrar.services.archive.certificate', compact('certificate'));
    }

    /**
     * Show retention policies
     */
    public function retentionPolicies()
    {
        $policies = RetentionPolicy::withCount('archives')
            ->orderBy('record_type')
            ->get();

        return view('registrar.services.archive.retention-policies', compact('policies'));
    }

    /**
     * Show archive settings
     */
    public function settings()
    {
        $settings = ArchiveSetting::all()->keyBy('setting_key');

        return view('registrar.services.archive.settings', compact('settings'));
    }

    /**
     * Show archive access logs
     */
    public function accessLogs(Request $request)
    {
        $query = ArchiveAccessLog::with(['archive', 'performer']);

        if ($request->has('archive_id') && $request->archive_id) {
            $query->forArchive($request->archive_id);
        }

        if ($request->has('action') && $request->action) {
            $query->byAction($request->action);
        }

        if ($request->has('user_id') && $request->user_id) {
            $query->byUser($request->user_id);
        }

        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('performed_at', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('performed_at', '<=', $request->to_date);
        }

        $logs = $query->orderByDesc('performed_at')->paginate(50);

        return view('registrar.services.archive.access-logs', compact('logs'));
    }

    /**
     * Get archive statistics
     */
    private function getArchiveStats(): array
    {
        return [
            'total_archives' => Archive::count(),
            'by_category' => [
                'academic' => Archive::academic()->count(),
                'administrative' => Archive::administrative()->count(),
                'personnel' => Archive::personnel()->count(),
                'compliance' => Archive::compliance()->count(),
            ],
            'pending_retrievals' => ArchiveRetrievalRequest::pending()->count(),
            'pending_disposals' => ArchiveDisposalQueue::pendingApproval()->count(),
            'temporarily_restored' => Archive::temporarilyRestored()->count(),
        ];
    }

    /**
     * Store a new retrieval request
     */
    public function storeRetrievalRequest(Request $request): JsonResponse
    {
    $this->validate($request, [
    'archive_id' => 'required|exists:archives,id',
    'reason' => 'required|string|max:500',
    ]);
    
    try {
    $archive = Archive::findOrFail($request->archive_id);
    $retrievalRequest = $this->archiveService->requestRetrieval(
    $archive,
    auth()->id(),
    $request->reason,
    'view'
    );
    
    return response()->json(['success' => true, 'message' => 'Retrieval request submitted successfully.']);
    } catch (\Exception $e) {
    return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
    }
    }
    
    /**
     * Approve a retrieval request
     */
    public function approveRetrievalRequest(Request $request, ArchiveRetrievalRequest $retrievalRequest): JsonResponse
    {
    try {
    $reviewer = auth()->user();
    $this->archiveService->approveRetrieval(
    $retrievalRequest,
    $reviewer,
    $request->notes ?? 'Approved',
    $request->restoration_days ?? 30
    );
    
    return response()->json(['success' => true, 'message' => 'Retrieval request approved.']);
    } catch (\Exception $e) {
    return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
    }
    }
    
    /**
     * Reject a retrieval request
     */
    public function rejectRetrievalRequest(Request $request, ArchiveRetrievalRequest $retrievalRequest): JsonResponse
    {
    $this->validate($request, [
    'notes' => 'required|string|max:500',
    ]);
    
    try {
    $reviewer = auth()->user();
    $this->archiveService->rejectRetrieval(
    $retrievalRequest,
    $reviewer,
    $request->notes
    );
    
    return response()->json(['success' => true, 'message' => 'Retrieval request rejected.']);
    } catch (\Exception $e) {
    return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
    }
    }
    
    /**
     * Fulfill a retrieval request
     */
    public function fulfillRetrievalRequest(ArchiveRetrievalRequest $retrievalRequest): JsonResponse
    {
    try {
    $result = $this->archiveService->fulfillRetrieval($retrievalRequest);
    
    return response()->json(['success' => true, 'message' => 'Retrieval request fulfilled.']);
    } catch (\Exception $e) {
    return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
    }
    }
    
    /**
     * Schedule disposal for an archive
     */
    public function scheduleDisposal(Request $request, Archive $archive): JsonResponse
    {
    $this->validate($request, [
    'disposal_method' => 'required|in:secure_delete,anonymize,export_then_delete',
    ]);
    
    try {
    $disposalQueue = $this->archiveService->scheduleDisposal(
    $archive,
    auth()->id(),
    $request->disposal_method
    );
    
    return response()->json(['success' => true, 'message' => 'Disposal scheduled successfully.']);
    } catch (\Exception $e) {
    return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
    }
    }
    
    /**
     * Cancel scheduled disposal
     */
    public function cancelDisposal(Archive $archive): JsonResponse
    {
    try {
    $this->archiveService->cancelDisposal($archive);
    
    return response()->json(['success' => true, 'message' => 'Disposal cancelled successfully.']);
    } catch (\Exception $e) {
    return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
    }
    }
    
    /**
     * View disposal certificate
     */
    public function disposalCertificate(Archive $archive)
    {
    $disposalQueue = $archive->disposalQueue()
    ->where('status', 'completed')
    ->first();
    
    if (!$disposalQueue || !$disposalQueue->certificate) {
    abort(404, 'Certificate not found.');
    }
    
    $certificate = $disposalQueue->certificate;
    $disposedBy = $certificate->disposer;
    $verifiedBy = $certificate->verifier ?? User::find(1);
    
    return view('registrar.services.archive.certificate', compact('archive', 'certificate', 'disposedBy', 'verifiedBy'));
    }
    
    /**
     * Update a retention policy
     */
    public function updateRetentionPolicy(Request $request, RetentionPolicy $retentionPolicy): JsonResponse
    {
    $this->validate($request, [
    'retention_months' => 'required|integer|min:1|max:120',
    'trigger_event' => 'required|in:end_of_academic_year,end_of_semester,graduation,separation,inactivity,manual',
    'disposal_method' => 'required|in:secure_delete,anonymize,export_then_delete',
    'is_active' => 'boolean',
    ]);
    
    try {
    $retentionPolicy->update([
    'retention_months' => $request->retention_months,
    'trigger_event' => $request->trigger_event,
    'disposal_method' => $request->disposal_method,
    'is_active' => $request->is_active ?? true,
    ]);
    
    return response()->json(['success' => true, 'message' => 'Retention policy updated successfully.']);
    } catch (\Exception $e) {
    return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
    }
    }
    
    /**
     * Destroy a retention policy
     */
    public function destroyRetentionPolicy(RetentionPolicy $retentionPolicy): JsonResponse
    {
    try {
    $retentionPolicy->delete();
    
    return response()->json(['success' => true, 'message' => 'Retention policy deleted successfully.']);
    } catch (\Exception $e) {
    return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
    }
    }
    
    /**
     * Update archive settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
    try {
    foreach ($request->except('_token') as $key => $value) {
    ArchiveSetting::setValue($key, $value, auth()->id());
    }
    
    return response()->json(['success' => true, 'message' => 'Settings updated successfully.']);
    } catch (\Exception $e) {
    return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
    }
    }
    
    /**
     * List archive categories
     */
    public function categories(): JsonResponse
    {
    $categories = ArchiveCategory::all();
    
    return response()->json(['success' => true, 'data' => $categories]);
    }
    
    /**
     * Store a new archive category
     */
    public function storeCategory(Request $request): JsonResponse
    {
    $this->validate($request, [
    'name' => 'required|string|max:100|unique:archive_categories,name',
    'description' => 'nullable|string|max:500',
    ]);
    
    try {
    $category = ArchiveCategory::create([
    'name' => $request->name,
    'description' => $request->description,
    ]);
    
    return response()->json(['success' => true, 'message' => 'Category created successfully.', 'data' => $category]);
    } catch (\Exception $e) {
    return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
    }
    }
    
    /**
     * Update an archive category
     */
    public function updateCategory(Request $request, ArchiveCategory $category): JsonResponse
    {
    $this->validate($request, [
    'name' => 'required|string|max:100|unique:archive_categories,name,' . $category->id,
    'description' => 'nullable|string|max:500',
    ]);
    
    try {
    $category->update([
    'name' => $request->name,
    'description' => $request->description,
    ]);
    
    return response()->json(['success' => true, 'message' => 'Category updated successfully.', 'data' => $category]);
    } catch (\Exception $e) {
    return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
    }
    }
    
    /**
     * Destroy an archive category
     */
    public function destroyCategory(ArchiveCategory $category): JsonResponse
    {
    try {
    $category->delete();
    
    return response()->json(['success' => true, 'message' => 'Category deleted successfully.']);
    } catch (\Exception $e) {
    return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
    }
    }
    
    /**
     * API: Get archives for a specific record type
     */
    public function apiGetArchives(Request $request): JsonResponse
    {
    $query = Archive::with(['archiver']);
    
    if ($request->has('record_type')) {
    $query->where('record_type', $request->record_type);
    }
    
    if ($request->has('category')) {
    $query->where('archive_category', $request->category);
    }
    
    $archives = $query->orderByDesc('archived_at')->limit(100)->get();
    
    return response()->json(['data' => $archives]);
    }
    
    /**
     * API: Get archive details
     */
    public function apiGetArchive(Archive $archive): JsonResponse
    {
    $archive->load(['archiver', 'retentionPolicy', 'retrievalRequests']);
    
    return response()->json(['data' => $archive]);
    }
}