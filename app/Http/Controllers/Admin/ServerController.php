<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Server;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    public function index()
    {
        // To be implemented in task 5
        abort(501, 'Not implemented yet');
    }

    public function create()
    {
        abort(501, 'Not implemented yet');
    }

    public function store(Request $request)
    {
        abort(501, 'Not implemented yet');
    }

    public function edit(Server $server)
    {
        abort(501, 'Not implemented yet');
    }

    public function update(Request $request, Server $server)
    {
        abort(501, 'Not implemented yet');
    }

    public function destroy(Server $server)
    {
        abort(501, 'Not implemented yet');
    }

    public function testConnection(Server $server)
    {
        abort(501, 'Not implemented yet');
    }
}
