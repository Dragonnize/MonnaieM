<?php 
/*
 +-------------------------------------------------------------------------+
 | Monnaie M - http://merome.net/monnaiem                                                              |
 +-------------------------------------------------------------------------+
 | Auteur : J�r�me VUITTENEZ - Merome : postmaster@merome.net              |
 +-------------------------------------------------------------------------+
*/
  session_start();

  if($_SESSION["citoyen"]["idcitoyen"]=="")
  {
    die("Session perdue. <a href=\"index.php\">Merci de cliquer ici</a>");
  }
  include './requete.php';
  include './fonctions_annonces.php';
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

    echo("<div id=\"accueil\"><a href=\"index.php\"><img border=\"0\" src=\"images/bandeau.png\"></a><br><br>");

    if($_POST["criteres"]=="" && $_GET["criteres"]=="")
      $_POST["criteres"]="%";

    if($_GET["criteres"]=="dep")
    {
      $mondep=exec_requete("select cp from citoyen where idcitoyen like '".$_SESSION["citoyen"]["idcitoyen"]."'");
      $dep=mysql_fetch_array($mondep);

      $resultats=exec_requete("select *,citoyen.idcitoyen as cit from produit,citoyen where citoyen.idcitoyen=produit.idcitoyen and nbex>0 and produit.valide=1 and (dateexpiration=0 or dateexpiration>=now()) and
            substring(cp,1,2)='".substr($dep["cp"],0,2)."' order by
            datesaisie desc");
    }
    else
    {
      $resultats=exec_requete("select *,citoyen.idcitoyen as cit from produit,citoyen where typeannonce='offre' and citoyen.idcitoyen=produit.idcitoyen and nbex>0 and produit.valide=1 and (dateexpiration=0 or dateexpiration>=now()) and
          ( (match(objet,description) against ('".$_POST["criteres"]."'))
          or citoyen.idcitoyen like '%".$_POST["criteres"]."%'
          or typeproduit like '%".$_POST["criteres"]."%'
          or categorie like '%".$_POST["criteres"]."%'
          or objet like '%".$_POST["criteres"]."%'
          or description like '%".$_POST["criteres"]."%') order by match(objet,description) against ('".$_POST["criteres"]."') desc,datesaisie desc");
    }
    $i=0;
    if(mysql_num_rows($resultats)>0)
    {
      echo("<b>R�sultats correspondants � la recherche :</b><br><table align=\"center\">");

      while(($annonce=mysql_fetch_array($resultats)) && $i<50)
      {
        affiche_annonce($annonce);
        $i++;
      }
    }
    else
      echo("Votre recherche n'a retourn� aucun r�sultat");

    echo("</div>");



  mysql_close();



?>
  </body>
</html>