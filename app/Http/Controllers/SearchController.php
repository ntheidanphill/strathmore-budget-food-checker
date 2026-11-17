<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Location;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * SearchController - the GUEST student search (R1) and the JSON endpoint.
 * No login is needed here. The same query is used by the web page and the API
 * so both always return identical results.
 */
class SearchController extends Controller
{
    /** GET /  and  GET /?budget=200&location=2 - the student search page. */
    public function index(Request $request)
    {
        // Filter drop-downs: only active locations / categories are offered
        $locations  = Location::where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        $items = null; // null = "student has not searched yet" (show the welcome message)

        // Only search once a budget has been submitted
        if ($request->has('budget')) {
            // On failure Laravel redirects back with the errors shown by the layout
            $data  = $request->validate($this->rules());
            $items = $this->searchQuery($data)->get();
        }

        return view('search', compact('locations', 'categories', 'items'));
    }

    /**
     * GET /api/items?budget=200&location=2&category=3  - SIMPLE JSON ENDPOINT (API brief 5.9).
     * Returns the same items the web page shows, as JSON. Invalid input gives HTTP 422.
     */
    public function api(Request $request): JsonResponse
    {
        // We use Validator manually here so we can answer with JSON instead of a redirect
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Turn each MenuItem into a small, clean array (we choose exactly what to expose)
        $items = $this->searchQuery($data)->get()->map(fn (MenuItem $item) => [
            'id'       => $item->id,
            'name'     => $item->name,
            'price'    => (float) $item->price,
            'vendor'   => $item->vendor->name,
            'location' => $item->vendor->location->name,
            'category' => $item->category->name,
        ]);

        return response()->json([
            'count'  => $items->count(),
            'budget' => (float) $data['budget'],
            'items'  => $items,
        ]);
    }

    /** Validation rules shared by the web page and the API (API brief 5.4). */
    private function rules(): array
    {
        return [
            'budget'   => ['required', 'numeric', 'min:1', 'max:100000'],
            'location' => ['nullable', 'exists:locations,id'],
            'category' => ['nullable', 'exists:categories,id'],
        ];
    }

    /**
     * The actual search - replaces the old PostgreSQL function GetItemsByBudget.
     *   1. active()        -> in stock, vendor active, location active  (R1)
     *   2. withinBudget()  -> price <= budget                           (R1)
     *   3. optional filters by location and category
     *   4. cheapest first so students see the best deals on top
     */
    private function searchQuery(array $data): Builder
    {
        return MenuItem::query()
            ->with(['vendor.location', 'category'])   // eager-load to avoid many small queries (speed, R6)
            ->active()
            ->withinBudget((float) $data['budget'])
            ->when(! empty($data['location']), function (Builder $q) use ($data) {
                $q->whereHas('vendor', fn (Builder $v) => $v->where('location_id', $data['location']));
            })
            ->when(! empty($data['category']), fn (Builder $q) => $q->where('category_id', $data['category']))
            ->orderBy('price')
            ->orderBy('name');
    }
}
