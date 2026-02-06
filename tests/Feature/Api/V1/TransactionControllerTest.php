<?php

use App\Models\Bill;
use App\Models\Category;
use App\Models\Team;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->user = User::factory()->withTeam()->create();
    $this->user->refresh();
    $this->team = $this->user->teams()->first();
    if ($this->team) {
        $this->user->switchTeam($this->team);
        $this->user->refresh();
    }
    $this->token = $this->user->createToken('test')->plainTextToken;
    Storage::fake('public');
    config()->set('filesystems.default', 'public');
});

test('can list transactions', function () {
    $bill = Bill::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    Transaction::factory()->count(5)->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'bill_id' => $bill->id,
    ]);

    $response = $this->withToken($this->token)
        ->getJson('/api/v1/transactions');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'amount',
                    'payment_date',
                    'payment_method',
                ],
            ],
            'links',
            'meta',
        ]);
});

test('can filter transactions by bill_id', function () {
    $bill1 = Bill::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    $bill2 = Bill::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'bill_id' => $bill1->id,
    ]);

    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'bill_id' => $bill2->id,
    ]);

    $response = $this->withToken($this->token)
        ->getJson('/api/v1/transactions?bill_id=' . $bill1->id);

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.bill_id'))->toBe($bill1->id);
});

test('can filter transactions by payment_method', function () {
    $bill = Bill::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'bill_id' => $bill->id,
        'payment_method' => 'Credit Card',
    ]);

    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'bill_id' => $bill->id,
        'payment_method' => 'Cash',
    ]);

    $response = $this->withToken($this->token)
        ->getJson('/api/v1/transactions?payment_method=Credit Card');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.payment_method'))->toBe('Credit Card');
});

test('can filter transactions by date range', function () {
    $bill = Bill::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'bill_id' => $bill->id,
        'payment_date' => '2025-01-15',
    ]);

    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'bill_id' => $bill->id,
        'payment_date' => '2025-02-15',
    ]);

    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'bill_id' => $bill->id,
        'payment_date' => '2025-03-15',
    ]);

    $response = $this->withToken($this->token)
        ->getJson('/api/v1/transactions?date_from=2025-02-01&date_to=2025-02-28');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.payment_date'))->toContain('2025-02-15');
});

test('can search transactions', function () {
    $bill = Bill::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'bill_id' => $bill->id,
        'notes' => 'Netflix monthly subscription',
    ]);

    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'bill_id' => $bill->id,
        'notes' => 'Spotify premium',
    ]);

    $response = $this->withToken($this->token)
        ->getJson('/api/v1/transactions?search=Netflix');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.notes'))->toContain('Netflix');
});

test('can create transaction', function () {
    $bill = Bill::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'status' => 'unpaid',
    ]);

    $response = $this->withToken($this->token)
        ->postJson('/api/v1/transactions', [
            'bill_id' => $bill->id,
            'amount' => 99.99,
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'Credit Card',
            'notes' => 'Test transaction',
        ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['id', 'amount', 'payment_date', 'payment_method'],
        ]);

    $this->assertDatabaseHas('transactions', [
        'bill_id' => $bill->id,
        'amount' => 99.99,
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    $this->assertDatabaseHas('bills', [
        'id' => $bill->id,
        'status' => 'paid',
    ]);
});

test('can create transaction with attachment', function () {
    $bill = Bill::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    $file = UploadedFile::fake()->create('receipt.pdf', 100);

    $response = $this->withToken($this->token)
        ->postJson('/api/v1/transactions', [
            'bill_id' => $bill->id,
            'amount' => 99.99,
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'Credit Card',
            'attachment' => $file,
        ]);

    $response->assertCreated();

    $transaction = Transaction::first();
    expect($transaction->attachment)->not->toBeNull();
    Storage::disk('public')->assertExists($transaction->attachment);
});

test('can show transaction', function () {
    $bill = Bill::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'bill_id' => $bill->id,
    ]);

    $response = $this->withToken($this->token)
        ->getJson("/api/v1/transactions/{$transaction->id}");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $transaction->id,
                'amount' => $transaction->amount,
            ],
        ]);
});

test('can update transaction', function () {
    $bill = Bill::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'bill_id' => $bill->id,
        'amount' => 50.00,
        'notes' => 'Old notes',
    ]);

    $response = $this->withToken($this->token)
        ->putJson("/api/v1/transactions/{$transaction->id}", [
            'amount' => 75.00,
            'notes' => 'Updated notes',
        ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Transaction updated successfully',
        ]);

    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'amount' => 75.00,
        'notes' => 'Updated notes',
    ]);
});

test('can update transaction attachment', function () {
    $bill = Bill::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    $oldFile = UploadedFile::fake()->create('old-receipt.pdf', 100);
    $oldPath = $oldFile->storePublicly('attachments', 'public');

    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'bill_id' => $bill->id,
        'attachment' => $oldPath,
    ]);

    $newFile = UploadedFile::fake()->create('new-receipt.pdf', 100);

    $response = $this->withToken($this->token)
        ->putJson("/api/v1/transactions/{$transaction->id}", [
            'attachment' => $newFile,
        ]);

    $response->assertOk();

    $transaction->refresh();
    expect($transaction->attachment)->not->toBe($oldPath);
    Storage::disk('public')->assertExists($transaction->attachment);
});

test('can delete transaction', function () {
    $bill = Bill::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'status' => 'paid',
    ]);

    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'bill_id' => $bill->id,
    ]);

    $response = $this->withToken($this->token)
        ->deleteJson("/api/v1/transactions/{$transaction->id}");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Transaction deleted successfully',
        ]);

    $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);

    $bill->refresh();
});

test('can delete transaction with attachment', function () {
    $bill = Bill::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    $file = UploadedFile::fake()->create('receipt.pdf', 100);
    $path = $file->storePublicly('attachments', 'public');

    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'bill_id' => $bill->id,
        'attachment' => $path,
    ]);

    $response = $this->withToken($this->token)
        ->deleteJson("/api/v1/transactions/{$transaction->id}");

    $response->assertOk();

    Storage::disk('public')->assertMissing($path);
});

test('can get transaction receipt', function () {
    $category = Category::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    $bill = Bill::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'category_id' => $category->id,
        'title' => 'Netflix Subscription',
    ]);

    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'bill_id' => $bill->id,
        'payment_method' => 'Credit Card',
    ]);

    $response = $this->withToken($this->token)
        ->getJson("/api/v1/transactions/{$transaction->id}/receipt");

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                'transaction',
                'receipt_details' => [
                    'transaction_id',
                    'bill_title',
                    'amount',
                    'payment_date',
                    'payment_method',
                    'category',
                    'team',
                    'paid_by',
                ],
            ],
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'receipt_details' => [
                    'bill_title' => 'Netflix Subscription',
                ],
            ],
        ]);
});

test('cannot access transactions without authentication', function () {
    $response = $this->getJson('/api/v1/transactions');

    $response->assertUnauthorized();
});

test('cannot access transactions without team', function () {
    $userWithoutTeam = User::factory()->create();
    $token = $userWithoutTeam->createToken('test')->plainTextToken;

    $response = $this->withToken($token)
        ->getJson('/api/v1/transactions');

    $response->assertStatus(403);
});

test('validates required fields when creating transaction', function () {
    $response = $this->withToken($this->token)
        ->postJson('/api/v1/transactions', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['bill_id', 'amount', 'payment_date']);
});

test('can sort transactions', function () {
    $bill = Bill::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'bill_id' => $bill->id,
        'payment_date' => '2025-01-01',
        'amount' => 100.00,
    ]);

    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'bill_id' => $bill->id,
        'payment_date' => '2025-02-01',
        'amount' => 50.00,
    ]);

    $response = $this->withToken($this->token)
        ->getJson('/api/v1/transactions?sort_by=payment_date&sort_direction=asc');

    $response->assertOk();
    expect($response->json('data.0.payment_date'))->toContain('2025-01-01');
    expect($response->json('data.1.payment_date'))->toContain('2025-02-01');
});
