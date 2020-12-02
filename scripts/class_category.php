<?php

# Program-wide category
class Category {
	public $shortname; # displayed over item image
	public $longname; # displayed in menu
	public $certainkeywords; # keywords guaranteeing 100% of inclusion
	public function __construct($thearr) {
		$this->shortname = $thearr['shortname'];
		$this->longname = $thearr['longname'];
		$this->certainkeywords = $thearr['certainkeywords'];
	}
}

$categories = [
	new Category(['shortname'=>'Audio', 'longname'=>'Audio', 'certainkeywords'=>['audio', 'speech', 'voice', 'sound', 'vocal', 'music']]),
	new Category(['shortname'=>'RL', 'longname'=>'Reinforcement Learning', 'certainkeywords'=>['reinforcement', 'agent', ' rl ', ' ppo']]),
	new Category(['shortname'=>'CV', 'longname'=>'Computer Vision', 'certainkeywords'=>['cv', 'vision', 'yolo', 'image', 'picture', 'deep fake', 'deepfake', 'object', 'pose', 'ocr ', 'facial', 'photo', 'sign language', 'sprites', 'drawing', ' gan', 'smart glass', 'recognit']]),
	new Category(['shortname'=>'NLP', 'longname'=>'Natural Language Processing', 'certainkeywords'=>['nlp', 'language', 'gpt', 'bert', 'transformer', 'translat', 'attention', 'social media', 'topics', 'summar']]),
	new Category(['shortname'=>'Self-Driving', 'longname'=>'Self-Driving', 'certainkeywords'=>['driving', 'self-driving', 'selfdriving', 'autopilot']])
];

# Simple util func, like 'in' in python
function contains($str, $arr) {
    foreach($arr as $a) {
        if (stripos($str,$a) !== false) return 1;
    }
    return 0;
}

# Basic keyword-based category classification
function classify($title) {
	global $categories;

	# checking each category, there may be more than one
	foreach( $categories as $cat ) {
		if ( contains($title, $cat->certainkeywords) ) {
			return $cat->shortname;
		}
	}

	# no category found
	return ' ';
}
