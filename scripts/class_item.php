<?php

# Items shown to the user, compatible between reddit posts, news, tweets etc
class Item {
	public $internalscore = 0.0;
	public $timestamp = 0;
	public $title = '';
	public $link = '';
	public $thumbnail = '';
	public $source = '';
	public $category = '';
	public $highlighttext = '';
	public $description = '';
    public $ahash = '';

    /*
	initiate by: new Item(array( 'internalscore'=>$internalscore, 'timestamp'=>$timestamp, 'title'=>$title, 'link'=>$link, 'thumbnail'=>$thumbnail, 'source'=>$source, 'category'=>$category, 'highlighttext'=>$highlighttext, 'description'=>$description ))
    */
	public function __construct($thearr) {
        $this->internalscore = $thearr['internalscore'];
        $this->timestamp = $thearr['timestamp'];
        $this->title = $thearr['title'];
        $this->link = $thearr['link'];
        $this->thumbnail = $thearr['thumbnail'];
        $this->source = $thearr['source'];
        $this->category = $thearr['category'];
        $this->highlighttext = $thearr['highlighttext'];
        $this->description = $thearr['description'];
        $this->ahash = hash('md5', $thearr['title']);
    }

    public function __toString() {
        return $this->title;
    }
}

# Comparison func for sorting of these objects
function cmpitem($a, $b) { return ($a->internalscore <= $b->internalscore) ? 1 : -1; }
function cmpitemstamp($a, $b) { return ($a->timestamp <= $b->timestamp) ? 1 : -1; }