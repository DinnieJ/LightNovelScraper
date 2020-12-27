<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Goutte\Client;
use Exception;
use App\Http\Resources\StoryResource;
use App\Http\Traits\DomHandler;
use App\Models\Hakore\Novel;

class ScrapController extends Controller
{
    use DomHandler;
    public function __construct()
    {
    }

    public function test()
    {
        $html = $this->getHtmlData('https://ln.hako.re/danh-sach?truyendich=1&dangtienhanh=1&tamngung=1&hoanthanh=1&sapxep=top', [
            'html', 'body' ,'form' ,'main', 'div.container'
        ], 1);
        $nodes = $this->getNodes($html, "thumb-item-flow col-4 col-md-3 col-lg-2");
        dd($html);
        $data = array();

        foreach ($nodes as $node) {
            array_push($data, new Novel([
                'content' => $this->getInnerHtml($node)
            ]));
        }  
        //dd($data); 
        $tmp = new \DomDocument();
        $tmp->loadHtml($data[0]->content);
        $element = $tmp->getElementsByTagName('a');
        dd($element);
        dd($element[0]->getAttribute('href'));
    }
}
