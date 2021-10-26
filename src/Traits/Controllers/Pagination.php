<?php

namespace Cmdobueno\Mod\Traits\Controllers;

use Illuminate\Database\Eloquent\Model;

trait Pagination
{
    protected $model = Model::class;
    protected $sort = [
        'field' => 'id',
        'dir' => 'asc'
    ];
    protected $filters = [];
    protected $per_page = 25;
    protected $page_options = [
        25,
        50,
        100,
        250,
        1000
    ];
    
    public function sort($field)
    {
        if ($this->sort['field'] === $field) {
            $this->sort['dir'] = $this->sort['dir'] === 'desc' ? 'asc' : 'desc';
        } else {
            $this->sort = [
                'field' => $field,
                'dir' => 'desc'
            ];
        }
    }
    
    public function getRecords()
    {
        $model = $this->model;
        $query = $model::query();
        
        foreach ($this->filters as $key => $value) {
            $query->where($key, 'LIKE', '%' . $value . '%');
        }
        $query->orderBy($this->sort['field'], $this->sort['dir']);
        return $query->paginate($this->per_page);
    }
}
