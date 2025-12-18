<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\NatVps;
use Illuminate\Http\Request;

class VpsController extends Controller
{
    public function index()
    {
        // To be implemented in task 10
        abort(501, 'Not implemented yet');
    }

    public function show(NatVps $natVps)
    {
        abort(501, 'Not implemented yet');
    }

    public function start(NatVps $natVps)
    {
        abort(501, 'Not implemented yet');
    }

    public function stop(NatVps $natVps)
    {
        abort(501, 'Not implemented yet');
    }

    public function restart(NatVps $natVps)
    {
        abort(501, 'Not implemented yet');
    }

    public function poweroff(NatVps $natVps)
    {
        abort(501, 'Not implemented yet');
    }
}
