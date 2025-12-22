<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Requests\RoleRequest;
use App\Models\Permission;
use App\Models\Role;
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
        return Role::withCount('permissions')->simplePaginate();
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
