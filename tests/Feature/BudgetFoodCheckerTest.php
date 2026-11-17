<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Location;
use App\Models\MenuItem;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for the Strathmore Budget Food Checker.
 * They map to the testing checklist in the API brief (section 5.10):
 *   1. a valid operation that succeeds
 *   2. invalid data that is rejected
 *   3. an unauthenticated request to a protected page
 *   4. a logged-in user doing something their ROLE does not allow
 *   5. the main business rule
 *
 * RefreshDatabase rebuilds the tables before each test.
 * WARNING: run these against a TEST database (see TEAM_GUIDE.md) - never your real data.
 * Run all tests with:  php artisan test
 */
class BudgetFoodCheckerTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    //  HELPERS - small builders so each test stays short and readable
    // ------------------------------------------------------------------

    private function makeVendor(string $name = 'Test Kitchen'): Vendor
    {
        $location = Location::firstOrCreate(['name' => 'Student Centre'], ['is_active' => true]);

        return Vendor::create(['name' => $name, 'location_id' => $location->id, 'is_active' => true]);
    }

    private function makeItem(Vendor $vendor, string $name, float $price, bool $active = true): MenuItem
    {
        $category = Category::firstOrCreate(['name' => 'Lunch'], ['is_active' => true]);

        return MenuItem::create([
            'vendor_id' => $vendor->id, 'category_id' => $category->id,
            'name' => $name, 'price' => $price, 'is_active' => $active,
        ]);
    }

    private function makeUser(string $role, ?Vendor $vendor = null, bool $active = true, string $username = 'tester'): User
    {
        return User::create([
            'name' => 'Test User', 'username' => $username, 'email' => $username . '@test.local',
            'password' => 'password123',   // hashed automatically by the model cast
            'role' => $role, 'vendor_id' => $vendor?->id, 'is_active' => $active,
        ]);
    }

    // ------------------------------------------------------------------
    //  1. VALID OPERATIONS THAT SHOULD SUCCEED
    // ------------------------------------------------------------------

    public function test_guest_student_can_search_by_budget_without_logging_in(): void
    {
        $vendor = $this->makeVendor();
        $this->makeItem($vendor, 'Cheap Chapati', 20);
        $this->makeItem($vendor, 'Fancy Burger', 500);

        $response = $this->get('/?budget=100');

        $response->assertOk();
        $response->assertSee('Cheap Chapati');        // within budget -> shown
        $response->assertDontSee('Fancy Burger');     // over budget   -> hidden
    }

    public function test_vendor_manager_can_update_price_of_own_item(): void
    {
        $vendor = $this->makeVendor();
        $item = $this->makeItem($vendor, 'Pilau', 150);
        $manager = $this->makeUser(User::ROLE_VENDOR, $vendor);

        $response = $this->actingAs($manager)->put("/items/{$item->id}", [
            'name' => 'Pilau', 'price' => 170, 'category_id' => $item->category_id, 'is_active' => 1,
        ]);

        $response->assertRedirect(route('items.index'));
        $this->assertDatabaseHas('menu_items', ['id' => $item->id, 'price' => 170]);
    }

    public function test_staff_can_log_in_with_correct_credentials(): void
    {
        $vendor = $this->makeVendor();
        $this->makeUser(User::ROLE_VENDOR, $vendor, true, 'mgr_test');

        $response = $this->post('/login', ['username' => 'mgr_test', 'password' => 'password123']);

        $response->assertRedirect(route('vendor.dashboard'));
        $this->assertAuthenticated();
    }

    // ------------------------------------------------------------------
    //  2. INVALID DATA THAT MUST BE REJECTED
    // ------------------------------------------------------------------

    public function test_negative_budget_is_rejected(): void
    {
        $this->get('/?budget=-5')->assertSessionHasErrors('budget');
    }

    public function test_non_numeric_budget_is_rejected(): void
    {
        $this->get('/?budget=abc')->assertSessionHasErrors('budget');
    }

    public function test_invalid_price_is_rejected_when_editing_an_item(): void
    {
        $vendor = $this->makeVendor();
        $item = $this->makeItem($vendor, 'Pilau', 150);
        $manager = $this->makeUser(User::ROLE_VENDOR, $vendor);

        $this->actingAs($manager)
            ->put("/items/{$item->id}", ['name' => 'Pilau', 'price' => -20, 'category_id' => $item->category_id])
            ->assertSessionHasErrors('price');
    }

    public function test_wrong_password_does_not_log_in(): void
    {
        $this->makeUser(User::ROLE_VENDOR, $this->makeVendor(), true, 'mgr_test');

        $this->post('/login', ['username' => 'mgr_test', 'password' => 'WRONG'])->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    // ------------------------------------------------------------------
    //  3. UNAUTHENTICATED REQUESTS TO PROTECTED PAGES
    // ------------------------------------------------------------------

    public function test_guest_is_redirected_to_login_from_vendor_dashboard(): void
    {
        $this->get('/vendor')->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_admin_area(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    // ------------------------------------------------------------------
    //  4. WRONG ROLE (authenticated but NOT allowed)
    // ------------------------------------------------------------------

    public function test_vendor_manager_cannot_open_admin_area(): void
    {
        $manager = $this->makeUser(User::ROLE_VENDOR, $this->makeVendor());

        $this->actingAs($manager)->get('/admin')->assertForbidden();   // HTTP 403
    }

    public function test_deactivated_account_cannot_log_in(): void
    {
        $this->makeUser(User::ROLE_VENDOR, $this->makeVendor(), false, 'old_mgr');

        $this->post('/login', ['username' => 'old_mgr', 'password' => 'password123'])->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    // ------------------------------------------------------------------
    //  5. THE MAIN BUSINESS RULES
    // ------------------------------------------------------------------

    /** BR1: a Vendor Manager cannot change ANOTHER vendor's item. */
    public function test_vendor_manager_cannot_edit_another_vendors_item(): void
    {
        $vendorA = $this->makeVendor('Vendor A');
        $vendorB = $this->makeVendor('Vendor B');
        $itemOfB = $this->makeItem($vendorB, 'B Special', 100);
        $managerOfA = $this->makeUser(User::ROLE_VENDOR, $vendorA);

        $response = $this->actingAs($managerOfA)->put("/items/{$itemOfB->id}", [
            'name' => 'B Special', 'price' => 1, 'category_id' => $itemOfB->category_id, 'is_active' => 1,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('menu_items', ['id' => $itemOfB->id, 'price' => 100]); // unchanged!
    }

    /** BR1 (delete): same protection when trying to DELETE. */
    public function test_vendor_manager_cannot_delete_another_vendors_item(): void
    {
        $itemOfB = $this->makeItem($this->makeVendor('Vendor B'), 'B Special', 100);
        $managerOfA = $this->makeUser(User::ROLE_VENDOR, $this->makeVendor('Vendor A'));

        $this->actingAs($managerOfA)->delete("/items/{$itemOfB->id}")->assertForbidden();
        $this->assertDatabaseHas('menu_items', ['id' => $itemOfB->id]);
    }

    /** BR2 (R1): out-of-stock items must never be shown to students. */
    public function test_inactive_items_are_hidden_from_students(): void
    {
        $vendor = $this->makeVendor();
        $this->makeItem($vendor, 'Sold Out Soup', 50, false);

        $this->get('/?budget=500')->assertDontSee('Sold Out Soup');
    }

    /** BR3: a vendor cannot list two items with the same name. */
    public function test_duplicate_item_name_for_same_vendor_is_rejected(): void
    {
        $vendor = $this->makeVendor();
        $first = $this->makeItem($vendor, 'Chapati', 20);
        $manager = $this->makeUser(User::ROLE_VENDOR, $vendor);

        $this->actingAs($manager)
            ->post('/items', ['name' => 'Chapati', 'price' => 25, 'category_id' => $first->category_id, 'is_active' => 1])
            ->assertSessionHasErrors('name');
    }

    // ------------------------------------------------------------------
    //  JSON ENDPOINT
    // ------------------------------------------------------------------

    public function test_json_endpoint_returns_expected_structure(): void
    {
        $vendor = $this->makeVendor();
        $this->makeItem($vendor, 'Mandazi', 15);

        $this->getJson('/api/items?budget=100')
            ->assertOk()
            ->assertJsonStructure(['count', 'budget', 'items' => [['id', 'name', 'price', 'vendor', 'location', 'category']]])
            ->assertJsonPath('count', 1)
            ->assertJsonPath('items.0.name', 'Mandazi');
    }

    public function test_json_endpoint_rejects_missing_budget_with_422(): void
    {
        $this->getJson('/api/items')->assertStatus(422)->assertJsonValidationErrors('budget');
    }
}
