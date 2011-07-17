function goTo(page) {
	var f = document.playerListForm;
	f.page.value = page;
	
	f.submit();
}

function goToCheck(page) {
	var f = document.playerListForm;
	
	if(f.faction.options[f.faction.selectedIndex].value == f.faction_orig.value && 
	   f.sort_by.options[f.sort_by.selectedIndex].value == f.sort_by_orig.value && 
	   f.order.options[f.order.selectedIndex].value == f.order_orig.value) {
	   
	   f.page.value = page;
	   
	} else {
		f.page.value = 1;
	}
	
	f.submit();
}