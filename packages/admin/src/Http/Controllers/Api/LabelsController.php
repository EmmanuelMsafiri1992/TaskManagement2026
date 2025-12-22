<?php

namespace Admin\Http\Controllers\Api;

use App\Http\Filters\LabelFilters;
use App\Http\Requests\LabelRequest;
use App\Models\Label;
use AhsanDev\Support\Authorization\Http\Controllers\AuthorizeController;
use AhsanDev\Support\Field;
use Facades\Admin\Static\Color;
use Illuminate\Http\Request;

class LabelsController extends AuthorizeController
{
    protected $name = 'label';

    /**
     * Display a listing of the resource.
     *
     * @param  \App\Http\Filters\LabelFilters  $filters
     * @return \Illuminate\Http\Response
     */
    public function index(LabelFilters $filters)
    {
        return Label::filter($filters)
                    ->simplePaginate();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \App\Models\Label  $label
     * @return \Illuminate\Http\Response
     */
    public function create(Label $label)
    {
        return $this->fields($label);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Label  $label
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Label $label)
    {
        return new LabelRequest($request, $label);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Label  $label
     * @return \Illuminate\Http\Response
     */
    public function show(Label $label)
    {
        return $label;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Label  $label
     * @return \Illuminate\Http\Response
     */
    public function edit(Label $label)
    {
        return $this->fields($label);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Label  $label
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Label $label)
    {
        return new LabelRequest($request, $label);
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
                'message' => 'This Label Cannot Delete!',
            ];
        }

        Label::destroy($request->items);

        return [
            'message' => count($request->items) > 1
                ? 'Labels Deleted Successfully!'
                : 'Label Deleted Successfully!',
        ];
    }

    public function fields($model)
    {
        return Field::make()
                ->field('name', $model->name)
                ->field('color', $model->meta['color'] ?? Color::default(), Color::options());
    }
}
