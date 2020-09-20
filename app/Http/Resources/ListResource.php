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
        $dir = $this['search'] ? "search" : "all";
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
        if ($dir == "all") {
            return $prevPage ? \Config::get('app.hakore_base_url') . "/$dir?page=$prevPage" . (array_key_exists('genre', $this->resource) ? "&genre={$this['genre']}" : "") : null;
        } else {
            return $prevPage ? \Config::get('app.hakore_base_url') . "/$dir?page=$prevPage" .
                                "&selected={$this['selectGenres']}" .
                                "&ignore={$this['ignoreGenres']}" .
                                "&keyword={$this['keyword']}"
                                : null;
        }
    }
    private function getNextPage($dir) {
        $nextPage = $this['currentPage'] < $this['maxPage'] ? $this['currentPage'] + 1 : null;

        if ($dir == "all") {
            return $nextPage ? \Config::get('app.hakore_base_url') . "/$dir?page=$nextPage" . (array_key_exists('genre', $this->resource) ? "&genre={$this['genre']}" : "") : null;
        } else {
            return $nextPage ? \Config::get('app.hakore_base_url') . "/$dir?page=$nextPage" .
                                "&selected={$this['selectGenres']}" .
                                "&ignore={$this['ignoreGenres']}" .
                                "&keyword={$this['keyword']}"
                                : null;
        }
    }

    private function getLastPageUrl($dir) {
        return \Config::get('app.hakore_base_url') . "/$dir?page={$this['maxPage']}" . (array_key_exists('genre', $this->resource) ? "&genre={$this['genre']}" : "");
    }
}
