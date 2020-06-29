<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Traits\DomHandler;

class StoryResource extends JsonResource
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
        $baseUrl = \URL::to("/") . "/api/hakore";
        //dd($this['content']);
        $urls = $this->getAllUrl();
        $img = $this->getImg();
        return [
            'title' => $this->getTitle(),
            'url' => $urls['url'],
            'latest_chapter' => $this->getLatestChapter(),
            'latest_chapter_url' => $urls['latest_chapter'],
            'latest_vol' => $this->getLatestVol(),
            'img_url' => $img,
        ];
    }

    private function getBase64Image($url) {
        return \base64_encode(\file_get_contents($url));
    }

    private function getTitle() {
        $node = $this->getNodes($this['content'], "", "", "a");
        $inner = $this->getInnerHtml($node[2]);
        return $inner;
    }

    private function getLatestChapter() {
        $node = $this->getNodes($this['content'], "thumb_attr chapter-title");
        return $this->getNodeAttrValue($node[0], 'title');
    }

    private function getLatestVol() {
        $node = $this->getNodes($this['content'], "thumb_attr volume-title");
        return $this->getInnerHtml($node[0]);
    }

    private function getImg() {
        $node = $this->getNodes($this['content'], "content img-in-ratio lazyload");
        $imgUrl = $this->getNodeAttrValue($node[0], 'data-bg');
        return $imgUrl;
    }

    private function getAllUrl() {
        $node = $this->getNodes($this['content'], "", "", "a");
        return [
            'url' => \Config::get('app.hakore_base_url') . \str_replace('truyen', 'novel',  $this->getNodeAttrValue($node[2], 'href')),
            'latest_chapter' => $this->getNodeAttrValue($node[0], 'href'),
        ];
    }
}
