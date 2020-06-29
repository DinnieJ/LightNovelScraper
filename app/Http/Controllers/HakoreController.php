<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\DomHandler;
use Goutte\Client;
use Exception;
use App\Http\Resources\StoryResource;

class HakoreController extends Controller
{
    use DomHandler;

    public function getList(Request $request){
        $page = $request->get('page', 1);
        $url = "https://ln.hako.re/danh-sach?truyendich=1&dangtienhanh=1&tamngung=1&hoanthanh=1&sapxep=top&page=$page";
        $html = $this->getHtmlData($url, [
            'body' => 0,
            'form' => 2,
            'main#mainpart' => 0,
            'div.container' => 1,
            'div' => 0
        ]);
        //dd($html);
        $novelNodes = $this->getNodes($html, "thumb-item-flow col-4 col-md-3 col-lg-2");
        if(count($novelNodes) == 0) {
            return \response()->json([
                'message' => 'The current list is empty'
            ], 404);
        }
        $maxPage = $this->getNodes($html, "paging_item paging_prevnext next ");
        $maxPageUrl = $this->getNodeAttrValue($maxPage[0], 'href');
        $maxPageNum = \preg_match('/&page=([0-9]+)/', $maxPageUrl, $out);
        $data = array();
        foreach($novelNodes as $node) {
            array_push($data, new StoryResource(['content' => $this->getInnerHtml($node)]));
        }
        return response()->json([
            'books' => $data,
            'currentPage' => $page,
            'maxPage' => (int) $out[1],
            'maxPageUrl' => $maxPageUrl
        ], 200);
    }

    public function getNovelDetail() {

    }

    public function getGenre() {

    }
}