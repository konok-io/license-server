<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(private readonly CustomerService $service)
    {
    }

    public function index(): View
    {
        $this->authorize('viewAny', Customer::class);

        return view('admin.customers.index');
    }

    /** Server-side DataTables endpoint. */
    public function data(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Customer::class);

        $query = Customer::query()->withCount('licenses');

        // Global search.
        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $total = Customer::count();
        $filtered = (clone $query)->count();

        $customers = $query
            ->orderBy($request->input('sort', 'created_at'), $request->input('dir', 'desc'))
            ->offset((int) $request->input('start', 0))
            ->limit((int) $request->input('length', 15))
            ->get();

        return response()->json([
            'draw'            => (int) $request->input('draw'),
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $customers->map(fn (Customer $c): array => [
                'id'             => $c->id,
                'uuid'           => $c->uuid,
                'name'           => $c->name,
                'company'        => $c->company,
                'email'          => $c->email,
                'country'        => $c->country,
                'licenses_count' => $c->licenses_count,
                'is_active'      => $c->is_active,
                'created_at'     => $c->created_at?->format('Y-m-d'),
            ]),
        ]);
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->service->create($request->validated());

        return response()->json([
            'message'  => 'Customer created successfully.',
            'customer' => $customer,
        ], 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        $this->authorize('view', $customer);

        $customer->loadCount('licenses');

        return response()->json(['customer' => $customer]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        $customer = $this->service->update($customer, $request->validated());

        return response()->json([
            'message'  => 'Customer updated successfully.',
            'customer' => $customer,
        ]);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $this->authorize('delete', $customer);

        $this->service->delete($customer);

        return response()->json(['message' => 'Customer deleted successfully.']);
    }
}
