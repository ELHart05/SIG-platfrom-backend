<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\History;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);
            $search = $request->query('search', '');
            $status = $request->query('status', '');
    
            $query = Report::with('user', 'location', 'histories');
    
            if (!empty($search)) {
                $query->whereHas('location', function ($q) use ($search) {
                    $q->where('label', 'like', '%' . $search . '%');
                });
            }
    
            if (!empty($status)) {
                $query->where('status', $status);
            }
    
            $reports = $query->paginate($perPage);
    
            if ($reports->isEmpty()) {
                return response()->json([], 200);
            }
    
            return response()->json($reports, 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve reports: ' . $e->getMessage());
    
            return response()->json(['error' => 'Failed to retrieve reports'], 500);
        }
    }      

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'description' => 'required|string',
                'anomalie' => 'required|string',
                'user.first_name' => 'required|string',
                'user.last_name' => 'required|string',
                'user.email' => 'required|email',
                'user.phone' => 'required|string',
                'photos.*' => 'nullable|file|mimes:jpg,jpeg,png',
                'location' => 'required|array',
                'location.longitude' => 'required|numeric',
                'location.latitude' => 'required|numeric',
                'location.label' => 'required|string',
                'history.*' => 'nullable|array',
            ]);
    
            // Search for the user based on provided user data
            $user = User::where('email', $validated['user']['email'])
                        ->orWhere('phone', $validated['user']['phone'])
                        ->first();
    
            if (!$user) {
                // If user not found, create a new one
                $user = User::create([
                    'first_name' => $validated['user']['first_name'],
                    'last_name' => $validated['user']['last_name'],
                    'email' => $validated['user']['email'],
                    'phone' => $validated['user']['phone'],
                ]);
            }
    
            $urlPrefix = config('app.url', 'http://localhost') . ':' . env('PORT', '8000') . '/storage/photos/';
    
            $photos = [];
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('photos', 'public');
                    $photos[] = $urlPrefix . basename($path);
                }
            }
    
            $locationId = null;
            if (isset($validated['location']) && !empty($validated['location'])) {
                $locationData = $validated['location'];
    
                $location = Location::where('longitude', $locationData['longitude'])
                                    ->where('latitude', $locationData['latitude'])
                                    ->where('label', $locationData['label'])
                                    ->first();
    
                if (!$location) {
                    $location = Location::create($locationData);
                }
    
                $locationId = $location->id;
            }
    
            $photosJson = !empty($photos) ? json_encode($photos) : null;
    
            $reportData = array_merge($validated, [
                'photos' => $photosJson,
                'location_id' => $locationId,
                'user_id' => $user->id,
                'status' => 'received',
                'clarification' => '',
            ]);
    
            $report = Report::create($reportData);
    
            if (isset($validated['history']) && is_array($validated['history'])) {
                foreach ($validated['history'] as $historyData) {
                    History::create([
                        'report_id' => $report->id,
                        'status_from' => $historyData['status_from'] ?? null,
                        'status_to' => $historyData['status_to'] ?? 'received', // Default status
                        'date' => $historyData['date'] ?? now(),
                        'clarification' => $historyData['clarification'] ?? '', // Default empty string for clarification
                    ]);
                }
            }
    
            return response()->json($report, 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create report'], 500);
        }
    }

    public function show($id)
    {
        try {
            $report = Report::with('user', 'location', 'histories')->findOrFail($id);
    
            // Return the report with its associated user, location, and histories
            return response()->json($report, 200);
        } catch (ModelNotFoundException $e) {
            // Return a JSON response with a 404 error if the report is not found
            return response()->json(['error' => 'Report not found'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve report: ' . $e->getMessage());
    
            // Return a JSON response with a 500 error for any other exceptions
            return response()->json(['error' => 'Failed to retrieve report'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'clarification' => 'nullable|string',
                'description' => 'nullable|string',
                'anomalie' => 'nullable|string',
                'status' => 'nullable|string|in:received,in-progress,resolved',
                'photos.*' => 'nullable|file|mimes:jpg,jpeg,png',
                'location' => 'sometimes|array',
                'location.longitude' => 'nullable|numeric',
                'location.latitude' => 'nullable|numeric',
                'location.label' => 'nullable|string',
                'history' => 'nullable|array',
            ]);
    
            // Find the report
            $report = Report::findOrFail($id);
    
            // Handle file uploads
            if ($request->hasFile('photos')) {
                $photos = [];
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('photos', 'public');
                    $photos[] = Storage::url($path);
                }
                // Merge new photos with existing ones if needed
                $existingPhotos = json_decode($report->photos, true) ?? [];
                $validated['photos'] = json_encode(array_merge($existingPhotos, $photos));
            }
    
            // Handle location
            if (isset($validated['location']) && !empty($validated['location'])) {
                $locationData = $validated['location'];
                $location = Location::updateOrCreate(
                    ['id' => $locationData['id'] ?? null],
                    $locationData
                );
                $validated['location_id'] = $location->id;
            }
    
            // Update the report
            $report->update($validated);
    
            // Handle history if provided
            if (isset($validated['history']) && is_array($validated['history'])) {
                foreach ($validated['history'] as $historyData) {
                    History::create([
                        'report_id' => $report->id,
                        'status_from' => $historyData['status_from'] ?? null,
                        'status_to' => $historyData['status_to'] ?? null,
                        'date' => $historyData['date'] ?? now(),
                        'clarification' => $historyData['clarification'] ?? null,
                    ]);
                }
            }
    
            return response()->json($report, 200);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update report'], 500);
        }
    }    

    public function destroy($id)
    {
        try {
            $report = Report::findOrFail($id);
            $report->delete();
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Report not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete report'], 500);
        }
    }

    public function getClientReports(Request $request, $id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
    
            $search = $request->query('search', '');
            $status = $request->query('status', '');
            $perPage = $request->query('perPage', 10);
    
            $query = Report::with('user', 'location', 'histories')
                            ->where('user_id', $id);
    
            if (!empty($search)) {
                $query->whereHas('location', function ($q) use ($search) {
                    $q->where('label', 'like', '%' . $search . '%');
                });
            }
    
            if (!empty($status)) {
                $query->where('status', $status);
            }
    
            $reports = $query->paginate($perPage);
    
            if ($reports->isEmpty()) {
                return response()->json([], 200);
            }
    
            return response()->json($reports, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve reports'], 500);
        }
    }

    public function statistics()
    {
        try {
            $topUsers = Report::select(
                DB::raw('CONCAT(users.first_name, " ", users.last_name) as full_name'),
                'reports.user_id',
                DB::raw('count(*) as report_count')
            )
            ->join('users', 'reports.user_id', '=', 'users.id')
            ->groupBy('users.first_name', 'users.last_name', 'reports.user_id')
            ->orderBy('report_count', 'desc')
            ->take(5)
            ->get();

            $statusCounts = Report::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get();

            $reportsPerMonth = Report::select(DB::raw('strftime("%Y-%m", created_at) as month'), DB::raw('count(*) as count'))
            ->groupBy(DB::raw('strftime("%Y-%m", created_at)'))
            ->orderBy(DB::raw('strftime("%Y-%m", created_at)'), 'asc')
            ->get();

            $anomalieCounts = Report::select('anomalie', DB::raw('count(*) as count'))
                ->groupBy('anomalie')
                ->get();

            return response()->json([
                'top_users' => $topUsers,
                'status_counts' => $statusCounts,
                'reports_per_month' => $reportsPerMonth,
                'anomalie_counts' => $anomalieCounts,
            ], 200);
        } catch (\Exception $e) {
            print($e->getMessage());
            Log::error('Failed to retrieve statistics: ' . $e->getMessage());
            // return response()->json(['error' => 'Failed to retrieve statistics'], 500);
        }
    }

    public function updateReport(Request $request, $id)
    {
        // Validate request data
        $validatedData = $request->validate([
            'clarification' => 'nullable|string',
            'status' => 'nullable|string|in:received,in-progress,resolved',
        ]);
    
        try {
            // Find the report with related models
            $report = Report::with('user', 'location', 'histories')->findOrFail($id);
    
            // Capture old values for history
            $oldClarification = $report->clarification;
            $oldStatus = $report->status;
    
            // Update the report with new values
            $report->update($validatedData);
    
            // Determine if any changes were made
            $newStatus = $validatedData['status'] ?? $oldStatus;
            $newClarification = $validatedData['clarification'] ?? $oldClarification;
    
            // Create a history entry if there's any change
            if ($newStatus !== $oldStatus || $newClarification !== $oldClarification) {
                History::create([
                    'report_id' => $report->id,
                    'status_from' => $oldStatus,
                    'status_to' => $newStatus,
                    'date' => now(),
                    'clarification' => $newClarification,
                ]);
            }

            $report->refresh();
    
            // Return the updated report along with its histories, user, and location
            return response()->json([
                'report' => $report
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Report not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update report'], 500);
        }
    }

}
