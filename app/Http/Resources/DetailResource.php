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
            'novel_code' => $this['code'],
            'title' => $this->getTitle(),
            'cover' => $this->getCover(),
            'description' => $this->getDescription(),
            'genres' => $this->getGenres(),
            'author' => $authorNames['author'],
            'artist'  => $authorNames['artist'],
            'volumes' => $this->getVolumes()
        ];
    }

    private function getTitle() {
        $title =  $this->getInnerHtml($this->getNodes($this['detail'], "", "", "a")[0]);
        return $title;
    }

    private function getCover() {
        $style = $this->getNodeAttrValue($this->getNodes($this['detail'], "content img-in-ratio")[0], 'style');
        $value = \preg_match("#\('(.*?)'\)#", $style, $out);
        return $out[1];
    }

    private function getDescription() {
        $descriptionNodes = $this->getNodes($this['detail'], "summary-content");

        $descriptionArr = array_filter(\explode("\n", strip_tags($this->getInnerHtml($descriptionNodes[0]))));
        
        return $descriptionArr;
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
        $nodes = $this->getNodes($this['detail'], "info-item", "", "div");
        $content = [];
        foreach($nodes as $node) {
            $content[] = \strip_tags($this->trimEndline($this->getInnerHtml($node)));
        }
        \preg_match("/Tác giả:\s+(.*)/", $content[0], $author);
        \preg_match("/Họa sĩ:\s+(.*)/", $content[1], $artist);

        return [
            'author' => $author[1] ?? "Unknown",
            'artist' => $artist[1] ?? "Unknown"
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
                \preg_match("/<a href=\"(.*)\" title=.*>(.*)<\/a>/", $chapterDetail, $detail);
                \preg_match('/\/.*\/.*\/(.*)/', $detail[1], $code);
                array_push($chapters, [
                    'code' => $code[1],
                    'title' => $detail[2],
                    'url' => \Config::get('app.hakore_base_url') . "/chapter/{$this['code']}" . "/{$code[1]}",
                ]);
            }
            $volTitle = \preg_replace("/<span .*>*<\/span>/", "", trim($out[1]));
            array_push($vols, [
                'title' => trim($volTitle),
                'chapters' => $chapters
            ]);
        }

        return $vols;
    }
}
