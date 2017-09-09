<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('memory_limit', '-1');
//session_start();
include 'SpellCorrector.php';
include 'parsertrial.php';

header('Content-Type:text/html; charset=utf-8');
$limit = 10;
$query= isset($_REQUEST['q'])?$_REQUEST['q']:false;
$results = false;

if($query){
	$split = explode(" ", $query);
	$check ="";
    for($sol : $split){
    	$check.= SpellCorrector::correct($sol)+' ';
    }
	$link = "http:/"."/localhost/dashboard/CSCI572/shreyansh.php?q=$check"; 
	if($check != $query.' '){
	echo "Did You Mean : <a href='$link'>$check</a>";
	echo "<br/>"; 
	echo "Showing Results for:  ".$query;
	}

	require_once('solr-php-client/Apache/Solr/Service.php');
        $solr = new Apache_Solr_Service('localhost', 8983, '/solr/newCore/');
        if(get_magic_quotes_gpc() == 1){
                $query = stripslashes($query);
        }
        try{
		if(!isset($_GET['algorithm']))$_GET['algorithm']="lucene";
		if($_GET['algorithm'] == "lucene"){

			 $results = $solr->search($query, 0, $limit);

		}else{

			$param = array('sort'=>'pageRankFile desc');
			$results = $solr->search($query, 0, $limit, $param);

		}

	 }
        catch(Exception $e){
                die("<html><head><title>SEARCH EXCEPTION</title></head><body><pre>{$e->__toString()}</pre></body></html>");
        }
}
?>


<html>
<head>
        <title> PHP Solr Client Example </title>
<style>
	body{
		background: lightblue; 
	}
	.box{
        display : iniline-block;
        background : white; 
        width: 150px; 
        height: 250px; 
        margin: 20px; 
        color: black; 
	}

	#suggestionBox{
        position: relative; 
        top: 20px; 
        left: 20px; 
	}

.textbox {
    width: 200px;
    margin-left: 0px;
}

ul {

    list-style-type: none;
    margin: 0;
    padding: 0;
}

.listitem{
    display: block;
    background-color: #FEFEFE;
}
.listitem:hover {
    background-color: #dddddd;
}
a{
	text-decoration : none; 
}
</style>



<script>




function showSuggestion(str){

	if(str.length == 0){
		document.getElementById("txtHint").innerHTML = ""; 
		return ; 
	}
	else{
		var xmlhttp = new XMLHttpRequest();
		xmlhttp.onreadystatechange = function() {
			if(this.readyState == 4 && this.status == 200){

				var availableTags = JSON.parse(this.responseText);
				var html = ''; 
				for(var i=1; i<availableTags.length; i++){
					html += '<div>' + availableTags[i] + '</div>';
				}
				showList(availableTags);
			}

		};
		xmlhttp.open("GET", "getSuggestion.php?q=" + str, true);
		xmlhttp.send();
	}

}



function click(obj) {
        var tree = document.getElementById('q');
        var textValue = tree.value.split(" ");
        textValue[textValue.length - 1] = obj.innerHTML;
        tree.value = textValue.join(" ");
        var myList = document.getElementById('list');
        myList.innerHTML = '';
        tree.focus();
}


function showList(availableTags) {
            var ul = document.getElementById("list");
            ul.innerHTML = '';
            for(var i = 0; i < availableTags.length; i++) {
				var li = document.createElement("li");
                li.appendChild(document.createTextNode(names[i]));
                li.setAttribute("onclick", "click(this)");
                li.setAttribute("class", "listitem");
                ul.appendChild(li);
            }
}








</script>
</head>
<body>
<h1> WitZZ Search </h1><br/>
<form accept-charset="utf-8" method="get">
        <input type="radio" name="algorithm" value="lucene" /> Solr's Default - Lucene
        <input type="radio" name="algorithm" value="pagerank" /> Google's - PageRank <br/><br/> 


	<div style="position:relative;width:200px;height:25px;border:0;padding:0;margin:0;">

	<input id="q" class="textbox" name="q" onkeyup="showSuggestion(this.value)" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8');?>"/>
	<ul id="list" style="border: 1px solid black;box-sizing:border-box;margin:0;"></ul>
        
	</div>



 
	<input type="submit" />
</form>
<p><span id="txtHint"></span></p>
<?php
if($results){
        $total = (int)$results->response->numFound; 
        $start = min(1,$total);
        $end = min($limit, $total); 
?>
<div> Results <?php echo $start; ?> - <<?php echo $end;?> of <?php echo $total;?>:</div> 
<ol style="list-style:none;">
<?php
	foreach($results->response->docs as $doc){

		foreach($doc as $f=>$v){

			if($f == "og_url")$link = $v; 
		}

?>

<li>
<a href = <?php echo $link ; ?>>
	<table style ="border: 1px solid black; text-align: left; border-radius:10px; ">

	<?php

$v = 0;
		foreach($doc as $field => $value){
			if($field!="id" && $field!="title" && $field!="description" && $field!="og_url")continue;

if($field == "id"){ 

	$v = htmlspecialchars(generate_snippet($value, $query), ENT_NOQUOTES, 'utf-8');
}
else if($field == "description"){

	if($v == "0"){
		$v = htmlspecialchars($value, ENT_NOQUOTES, 'utf-8');
	}
}


if(sizeof($value)==1){
			?>
			<tr><th><?php echo htmlspecialchars($field, ENT_NOQUOTES, 'utf-8') ; ?></th>
			<td><?php echo htmlspecialchars($value,  ENT_NOQUOTES, 'utf-8') ; ?></td></tr>

			<?php } else {?>
			<tr><th><?php echo htmlspecialchars($field, ENT_NOQUOTES, 'utf-8') ; ?></th>
			<td><ol>

				<?php 
					foreach($value as $item){

				?>

					<li><?php echo htmlspecialchars($item, ENT_NOQUOTES, 'utf-8' ); ?></li>

				<?php } ?>
			</ol></td></tr>
			<?php } ?>


	<?php }

	?>
	<tr>
	<th> Snippet: </th>
	<td><?php echo $v;  ?></td>
	</tr>
	</table></a></li>
	<?php } ?>
	</ol>
	<?php  }   ?>
</body>
</html>

