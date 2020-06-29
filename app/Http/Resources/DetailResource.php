<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Traits\DomHandler;

class DetailResource extends JsonResource
{
    use DomHandler;
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'title' => $this->getTitle(),
            'cover' => $this->getCover(),
            'genre' => $this->getGenres(),
            'author' => $this->getAuthor(),
            'artist'  => $this->getArtist(),
            'volumes' => $this->getVolumes()
        ];
    }

    private function getTitle() {
        return $this->getInnerHtml($this->getNodes($this['detail'], "", "", "a")[0]);
    }

    private function getCover() {
        $style = $this->getNodeAttrValue($this->getNodes($this['detail'], "content img-in-ratio")[0], 'style');
        $value = \preg_match("#\('(.*?)'\)#", $style, $out);
        return $out[1];
    }

    private function getGenres() {
        $genresNodes = $this->getNodes($this['detail'], "series-gerne-item");
        $genres = [];

        foreach($genresNodes as $node) {
            array_push($genres, $this->getInnerHtml($node));
        } 

        return $genres;
    }

    private function getAuthor() {
        $node = $this->getNodes($this['detail'], "", "", "a")[4];
        return $this->getInnerHtml($node);
    }

    private function getArtist() {
        $node = $this->getNodes($this['detail'], "", "", "a")[5];
        return $this->getInnerHtml($node);
    }

    private function getVolumes() {
        $nodes = $this->getNodes($this['volList'], "volume-list at-series basic-section volume-mobile gradual-mobile ");
        $vols = array();
        
        foreach($nodes as $node) {
            $detail = \preg_replace('/\s+/', ' ', $this->getInnerHtml($node));
            $preg = \preg_match("/<span class=\"sect-title\">(.*)<\/span>/m", $detail, $out);
            $chapterNodes = $this->getNodes($detail, "chapter-name", "", "div");
            //dd($chapterNodes);
            $chapters = [];

            foreach($chapterNodes as $node) {
                $chapterDetail = \preg_replace('/\s+/', ' ', $this->getInnerHtml($node));
                $chapterDetailArr = \preg_match("/<a href=\"(.*)\" title=\"(.*)\">.*<\/a>/", $chapterDetail, $detail);
                array_push($chapters, [
                    'title' => $detail[2],
                    'url' => $detail[1]
                ]);
            }
            array_push($vols, [
                'title' => trim($out[1]),
                'chapters' => $chapters
            ]);
        }

        return $vols;
    }
}
