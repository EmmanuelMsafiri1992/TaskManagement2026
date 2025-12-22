<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Filters\InvitationFilters;
use App\Http\Requests\InvitationRequest;
use App\Models\Invitation;
use App\Models\Role;
use AhsanDev\Support\Authorization\Http\Controllers\AuthorizeController;
use AhsanDev\Support\Field;
use Illuminate\Http\Request;

class InvitationsController extends AuthorizeController
{
    protected $name = 'user';

    /**
     * Display a listing of the resource.
     *
     * @param  \App\Http\Filters\InvitationFilters  $filters
     * @return \Illuminate\Http\Response
     */
    public function index(InvitationFilters $filters)
    {
        return Invitation::filter($filters)
                    ->with('role')
                    ->simplePaginate();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \App\Models\Invitation  $invitation
     * @return \Illuminate\Http\Response
     */
    public function create(Invitation $invitation)
    {
        return $this->fields($invitation);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Invitation  $invitation
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Invitation $invitation)
    {
        return new InvitationRequest($request, $invitation);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Invitation  $invitation
     * @return \Illuminate\Http\Response
     */
    public function show(Invitation $invitation)
    {
        return $invitation;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Invitation  $invitation
     * @return \Illuminate\Http\Response
     */
    public function edit(Invitation $invitation)
    {
        return $this->fields($invitation);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Invitation  $invitation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Invitation $invitation)
    {
        return new InvitationRequest($request, $invitation);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        Invitation::destroy($request->items);

        return [
            'message' => count($request->items) > 1
                ? 'Invitations Deleted Successfully!'
                : 'Invitation Deleted Successfully!',
        ];
    }

    public function fields($model)
    {
        return Field::make()
                ->field('email', $model->email)
                ->field('role', optional($model->role)->id, Role::options());
    }
}
