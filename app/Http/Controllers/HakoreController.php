<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\DomHandler;
use Goutte\Client;
use Exception;
use App\Http\Resources\StoryResource;
use App\Http\Resources\DetailResource;
use App\Http\Resources\ListResource;
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
        $maxPageNodes = $this->getNodes($html, "paging_item paging_prevnext next ");
        $maxPage = 1;
        if (count($maxPageNodes) > 0) {
            $maxPageUrl = $this->getNodeAttrValue($maxPageNodes[0], 'href');
            \preg_match('/&page=([0-9]+)/', $maxPageUrl, $out);
            $maxPage = (int) $out[1];
        }
        $data = array();
        foreach ($novelNodes as $node) {
            array_push($data, new StoryResource(['content' => $this->getInnerHtml($node)]));
        }
        
        return response()->json(new ListResource([
            'books' => $data,
            'genre' => $request->genre ?? null,
            'maxPage' => $maxPage,
            'currentPage' => $page
        ]), 200);
    }

    public function getNovelDetail(Request $request, $url)
    {
        $urlDetail = explode('_', $url);
        // /dd($urlDetail[0]);
        $pageUrl = \Config::get('app.hakore_source_url') . "/{$urlDetail[0]}/{$urlDetail[1]}";
        $html = $this->getHtmlData($pageUrl);
        $nodes = $this->getNodes($html, "col-12 col-lg-9 float-left");
        return response()->json(new DetailResource([
            'detail' => $this->getInnerHtml($nodes[0]),
            'volList' => $this->getInnerHtml($nodes[1]),
            'code' => $url,
        ]), 200);
    }

    public function getChapterDetail(Request $request, $novel, $chapter)
    {
        $content = explode('_', $novel);
        $pageUrl = \Config::get('app.hakore_source_url') . "/$content[0]/$content[1]/$chapter";

        $html = $this->getHtmlData($pageUrl);
        $chapterSettingNodes = $this->getNodes($html, "rd_sd-button_item rd_top", "", "a");
        $prevUrl = \preg_replace('/\/(.*)\/(.*)\/(.*)/', "/api/hakore/chapter/$1_$2/$3", $this->getNodeAttrValue($chapterSettingNodes[0], 'href'));
        $nextUrl = \preg_replace('/\/(.*)\/(.*)\/(.*)/', "/api/hakore/chapter/$1_$2/$3", $this->getNodeAttrValue($chapterSettingNodes[1], 'href'));
        $content = [];
        foreach ($this->getNodes($html, "", "", "p") as $node) {
            $txt = $this->getInnerHtml($node);
            if (\preg_match('/src=\"(.+?)\"/', $txt, $url)) {
                $txt = "--img--[$url[1]]";
            } else {
                $txt = $this->getInnerHtml($node);
            }
            $content[] = \strip_tags($txt);
        };

        return response()->json([
            'content' => $content,
            'prev' => $prevUrl,
            'next' => $nextUrl,
        ], 200);
    }

    public function getGenreFilter(Request $request)
    {
        $pageUrl = Config::get('app.hakore_source_url') . "/tim-kiem-nang-cao";
        //dd($pageUrl);
        $html = $this->getHtmlData($pageUrl);
        $nodes = $this->getNodes($html, "search-gerne_item include col-4 col-md-3 col-lg-4 col-xl-3");
        $genres = [];

        foreach ($nodes as $node) {
            $inner = $this->getInnerHtml($node);

            \preg_match("/<label class=\"genre_label\" data-genre-id=\"(.*)\">/", $inner, $id);
            \preg_match("/<span class=\"gerne-name\">(.*)<\/span>/", $inner, $name);
            $genres[$name[1]] = $id[1];
        }

        return response()->json([
            'genres' => $genres
        ], 200);
    }

    public function getListByGenreUrl(Request $request)
    {
        $pageUrl = Config::get('app.hakore_source_url') ."/danh-sach";
        $html = $this->getHtmlData($pageUrl, [
            '.section-content' => 2,
            'ul' => 0
        ]);
        $nodes = $this->getNodes($html, "filter-type_item");
        $genres = [];
        foreach ($nodes as $node) {
            \preg_match("/<a href=\"(.*)\">(.*)<\/a>/", $this->getInnerHtml($node), $output);

            $genres[$output[2]] = \preg_replace("/https:\/\/ln.hako.re\/the-loai\//", Config::get('app.hakore_base_url') . "/all?genre=", $output[1]);
        }
        
        return response()->json([
            'urls' => $genres
        ], 200);
    }

    public function search(Request $request)
    {
        $keyword = $request->keyword ?? "";
        $selectedGenres = $request->selected ?? [];
        $ignoreGenres = $request->ignore ?? [];
        $status = $request->status ?? 0;

        $selectGenresString = \implode(',', $selectedGenres);
        $ignoreGenresString = \implode(',', $ignoreGenres);

        $pageUrl =  Config::get('app.hakore_source_url')
                    . "/tim-kiem-nang-cao?"
                    . "title=$keyword"
                    . "&selectgenres=$selectGenresString"
                    . "&rejectgenre=$ignoreGenresString"
                    . "&status=$status";

        $html = $this->getHtmlData($pageUrl, [
            '#mainpart' => 0,
            '.container' => 2,
            '.basic-section' => 0,
        ]);
        $nodes = $this->getNodes($html, "thumb-item-flow col-4 col-md-3 col-lg-2");

        if (count($nodes) == 0) {
            return response()->json([
                'message' => 'Found nothing!'
            ], 404);
        }
        $result = [];
        foreach ($nodes as $node) {
            array_push($result, new StoryResource([
                'content' => $this->getInnerHtml($node)
            ]));
        }
        
        return response()->json([
            'result' => $result
        ], 200);
    }
}
