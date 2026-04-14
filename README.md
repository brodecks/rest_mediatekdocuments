<h1>Présentation de l'API</h1>
Lien vers le dépôt d'origine dont le "README" contient la présentation de l'API REST d'origine <strong>avec explications sur comment l'exploiter</strong> :<br>
https://github.com/CNED-SLAM/rest_mediatekdocuments<br><br>
Le readme de ce dépôt présente comment installer en local l'API REST et les nouvelles fonctionnalités ajoutées à cette dernière.<br><br>
La vocation actuelle de l'API REST est de répondre aux demandes de l'application de bureau MediaTekDocuments, mise en ligne sur le dépôt :<br>
https://github.com/brodecks/MediaTekDocuments

<h1>Installation de l'API en local</h1>
Pour tester l'API REST en local, voici le mode opératoire (similaire à celui donné dans le dépôt d'API de base) :
<ul>
   <li>Installer les outils nécessaires (WampServer ou équivalent, NetBeans ou équivalent pour gérer l'API dans un IDE, Postman pour les tests).</li>
   <li>Télécharger le zip du code de l'API et le dézipper dans le dossier www de wampserver (renommer le dossier en "rest_mediatekdocuments", donc en enlevant "_master").</li>
   <li>Si 'Composer' n'est pas installé, le télécharger avec ce lien et l'installer : https://getcomposer.org/Composer-Setup.exe </li>
   <li>Dans une fenêtre de commandes ouverte en mode admin, aller dans le dossier de l'API et taper 'composer install' puis valider pour recréer le vendor.</li>
   <li>Récupérer le script metiak86.sql en racine du projet puis, avec phpMyAdmin, créer la BDD mediatek86 et, dans cette BDD, exécuter le script pour remplir la BDD.</li>
   <li>Ouvrir l'API dans NetBeans pour pouvoir analyser le code.</li>
   <li>Pour tester l'API avec Postman, ne pas oublier de configurer l'authentification (onglet "Authorization", Type "Basic Auth", Username "adminrestmediatekdocuments", Password "AP2userrestmediatekdocumentsADMIN2026apirest".</li>
   <li>Par ailleurs, l'adresse de l'API REST en local est la suivante : http://localhost/rest_mediatekdocuments/ </li>
</ul>

<h1>Les fonctionnalités ajoutées</h1>
Dans MyAccessBDD, plusieurs fonctions ont été ajoutées pour répondre aux nouvelles demandes de l'application C# MediaTekDocuments :<br>
<ul>
   <li><strong>insertLivre : </strong>ajoute un livre avec ses informations dans la base de données.</li>
   <li><strong>insertDvd : </strong>même chose pour les dvd.</li>
   <li><strong>insertRevue : </strong>même chose pour les revues.</li>
   <li><strong>insertCommandeDocument : </strong>même chose pour les commandes de livre ou dvd.</li>
   <li><strong>insertAbonnement : </strong>même chose pour les commandes (abonnements) de revues.</li>
   <li><strong>updateOneTupleOneTableSeveralKeys : </strong>modifie un tuple dans une table ayant une clé primaire composée.</li>
   <li><strong>updateLivre : </strong>modifie les informations d'un livre dans la base de données.</li>
   <li><strong>updateDvd : </strong>même chose pour les dvd.</li>
   <li><strong>updateRevue : </strong>même chose pour les revues.</li>
   <li><strong>updateCommandeDocument : </strong>modifie l'étape de suivi d'une commande de livre ou dvd dans la base de données.</li>
   <li><strong>updateExemplaire : </strong>modifie l'état d'un exemplaire dans la base de données.</li>
   <li><strong>deleteLivre : </strong>supprime un livre et ses informations de la base de données.</li>
   <li><strong>deleteDvd : </strong>même chose pour les dvd.</li>
   <li><strong>deleteRevue : </strong>même chose pour les revues.</li>
   <li><strong>selectExemplaires : </strong>récupère les exemplaires d'un document dont l'id sera donné.</li>
   <li><strong>selectCommandeDocument : </strong>récupère les commandes d'un livre ou d'un dvd dont l'id sera donné.</li>
   <li><strong>selectAbonnement : </strong>récupère les commandes (abonnements) d'une revue dont l'id sera donné.</li>
   <li><strong>selectAbonnementsFinissant : </strong>récupère les abonnements se finissant dans moins de 30 jours.</li>
</ul>