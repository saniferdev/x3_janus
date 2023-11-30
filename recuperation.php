<?php

include('config.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function envoiMail(){
    $mail = new PHPMailer(true);
    $mail->setLanguage('fr', '/PHPMailer/language/');
    $mail->IsSMTP(); 
    $mail->SMTPOptions = array(
      'ssl' => array(
      'verify_peer' => false,
      'verify_peer_name' => false,
      'allow_self_signed' => true
      )
    );
    $mail->SMTPDebug  = 4;  
    $mail->SMTPAuth   = true;  
    $mail->SMTPSecure = 'tls'; 
    $mail->Host       = gethostbyname('smtp.gmail.com');
    $mail->Port       = 587; 
    $mail->Username   = 'sanifer.informatique@gmail.com';
    $mail->Password   = '7dJbW5h8';
    $mail->SetFrom('admin@groupesanifer.com', 'Sanifer');
    $mail->Subject    = "Erreur rencontré sur l'API CLICTILL vers JANUS";
    $mail->CharSet    = 'UTF-8';
    $mail->Body       = '
    					Bonjour,<br>
    					Une erreur a été rencontrée au niveau de la passerelle CLICTILL vers JANUS.<br><br>

    ';
    $mail->AddAddress('winny.info@talys.mg', 'Winny');
    $mail->addReplyTo('winny.info@talys.mg', 'Winny');
    $mail->isHTML(true);
    if(!$mail->Send()) {
      $return         = "Erreur d'envoi de mail: ".$mail->ErrorInfo;
    } else {
      $return         = 'Mail envoyé avec succès!';
    }
    return $return;
}

require_once("classes/api_recup.php");

$api   		= new API();

$api->link 	= $link;
$api->url 	= $url;
$api->key 	= $key;

/*echo $api->getDate();
die();*/

$rest  		= $api->getRest($url,$key);

$data  		= json_decode($rest);

$donne 		= $data->response->data;


$site  		= array("SAN01"=>1,"SAN02"=>2,"SAN03"=>3,"SAN04"=>7);
$depot 		= array("SAN01"=>31,"SAN02"=>29,"SAN03"=>27,"SAN04"=>34);


foreach ($donne as $value) {

	$date_facture = $value->created_date;
	$date_facture = date('Y-d-m H:i:s',strtotime($date_facture));
	
	if($api->getDO_Piece($value->receipt_number) == 1 ) continue;

	if($value->flag_sale == "1"){
		$DO_Provenance = "0";
	}
	else{
		$DO_Provenance = "1";
	}

    $entete		= $api->insertDocument_Entete($value->receipt_number,$date_facture,$value->orderby_code,$site[$value->shop_code],$value->orderby_last_name,$value->orderby_first_name,$value->orderby_adr1,$value->orderby_phone,$DO_Provenance);

    //if($entete != $value->receipt_number) continue;

    echo "Entete ".$entete." inseré <br>";

    $ligne      = 1000;
    $art        = $value->articles;

    foreach ($art as $val) {

      $facture_ligne	= $api->insertDocument_Ligne($value->receipt_number,$date_facture,$value->orderby_code,$ligne,$val->reference,$val->quantity,$depot[$value->shop_code]);

      /*if($facture_ligne != $value->receipt_number){
      	 $api->deleteFacture($value->receipt_number);
      	 //envoiMail();
      	 continue;
      }
      else*/ //$api->updateDate();

      echo "Ligne ".$val->reference." inserée <br>";

      $ligne += 1000;
    }

    echo "<br>";

}

?>