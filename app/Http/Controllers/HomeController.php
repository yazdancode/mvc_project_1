<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{

    public function index()
    {
        echo "index method in HomeController";
    }

    public function create()
    {
        echo "create method in HomeController";
    }

    public function store()
    {
        echo "store method in HomeController";
    }

    public function edit($id)
    {
        echo "edit method in HomeController". $id;

    }

    public function update($id)
    {
        echo "update method in HomeController" . $id;

    }

    public function destroy($id)
    {
        echo "destroy method in HomeController" . $id;

    }

}