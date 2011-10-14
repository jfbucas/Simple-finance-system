<script language="JavaScript">
shownDIV = 0 ;
shownTR = 0 ;

function hidediv(id) {
	//safe function to hide an element with a specified id
	if (document.getElementById) { // DOM3 = IE5, NS6
		document.getElementById(id).style.display = 'none';
	}
	else {
		if (document.layers) { // Netscape 4
			document.id.display = 'none';
		}
		else { // IE 4
			document.all.id.style.display = 'none';
		}
	}
}

function showdiv(id) {
	//safe function to show an element with a specified id
        // if there is a div being shown already we hide it
	if (shownDIV){
	  hidediv(shownDIV);
	}
	if (document.getElementById) { // DOM3 = IE5, NS6
		document.getElementById(id).style.display = 'block';
	}
	else {
		if (document.layers) { // Netscape 4
			document.id.display = 'block';
		}
		else { // IE 4
			document.all.id.style.display = 'block';
		}
	}

	shownDIV = id;
}

function showtr(id) {
	//safe function to show an element with a specified id
        // if there is a div being shown already we hide it
	if (shownTR){
	  hidediv(shownTR);
	}
	if (shownTR != id) {
		if (document.getElementById) { // DOM3 = IE5, NS6
			document.getElementById(id).style.display = 'table-row';
		}
		else {
			if (document.layers) { // Netscape 4
				document.id.display = 'table-row';
				}
			else { // IE 4
				document.all.id.style.display = 'table-row';
			}
		}
		shownTR = id;
	} else {
		shownTR = 0;
	}

}
</script>
