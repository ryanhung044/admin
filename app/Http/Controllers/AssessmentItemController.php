<?php

namespace App\Http\Controllers;

use App\Models\AssessmentItem;
use Illuminate\Http\Request;

class AssessmentItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $model = AssessmentItem::all();
        return response()->json($model);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(AssessmentItem $assessmentItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AssessmentItem $assessmentItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AssessmentItem $assessmentItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AssessmentItem $assessmentItem)
    {
        //
    }
}
