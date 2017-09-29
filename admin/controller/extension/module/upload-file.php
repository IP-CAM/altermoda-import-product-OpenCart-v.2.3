<?php
$uploaddir = './uploads/'; 
$file = $uploaddir . basename('import.csv'); 
 
$ext = substr($_FILES['uploadfile']['name'],strpos($_FILES['uploadfile']['name'],'.'),strlen($_FILES['uploadfile']['name'])-1); 
$filetypes = array('.csv');
 
if(!in_array($ext,$filetypes)){
	echo "<p>Äàííûé ôîğìàò ôàéëîâ íå ïîääåğæèâàåòñÿ</p>";}
else{ 
	if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $file)) { 
	  echo "success"; 
	} else {
		echo "error";
	}
}
 
?>