<?php 
/*
 +-------------------------------------------------------------------------+
 | Monnaie M - http://merome.net/monnaiem                                                              |
 +-------------------------------------------------------------------------+
 | Auteur : J�r�me VUITTENEZ - Merome : postmaster@merome.net              |
 +-------------------------------------------------------------------------+
*/

  session_start();
  include './requete.php';

  if($_SESSION["citoyen"]["idcitoyen"]=="")
  {
    die("Session perdue. <a href=\"index.php\">Merci de cliquer ici</a>");
  }
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

    echo("<a onclick=\"javascript:if(document.getElementById('global').style.display=='block') document.getElementById('global').style.display='none'; else document.getElementById('global').style.display='block'\">Cliquez ici pour voir ou faire disparaitre l'historique global de Monnaie M</a><br><br>");

    $historiques=exec_requete("select * from historique order by datemesure");
    echo("<div id=\"global\" style=\"display:none;\"><table border=\"1\" align=\"center\"><tr align=\"center\"><td>Date</td><td>Nombre d'utilisateurs</td><td>Masse mon�taire totale (apr�s revenu)</td><td>Masse mon�taire moyenne</td><td>Revenu de base</td></tr>");
    while($historique=mysql_fetch_array($historiques))
    {
      echo("<tr align=\"center\"><td>".to_str($historique["datemesure"])."</td><td>".$historique["nbutilisateurs"]."</td><td>".$historique["massetotale"]."&nbsp;<img align=\"middle\" src=\"images/m.png\"></td><td>".$historique["massemoyenne"]."&nbsp;<img align=\"middle\" src=\"images/m.png\"></td><td>".$historique["rdb"]."&nbsp;<img align=\"middle\" src=\"images/m.png\"></td></tr>");
    }
    echo("</table><br><br></div>");

    $listes=exec_requete("select idcitoyen from citoyen where valide=1 and citoyen.idcitoyen='".$_SESSION["citoyen"]["idcitoyen"]."'");
    while($liste=mysql_fetch_array($listes))
    {


    $nomh=$liste["idcitoyen"];
    echo("Historique de <b>".$nomh."</b>");

    $ccitoyen=mysql_fetch_array(exec_requete("select dateadhesion,solde from citoyen where idcitoyen='".$nomh."'"));
    $solde=50;

    echo("<table border=\"1\" align=\"center\"><tr><td>Date</td><td>Acheteur</td><td>Vendeur</td><td>Ev�nement</td><td>Note</td><td>Commentaires de l'acheteur</td><td>Solde</td></tr>
          <tr><td>".to_str($ccitoyen["dateadhesion"])."</td><td>&nbsp;</td><td>&nbsp;</td><td>Inscription � Monnaie M</td><td>&nbsp;</td><td>&nbsp;</td><td>50&nbsp;<img align=\"middle\" src=\"images/m.png\"></td>");

      $transactions=exec_requete("select *,transaction.prix as prixt from citoyen,transaction,produit where ((vendeur=citoyen.idcitoyen) or (acheteur=citoyen.idcitoyen)) and produit.idproduit=transaction.idproduit and citoyen.idcitoyen='".$nomh."' order by datevente");
      if(mysql_num_rows($transactions)>0)
      {
        while ($transaction=mysql_fetch_array($transactions))
        {
          if($transaction["vendeur"]==$nomh)
          {
            switch($transaction["statut"])
            {
              case "Termin�":
                $solde+=$transaction["prixt"];
                echo("<tr bgcolor=\"#CCFF99\"><td>".to_str($transaction["datevente"])."</td><td>".$transaction["acheteur"]."</td><td>".$transaction["vendeur"]."</td><td>Vente / ".$transaction["categorie"]."</td><td>".$transaction["note"]."/5</td><td>".$transaction["commentaires"]."</td><td>".$solde."&nbsp;<img align=\"middle\" src=\"images/m.png\">&nbsp;(+".$transaction["prixt"]."&nbsp;<img align=\"middle\" src=\"images/m.png\">)</td></tr>");
                break;
              case "Command�":
              case "confirm�":
                echo("<tr bgcolor=\"#FFFF99\"><td>".to_str($transaction["datevente"])."</td><td>".$transaction["acheteur"]."</td><td>".$transaction["vendeur"]."</td><td>Vente / ".$transaction["categorie"]."</td><td>".$transaction["note"]."/5</td><td>En attente de finalisation</td><td>(+".$transaction["prixt"]."&nbsp;<img align=\"middle\" src=\"images/m.png\">&nbsp;en&nbsp;attente)</td></tr>");
                break;
              case "Annul�":
                echo("<tr bgcolor=\"#FF9999\"><td>".to_str($transaction["datevente"])."</td><td>".$transaction["acheteur"]."</td><td>".$transaction["vendeur"]."</td><td>Vente / ".$transaction["categorie"]."</td><td>-</td><td>Annul� : ".$transaction["commentaires"]."</td><td>-</td></tr>");
                break;
            }
          }
          else
          {
            switch($transaction["statut"])
            {
              case "Termin�":
                $solde-=$transaction["prixt"];
                echo("<tr bgcolor=\"#FFCCFF\"><td>".to_str($transaction["datevente"])."</td><td>".$transaction["acheteur"]."</td><td>".$transaction["vendeur"]."</td><td>Achat / ".$transaction["categorie"]."</td><td>".$transaction["note"]."/5</td><td>".$transaction["commentaires"]."</td><td>".$solde."&nbsp;<img align=\"middle\" src=\"images/m.png\">&nbsp;(-".$transaction["prixt"]."&nbsp;<img align=\"middle\" src=\"images/m.png\">)</td></tr>");
                break;
              case "Command�":
              case "confirm�":
                $solde-=$transaction["prixt"];
                echo("<tr bgcolor=\"#FFCCFF\"><td>".to_str($transaction["datevente"])."</td><td>".$transaction["acheteur"]."</td><td>".$transaction["vendeur"]."</td><td>Achat / ".$transaction["categorie"]."</td><td>En attente</td><td>En attente de finalisation</td><td>".$solde."&nbsp;<img align=\"middle\" src=\"images/m.png\">&nbsp;(-".$transaction["prixt"]."&nbsp;<img align=\"middle\" src=\"images/m.png\">)</td></tr>");
                break;
              case "Annul�":
                echo("<tr bgcolor=\"#FF9999\"><td>".to_str($transaction["datevente"])."</td><td>".$transaction["acheteur"]."</td><td>".$transaction["vendeur"]."</td><td>Achat / ".$transaction["categorie"]."</td><td>Annul�</td><td>".$transaction["commentaires"]."</td><td>-</td></tr>");
                break;
            }
          }
        }
      }
      echo("<tr bgcolor=\"#CCFF99\"><td>Chaque mois depuis le ".to_str($ccitoyen["dateadhesion"])."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>Revenu de base</td><td>".$ccitoyen["solde"]."&nbsp;<img align=\"middle\" src=\"images/m.png\">&nbsp;(+".($ccitoyen["solde"]-$solde)."&nbsp;<img align=\"middle\" src=\"images/m.png\">)</td></tr>");
      echo("</table><br><br>");
    }
    echo("</div>");

    mysql_close();
?>
  </body>
</html>