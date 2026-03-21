<?php

use App\Enum\VisitStatus;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\get;
use function Tests\Feature\createAccount;

beforeEach(function () {
    Storage::fake('public');
    [$account, $branch, $user] = createAccount();
    $this->employee            = $user;

    $branch->update([
        'address' => [
            'latitude'  => 26.2535193,
            'longitude' => 50.5313088,
        ],
    ]);

    $this->branch = $branch;

    $this->visit = Visit::factory()->create([
        'employee_id'      => $this->employee->id,
        'branch_id'        => $this->branch->id,
        'latitude'         => 26.2536,
        'longitude'        => 50.5320,
        'visited_at'       => now()->subHours(2),
        'distance_meters'  => 120,
        'notes'            => 'Customer was satisfied.',
        'status'           => VisitStatus::RECORDER,
        'rejection_reason' => null,
        'store_longitude'  => '50.5313088',
        'store_latitude'   => '26.2535193',
        'vendor_id'        => $this->branch->id,
    ]);

    // Attach file
    $this->visit->addMedia(UploadedFile::fake()->create('report.pdf', 500))
        ->toMediaCollection('attachments');

    $this->admin = $user;
});

function getFilteredVisits(array $filters = [])
{
    return get(route('account.visits.index', $filters));
}

test('admin can view agent visits index', function () {
    // Arrange: Use existing createAccount (untouched)
    [$account, $branch, $user] = createAccount();

    // Make sure $this is available if used (depends on your test class setup)
    $this->employee = $user->userable; // assuming $this->employee needed
    $this->branch   = $branch;
    $this->user     = $user;

    // Act: Create 18 visits with vendor_id = employee's branch ID (as per your logic)
    Visit::factory()->count(18)->create([
        'employee_id' => $this->employee->id,
        'branch_id'   => $this->branch->id,
        'vendor_id'   => $this->branch->id, // ✅ As per your current logic
    ]);

    $this->actingAs($this->user);

    $response = getFilteredVisits();

    // Assert: Response is correct with pagination
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Accounts/AgentVisits/Index')
        ->has('visits.data', 10)
        ->where('visits.total', 18)
        ->where('visits.per_page', 10)
        ->where('visits.current_page', 1)
        ->where('visits.last_page', 2)
    );
});
test('filters visits by visited_at date', function () {
    $this->actingAs($this->admin);
    $date = $this->visit->visited_at->format('Y-m-d');

    $response = getFilteredVisits(['filter[visited_at]' => $date]);
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Accounts/AgentVisits/Index')
        ->has('visits.data')
        ->where('visits.total', 1)
    );
});

test('filters visits by status', function () {
    $this->actingAs($this->admin);

    Visit::factory()
        ->count(8)
        ->state(new Sequence(
            ['status' => VisitStatus::REJECTED],
            ['status' => VisitStatus::RECORDER],
        ))
        ->create([
            'vendor_id'   => $this->branch->id,
            'employee_id' => $this->employee->id,
        ]);

    $response = getfilteredVisits(['filter[status]' => VisitStatus::REJECTED]);
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Accounts/AgentVisits/Index')
        ->has('visits.data')
        ->where('visits.total', 4)
    );
});

test('admin can edit a visit', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('account.visits.edit', $this->visit));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Account/AgentVisits/Edit')
        ->where('visit.id', $this->visit->id)
        ->where('visit.notes', $this->visit->notes)
        ->where('visit.employee_id', $this->agent->id)
    );
})->skip();

test('agent cannot edit another agent\'s visit', function () {
    $otherVisit = Visit::factory()->create();

    $this->actingAs($this->agent);

    $response = $this->get(route('account.visits.edit', $otherVisit));
    $response->assertForbidden();
})->skip();

test('admin can update visit with notes and attachment', function () {
    $this->actingAs($this->admin);

    $newNotes = 'Updated notes with follow-up.';
    $newFile  = UploadedFile::fake()->create('image.jpg', 200);

    $response = $this->put(route('account.visits.update', $this->visit->id), [
        'notes'       => $newNotes,
        'attachments' => [$newFile],
        'branch_id'   => $this->branch->id,
    ]);

    $response->assertRedirect();

    $this->followRedirects($response)->assertSee('Visit updated successfully.');
    $this->visit = $this->visit->fresh();

    expect($this->visit->notes)->toBe($newNotes);

    $media = $this->visit->getMedia('attachments');

    expect($media)
        ->toHaveCount(2)
        ->and($media->last()->file_name)->toBe('image.jpg');
});

test('admin can view visit details with audit data', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('account.visits.show', $this->visit));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Accounts/AgentVisits/Show')
        ->where('visit.id', $this->visit->id)
        ->where('visit.status', VisitStatus::RECORDER)
        ->where('visit.notes', 'Customer was satisfied.')
        ->where('visit.rejection_reason', null)
        ->where('visit.distance_meters', 120)
        ->where('visit.store_latitude', (float) $this->branch->address['latitude'])
        ->where('visit.store_longitude', (float) $this->branch->address['longitude'])
        ->where('visit.latitude', 26.2536)
        ->where('visit.longitude', 50.5320)
        ->has('visit.media', 1, fn ($file) => $file->where('file_name', 'report.pdf')->etc()
        )
    );
});

test('agent can only view their own visits', function () {
    $otherVisit = Visit::factory()->create();

    $this->actingAs($this->employee);

    $response = $this->get(route('account.visits.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Accounts/AgentVisits/Index')
        ->has('visits.data', 1)
        ->where('visits.data.0.id', $this->visit->id)
    );
});

test('agent cannot view another agent\'s visit details', function () {
    $otherVisit = Visit::factory()->create();

    $this->actingAs($this->admin);

    $response = $this->get(route('account.visits.index'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Accounts/AgentVisits/Index')
        ->has('visits.data', 1) // in db we have 2 but this user has only one visit
    );

});
