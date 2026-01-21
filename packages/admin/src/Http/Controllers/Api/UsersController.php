<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Filters\UserFilters;
use App\Http\Requests\UserRequest;
use App\Models\Role;
use App\Models\User;
use AhsanDev\Support\Authorization\Http\Controllers\AuthorizeController;
use AhsanDev\Support\Field;
use Illuminate\Http\Request;

class UsersController extends AuthorizeController
{
    protected $name = 'user';

    /**
     * Display a listing of the resource.
     *
     * @param  \App\Http\Filters\UserFilters  $filters
     * @return \Illuminate\Http\Response
     */
    public function index(UserFilters $filters)
    {
        return User::filter($filters)
                    ->with('roles')
                    ->simplePaginate();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function create(User $user)
    {
        return $this->fields($user);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, User $user)
    {
        return new UserRequest($request, $user);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return $user;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        return $this->fields($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        return new UserRequest($request, $user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if ($request->items[0] == 1) {
            return [
                'message' => 'This User Cannot Delete!',
            ];
        }

        User::destroy($request->items);

        return [
            'message' => count($request->items) > 1
                ? 'Users Deleted Successfully!'
                : 'User Deleted Successfully!',
        ];
    }

    public function fields($model)
    {
        return Field::make()
                ->field('name', $model->name)
                ->field('email', $model->email)
                ->field('role', optional($model->roles->first())->id, Role::options());
    }

    /**
     * Get list of archived users.
     *
     * @param  \App\Http\Filters\UserFilters  $filters
     * @return \Illuminate\Http\Response
     */
    public function archived(UserFilters $filters)
    {
        return User::onlyTrashed()
                    ->filter($filters)
                    ->with('roles')
                    ->simplePaginate();
    }

    /**
     * Archive a team member (soft delete).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function archive($id)
    {
        if ($id == 1) {
            return response()->json([
                'message' => 'This team member cannot be archived!',
            ], 403);
        }

        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'Team member archived successfully!',
        ]);
    }

    /**
     * Unarchive a team member (restore from soft delete).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function unarchive($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return response()->json([
            'message' => 'Team member restored successfully!',
        ]);
    }
}
