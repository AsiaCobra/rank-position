<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Options;
use PHPHtmlParser\Dom\Node\HtmlNode;
use PHPHtmlParser\Dom\Node\TextNode;

class HomeController extends Controller
{
    public function home(Request $request){
        // $validatedData = $request->validate([
        //     'keyword' => ['required'],
        //     'domain' => ['required'],
        // ]);
        
        
        $dom = new Dom;
        $dom->setOptions(
            // this is set as the global option level.
            (new Options())
                ->setstrict(false)
                ->setremoveScripts(false)
                ->setremoveStyles(false)
                ->setRemoveDoubleSpace(false)
                ->setRemoveSmartyScripts(false)
                // ->setEnforceEncoding(false)
                ->setCleanupInput(false)
        );
        $keyword = $request->keyword ?? "digital marketing agency in malaysia";
        $domain  = $request->domain ?? "https://www.impossible.com.my/";
        $country = $request->country ?? 'sg';
        $countries = $this->getCountry();
        // return $countries;
        
        // return $keyword
        $getHtml = $this->getData($keyword,$country);
        // $dom->loadFromUrl("https://www.google.com/search?q=php+tutorial&gl=us&hl=en&num=100");
        $dom->loadStr($getHtml);
        $results = $dom->find(".MjjYud > div.g");
        // return count($results);
        $c = 1;
        $respon  = [];
        if( count($results) ){
            foreach ($results as $result) {
                $position = ($c++);

                $innerNode = new HtmlNode("div");
                $innerNode->setAttribute("class",'rank-position');
                $innerNode->setAttribute("style","border: 1px solid #c4c4c4; color: #ea4335; float: left; font-size: 18px; margin-right: 12px; margin-top: 20px; padding-left: 5px; padding-right: 5px;");

                
                // Extract the title and link of the result
                $title = $result->find("h3")[0]->text ?? '';
                $link = $result->find(".yuRUbf > a")[0]->href ?? '';

                // $snippet = $result->find(".VwiC3b",0)->innerText() ?? '';
                $snippet =  '';
                $result->find(".yuRUbf",0)->addChild($innerNode);
                $note = new TextNode("#$position");
                $result->find('.rank-position',0)->addChild($note);
                //match
                $match = false;
                if($link && parse_url($link)['host'] == parse_url($domain)['host']){
                    $match = true;
                    $result->setAttribute('style','background-color:rgb(191, 255, 37);');
                }
                $respon[] = [
                    'title'     => $title,
                    'link'      => $link,
                    'snippet'   => $snippet,
                    'position'  => $position,
                    'match'     => $match,
                    // 'result'    => $result
                ];
                
            }
        }
        $html = $dom->outerHtml;
        
        return view('home',[
            'html'=> $html,
            'respon'=> $respon,
            'keyword'=>$keyword,
            'country'=>$country,
            'countries'=>$countries,
            'domain'=>$domain
        ]);
    }

    public function getData($keyword,$country) {
        $keyword = str_replace(' ','+',$keyword);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.google.com/search?q=$keyword&gl=$country&hl=en&num=100&sclient=gws-wiz-serp");
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.4951.54 Safari/537.36');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $html = curl_exec($ch);
        return $html;
    }

    public function getCountry(){
        $path = resource_path() . '/json/google-countries.json';
        return json_decode(file_get_contents($path), true);
        
    }
}
