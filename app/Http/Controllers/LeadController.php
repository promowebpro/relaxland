<?php

namespace App\Http\Controllers;

use App\Domain\Leads\CreateLead;
use App\Http\Requests\StoreLeadRequest;
use Illuminate\Http\RedirectResponse;

class LeadController extends Controller
{
    public function store(StoreLeadRequest $request, CreateLead $createLead): RedirectResponse
    {
        $createLead->handle($request->validated(), $request);

        return redirect()->route('success');
    }
}
