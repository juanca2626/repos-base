<?php

namespace App;

use Illuminate\Database\Eloquent\Collection;

class CustomCollection extends Collection
{
    /**
     * Transform each item in the collection using a callback.
     *
     * @param  callable  $callback
     * @param  array|callable|string  $groupBy
     * @return $this
     */
    public function transformGoupBy(callable $callback, $groupBy)
    {
        $this->transform($callback);

        $this->items = $this->groupBy($groupBy)->items;

        return $this;
    }
}
