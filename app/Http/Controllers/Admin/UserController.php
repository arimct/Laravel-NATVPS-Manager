<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        // To be implemented in task 7
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

    public function show(User $user)
    {
        abort(501, 'Not implemented yet');
    }

    public function edit(User $user)
    {
        abort(501, 'Not implemented yet');
    }

    public function update(Request $request, User $user)
    {
        abort(501, 'Not implemented yet');
    }

    public function destroy(User $user)
    {
        abort(501, 'Not implemented yet');
    }

    public function resetPassword(User $user)
    {
        abort(501, 'Not implemented yet');
    }
}
