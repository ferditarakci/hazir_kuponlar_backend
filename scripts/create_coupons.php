<?php 
date_default_timezone_set('Europe/Istanbul');
header('Content-Type: text/html; charset=utf-8');


$db_user = "tahminkrali2";
$db_pass = "IEL02EnAyUt";
$db_name = "tahminkr_maclar";
$db_host = "localhost";

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
// Check connection
if ($conn->connect_error) 
{
    die("Connection failed: " . $conn->connect_error);
} 


$coupon[0]["ratetypes"] = array(3,1,0,0,0);
$coupon[1]["ratetypes"] = array(3,1,0,0,0);
$coupon[2]["ratetypes"] = array(1,3,0,0,0);
$coupon[3]["ratetypes"] = array(1,3,0,0,0);
$coupon[4]["ratetypes"] = array(0,0,3,1,0);
$coupon[5]["ratetypes"] = array(0,0,3,1,0);
$coupon[6]["ratetypes"] = array(0,0,0,2,1);
$coupon[7]["ratetypes"] = array(0,0,0,2,1);
$coupon[8]["ratetypes"] = array(0,0,2,2,2);
$coupon[9]["ratetypes"] = array(0,0,2,3,1);



$coupon[0]["betgroups"] = "ub.ratetype_id>0";
$coupon[1]["betgroups"] = "ub.ratetype_id>0";
$coupon[2]["betgroups"] = "ub.ratetype_id IN (7,8,11)";
$coupon[3]["betgroups"] = "ub.ratetype_id IN (7,8,11)";
$coupon[4]["betgroups"] = "ub.ratetype_id IN (1,10)";
$coupon[5]["betgroups"] = "ub.ratetype_id IN (1,10)";
$coupon[6]["betgroups"] = "ub.ratetype_id=10";
$coupon[7]["betgroups"] = "ub.ratetype_id=10";
$coupon[8]["betgroups"] = "ub.ratetype_id IN (1,10)";
$coupon[9]["betgroups"] = "ub.ratetype_id IN (1,10)";



$startDate 	= mktime(12,0,0,date("m"),date("d"),date("Y"));
$endDate 	= $startDate+(36*3600);

$query = "select ub.id,ub.ratetype_id,ub.betgroup_id 
	from tk_userbets ub 
	join tk_games g on ub.game_id=g.puid
	where g.datetime>=".$startDate." 
	AND g.datetime<".$endDate." 
	ORDER BY ub.probability desc";
$result 	= $conn->query($query);
$rows		= $result->num_rows;


while ($row = $result->fetch_array(MYSQLI_ASSOC))
{
	$ratetype_id 	= $row["ratetype_id"];
	$betgroup_id 	= $row["betgroup_id"];
	$userbet_id 	= $row["id"];
	
	$bets[$ratetype_id][] = array($userbet_id,$betgroup_id);
}
print sizeof($bets[0])."<br>";
print sizeof($bets[1])."<br>";
print sizeof($bets[2])."<br>";
print sizeof($bets[3])."<br>";
print sizeof($bets[4])."<br>";


for ($i=0; $i<sizeof($coupon);$i++)
{
	if ($i % 2 == 0)
		unset($used);
	/* her 2 kuponda bir kupon tipi de??i??ti??i i??in kullan??lan bet array s??f??rlan??yor */
	
	unset($c_bets);
	for ($ratetype=0; $ratetype<5; $ratetype++)
	{
		print $coupon[0]["ratetypes"][$ratetype]."<br>";
		print_r($bets[$ratetype]);
		print "<br>";
		$k=0;
		do
		{
			print $bets[$ratetype][$k][0]."<br>";
			
			if (!in_array($bets[$ratetype][$k][0],$used))
			{
				$c_bets[] = $bets[$ratetype][$k][0];
				$used[] = $bets[$ratetype][$k][0];
			}
			
			$k++;

			
		} while (sizeof($c_bets) == $coupon[0]["ratetypes"][$ratetype]);
	
		
	}
	
	print_r($c_bets);
	print "<br>";
	exit;
	
	$c_betgs = $coupon[$i]["betgroups"][0];
	
	/*
	print_r($c_betgs);

	*/
	/*

	$sql = "INSERT INTO tk_coupons (coupon_date,coupon_time,shown_until,coupon_type,coupon_rate,result) 
	VALUES ('".date("Y-m-d")."','".time()."','','1','0','0')";

	if ($conn->query($sql) === TRUE) 
		echo "New record created successfully<br>";
	else 
		echo "Error: " . $sql . "<br>" . $conn->error;
	*/
}



/*
mysqli_query("INSERT INTO tk_coupons 
	(coupon_date,coupon_time,shown_until,coupon_type,coupon_rate,result) 
	VALUES ('".date("Y-m-d")."','','','1','0','0')",$link) or die(mysql_error());



$id = mysqli_insert_id($link) or die(mysql_error());




$query = "select ub.id,g.title,bg.description,bt.description,rt.name,MAX(ub.rate) from tk_userbets ub join tk_games g on ub.game_id=g.puid join tk_bettypes bt on bt.id=ub.bettype_id join tk_betgroups bg on bg.id=ub.betgroup_id join tk_ratetypes rt on ub.ratetype_id=rt.id where g.datetime>=1539973680 AND g.datetime<1540069200 AND ub.ratetype_id=1 GROUP BY g.puid,bg.description,bt.description,rt.name ORDER BY MAX(ub.rate) desc limit 3";
*/


/*
1. Banko Kuponlar Sayfas??:

 

- 2 kupon da 3 Banko + 1 Favori orandan olacak. Kupon ba???? toplam 4 ma??.

- B??t??n kategoriler(MS, TG vs..) kullan??labilir.

- Yeni kupon yarat??labilmesi i??in min. 2 ma?? de??i??meli.

 

 

2. Golc?? Kuponlar Sayfas??:

 

- 2 kupon da 1 Banko + 3 Favori orandan olacak. Kupon ba???? toplam 4 ma??.

- Tahmin tipi olarak sadece Toplam Gol, Kar????l??kl?? Gol, ilk yar?? Alt/??st kullan??lacak.

- Yeni kupon yarat??labilmesi i??in min. 2 ma?? de??i??meli.

 

 

3. S??rpriz Kuponlar Sayfas??:

 

- 2 kupon da 3 Plase + 1 S??rpriz orandan olu??acak. Kupon ba???? toplam 4 ma??.

- Tahmin tipi olarak sadece Ma?? sonucu ve ??Y/MS kullan??lacak.

- Yeni kupon yarat??labilmesi i??in min. 2 ma?? de??i??meli.

 

 

4. Sistem 2-3 Kuponlar?? Sayfas??:

 

- 1. kupon 1 S??rpriz + 2 Abart?? orandan olacak. Kupon ba???? toplam 3 ma??.

- 2. kupon 2 S??rpriz + 1 Abart?? orandan olacak. Kupon ba???? toplam 3 ma??.

- Tahmin tipi olarak sadece ??Y/MS kullan??lacak.

- Yeni kupon yarat??labilmesi i??in min. 2 ma?? de??i??meli.

 

 

5. Sistem 4-5-6 Kuponlar?? Sayfas??:

 

- 1. kupon 2 Plase + 2 S??rpriz + 2 Abart?? orandan olacak. Kupon ba???? toplam 6 ma??.

- 2. kupon 2 Plase + 3 S??rpriz + 1 Abart?? orandan olacak. Kupon ba???? toplam 6 ma??.

- Tahmin tipi olarak sadece Mac sonucu ve ??Y/MS kullan??lacak.

- Yeni kupon yarat??labilmesi i??in min. 3 ma?? de??i??meli.
*/

$conn->close();


?>