<?php

namespace App\Services;

use App\Repositories\BaseRepository\BaseRepository;
use App\Models\Branch;

class BranchService
{
    protected $branchRepository;

    public function __construct()
    {
        $this->branchRepository = new BaseRepository(new Branch());
    }

    public function getAllBranches()
    {
        $parameters = [
            'with'    => [],
            'select'  => ['id', 'name', 'retention_rate'],
            'orderBy' => [],
            'limit'   => 10
        ];
        return $this->branchRepository->getAll($parameters);
    }

    public function findBranchById($id)
    {
        $parameters = [
            'with'    => [],
            'select'  => ['id', 'name', 'retention_rate'],
            'orderBy' => [],
            'limit'   => 10
        ];
        return $this->branchRepository->getById($id, $parameters);
    }

    public function createBranch(array $data)
    {
        return $this->branchRepository->create($data);
    }

    public function updateBranch($id, array $data)
    {
        return $this->branchRepository->update($id, $data);
    }

    public function deleteBranch($id)
    {
        return $this->branchRepository->delete($id);
    }
}
