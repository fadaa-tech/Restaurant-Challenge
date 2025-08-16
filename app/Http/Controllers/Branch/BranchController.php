<?php

namespace App\Http\Controllers\Branch;
use App\Traits\ResponseTrait;
use App\Services\BranchService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Branch\CreateBranchRequest;

class BranchController extends Controller
{
    use ResponseTrait;
     protected BranchService $service;

    public function __construct(BranchService $service)
    {
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
       return $this->success($this->service->getAllBranches(),"Branches retrieved successfully")??$this->error("Could not retrieve branches");
    }

    public function store(CreateBranchRequest $request)
    {
        $branch = $this->service->createBranch($request->validated());
        return $this->success($branch, "Branch created successfully")??$this->error("Could not create branch");
    }

    public function show(int $id)
    {
        $branch = $this->service->findBranchById($id);
        if (!$branch) {
            return $this->error("Branch not found", 404);
        }
        return $this->success($branch,"Branch retrieved successfully") ?? $this->error("Could not retrieve branch");
    }

    public function update(CreateBranchRequest $request, int $id)
    {
        $branch = $this->service->updateBranch($id, $request->validated());
        if (!$branch) {
           return $this->error("Branch not found", 404);
        }
        return $this->success($branch,"Branch updated successfully") ?? $this->error("Could not update branch");
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->service->deleteBranch($id);
        if (!$deleted) {
            return $this->error("Branch not found", 404);
        }
        return $this->success(null, "Branch deleted successfully") ?? $this->error("Could not delete branch");
    }
}
