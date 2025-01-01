<?php
namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Validator;

class BaseRepository
{
    protected $model;
    protected $validationRules = [];

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->all();
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function findOrFail($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $instance = $this->findOrFail($id);
        $instance->update($data);
        return $instance;
    }

    public function delete($id)
    {
        $instance = $this->findOrFail($id);
        $instance->delete();
        return $instance;
    }

    public function validate(array $data, array $rules = null)
    {
        $rules = $rules ?? $this->validationRules;
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new \Exception($validator->errors());
        }

        return true;
    }
}
