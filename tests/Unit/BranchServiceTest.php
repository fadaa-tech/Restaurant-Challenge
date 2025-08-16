<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Branch;
use App\Services\BranchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class BranchServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BranchService $branchService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->branchService = $this->app->make(BranchService::class);
    }

    #[Test]
    public function it_can_create_a_branch()
    {
        $data = [
            'name' => 'Main Branch',
            'retention_rate' => 85,
        ];

        $branch = $this->branchService->createBranch($data);

        $this->assertDatabaseHas('branches', $data);
        $this->assertInstanceOf(Branch::class, $branch);
    }

    #[Test]
    public function it_can_update_a_branch()
    {
        $branch = Branch::factory()->create([
            'name' => 'Old Branch',
            'retention_rate' => 50,
        ]);

        $updated = $this->branchService->updateBranch($branch->id, [
            'name' => 'Updated Branch',
            'retention_rate' => 95,
        ]);

        $this->assertInstanceOf(Branch::class, $updated);
        $this->assertEquals('Updated Branch', $updated->name);
        $this->assertEquals(95, $updated->retention_rate);

        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'name' => 'Updated Branch',
            'retention_rate' => 95,
        ]);
    }

    #[Test]
    public function it_can_delete_a_branch()
    {
        $branch = Branch::factory()->create();

        $deleted = $this->branchService->deleteBranch($branch->id);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('branches', ['id' => $branch->id]);
    }

    #[Test]
    public function it_can_find_a_branch()
    {
        $branch = Branch::factory()->create([
            'name' => 'Findable Branch',
            'retention_rate' => 70,
        ]);

        $found = $this->branchService->findBranchById($branch->id);

        $this->assertNotNull($found);
        $this->assertEquals('Findable Branch', $found->name);
        $this->assertEquals(70, $found->retention_rate);
    }
}
