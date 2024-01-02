<?php

namespace App\Controllers\Admin;

use CodeIgniter\RESTful\ResourceController;

class Notifications extends ResourceController
{
protected $modelName = '\App\Models\Notifications';
protected $format = 'json';


    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
    public function index()
    {
        //
    }

    /**
     * Return the properties of a resource object
     *
     * @return mixed
     */
    public function show($id = null)
    {
        //
    }

    /**
     * Return a new resource object, with default properties
     *
     * @return mixed
     */
    public function new()
    {
        //
    }

    /**
     * Create a new resource object, from "posted" parameters
     *
     * @return mixed
     */
    public function create()
    {
        //
    }

    /**
     * Return the editable properties of a resource object
     *
     * @return mixed
     */
    public function edit($id = null)
    {
        //
    }

    /**
     * Add or update a model resource, from "posted" properties
     *
     * @return mixed
     */
    public function update($id = null)
    {
$rules = [
'action' => 'required|in_list[read,dismissed]'];
if(!$this->validate($rules))
return $this->failValidations($this->validator->getErrors());
$request = (array) $this->request->getVar();

$this->model->update($id, ['action' => $request['action']]);
return $this->respond('success');
    }

    /**
     * Delete the designated resource object from the model
     *
     * @return mixed
     */
    public function delete($id = null)
    {
        //
    }
}
