<?php

namespace App\Controllers\Admin;

use CodeIgniter\RESTful\ResourceController;

class Messages extends ResourceController
{

protected $modelName = '\App\Models\Messages';
protected $format = 'json';

    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
    public function index()
    {
return $this->respond($this->model->join('users', 'users.id = messages.to')->select('messages.*, concat(users.first_name, " ", users.last_name) as name, users.email')->where('to', $this->request->user->uuid)->findAll());

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
$rules = [
'from' => 'required|is_not_unique[users.id]',
'to' => 'required|is_not_unique[users.id]',
'subject' => 'if_exist|string',
'message' => 'if_exist|string'];
$errors = [];
if(!$this->validate($rules, $errors))
return $this->failValidationErrors($this->validator->getErrors());

$id = $this->model->insert($this->request->getVar());
return $this->respondCreated(['id' => $id]);

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
        //
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
