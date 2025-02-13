<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProjectController extends Controller
{
    protected function getTenantFromHeader(Request $request): string
    {
        $tenantEntity = $request->header('X-Tenant-Entity');
        
        if (!$tenantEntity) {
            throw new NotFoundHttpException('Tenant entity not provided in headers');
        }

        // Switch to the tenant's database
        $database = $tenantEntity . '_db';
        
        try {
            // Check if the database exists
            $databases = DB::select("SHOW DATABASES LIKE ?", [$database]);
            if (empty($databases)) {
                throw new NotFoundHttpException("Database {$database} does not exist");
            }

            DB::purge('mysql');
            config(['database.connections.mysql.database' => $database]);
            DB::reconnect('mysql');
            
            // Verify connection
            DB::connection()->getPdo();
            
            Log::info("Successfully connected to database: {$database}");
            return $tenantEntity;
            
        } catch (\Exception $e) {
            Log::error("Database connection error: " . $e->getMessage());
            throw new NotFoundHttpException("Could not connect to tenant database: {$e->getMessage()}");
        }
    }

    /**
     * Display a listing of projects.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $tenantEntity = $this->getTenantFromHeader($request);
            
            Log::info("Fetching projects for tenant: {$tenantEntity}");
            
            $projects = Project::query()
                ->when($request->filled('search'), function ($query) use ($request) {
                    $search = $request->input('search');
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhereHas('customer', function ($q) use ($search) {
                              $q->where('name', 'like', "%{$search}%");
                          });
                    });
                })
                ->when($request->filled('status'), function ($query) use ($request) {
                    if ($request->input('status') !== 'all') {
                        $query->where('status', $request->input('status'));
                    }
                })
                ->when($request->filled('sort_by'), function ($query) use ($request) {
                    $sortBy = $request->input('sort_by');
                    $sortDirection = $request->input('sort_direction', 'asc');
                    
                    if ($sortBy === 'client') {
                        $query->join('customers', 'projects.customer_id', '=', 'customers.id')
                              ->orderBy('customers.name', $sortDirection)
                              ->select('projects.*');
                    } else {
                        $query->orderBy($sortBy, $sortDirection);
                    }
                }, function ($query) {
                    $query->orderBy('created_at', 'desc');
                })
                ->with(['customer', 'createdBy'])
                ->get();

            Log::info("Found " . $projects->count() . " projects");
            
            return response()->json([
                'data' => $projects
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error in projects index: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Store a newly created project.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $tenantEntity = $this->getTenantFromHeader($request);

            Log::info("Creating project for tenant: {$tenantEntity}");
            
            $project = Project::create($request->all());

            Log::info("Project created successfully");
            
            return response()->json([
                'message' => 'Project created successfully',
                'data' => $project
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error in project store: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Display the specified project.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $tenantEntity = $this->getTenantFromHeader($request);

            Log::info("Fetching project {$id} for tenant: {$tenantEntity}");
            
            $project = Project::findOrFail($id);

            Log::info("Project found");
            
            return response()->json([
                'data' => $project
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error in project show: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update the specified project.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $tenantEntity = $this->getTenantFromHeader($request);

            Log::info("Updating project {$id} for tenant: {$tenantEntity}");
            
            $project = Project::findOrFail($id);
            $project->update($request->all());

            Log::info("Project updated successfully");
            
            return response()->json([
                'message' => 'Project updated successfully',
                'data' => $project
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error in project update: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Remove the specified project.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $tenantEntity = $this->getTenantFromHeader($request);

            Log::info("Deleting project {$id} for tenant: {$tenantEntity}");
            
            $project = Project::findOrFail($id);
            $project->delete();

            Log::info("Project deleted successfully");
            
            return response()->json([
                'message' => 'Project deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error in project destroy: " . $e->getMessage());
            throw $e;
        }
    }
}
