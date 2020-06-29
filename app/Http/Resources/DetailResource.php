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
        $authorNames = $this->getAuthor();
        return [
            'title' => $this->getTitle(),
            'cover' => $this->getCover(),
            'genres' => $this->getGenres(),
            'author' => $authorNames['author'],
            'artist'  => $authorNames['artist'],
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
            $genre = $this->getInnerHtml($node);
            if(!\preg_match('/<i class=\".*\">.*<\/i>/', $genre)) {
                array_push($genres, $this->trimEndline($this->getInnerHtml($node)));
            }
        } 

        return $genres;
    }

    private function getAuthor() {
        $nodes = $this->getNodes($this['detail'], "series-information", "", "div");
        $html = $this->trimEndline($this->getInnerHtml($nodes[0]));
        //dd($html);
        $authorPreg = \preg_match("/<div class=\"info-item\"> <span class=\"info-name\">Tác giả:<\/span> <span class=\"info-value \"><a href=\".*\">(.*)<\/a><\/span> <\/div> <div class=\"info-item\"> <span class=\"info-name\">Họa sĩ:<\/span> <span class=\"info-value\"><a href=\".*\">(.*)<\/a><\/span> <\/div>/", $html, $author);
        return [
            'author' => $author[1],
            'artist' => $author[2]
        ];
        
    }

    private function getVolumes() {
        $nodes = $this->getNodes($this['volList'], "volume-list at-series basic-section volume-mobile gradual-mobile ");
        $vols = array();
        
        foreach($nodes as $node) {
            $detail = $this->trimEndline($this->getInnerHtml($node));
            $preg = \preg_match("/<span class=\"sect-title\">(.*)<\/span>/m", $detail, $out);
            $chapterNodes = $this->getNodes($detail, "chapter-name", "", "div");
            //dd($chapterNodes);
            $chapters = [];

            foreach($chapterNodes as $node) {
                $chapterDetail = \preg_replace('/\s+/', ' ', $this->getInnerHtml($node));
                $chapterDetailArr = \preg_match("/<a href=\"(.*)\" title=\"(.*)\">.*<\/a>/", $chapterDetail, $detail);
                array_push($chapters, [
                    'title' => $detail[2],
                    'url' => \Config::get('app.hakore_base_url') . \preg_replace('/truyen/', 'chapter', $detail[1]),
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
