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

    if($_POST["tr"]!="")
    {
      $transaction=mysql_fetch_array(exec_requete("select * from transaction,produit where transaction.idproduit=produit.idproduit and idtransaction=".$_POST["tr"]));
      if($transaction["acheteur"]==$_SESSION["citoyen"]["idcitoyen"] || $transaction["vendeur"]==$_SESSION["citoyen"]["idcitoyen"])
      {
//        $citoyens=exec_requete("select mail from citoyen where idcitoyen='".$transaction["acheteur"]."' or idcitoyen='".$transaction["vendeur"]."' or idcitoyen='Merome'");
        $citoyens=exec_requete("select mail,idcitoyen from citoyen where idcitoyen='Merome'");
        while($citoyen1=mysql_fetch_array($citoyens))
        {
            if(mail($citoyen1["mail"], "[Monnaie M] ".$_SESSION["citoyen"]["idcitoyen"]." signale un probl�me sur la transaction concernant \"".$transaction["objet"]."\"",
                    "Ce message a �t� envoy� par ".$_SESSION["citoyen"]["idcitoyen"]." depuis le site Monnaie M. Merci de ne pas utiliser le bouton 'R�pondre' de votre messagerie, mais ce lien : http://merome.net/monnaiem/mail.php?id=".$citoyen1["code"]."&c=".urlencode($_SESSION["citoyen"]["idcitoyen"])." pour lui faire une r�ponse.\r\n\r\n".stripslashes($_POST["contenu"])."\r\n".
                    "\r\nPour r�pondre � ce message, cliquez ici : http://merome.net/monnaiem/mail.php?id=".$citoyen1["code"]."&c=".urlencode($_SESSION["citoyen"]["idcitoyen"])."\r\n",
                    "From: ".FROM."\r\n"
          					."Reply-To: ".FROM."\r\n"
          					."X-Mailer: PHP/" . phpversion()))
              echo("Message envoy� � ".$citoyen1["idcitoyen"]."<br>");
            else
               echo("Erreur lors de l'envoi du message � ".$citoyen1["idcitoyen"]."<br>");

        }
      }
      else
         echo("Cette transaction ne vous concerne pas");

    }

    if($_GET["t"]>0)
    {
      $transaction=mysql_fetch_array(exec_requete("select * from transaction,produit where transaction.idproduit=produit.idproduit and idtransaction=".$_GET["t"]));
      if($transaction["acheteur"]==$_SESSION["citoyen"]["idcitoyen"] || $transaction["vendeur"]==$_SESSION["citoyen"]["idcitoyen"])
      {
        echo("Vous pouvez utiliser ce formulaire pour demander l'annulation de la transaction, pour informer l'acheteur ou le vendeur d'un retard ou d'un probl�me quelconque...<br><br>");
        echo("<b>Signaler un probl�me sur la transaction concernant l'article <b><i>".$transaction["objet"]."</i></b> :</b><br>");
        ?>
          <form method="post" action="litige.php"><input type="hidden" name="tr" value="<?php  echo($_GET["t"]); ?>">
            <textarea name="contenu" rows="10" cols="80"></textarea><br>
            <input type="submit" value="Envoyer ce message � l'acheteur, au vendeur et � l'administrateur du site">
          </form>
        <?php 
       }
       else
         echo("Cette transaction ne vous concerne pas");

    }
    echo("</div>");



  mysql_close();



?>
  </body>
</html>