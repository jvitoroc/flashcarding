$('.flashcard-item').click((e)=>{
	var clicked = $(e.target);
	if(!clicked.hasClass("flashcard-item")){
		clicked.closest(".flashcard-item").toggleClass("back");
	}else{
		clicked.toggleClass("back");
	}
});