<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Requests\RoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use AhsanDev\Support\Authorization\Http\Controllers\AuthorizeController;
use AhsanDev\Support\Field;
use Illuminate\Http\Request;

class RolesController extends AuthorizeController
{
    protected $name = 'role';

    /**
     * Display a listing of the resource.
     *
     * @param  \App\Http\Filters\RoleFilters  $filters
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles = Role::withCount(['permissions', 'users'])
            ->with(['users:id,name,email,avatar'])
            ->get();

        return response()->json([
            'data' => $roles,
        ]);
    }

    /**
     * Get all users for role assignment
     */
    public function users()
    {
        $users = User::select(['id', 'name', 'email', 'avatar'])
            ->with('roles:id,name')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $users]);
    }

    /**
     * Assign users to a role
     */
    public function assignUsers(Request $request, Role $role)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        // Sync users to the role (this will add new and remove unchecked)
        $role->users()->syncWithoutDetaching($request->user_ids);

        return response()->json([
            'message' => 'Users assigned successfully',
            'role' => $role->load('users:id,name,email,avatar'),
        ]);
    }

    /**
     * Remove a user from a role
     */
    public function removeUser(Request $request, Role $role)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $role->users()->detach($request->user_id);

        return response()->json([
            'message' => 'User removed from role successfully',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function create(Role $role)
    {
        return $this->fields($role);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Role $role)
    {
        return new RoleRequest($request, $role);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        return $role;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function edit(Role $role)
    {
        return $this->fields($role);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Role $role)
    {
        return new RoleRequest($request, $role);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        // if ($request->items[0] == 1) {
        //     return [
        //         'message' => 'This Role Cannot Delete!'
        //     ];
        // }

        foreach ($request->items as $item) {
            $hasUsers = Role::has('users')->find($item);

            if (! $hasUsers) {
                $items[] = $item;
            } else {
                throw new \Exception('Please first delete users related to the role!', 1);
            }
        }

        Role::destroy($request->items);

        return [
            'message' => count($request->items) > 1
                ? 'Roles Deleted Successfully!'
                : 'Role Deleted Successfully!',
        ];
    }

    public function fields($model)
    {
        return Field::make()
                ->field('name', $model->name)
                ->field('permissions', $model->permissions->pluck('id'), Permission::options());
    }
}
