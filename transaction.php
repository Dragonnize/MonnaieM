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

    if($_POST["recu"]>0)
    {
      $transactions=exec_requete("select *,transaction.prix as prixt from transaction,citoyen,produit where produit.idproduit=transaction.idproduit and vendeur=citoyen.idcitoyen and idtransaction=".$_POST["recu"]);
      if(mysql_num_rows($transactions)==1)
      {
        $transaction=mysql_fetch_array($transactions);
        // Je suis bien le vendeur
        if($transaction["acheteur"]==$_SESSION["citoyen"]["idcitoyen"])
        {
            exec_requete("update transaction set statut='Termin�',note=".$_POST["note"].",commentaires='".$_POST["commentaires"]."' where idtransaction=".$_POST["recu"]);
            $moyennes=exec_requete("select avg(note) as moy from transaction where vendeur='".$transaction["vendeur"]."' and statut='Termin�'");
            $moyenne=mysql_fetch_array($moyennes);
            exec_requete("update citoyen set solde=solde+".$transaction["prixt"].",nbventes=nbventes+1,notevendeur=".$moyenne["moy"]." where idcitoyen='".$transaction["vendeur"]."'");
            $moyennes=exec_requete("select avg(note) as moy from transaction where acheteur='".$transaction["acheteur"]."'");
            $moyenne=mysql_fetch_array($moyennes);
            exec_requete("update citoyen set noteacheteur=".$moyenne["moy"]." where idcitoyen='".$transaction["acheteur"]."'");
            echo("Cette transaction est maintenant termin�e. Merci.<br>");
        }
        else
          die("Je ne suis pas concern� par cela");
      }

    }

    if($_POST["annule"]>0)
    {
      $transactions=exec_requete("select *,transaction.prix as prixt from transaction,citoyen,produit where produit.idproduit=transaction.idproduit and acheteur=citoyen.idcitoyen and idtransaction=".$_POST["annule"]);
      if(mysql_num_rows($transactions)==1)
      {
        $transaction=mysql_fetch_array($transactions);
        // Je suis bien le vendeur
        if($transaction["vendeur"]==$_SESSION["citoyen"]["idcitoyen"])
        {
            exec_requete("update transaction set statut='Annul�' where idtransaction=".$_POST["annule"]);
            echo("Cette transaction est maintenant annul�e.<br>");
            exec_requete("update citoyen set solde=solde+".$transaction["prixt"]." where idcitoyen='".$transaction["acheteur"]."'");
            mail($transaction["mail"], "Annulation de votre achat sur Monnaie M",
                      "Le vendeur vient d'annuler la vente du produit ".$transaction["objet"].". ".$transaction["prixt"]." M ont �t� recr�dit�s sur votre compte.\r\n",
                      "From: ".FROM."\r\n"
							."Reply-To: ".FROM."\r\n"
							."X-Mailer: PHP/" . phpversion());
        }
        if($transaction["acheteur"]==$_SESSION["citoyen"]["idcitoyen"])
        {
            exec_requete("update transaction set statut='Annul�' where idtransaction=".$_POST["annule"]);
            echo("Cette transaction est maintenant annul�e.<br>");
        }
      }
    }

    if($_POST["confirme"]>0)
    {
      $transactions=exec_requete("select *,transaction.prix as prixt from transaction,citoyen,produit where produit.idproduit=transaction.idproduit and acheteur=citoyen.idcitoyen and idtransaction=".$_POST["confirme"]);
      if(mysql_num_rows($transactions)==1)
      {
        $transaction=mysql_fetch_array($transactions);
        // Je suis bien le vendeur
        if($transaction["vendeur"]==$_SESSION["citoyen"]["idcitoyen"] && $transaction["statut"]=="Command�")
        {
            exec_requete("update transaction set statut='confirm�' where idtransaction=".$_POST["confirme"]);
            if($transaction["port"]==1)
            {
              echo("Cette transaction est maintenant confirm�e. L'acheteur a choisi un envoi par la Poste.<br>Vous avez une semaine pour la transmettre � l'acheteur :<br>".
                $transaction["nom"]." ".$transaction["prenom"]."<br>".
                $transaction["adresse"]."<br>".
                $transaction["cp"]." ".$transaction["ville"]);
              echo("<br>Vous pouvez communiquer avec l'acheteur <a href=\"http://merome.net/monnaiem/mail.php?c=".urlencode($transaction["acheteur"])."\">en cliquant ici</a>");
            }
            else
            {
              echo("Cette transaction est maintenant confirm�e. L'acheteur a choisi une remise en mains propres.<br>");
              echo("Vous pouvez communiquer avec l'acheteur <a href=\"http://merome.net/monnaiem/mail.php?c=".urlencode($transaction["acheteur"])."\">en cliquant ici</a>");
            }

            mail($transaction["mail"], "Confirmation de votre achat sur Monnaie M",
                      "Le vendeur vient de confirmer la vente du produit ".$transaction["objet"]." pour ".$transaction["prixt"]." M.\nMerci de noter le vendeur au moment de la r�ception du produit. Sans validation de votre part apr�s 30 jours, la note maximale sera attribu�e au vendeur.\r\n",
                      "From: ".FROM."\r\n"
							."Reply-To: ".FROM."\r\n"
							."X-Mailer: PHP/" . phpversion());


        }
        else
        {
          if($transaction["acheteur"]==$_SESSION["citoyen"]["idcitoyen"] && $transaction["statut"]=="Propos�")
          {
	    $transactions=exec_requete("select *,transaction.prix as prixt from transaction,citoyen,produit where produit.idproduit=transaction.idproduit and vendeur=citoyen.idcitoyen and idtransaction=".$_POST["confirme"]);
	    $transaction=mysql_fetch_array($transactions);

            exec_requete("update transaction set statut='confirm�' where idtransaction=".$_POST["confirme"]);
              echo("Cette transaction est maintenant confirm�e.<br>Vous pouvez communiquer avec le vendeur <a href=\"http://merome.net/monnaiem/mail.php?c=".urlencode($transaction["vendeur"])."\">en cliquant ici</a><br><br>");

              exec_requete("update citoyen set solde=solde-".$transaction["prixt"]. " where idcitoyen='".$_SESSION["citoyen"]["idcitoyen"]."'");

              mail($transaction["mail"], "Votre proposition a �t� valid�e sur Monnaie M",
                    $_SESSION["citoyen"]["idcitoyen"]." vient de valider votre proposition pour le produit ou service ".$transaction["objet"]." pour ".$transaction["prixt"]." M. Merci de lui faire parvenir rapidement sa commande. Votre compte sera cr�dit� lorsqu'il l'aura r�ceptionn�e.\r\n",
                    "From: ".FROM."\r\n"
						."Reply-To: ".FROM."\r\n"
						."X-Mailer: PHP/" . phpversion());

            mail($_SESSION["citoyen"]["mail"], "Enregistrement de votre achat sur Monnaie M",
                    "Vous venez d'accepter sur Monnaie M la proposition pour le produit ou service ".$transaction["objet"]." pour ".$transaction["prixt"]." M.\n".$transaction["vendeur"]." a �t� pr�venu par mail. \r\n",
                    "From: ".FROM."\r\n"
						."Reply-To: ".FROM."\r\n"
						."X-Mailer: PHP/" . phpversion());

              die("Un mail vient d'�tre envoy� au vendeur pour l'avertir de votre achat. Lorsque vous recevrez votre commande, merci de confirmer la r�ception et de noter le vendeur<br>");

          }
          else
            die("Je ne suis pas concern� par cela");
        }
      }

    }

    if($_GET["t"]>0)
    {
      $transactions=exec_requete("select *,transaction.prix as prixt from transaction,produit where transaction.idproduit=produit.idproduit and idtransaction=".$_GET["t"]);
      if(mysql_num_rows($transactions)==1)
      {
        $transaction=mysql_fetch_array($transactions);
        // Je suis l'acheteur
        if($transaction["acheteur"]==$_SESSION["citoyen"]["idcitoyen"])
        {
          if($transaction["statut"]=="Propos�")
          {
              if($transaction["port"]==1)
                $port=" (avec envoi par la poste).";
              else
                $port=" (avec remise en mains propres � organiser avec le vendeur).";
              if($transaction["icone"]!="" && file_exists(str_replace("http://merome.net/","/var/www/",$transaction["icone"])))
                echo("Cette proposition est en attente de validation.<br>Validez-vous la transaction ".$port."?<br>
                    <img src=\"".$transaction["icone"]."\"><br>".$transaction["objet"]." (".$transaction["prixt"]." <img align=\"middle\" src=\"images/m.png\">)<br><b>Command� le : </b>".$transaction["datevente"]." � ".$transaction["vendeur"]);
              else
                echo("Cette proposition est en attente de validation.<br>Validez-vous la transaction ".$port."?<br>
                    <i>Pas d'image disponible</i><br>".$transaction["objet"]." (".$transaction["prixt"]." <img align=\"middle\" src=\"images/m.png\">)<br><b>Command� le : </b>".$transaction["datevente"]." � ".$transaction["vendeur"]);
              ?>
                <br>
                <form method="post" action="transaction.php"><input type="hidden" name="confirme" value="<?php  echo($transaction["idtransaction"]); ?>">
                <input type="submit" value="Je valide cette proposition"></form>

                <form method="post" action="transaction.php"><input type="hidden" name="annule" value="<?php  echo($transaction["idtransaction"]); ?>">
                <input type="submit" value="J'annule cette proposition"></form>

              <?php 


          }
          if($transaction["statut"]=="confirm�")
          {
            if($transaction["icone"]!="" && file_exists(str_replace("http://merome.net/","/var/www/",$transaction["icone"])))
              echo("Cette commande est en attente de r�ception. L'avez-vous re�ue ?<br>
                  <img src=\"".$transaction["icone"]."\"><br>".$transaction["objet"]." (".$transaction["prixt"]." <img align=\"middle\" src=\"images/m.png\">)<br><b>Command� le : </b>".$transaction["datevente"]." � ".$transaction["vendeur"]);
            else
              echo("Cette commande est en attente de r�ception. L'avez-vous re�ue ?<br>
                  <i>Pas d'image disponible</i><br>".$transaction["objet"]." (".$transaction["prixt"]." <img align=\"middle\" src=\"images/m.png\">)<br><b>Command� le : </b>".$transaction["datevente"]." � ".$transaction["vendeur"]);

            ?>
              <br><br>
              <form method="post" action="transaction.php"><input type="hidden" name="recu" value="<?php  echo($transaction["idtransaction"]); ?>">
              Je confirme que j'ai re�u cette commande et je note le vendeur sur cette transaction : <select name="note">
              <option value="5">Parfait</option>
              <option value="4">Bon</option>
              <option value="3">Correct</option>
              <option value="2">M�diocre</option>
              <option value="1">Mauvais</option>
              <option value="0">Tr�s mauvais</option>
              </select><br><br>
              Commentaires : <input type="text" name="commentaires" size="70"><br><br>
              <input type="submit" value="OK"></form>
            <?php 
          }
        }
        else
        {
          // Je suis le vendeur
          if($transaction["vendeur"]==$_SESSION["citoyen"]["idcitoyen"])
          {
            if($transaction["statut"]=="Command�")
            {
              if($transaction["port"]==1)
                $port=" (avec envoi par la poste � vos frais).";
              else
                $port=" (avec remise en mains propres � organiser avec l'acheteur).";
                if($transaction["icone"]!="" && file_exists(str_replace("http://merome.net/","/var/www/",$transaction["icone"])))
                  echo("Cette commande est en attente de confirmation.<br>Confirmez-vous la vente ".$port."?<br>
                    <img src=\"".$transaction["icone"]."\"><br>".$transaction["objet"]." (".$transaction["prixt"]." <img align=\"middle\" src=\"images/m.png\">)<br><b>Command� le : </b>".$transaction["datevente"]." � ".$transaction["vendeur"]);
                else
                  echo("Cette commande est en attente de confirmation.<br>Confirmez-vous la vente ".$port."?<br>
                    <i>Pas d'image disponible</i><br>".$transaction["objet"]." (".$transaction["prixt"]." <img align=\"middle\" src=\"images/m.png\">)<br><b>Command� le : </b>".$transaction["datevente"]." � ".$transaction["vendeur"]);

              ?>
                <br>
                <form method="post" action="transaction.php"><input type="hidden" name="confirme" value="<?php  echo($transaction["idtransaction"]); ?>">
                <input type="submit" value="Je confirme cette vente"></form>

                <form method="post" action="transaction.php"><input type="hidden" name="annule" value="<?php  echo($transaction["idtransaction"]); ?>">
                <input type="submit" value="J'annule cette vente"></form>

              <?php 
            }
          }
          else
          {
            echo("Vous n'�tes pas concern� par cette transaction<br>");
          }
        }
      }
      else
        echo("Transaction introuvable");

    }

    echo("</div>");



  mysql_close();



?>
  </body>
</html>
