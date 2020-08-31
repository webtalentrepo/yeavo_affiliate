<?php

namespace App\Http\Repositories;


abstract class Repository
{
    /**
     * Getting all data of model.
     * @return mixed
     */
    public function all()
    {
        return $this->model()->all();
    }

    public abstract function model();

    /**
     * Getting model data by given id.
     * @param $id
     * @return mixed
     */
    public function getValueById($id)
    {
        return $this->model()->find($id);
    }

    /**
     * Getting data by given columns and conditions, orders.
     * @param $columns
     * @param array $where
     * @param string $order
     * @return array
     */
    public function getValueByColumns($columns, $where = [], $order = '')
    {
        $query = $this->model()->select($columns);

        if (!empty($where)) {
            $query = $query->where($where);
        }

        if (!empty($order)) {
            if (is_array($order)) {
                $query = $query->orderBy($order[0], $order[1]);
            } else {
                $query = $query->orderBy($order);
            }
        }

        $re_rows = $query->get()->toArray();

        $reArray = [];

        if (sizeof($re_rows) > 0) {
            foreach ($re_rows as $key => $row) {
                $reArray[$key] = [];

                for ($i = 0; $i < count($columns); $i++) {
                    $reArray[$key][$columns[$i]] = $row[$columns[$i]];
                }
            }
        }

        return $reArray;
    }

    public function getAll()
    {
        return $this->model()->get();
    }
}
