<?php
class FlashcardMapper extends DatabaseConnection{
	
	private $word;
	private $descr;
	private $private;

	function getAllFlashcardsById($id){
		$query = $this->db->prepare("select word, descr, flashcardid, creationdate, private from flashcards where userid = :userid");
        $query->bindParam(':userid', $id, PDO::PARAM_INT);
        $query->execute();
        $error = $query->errorCode();
        if($this->checkQueryError($error) !== false){
	        return '302';
	    }else{
	    	if($query->rowCount() > 0){
	            $rows = $query->fetchAll();

	            if($rows){
	            	$flashcards = array();
	            	foreach($rows as $row)
	            		$flashcards[] = new FlashcardEntity($row);
	            	return $flashcards;
	            }
	        }else{
	            return '301';
	        }
	    }
	}
}