<?php 
defined('ABSPATH') or die();
if(! class_exists( 'ai_internal_link_building' ) ){

    Class ai_internal_link_building{



    static function comment_filter($content){
        return ai_internal_link_building::filter($content,1);
    }


    static function replace($haystack, $needle, $replace1, $replace2, $times=-1,$insensitive=''){
    global $replace;
        $replace = array($replace1,$replace2);
        $result = preg_replace_callback($needle.$insensitive,array('ai_internal_link_building','replace_callback'),$haystack,$times);

        return $result;
    }

    static function replace_callback($matches){
    global $replace;
    $x='';
        $par_open = strpos($matches[2],'('); //check to see if their are an even number of parenthesis.
        $par_close = strpos($matches[2],')');

        if($par_open !== false && $par_close === false || $par_open === false && $par_close !== false )
            return $matches[1].$matches[2].$matches[count($matches)-1];
    $result = $matches[1].$replace[0].$x.$matches[2].$replace[1].$matches[count($matches)-1];
    return $result;
    }


    function convert_cvs($cvs){
        $del = "\t";
        if(strpos($cvs, "\t") === false)
            $del = ',';

        $linebr = "\n";
        if(strpos($cvs, "\n") === false)
            $linebr = "\r";

        $lines = explode($linebr,$cvs);
        foreach( $lines as $line){
            unset($items);
            $items = explode($del,$line);

            if($items[0] && $items[1])
                $keywords[trim($items[0])] =array( 'url' => trim($items[1]), 'times' => trim($items[2]), 'case' => trim($items[3]), 'nofollow' => trim($items[4]), 'between' => trim($items[5]), 'before' => trim($items[6]), 'after' => trim($items[7]), 'newwindow' => trim($items[8]));
        }
        return $keywords;
    }



} /*Class ends*/

}

if( ! class_exists( 'ai_internal_link_building_blocks' ) ){

    class ai_internal_link_building_blocks{

    static function findtags($content,$firstrun=true){
    global $protectblocks;

//protects a tags
        $content = preg_replace_callback('!(\<a[^>]*\>([^>]*)\>)!ims', array('ai_internal_link_building_blocks','returnblocks'), $content);


        if($firstrun){
    

    //protects code tags.
            $content = preg_replace_callback('!(\<code\>[\S\s]*?\<\/code\>)!ims', array('ai_internal_link_building_blocks','returnblocks'), $content);

    //protects simple tags tags
            $content = preg_replace_callback('!(\[tags*\][\S\s]*?\[\/tags*\])!ims', array('ai_internal_link_building_blocks','returnblocks'), $content);

    //protects img tags
            $content = preg_replace_callback('!(\<img[^>]*\>)!ims', array('ai_internal_link_building_blocks','returnblocks'), $content);


            $content = preg_replace_callback('!(\<h1[^>]*\>([^>]*)\>)!ims', array('ai_internal_link_building_blocks','returnblocks'), $content);

            $content = preg_replace_callback('!(\<h2[^>]*\>([^>]*)\>)!ims', array('ai_internal_link_building_blocks','returnblocks'), $content);

            $content = preg_replace_callback('!(\<h3[^>]*\>([^>]*)\>)!ims', array('ai_internal_link_building_blocks','returnblocks'), $content);

            $content = preg_replace_callback('!(\<h4[^>]*\>([^>]*)\>)!ims', array('ai_internal_link_building_blocks','returnblocks'), $content);

            $content = preg_replace_callback('!(\<h5[^>]*\>([^>]*)\>)!ims', array('ai_internal_link_building_blocks','returnblocks'), $content);

            $content = preg_replace_callback('!(\<h6[^>]*\>([^>]*)\>)!ims', array('ai_internal_link_building_blocks','returnblocks'), $content);

            $content = preg_replace_callback('!(\<h7[^>]*\>([^>]*)\>)!ims', array('ai_internal_link_building_blocks','returnblocks'), $content);

            $content = preg_replace_callback('!(\<h8[^>]*\>([^>]*)\>)!ims', array('ai_internal_link_building_blocks','returnblocks'), $content);




    //protects all correctly formatted URLS
            $content = preg_replace_callback('!(([A-Za-z]{3,9})://([-;:&=\+\$,\w]+@{1})?([-A-Za-z0-9\.]+)+:?(\d+)?((/[-\+~%/\.\w]+)?\??([-\+=&;%@\.\w]+)?#?([\w]+)?)?)!', array('ai_internal_link_building_blocks','returnblocks'), $content);

    //protects urls of the form yahoo.com
            $content = preg_replace_callback('!([-A-Za-z0-9_]+\.[A-Za-z][A-Za-z][A-Za-z]?\W?)!', array('ai_internal_link_building_blocks','returnblocks'), $content);
        }

        return $content;
    }

    static function returnblocks($blocks){
        global $protectblocks;
        $protectblocks[] = $blocks[1];
        return '[block]'.(count($protectblocks)-1).'[/block]';
    }


    static function findblocks($output){
    global $protectblocks;
        if(!empty($protectblocks)){
            $output = preg_replace_callback('!(\[block\]([0-9]*?)\[\/block\])!', array('ai_internal_link_building_blocks','return_tags'), $output);
        }

    return $output;
    }

    static function return_tags($blocks){
        global $protectblocks;
        return $protectblocks[$blocks[2]];
    }
}
}
