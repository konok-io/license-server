<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Control\ControlActionRequest;
use App\Http\Requests\Control\DisableDomainRequest;
use App\Http\Requests\Control\DisableInstallationRequest;
use App\Models\Customer;
use App\Models\License;
use App\Services\LicenseControlService;
use Illuminate\Http\JsonResponse;

/**
 * Enforcement operations: remote kill, suspend, and targeted disable of
 * customers, domains, and installations. All actions are AJAX/JSON.
 */
class ControlController extends Controller
{
    public function __construct(private readonly LicenseControlService $control)
    {
    }

    /** POST /admin/control/licenses/{license}/kill */
    public function killLicense(ControlActionRequest $request, License $license): JsonResponse
    {
        $this->authorize('kill', $license);

        $license = $this->control->killLicense(
            $license,
            $request->input('reason'),
            $request->boolean('blacklist'),
        );

        return response()->json([
            'message' => 'Kill switch engaged. The license is blocked on next verification.',
            'license' => $license,
        ]);
    }

    /** POST /admin/control/licenses/{license}/revive */
    public function reviveLicense(License $license): JsonResponse
    {
        $this->authorize('kill', $license);

        $license = $this->control->reviveLicense($license);

        return response()->json([
            'message' => 'Kill switch released. Clients must re-activate.',
            'license' => $license,
        ]);
    }

    /** POST /admin/control/licenses/{license}/suspend */
    public function suspendLicense(ControlActionRequest $request, License $license): JsonResponse
    {
        $this->authorize('update', $license);

        $license = $this->control->suspendLicense($license, $request->input('reason'));

        return response()->json([
            'message' => 'License suspended.',
            'license' => $license,
        ]);
    }

    /** POST /admin/control/licenses/{license}/resume */
    public function resumeLicense(License $license): JsonResponse
    {
        $this->authorize('update', $license);

        $license = $this->control->resumeLicense($license);

        return response()->json([
            'message' => 'License resumed.',
            'license' => $license,
        ]);
    }

    /** POST /admin/control/customers/{customer}/disable */
    public function disableCustomer(ControlActionRequest $request, Customer $customer): JsonResponse
    {
        $this->authorize('update', $customer);

        $affected = $this->control->disableCustomer($customer, $request->input('reason'));

        return response()->json([
            'message'           => "Customer disabled. {$affected} license(s) killed.",
            'licenses_affected' => $affected,
        ]);
    }

    /** POST /admin/control/customers/{customer}/enable */
    public function enableCustomer(Customer $customer): JsonResponse
    {
        $this->authorize('update', $customer);

        $customer = $this->control->enableCustomer($customer);

        return response()->json([
            'message'  => 'Customer re-enabled. Revive licenses individually as needed.',
            'customer' => $customer,
        ]);
    }

    /** POST /admin/control/licenses/{license}/disable-domain */
    public function disableDomain(DisableDomainRequest $request, License $license): JsonResponse
    {
        $revoked = $this->control->disableDomain(
            $license,
            $request->string('domain')->toString(),
            $request->input('reason'),
        );

        return response()->json([
            'message'             => "Domain disabled. {$revoked} activation(s) revoked and blacklisted.",
            'activations_revoked' => $revoked,
        ]);
    }

    /** POST /admin/control/licenses/{license}/disable-installation */
    public function disableInstallation(DisableInstallationRequest $request, License $license): JsonResponse
    {
        $done = $this->control->disableInstallation(
            $license,
            $request->string('installation_id')->toString(),
            $request->input('reason'),
        );

        if (! $done) {
            return response()->json([
                'message' => 'No matching installation found for this license.',
            ], 404);
        }

        return response()->json([
            'message' => 'Installation disabled and blacklisted.',
        ]);
    }
}
