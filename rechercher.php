<?php 
/*
 +-------------------------------------------------------------------------+
 | Monnaie M - http://merome.net/monnaiem                                                              |
 +-------------------------------------------------------------------------+
 | Auteur : Jérôme VUITTENEZ - Merome : postmaster@merome.net              |
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
  <?php include("head.php"); ?>
  <body>
<?php 

    echo("<div id=\"accueil\"><a href=\"index.php\"><img border=\"0\" src=\"images/bandeau.png\"></a><br><br>");

    if($_POST["criteres"]=="" && $_GET["criteres"]=="")
      $_POST["criteres"]="%";

    if($_GET["criteres"]=="dep")
    {
      $mondep=exec_requete("select cp from citoyen where idcitoyen like '".$_SESSION["citoyen"]["idcitoyen"]."'", $conn);
      $dep=mysqli_fetch_array($mondep);

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
    if(mysqli_num_rows($resultats)>0)
    {
      echo("<b>Résultats correspondants à la recherche :</b><br><table align=\"center\">");

      while(($annonce=mysqli_fetch_array($resultats)) && $i<50)
      {
        affiche_annonce($annonce);
        $i++;
      }
    }
    else
      echo("Votre recherche n'a retourné aucun résultat");

    echo("</div>");



  mysqli_close();



?>
  </body>
</html>
