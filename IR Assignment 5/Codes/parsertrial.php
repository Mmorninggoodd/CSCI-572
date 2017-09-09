
<?php

	ini_set('memory_limit','2048M');
        include("simple_html_dom.php");
//	generate_snippet("/home/shreyansh/Documents/solr-6.5.0/crawl_data/NBCNewsData/NBCNewsDownloadData/d1ff1de7-5868-4091-9e5b-20619d79b48e.html", "snapshot");

	function generate_snippet($value, $query){


//		$file= file_get_contents("/home/shreyansh/Documents/solr-6.5.0/crawl_data/NBCNewsData/NBCNewsDownloadData/33eb2dbc-3efa-44d5-87ff-12ae64ffcbe0.html");
//		echo "In generate".$query.$value;
		$file = file_get_contents($value);
		$html = str_get_html($file);
		$s =  strtolower($html->plaintext);
//		echo $s;
//		$s = str_replace('. ',' . ',$s);


		$strips = explode(" ",$query);
		$query = array_pop($strips);

		$s = str_replace("\'","",$s);
		$s = str_replace("!","",$s);
		$s = str_replace("?","",$s);
		$s = str_replace(",","",$s);
		$s = str_replace(",","",$s);


		$piece = explode(" ", $s);
		$pieces = array_values(array_filter($piece));
//		print_r($pieces); 
		if(false !== $start = array_search($query, $pieces)){
			$start -=10;
		}
		else{
			return "0"; 
		}

	//	if($start < 0) $start = 0; 

	//	if(false !== $end = array_search("Trump",$pieces)){
	//		$end +=2 ;
	//	}
		$end = $start+40;
		if($end>count($pieces))$end=count($pieces)-1;
		$str = "";
	//	echo $start;
	//	echo $end;
	//	echo $pieces[$start+2];
	//	echo $pieces[$end-2];


		if($start<0)$start =0;

//		echo $start . " " . $end; 



		if($start < $end){


			for($i = $start ; $i<$end; $i++)$str.=" ".$pieces[$i];
			return "... ".$str." ...";
		}
		else{

			return "0";
		}
	}


	
?>
