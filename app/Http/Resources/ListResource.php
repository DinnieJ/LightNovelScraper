<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $dir = $this['search'] ? "tim-kiem-nang-cao" : "all";
        return [
            'books' => $this['books'],
            'count' => count($this['books']),
            'prevPage' => $this->getPrevPage($dir),
            'nextPage' => $this->getNextPage($dir),
            'current' => (int) $this['currentPage'],
            'lastPage' => $this['maxPage'],
            'lastPageUrl' => $this->getLastPageUrl($dir)
        ];
    }

    private function getPrevPage($dir) {
        $prevPage = $this['currentPage'] > 1 ? $this['currentPage'] - 1 : null;

        return $prevPage ? \Config::get('app.hakore_base_url') . "/$dir?page=$prevPage" . (array_key_exists('genre', $this->resource) ? "&genre={$this['genre']}" : "") : null;
    }

    private function getNextPage($dir) {
        $nextPage = $this['currentPage'] < $this['maxPage'] ? $this['currentPage'] + 1 : null;

        return $nextPage ? \Config::get('app.hakore_base_url') . "/$dir?page=$nextPage" . (array_key_exists('genre', $this->resource) ? "&genre={$this['genre']}" : "") : null;
    }

    private function getLastPageUrl($dir) {
        return \Config::get('app.hakore_base_url') . "/$dir?page={$this['maxPage']}" . (array_key_exists('genre', $this->resource) ? "&genre={$this['genre']}" : "");
    }
}
