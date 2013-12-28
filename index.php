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

  if($_GET["logoff"]==1)
  {
    session_destroy();
    session_unset();
    die("<div id=\"accueil\"><img src=\"images/logo.png\"><br><br>Merci d'avoir utilis� Monnaie M. A bient�t.<br><a href=\"index.php\">Se reconnecter � Monnaie M.</a></div>");
  }

  if($_POST["id"]!="")
  {


    $verif=exec_requete("select * from citoyen where valide=1 and idcitoyen='".$_POST["id"]."' and md5('".$_POST["pass"]."')=mdp");
    if(mysql_num_rows($verif)==1)
    {
      $_SESSION["citoyen"]=mysql_fetch_array($verif);
      exec_requete("update citoyen set derniereconnexion=now() where idcitoyen='".$_POST["id"]."'");

      $transactions=exec_requete("select * from transaction where statut='confirm�' and to_days( now( ) ) - to_days( datevente ) >30");
      while($transaction=mysql_fetch_array($transactions))
      {
        exec_requete("update transaction set statut='Termin�',note=5,commentaires='(Annonce valid�e automatiquement apr�s 30 jours)' where idtransaction=".$transaction["idtransaction"]);
        $moyennes=exec_requete("select avg(note) as moy from transaction where vendeur='".$transaction["vendeur"]."' and statut='Termin�'");
        $moyenne=mysql_fetch_array($moyennes);
        exec_requete("update citoyen set solde=solde+".$transaction["prix"].",nbventes=nbventes+1,notevendeur=".$moyenne["moy"]." where idcitoyen='".$transaction["vendeur"]."'");
        $moyennes=exec_requete("select avg(note) as moy from transaction where acheteur='".$transaction["acheteur"]."'");
        $moyenne=mysql_fetch_array($moyennes);
        exec_requete("update citoyen set noteacheteur=".$moyenne["moy"]." where idcitoyen='".$transaction["acheteur"]."'");
      }

      $transactions=exec_requete("select * from transaction where statut='Command�' and to_days( now( ) ) - to_days( datevente ) >30");
      while($transaction=mysql_fetch_array($transactions))
      {
        exec_requete("update transaction set statut='Annul�',note=5,commentaires='(Annonce annul�e automatiquement apr�s 30 jours)' where idtransaction=".$transaction["idtransaction"]);
        exec_requete("update citoyen set solde=solde+".$transaction["prix"]." where idcitoyen='".$transaction["acheteur"]."'");
      }
    }
    else
      echo("<center><b>Nom d'utilisateur ou mot de passe incorrect</b></center>");

  }


  if($_SESSION["citoyen"]["idcitoyen"]=="")
  {
    echo("<div id=\"accueil\"><table align=\"center\"><tr><td><img src=\"images/logo.png\"></td><td>");
    echo("<center><b>Monnaie M</b> est une exp�rimentation d'initiative citoyenne visant � faire conna�tre et promouvoir :<br>
    - le fonctionnement et le r�le d'une monnaie,<br>
    - les Syst�mes d'Echanges Locaux (SEL),<br>
    - le concept de revenu de base,<br>
    - les monnaies compl�mentaires.<br><br>
    Le code de Monnaie M est libre ! Pour l'examiner ou contribuer � son d�veloppement, <a href=\"https://github.com/Mer0me/MonnaieM\">cliquez ici</a></center></td></tr></table><hr>
<b>Comment �a marche ?</b><br>C'est comme \"Le bon coin\" ou \"Priceminister\", mais avec une monnaie virtuelle et un revenu de base :<br><br>
<a href=\"http://merome.net/monnaiem/compte.php\"><img border=\"0\" src=\"images/1.png\" title=\"Je communique mon adresse postale � l'inscription\"></a>
<img src=\"images/2.png\" title=\"Un cr�dit de 50 unit�s m'est attribu� pour commencer les �changes\">
<img src=\"images/3.png\" title=\"J'�change des biens et des services avec les autres utilisateurs en utilisant la monnaie M\">
<img src=\"images/4.png\" title=\"Un revenu de base est attribu� � chaque utilisateur, sans condition.\">
                          <hr>
<b>Se connecter</b><br>
    <small>
      <a href=\"compte.php\">Je n'ai pas encore de compte sur Monnaie M - Cr�er un compte</a><br>
      <a href=\"compte.php?activer=1\">J'ai re�u mon code d'activation par courrier - Activer mon compte</a><br>
      <a href=\"http://merome.net/monnaiem/compte.php?oubli=1\">J'ai oubli� mon mot de passe ou je souhaite le modifier</a>
    </small>
    <br><br>
   <form method=\"post\" action=\"index.php\">
        Identifiant : <input type=\"text\" name=\"id\"><br>
        Mot de passe : <input type=\"password\" name=\"pass\"><br><br>
        <input type=\"submit\" value=\"OK\">
      </form><br>

    <a href=\"http://merome.net/monnaiem/phpBB3\">Acc�der au forum de Monnaie M (n�cessite la cr�ation d'un compte sp�cifique au forum !)</a><br><br>

    <a href=\"http://merome.net/monnaiem/ReglementMonnaieM.pdf\">Voir le r�glement de Monnaie M</a><br><br>");

    echo("<hr><b>Un aper�u des annonces d�j� en ligne avec une annonce au hasard<br><table align=\"center\">");
    $annoncehasards=exec_requete("select * from produit,citoyen where produit.valide=1 and nbex>0 and (dateexpiration=0 or dateexpiration>now()) and citoyen.idcitoyen=produit.idcitoyen and citoyen.idcitoyen<>'".$_SESSION["citoyen"]["idcitoyen"]."' order by rand()");
    $annoncehasard=mysql_fetch_array($annoncehasards);
    affiche_annonce($annoncehasard);
    echo("</table></div>");
  }
  else
  {
    echo("<div id=\"accueil\"><img src=\"images/bandeau.png\"><br><br>");

    $verif=exec_requete("select * from citoyen where idcitoyen='".$_SESSION["citoyen"]["idcitoyen"]."'");
    if(mysql_num_rows($verif)==1)
    {
      $_SESSION["citoyen"]=mysql_fetch_array($verif);
    }
    else
      die("erreur");

    echo("<a target=\"_new\" href=\"http://merome.net/monnaiem/phpBB3\">Acc�der au forum de monnaie M</a><br>");

    mesannonces();

    $cats=exec_requete("select distinct(categorie) from produit where produit.valide=1 and nbex>0");
    while($cat=mysql_fetch_array($cats))
    {
      $listecat.="<option value=\"".$cat["categorie"]."\">".$cat["categorie"]."</option>";
    }

    echo("<small><a href=\"rechercher.php?criteres=dep\">Les annonces dans votre d�partement</a><form method=\"post\" action=\"rechercher.php\"><select name=\"criteres\">".$listecat."</select>&nbsp;<input type=\"submit\" value=\"Voir les produits de cette cat�gorie\"></form><br></small>");

    $lesannonces=exec_requete("select * from produit,citoyen where produit.valide=1 and nbex>0 and (dateexpiration=0 or dateexpiration>now()) and citoyen.idcitoyen=produit.idcitoyen and citoyen.idcitoyen<>'".$_SESSION["citoyen"]["idcitoyen"]."' order by datesaisie desc");
    if(mysql_num_rows($lesannonces)>0)
    {

        echo("<form method=\"post\" action=\"rechercher.php\"><input type=\"text\" name=\"criteres\"><input type=\"submit\" value=\"Rechercher\"></form><br>");
        echo("<b>Une annonce au hasard<br><table align=\"center\">");
        $annoncehasards=exec_requete("select * from produit,citoyen where produit.valide=1 and nbex>0 and (dateexpiration=0 or dateexpiration>now()) and citoyen.idcitoyen=produit.idcitoyen and citoyen.idcitoyen<>'".$_SESSION["citoyen"]["idcitoyen"]."' order by rand()");
        $annoncehasard=mysql_fetch_array($annoncehasards);
        affiche_annonce($annoncehasard);


        echo("<tr><td colspan=\"3\" align=\"center\"><b><big>Les derni�res annonces saisies (".mysql_num_rows($lesannonces).") :</big></b><br><small><i>Vos annonces n'apparaissent pas dans cette liste</i></small></td></tr>");

        $i=0;
        while(($annonce=mysql_fetch_array($lesannonces)) && $i<(10+$debut))
        {
          if($annonce["idproduit"]!=$annoncehasard["idproduit"])
          {
            if($i>=$debut)
            {
              affiche_annonce($annonce);
            }
            $i++;
          }
        }
        echo("</table></p>");
        if(mysql_num_rows($lesannonces)>$i)
        {
          echo("<a href=\"index.php?debut=".$i."\">Voir les annonces suivantes</a>");
        }
    }
    else
    {
      echo("Aucune offre d'autres utilisateurs pour l'instant");
    }

    echo("</div>");
  }


  mysql_close();



?>
  </body>
</html>
