<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Transaction;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Escape special LIKE wildcards to prevent SQL injection
     */
    private function escapeLike($value)
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    public function index(Request $request)
    {
        $query = Project::query();

        // Search - sanitize to prevent SQL injection
        if ($request->filled('search')) {
            $searchTerm = $this->escapeLike($request->search);
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('code', 'like', '%' . $searchTerm . '%')
                    ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $projects = $query->withCount('transactions')
            ->withSum('transactions as total_spent', 'amount')
            ->paginate(15)
            ->withQueryString();

        // Stats
        $totalProjects = Project::count();
        $activeProjects = Project::where('status', 'active')->count();
        $completedProjects = Project::where('status', 'completed')->count();

        // Top Costing Projects (Graphics)
        // We want the projects with the most NEGATIVE sum of transactions (expenses).
        // Sorting by 'total_cost' ascending gives us -1000, -500, -100 (most expensive first).
        $topProjects = Project::withSum([
            'transactions as total_cost' => function ($q) {
                $q->where('amount', '<', 0);
            }
        ], 'amount')
            ->whereHas('transactions', function ($q) {
                $q->where('amount', '<', 0);
            })
            ->orderBy('total_cost', 'asc')
            ->take(5) // use take() instead of limit() for collection/builder consistency, though limit() works on builder
            ->get()
            // Filter out projects with 0 or positive balance (no expenses) if any sneak in
            ->filter(function ($p) {
                return $p->total_cost < 0;
            })
            // Map for the view
            ->map(function ($p) {
                return [
                    'name' => $p->name,
                    'cost' => abs($p->total_cost),
                    'color' => $p->color ?? '#6366f1'
                ];
            })
            ->values();

        return view('projects.index', compact(
            'projects',
            'totalProjects',
            'activeProjects',
            'completedProjects',
            'topProjects'
        ))->with('page', 'projects');
    }

    public function create()
    {
        return view('projects.create')->with('page', 'projects');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:projects,code',
            'description' => 'nullable|string|max:1000',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0|max:999999999.99',
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'status' => 'required|in:planning,active,completed,archived',
        ]);

        $project = Project::create($validated);

        return redirect()->route('projects.index')
            ->with('success', 'Project created successfully.')
            ->with('created_project_id', $project->id)
            ->with('created_project_name', $project->name);
    }

    public function show(Project $project)
    {
        $transactions = Transaction::with(['account', 'category'])
            ->where('project_id', $project->id)
            ->orderBy('transaction_date', 'desc')
            ->paginate(20);

        $totalSpent = Transaction::where('project_id', $project->id)
            ->where('amount', '<', 0)
            ->sum('amount');

        $totalIncome = Transaction::where('project_id', $project->id)
            ->where('amount', '>', 0)
            ->sum('amount');

        $transactionCount = Transaction::where('project_id', $project->id)->count();

        return view('projects.show', compact(
            'project',
            'transactions',
            'totalSpent',
            'totalIncome',
            'transactionCount'
        ))->with('page', 'projects');
    }

    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'))->with('page', 'projects');
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:projects,code,' . $project->id,
            'description' => 'nullable|string|max:1000',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0|max:999999999.99',
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'status' => 'required|in:planning,active,completed,archived',
        ]);

        $project->update($validated);

        return redirect()->route('projects.index')
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        // Unlink transactions (set project_id to null)
        Transaction::where('project_id', $project->id)->update(['project_id' => null]);

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }
}
