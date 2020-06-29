<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\DomHandler;
use Goutte\Client;
use Exception;
use App\Http\Resources\StoryResource;
use App\Http\Resources\DetailResource;
use Config;
use Html2Text\Html2Text;

class HakoreController extends Controller
{
    use DomHandler;

    public function getList(Request $request)
    {
        $page = $request->get('page', 1);
        if ($request->genre) {
            $url = Config::get('app.hakore_source_url') . "/the-loai/$request->genre?truyendich=1&dangtienhanh=1&tamngung=1&hoanthanh=1&sapxep=top&page=$page";
        } else {
            $url = Config::get('app.hakore_source_url') . "/danh-sach?truyendich=1&dangtienhanh=1&tamngung=1&hoanthanh=1&sapxep=top&page=$page";
        }
        $html = $this->getHtmlData($url, [
            'body' => 0,
            'form' => 2,
            'main#mainpart' => 0,
            'div.container' => 1,
            'div' => 0
        ]);
        //dd($html);
        $novelNodes = $this->getNodes($html, "thumb-item-flow col-4 col-md-3 col-lg-2");
        if (count($novelNodes) == 0) {
            return \response()->json([
                'message' => 'The current list is empty'
            ], 404);
        }
        $maxPage = $this->getNodes($html, "paging_item paging_prevnext next ");
        $maxPageUrl = $this->getNodeAttrValue($maxPage[0], 'href');
        $maxPageNum = \preg_match('/&page=([0-9]+)/', $maxPageUrl, $out);
        $data = array();
        foreach ($novelNodes as $node) {
            array_push($data, new StoryResource(['content' => $this->getInnerHtml($node)]));
        }
        return response()->json([
            'books' => $data,
            'count' => count($data),
            'currentPage' => $page,
            'maxPage' => (int) $out[1],
            'maxPageUrl' => $maxPageUrl
        ], 200);
    }

    public function getNovelDetail(Request $request, $url)
    {
        $pageUrl = \Config::get('app.hakore_source_url') . "/truyen/$url";
        $html = $this->getHtmlData($pageUrl);
        $nodes = $this->getNodes($html, "col-12 col-lg-9 float-left");
        return response()->json(new DetailResource([
            'detail' => $this->getInnerHtml($nodes[0]),
            'volList' => $this->getInnerHtml($nodes[1]),
        ]), 200);
    }

    public function getChapterDetail(Request $request, $novel, $chapter)
    {
        $pageUrl = \Config::get('app.hakore_source_url') . "/truyen/$novel/$chapter";
        $html = $this->getHtmlData($pageUrl, [
            '#chapter-content' => 0,
        ]);
        $content = "";
        foreach ($this->getNodes($html, "", "", "p") as $node) {
            $txt = $this->getInnerHtml($node);
            if (\preg_match("<img src=\"(.*)\" alt=\".*\">", $txt, $imgSrc)) {
                $content .= "--img--[" . $imgSrc[1] . "]\n";
            } else {
                $content .= $txt . "\n";
            }
        };

        return response()->json([
            'content' => $content
        ], 200);
    }

    public function getGenre()
    {
    }
}
