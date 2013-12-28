<?php 
/*
 +-------------------------------------------------------------------------+
 | Monnaie M - http://merome.net/monnaiem                                                              |
 +-------------------------------------------------------------------------+
 | Auteur : J�r�me VUITTENEZ - Merome : postmaster@merome.net              |
 +-------------------------------------------------------------------------+
*/

  include '/var/www/monnaiem/requete.php';
 ?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <title>Monnaie M - Exp�rimentation d'une monnaie compl�mentaire assortie d'un revenu de base</title>
  <link rel="stylesheet" href="monnaiem.css" typeproduit="text/css">
  <meta name="description" content="Monnaie M est une exp�rimentation visant � faire conna�tre et promouvoir le fonctionnement et le r�le d'une monnaie, 
  les Syst�mes d'Echanges Locaux, le concept de revenu de base, les monnaies compl�mentaires.">
  <meta name="keywords" lang="fr" content="monnaie bitcoin openudc cr�ation mon�taire SEL revenu de base dividende universel">
  </head>
  <body>
<?php 

  if(date("d")==1)
  {
    $massem1=mysql_fetch_array(exec_requete("select sum(solde) as massem1 from citoyen where valide=1"));
    $massem2=mysql_fetch_array(exec_requete("select sum(prix) as massem2 from transaction where statut<>'Termin�' and statut<>'Propos�' and statut<>'Annul�'"));
    $populations=mysql_fetch_array(exec_requete("select count(*) as population from citoyen where valide=1"));

    $revenu=ceil((0.8*($massem1["massem1"]+$massem2["massem2"])/100/$populations["population"]));
    exec_requete("update citoyen set solde=solde+".$revenu." where valide=1");

    $citoyens=exec_requete("select idcitoyen,mail,solde from citoyen where valide=1");
    while($citoyen=mysql_fetch_array($citoyens))
    {
      mail($citoyen["mail"], "Vous avez re�u votre revenu de base sur Monnaie M",
                  "Conform�ment au r�glement de Monnaie M ( http://merome.net/monnaiem/ReglementMonnaieM.pdf ), votre compte vient d'�tre cr�dit� de ".$revenu." M au titre du revenu de base mensuel, soit 0.8% de la masse mon�taire totale (".($massem1["massem1"]+$massem2["massem2"]).") divis�e par le nombre d'utilisateurs inscrits (".$populations["population"]."), arrondi � l'entier sup�rieur.\r\n\r\n Votre solde s'�l�ve aujourd'hui � ".$citoyen["solde"]." M.\r\n\r\nMonnaie M est une initiative citoyenne qui fonctionne � la mesure de l'investissement de ses utilisateurs :\r\n- En utilisant vos M pour acheter des biens et des services aux autres utilisateurs\r\n- En d�posant vous-m�me des annonces (on a tous quelque chose � offrir)\r\n- En faisant connaitre le site � d'autres\r\n\r\nJe compte sur vous...\r\n\r\nhttp://merome.net/monnaiem\r\n\r\nMerome",
                  "From: ".FROM."\r\n"
        					."Reply-To: ".FROM."\r\n"
        					."X-Mailer: PHP/" . phpversion());
    }

    $nouvellemassetotale=($massem1["massem1"]+$massem2["massem2"]+$revenu*$populations["population"]);
    $nouvellemassemoyenne=round($nouvellemassetotale/$populations["population"],2);
    exec_requete("insert into historique (datemesure,nbutilisateurs,massetotale,massemoyenne,rdb) values (now(),".$populations["population"].",".$nouvellemassetotale.",".$nouvellemassemoyenne.",".$revenu.")");

  }



  mysql_close();



?>
  </body>
</html>
