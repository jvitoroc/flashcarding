<?php
class FlashcardEntity{
	
	private $word;
	private $descr;
	private $private;
	private $creationDate;
	private $id;

	function __construct($flashcard){
		$this->word = $flashcard['word'];
		$this->descr = $flashcard['descr'];
		$this->private = $flashcard['private'];
		$this->creationDate = $flashcard['creationdate'];
		$this->id = $flashcard['flashcardid'];
	}

	function getWord(){
		return $this->word;
	}

	function getDescr(){
		return $this->descr;
	}

	function getPrivate(){
		return $this->private;
	}

	function getCreateDate(){
		return $this->creationDate;
	}

	function getId(){
		return $this->id;
	}
}