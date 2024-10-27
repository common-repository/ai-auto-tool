<?php 
defined('ABSPATH') or die();

class SinglePost  {
    private $inputString;
    private $title;
    private $lang;
    private $bard;

    public function __construct($inputString, $lang) {
	    $this->inputString = $inputString;
	    $this->lang = $lang;

	    
	    if (class_exists('BardGenContent')) {
	        $this->bard = new BardGenContent();
	    } else {
	        
	        include_once 'bard.php';
	        $this->bard = new BardGenContent();
	    }
}


    public function generateTitle() {
        
    	$this->title =  $this->bard->gentitle($this->inputString, $this->lang);
        return $this->title;
    }

    public function generateContent() {
        


        return $this->bard->bardcontent($this->title, $this->lang);
    }

    public function findImage() {
        
        $this->bard->searchimg($this->inputString);
       
        return $this->bard->searchimg($this->inputString);;
    }

    public function getPost() {
        $title = $this->generateTitle();
        $content = $this->generateContent();
        $image = $this->findImage();
        if (class_exists('Imagescontent')) {
	        $img = new Imagescontent();
	    } else {
	        
	        include_once 'imageinsert.php';
	        $img = new Imagescontent();
	    }
        
        $post = [
            'title' => $title,
            'content' => $img->insertImages($content,$image),
            'image' => $image,
        ];

        return $post;
    }
}

