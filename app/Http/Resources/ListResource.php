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
        return [
            'books' => $this['books'],
            'count' => count($this['books']),
            'prevPage' => $this->getPrevPage(),
            'nextPage' => $this->getNextPage(),
            'current' => $this['currentPage'],
            'lastPage' => $this['maxPage'],
            'lastPageUrl' => $this->getLastPageUrl()
        ];
    }

    private function getPrevPage() {
        $prevPage = $this['currentPage'] > 1 ? $this['currentPage'] - 1 : null;

        return $prevPage ? \Config::get('app.hakore_base_url') . "/all?page=$prevPage" . (array_key_exists('genre', $this->resource) ? "&genre={$this['genre']}" : "") : null;
    }

    private function getNextPage() {
        $nextPage = $this['currentPage'] < $this['maxPage'] ? $this['currentPage'] + 1 : null;

        return $nextPage ? \Config::get('app.hakore_base_url') . "/all?page=$nextPage" . (array_key_exists('genre', $this->resource) ? "&genre={$this['genre']}" : "") : null;
    }

    private function getLastPageUrl() {
        return \Config::get('app.hakore_base_url') . "/all?page={$this['maxPage']}" . (array_key_exists('genre', $this->resource) ? "&genre={$this['genre']}" : "");
    }
}
