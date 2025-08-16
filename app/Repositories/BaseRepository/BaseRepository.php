<?php
namespace App\Repositories\BaseRepository;

use Illuminate\Database\Eloquent\Model;

class BaseRepository
{
   protected $model;

   public function __construct(Model $model)
   {
       $this->model = $model;
   }

  public function getAll(array $parameters = [])
{
    $query = $this->model->query();

    // العلاقات
    if (!empty($parameters['with'])) {
        $query->with($parameters['with']);
    }

    // تحديد الأعمدة
    if (!empty($parameters['select'])) {
        $query->select($parameters['select']);
    } else {
        $query->select(['*']);
    }

    // الترتيب
    if (!empty($parameters['orderBy'])) {
        foreach ($parameters['orderBy'] as $order) {
            $query->orderBy($order['column'], $order['direction'] ?? 'asc');
        }
    }

    // Limit
    if (!empty($parameters['limit'])) {
        $query->limit($parameters['limit']);
    }

    return $query->get();
}


   public function getById($id, array $parameters = [])
   {
     $query = $this->model->query();

    // العلاقات
    if (!empty($parameters['with'])) {
        $query->with($parameters['with']);
    }

    // تحديد الأعمدة
    if (!empty($parameters['select'])) {
        $query->select($parameters['select']);
    } else {
        $query->select(['*']);
    }

    // الترتيب
    if (!empty($parameters['orderBy'])) {
        foreach ($parameters['orderBy'] as $order) {
            $query->orderBy($order['column'], $order['direction'] ?? 'asc');
        }
    }

    // Limit
    if (!empty($parameters['limit'])) {
        $query->limit($parameters['limit']);
    }
    return $query->find($id);
    }

   public function create(array $data)
   {
       return $this->model->create($data);
   }

   public function update($id, array $data)
   {
       $item = $this->model->find($id);
       if ($item) {
           $item->update($data);
           return $item;
       }
       return null;
   }

   public function delete($id)
   {
       $item = $this->model->find($id);
       if ($item) {
           $item->delete();
           return true;
       }
       return false;
   }
}
