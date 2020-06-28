<?php

namespace App\Http\Traits;

use Goutte\Client;

trait DomHandler
{
    public function getHtmlData($url, $filter = array())
    {
        $client = new Client();
        try {
            $crawler = $client->request('GET', $url);
        } catch (\Exception $e) {
            throw new Exception('Lost connection to server');
        }
        foreach($filter as $attr => $pos) {
            $crawler = $crawler->filter($attr)->eq($pos);
        }
        $html = $crawler->html();
        return $html;

    }
    public function getNodes($html, $class = "", $id = "", $tag = "*", $customAttr = array())
    {
        $document = new \DomDocument();
        $document->preserveWhiteSpace = false;
        $document->formatOutput = true;

        @$document->loadHtml('<?xml charset="utf-8"?>' . $html);

        $finder = new \DomXPath($document);
        $query = "//$tag" . ($class ? "[contains(@class, '$class')]": "") . ($id ? "[@id='$id']" : "");
        foreach ($customAttr as $attr => $value) {
            $query .= ($value ? "[@$attr='$value']": "");
        }
        $nodes = $finder->query($query);
    
        return $nodes;
    }

    public function getInnerHtml($element)
    {
        $innerHTML = "";
        $children  = $element->childNodes;
        foreach ($children as $child) {
            $innerHTML .= $element->ownerDocument->saveHTML($child);
        }

        return $innerHTML;
    }

    public function getNodeAttrValue($node, $attr)
    {
        return $node->getAttribute($attr);
    }

    public function getNodeByTag($html, $tag)
    {
        $dom = new \DomDocument();
        $dom->loadHtml($html);

        $element = $dom->getElementsByTagName($tag);

        return $element;
    }
}
