<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DomainForwarding;
use App\Models\NatVps;
use Illuminate\Http\Request;

class DomainForwardingController extends Controller
{
    public function index(NatVps $natVps)
    {
        // To be implemented in task 11
        abort(501, 'Not implemented yet');
    }

    public function store(Request $request, NatVps $natVps)
    {
        abort(501, 'Not implemented yet');
    }

    public function destroy(NatVps $natVps, DomainForwarding $domainForwarding)
    {
        abort(501, 'Not implemented yet');
    }
}
