<?php
include_once("AccessBDD.php");

/**
 * Classe de construction des requêtes SQL
 * hérite de AccessBDD qui contient les requêtes de base
 * Pour ajouter une requête :
 * - créer la fonction qui crée une requête (prendre modèle sur les fonctions 
 *   existantes qui ne commencent pas par 'traitement')
 * - ajouter un 'case' dans un des switch des fonctions redéfinies 
 * - appeler la nouvelle fonction dans ce 'case'
 */
class MyAccessBDD extends AccessBDD {
	    
    /**
     * constructeur qui appelle celui de la classe mère
     */
    public function __construct(){
        try{
            parent::__construct();
        }catch(\Exception $e){
            throw $e;
        }
    }

    /**
     * demande de recherche
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return array|null tuples du résultat de la requête ou null si erreur
     * @override
     */	
    protected function traitementSelect(string $table, ?array $champs) : ?array{
        switch($table){  
            case "livre" :
                return $this->selectAllLivres();
            case "dvd" :
                return $this->selectAllDvd();
            case "revue" :
                return $this->selectAllRevues();
            case "exemplaire" :
                return $this->selectExemplaires($champs);
            case "commandedocument" :
                return $this->selectCommandeDocument($champs);
            case "abonnement" :
                return $this->selectAbonnement($champs);
            case "abonnementfinissant" :
                return $this->selectAbonnementsFinissant($champs);
            case "genre" :
            case "public" :
            case "rayon" :
            case "etat" :
            case "suivi" :
                // select portant sur une table contenant juste id et libelle
                return $this->selectTableSimple($table);
            case "" :
                // return $this->uneFonction(parametres);
            default:
                // cas général
                return $this->selectTuplesOneTable($table, $champs);
        }	
    }

    /**
     * demande d'ajout (insert)
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples ajoutés ou null si erreur
     * @override
     */	
    protected function traitementInsert(string $table, ?array $champs) : ?int{
        switch($table){
            case "livre" :
                return $this->insertLivre($champs);
            case "dvd" :
                return $this->insertDvd($champs);
            case "revue" :
                return $this->insertRevue($champs);
            case "commandedocument" :
                return $this->insertCommandeDocument($champs);
            case "abonnement" :
                return $this->insertAbonnement($champs);
            default:                    
                // cas général
                return $this->insertOneTupleOneTable($table, $champs);	
        }
    }
    
    /**
     * demande de modification (update)
     * @param string $table
     * @param string|null $id
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples modifiés ou null si erreur
     * @override
     */	
    protected function traitementUpdate(string $table, ?string $id, ?array $champs) : ?int{
        switch($table){
            case "livre" :
                return $this->updateLivre($id, $champs);
            case "dvd" :
                return $this->updateDvd($id, $champs);
            case "revue" :
                return $this->updateRevue($id, $champs);
            case "commandedocument" :
                return $this->updateCommandeDocument($id, $champs);
            case "exemplaire" :
                return $this->updateExemplaire($id, $champs);
            default:                    
                // cas général
                return $this->updateOneTupleOneTable($table, $id, $champs);
        }	
    }  
    
    /**
     * demande de suppression (delete)
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples supprimés ou null si erreur
     * @override
     */	
    protected function traitementDelete(string $table, ?array $champs) : ?int{
        switch($table){
            case "livre" :
                return $this->deleteLivre($champs);
            case "dvd" :
                return $this->deleteDvd($champs);
            case "revue" :
                return $this->deleteRevue($champs);
            default:                    
                // cas général
                return $this->deleteTuplesOneTable($table, $champs);	
        }
    }	    
        
    /**
     * récupère les tuples d'une seule table
     * @param string $table
     * @param array|null $champs
     * @return array|null 
     */
    private function selectTuplesOneTable(string $table, ?array $champs) : ?array{
        if(empty($champs)){
            // tous les tuples d'une table
            $requete = "select * from $table;";
            return $this->conn->queryBDD($requete);  
        }else{
            // tuples spécifiques d'une table
            $requete = "select * from $table where ";
            foreach ($champs as $key => $value){
                $requete .= "$key=:$key and ";
            }
            // (enlève le dernier and)
            $requete = substr($requete, 0, strlen($requete)-5);	          
            return $this->conn->queryBDD($requete, $champs);
        }
    }	

    /**
     * demande d'ajout (insert) d'un tuple dans une table
     * @param string $table
     * @param array|null $champs
     * @return int|null nombre de tuples ajoutés (0 ou 1) ou null si erreur
     */	
    private function insertOneTupleOneTable(string $table, ?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // construction de la requête
        $requete = "insert into $table (";
        foreach ($champs as $key => $value){
            $requete .= "$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);
        $requete .= ") values (";
        foreach ($champs as $key => $value){
            $requete .= ":$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);
        $requete .= ");";
        return $this->conn->updateBDD($requete, $champs);
    }
    
    /**
     * demande d'ajout (insert) d'un livre dans la base de données
     * @param array|null $champs
     * @return 1 si l'insert a fonctionné, null si erreur
     */	
    private function insertLivre(?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // création d'une transaction
        $this->conn->beginTransaction();
        try{
            // ajout de certaines informations du livre dans la table document
            $champsTableDocument = [
                "id" => $champs["Id"],
                "titre" => $champs["Titre"],
                "image" => $champs["Image"],
                "idRayon" => $champs["IdRayon"],
                "idPublic" => $champs["IdPublic"],
                "idGenre" => $champs["IdGenre"]
            ];
            $resultat = $this->insertOneTupleOneTable("document", $champsTableDocument);
            if($resultat === null){
                throw new Exception("Erreur lors de l'insertion dans la table document");
            }
            // ajout de certaines informations du livre dans la table livres_dvd
            $champsTableLivres_dvd = [
                "id" => $champs["Id"]
            ];
            $resultat = $this->insertOneTupleOneTable("livres_dvd", $champsTableLivres_dvd);
            if($resultat === null){
                throw new Exception("Erreur lors de l'insertion dans la table livres_dvd");
            }
            // ajout de certaines informations du livre dans la table livre
            $champsTableLivre = [
                "id" => $champs["Id"],
                "ISBN" => $champs["Isbn"],
                "auteur" => $champs["Auteur"],
                "collection" => $champs["Collection"]
            ];
            $resultat = $this->insertOneTupleOneTable("livre", $champsTableLivre);
            if($resultat === null){
                throw new Exception("Erreur lors de l'insertion dans la table livre");
            }
            // Application des actions effectuées dans la transaction s'il n'y a pas d'erreur
            $this->conn->commit();
            return 1;
        }catch (Exception $e){
            // Annulation des actions effectuées lors de la transaction s'il y a une erreur
            $this->conn->rollBack();
            return null;
        }
    }
    
    /**
     * demande d'ajout (insert) d'un dvd dans la base de données
     * @param array|null $champs
     * @return 1 si l'insert a fonctionné, null si erreur
     */	
    private function insertDvd(?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // création d'une transaction
        $this->conn->beginTransaction();
        try{
            // ajout de certaines informations du dvd dans la table document
            $champsTableDocument = [
                "id" => $champs["Id"],
                "titre" => $champs["Titre"],
                "image" => $champs["Image"],
                "idRayon" => $champs["IdRayon"],
                "idPublic" => $champs["IdPublic"],
                "idGenre" => $champs["IdGenre"]
            ];
            $resultat = $this->insertOneTupleOneTable("document", $champsTableDocument);
            if($resultat === null){
                throw new Exception("Erreur lors de l'insertion dans la table document");
            }
            // ajout de certaines informations du dvd dans la table livres_dvd
            $champsTableLivres_dvd = [
                "id" => $champs["Id"]
            ];
            $resultat = $this->insertOneTupleOneTable("livres_dvd", $champsTableLivres_dvd);
            if($resultat === null){
                throw new Exception("Erreur lors de l'insertion dans la table livres_dvd");
            }
            // ajout de certaines informations du dvd dans la table dvd
            $champsTableDvd = [
                "id" => $champs["Id"],
                "duree" => $champs["Duree"],
                "realisateur" => $champs["Realisateur"],
                "synopsis" => $champs["Synopsis"]
            ];
            $resultat = $this->insertOneTupleOneTable("dvd", $champsTableDvd);
            if($resultat === null){
                throw new Exception("Erreur lors de l'insertion dans la table dvd");
            }
            // Application des actions effectuées dans la transaction s'il n'y a pas d'erreur
            $this->conn->commit();
            return 1;
        }catch (Exception $e){
            // Annulation des actions effectuées lors de la transaction s'il y a une erreur
            $this->conn->rollBack();
            return null;
        }
    }
    
    /**
     * demande d'ajout (insert) d'une revue dans la base de données
     * @param array|null $champs
     * @return 1 si l'insert a fonctionné, null si erreur
     */	
    private function insertRevue(?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // création d'une transaction
        $this->conn->beginTransaction();
        try{
            // ajout de certaines informations de la revue dans la table document
            $champsTableDocument = [
                "id" => $champs["Id"],
                "titre" => $champs["Titre"],
                "image" => $champs["Image"],
                "idRayon" => $champs["IdRayon"],
                "idPublic" => $champs["IdPublic"],
                "idGenre" => $champs["IdGenre"]
            ];
            $resultat = $this->insertOneTupleOneTable("document", $champsTableDocument);
            if($resultat === null){
                throw new Exception("Erreur lors de l'insertion dans la table document");
            }
            // ajout de certaines informations de la revue dans la table revue
            $champsTableRevue = [
                "id" => $champs["Id"],
                "periodicite" => $champs["Periodicite"],
                "delaiMiseADispo" => $champs["DelaiMiseADispo"]
            ];
            $resultat = $this->insertOneTupleOneTable("revue", $champsTableRevue);
            if($resultat === null){
                throw new Exception("Erreur lors de l'insertion dans la table revue");
            }
            // Application des actions effectuées dans la transaction s'il n'y a pas d'erreur
            $this->conn->commit();
            return 1;
        }catch (Exception $e){
            // Annulation des actions effectuées lors de la transaction s'il y a une erreur
            $this->conn->rollBack();
            return null;
        }
    }
    
    /**
     * demande d'ajout (insert) d'une commande de livre ou dvd dans la base de données
     * @param array|null $champs
     * @return 1 si l'insert a fonctionné, null si erreur
     */	
    private function insertCommandeDocument(?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // création d'une transaction
        $this->conn->beginTransaction();
        try{
            // ajout de certaines informations de la commande dans la table commande
            $champsTableCommande = [
                "id" => $champs["Id"],
                "dateCommande" => $champs["DateCommande"],
                "montant" => $champs["Montant"]
            ];
            $resultat = $this->insertOneTupleOneTable("commande", $champsTableCommande);
            if($resultat === null){
                throw new Exception("Erreur lors de l'insertion dans la table commande");
            }
            // ajout de certaines informations de la commande dans la table commandedocument
            $champsTableCommandedocument = [
                "id" => $champs["Id"],
                "nbExemplaire" => $champs["NbExemplaire"],
                "idLivreDvd" => $champs["IdLivreDvd"],
                "idSuivi" => $champs["IdSuivi"]
            ];
            $resultat = $this->insertOneTupleOneTable("commandedocument", $champsTableCommandedocument);
            if($resultat === null){
                throw new Exception("Erreur lors de l'insertion dans la table commandedocument");
            }
            // Application des actions effectuées dans la transaction s'il n'y a pas d'erreur
            $this->conn->commit();
            return 1;
        }catch (Exception $e){
            // Annulation des actions effectuées lors de la transaction s'il y a une erreur
            $this->conn->rollBack();
            return null;
        }
    }
    
    /**
     * demande d'ajout (insert) d'un abonnement de revue dans la base de données
     * @param array|null $champs
     * @return 1 si l'insert a fonctionné, null si erreur
     */	
    private function insertAbonnement(?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // création d'une transaction
        $this->conn->beginTransaction();
        try{
            // ajout de certaines informations de l'abonnement dans la table commande
            $champsTableCommande = [
                "id" => $champs["Id"],
                "dateCommande" => $champs["DateCommande"],
                "montant" => $champs["Montant"]
            ];
            $resultat = $this->insertOneTupleOneTable("commande", $champsTableCommande);
            if($resultat === null){
                throw new Exception("Erreur lors de l'insertion dans la table commande");
            }
            // ajout de certaines informations de l'abonnement dans la table abonnement
            $champsTableAbonnement = [
                "id" => $champs["Id"],
                "dateFinAbonnement" => $champs["DateFinAbonnement"],
                "idRevue" => $champs["IdRevue"]
            ];
            $resultat = $this->insertOneTupleOneTable("abonnement", $champsTableAbonnement);
            if($resultat === null){
                throw new Exception("Erreur lors de l'insertion dans la table abonnement");
            }
            // Application des actions effectuées dans la transaction s'il n'y a pas d'erreur
            $this->conn->commit();
            return 1;
        }catch (Exception $e){
            // Annulation des actions effectuées lors de la transaction s'il y a une erreur
            $this->conn->rollBack();
            return null;
        }
    }

    /**
     * demande de modification (update) d'un tuple dans une table
     * @param string $table
     * @param string\null $id
     * @param array|null $champs 
     * @return int|null nombre de tuples modifiés (0 ou 1) ou null si erreur
     */	
    private function updateOneTupleOneTable(string $table, ?string $id, ?array $champs) : ?int {
        if(empty($champs)){
            return null;
        }
        if(is_null($id)){
            return null;
        }
        // construction de la requête
        $requete = "update $table set ";
        foreach ($champs as $key => $value){
            $requete .= "$key=:$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);				
        $champs["id"] = $id;
        $requete .= " where id=:id;";		
        return $this->conn->updateBDD($requete, $champs);	        
    }
    
    /**
     * demande de modification (update) d'un tuple dans une table ayant une clé primaire composée
     * @param string $table
     * @param string\null $id
     * @param array|null $champs 
     * @return int|null nombre de tuples modifiés (0 ou 1) ou null si erreur
     */	
    private function updateOneTupleOneTableSeveralKeys(string $table, ?string $id, ?array $champs) : ?int {
        if(empty($champs)){
            return null;
        }
        if(is_null($id)){
            return null;
        }
        $numero = $champs["numero"];
        unset($champs["numero"]);
        // construction de la requête
        $requete = "update $table set ";
        foreach ($champs as $key => $value){
            $requete .= "$key=:$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);				
        $champs["id"] = $id;
        $champs["numero"] = $numero;
        $requete .= " where id=:id and numero=:numero;";		
        return $this->conn->updateBDD($requete, $champs);	        
    }
    
    /**
     * demande de modification (update) d'un livre dans la base de données
     * @param string\null $id
     * @param array|null $champs 
     * @return 1 si la modification a fonctionné, null si erreur
     */	
    private function updateLivre(?string $id, ?array $champs) : ?int {
        if(empty($champs)){
            return null;
        }
        if(is_null($id)){
            return null;
        }
        // création d'une transaction
        $this->conn->beginTransaction();
        try
        {
            // modification de certaines informations du livre dans la table document
            $champsTableDocument = [
                "titre" => $champs["Titre"],
                "image" => $champs["Image"],
                "idRayon" => $champs["IdRayon"],
                "idPublic" => $champs["IdPublic"],
                "idGenre" => $champs["IdGenre"]
            ];
            $resultat = $this->updateOneTupleOneTable("document", $id, $champsTableDocument);
            if($resultat === null){
                throw new Exception("Erreur lors de la modification dans la table document");
            }
            // modification de certaines informations du livre dans la table livre
            $champsTableLivre = [
                "ISBN" => $champs["Isbn"],
                "auteur" => $champs["Auteur"],
                "collection" => $champs["Collection"]
            ];
            $resultat = $this->updateOneTupleOneTable("livre", $id, $champsTableLivre);
            if($resultat === null){
                throw new Exception("Erreur lors de la modification dans la table livre");
            }
            // Application des actions effectuées dans la transaction s'il n'y a pas d'erreur
            $this->conn->commit();
            return 1;
        }catch (Exception $e){
            // Annulation des actions effectuées lors de la transaction s'il y a une erreur
            $this->conn->rollBack();
            return null;
        }	        
    }
    
    /**
     * demande de modification (update) d'un dvd dans la base de données
     * @param string\null $id
     * @param array|null $champs 
     * @return 1 si la modification a fonctionné, null si erreur
     */	
    private function updateDvd(?string $id, ?array $champs) : ?int {
        if(empty($champs)){
            return null;
        }
        if(is_null($id)){
            return null;
        }
        // création d'une transaction
        $this->conn->beginTransaction();
        try
        {
            // modification de certaines informations du dvd dans la table document
            $champsTableDocument = [
                "titre" => $champs["Titre"],
                "image" => $champs["Image"],
                "idRayon" => $champs["IdRayon"],
                "idPublic" => $champs["IdPublic"],
                "idGenre" => $champs["IdGenre"]
            ];
            $resultat = $this->updateOneTupleOneTable("document", $id, $champsTableDocument);
            if($resultat === null){
                throw new Exception("Erreur lors de la modification dans la table document");
            }
            // modification de certaines informations du dvd dans la table dvd
            $champsTableDvd = [
                "duree" => $champs["Duree"],
                "realisateur" => $champs["Realisateur"],
                "synopsis" => $champs["Synopsis"]
            ];
            $resultat = $this->updateOneTupleOneTable("dvd", $id, $champsTableDvd);
            if($resultat === null){
                throw new Exception("Erreur lors de la modification dans la table dvd");
            }
            // Application des actions effectuées dans la transaction s'il n'y a pas d'erreur
            $this->conn->commit();
            return 1;
        }catch (Exception $e){
            // Annulation des actions effectuées lors de la transaction s'il y a une erreur
            $this->conn->rollBack();
            return null;
        }	        
    }
    
    /**
     * demande de modification (update) d'une revue dans la base de données
     * @param string\null $id
     * @param array|null $champs 
     * @return 1 si la modification a fonctionné, null si erreur
     */	
    private function updateRevue(?string $id, ?array $champs) : ?int {
        if(empty($champs)){
            return null;
        }
        if(is_null($id)){
            return null;
        }
        // création d'une transaction
        $this->conn->beginTransaction();
        try
        {
            // modification de certaines informations de la revue dans la table document
            $champsTableDocument = [
                "titre" => $champs["Titre"],
                "image" => $champs["Image"],
                "idRayon" => $champs["IdRayon"],
                "idPublic" => $champs["IdPublic"],
                "idGenre" => $champs["IdGenre"]
            ];
            $resultat = $this->updateOneTupleOneTable("document", $id, $champsTableDocument);
            if($resultat === null){
                throw new Exception("Erreur lors de la modification dans la table document");
            }
            // modification de certaines informations de la revue dans la table revue
            $champsTableRevue = [
                "periodicite" => $champs["Periodicite"],
                "delaiMiseADispo" => $champs["DelaiMiseADispo"]
            ];
            $resultat = $this->updateOneTupleOneTable("revue", $id, $champsTableRevue);
            if($resultat === null){
                throw new Exception("Erreur lors de la modification dans la table revue");
            }
            // Application des actions effectuées dans la transaction s'il n'y a pas d'erreur
            $this->conn->commit();
            return 1;
        }catch (Exception $e){
            // Annulation des actions effectuées lors de la transaction s'il y a une erreur
            $this->conn->rollBack();
            return null;
        }	        
    }
    
    /**
     * demande de modification (update) de l'étape de suivi d'une commande dans la base de données
     * @param string\null $id
     * @param array|null $champs 
     * @return 1 si la modification a fonctionné, null si erreur
     */	
    private function updateCommandeDocument(?string $id, ?array $champs) : ?int {
        if(empty($champs)){
            return null;
        }
        if(is_null($id)){
            return null;
        }
        // création d'une transaction
        $this->conn->beginTransaction();
        try
        {
            // modification de l'étape de suivi de la commande dans la table commandedocument
            $champsTableCommandedocument = [
                "idSuivi" => $champs["IdSuivi"]
            ];
            $resultat = $this->updateOneTupleOneTable("commandedocument", $id, $champsTableCommandedocument);
            if($resultat === null){
                throw new Exception("Erreur lors de la modification dans la table commandedocument");
            }
            // Application des actions effectuées dans la transaction s'il n'y a pas d'erreur
            $this->conn->commit();
            return 1;
        }catch (Exception $e){
            // Annulation des actions effectuées lors de la transaction s'il y a une erreur
            $this->conn->rollBack();
            return null;
        }	        
    }
    
    /**
     * demande de modification (update) de l'état d'un exemplaire dans la base de données
     * @param string\null $id
     * @param array|null $champs 
     * @return 1 si la modification a fonctionné, null si erreur
     */	
    private function updateExemplaire(?string $id, ?array $champs) : ?int {
        if(empty($champs)){
            return null;
        }
        if(is_null($id)){
            return null;
        }
        // création d'une transaction
        $this->conn->beginTransaction();
        try
        {
            // modification de l'état de l'exemplaire dans la table exemplaire
            $champsTableExemplaire = [
                "numero" => $champs["Numero"],
                "idEtat" => $champs["IdEtat"]
            ];
            $resultat = $this->updateOneTupleOneTableSeveralKeys("exemplaire", $id, $champsTableExemplaire);
            if($resultat === null){
                throw new Exception("Erreur lors de la modification dans la table exemplaire");
            }
            // Application des actions effectuées dans la transaction s'il n'y a pas d'erreur
            $this->conn->commit();
            return 1;
        }catch (Exception $e){
            // Annulation des actions effectuées lors de la transaction s'il y a une erreur
            $this->conn->rollBack();
            return null;
        }	        
    }
    
    /**
     * demande de suppression (delete) d'un ou plusieurs tuples dans une table
     * @param string $table
     * @param array|null $champs
     * @return int|null nombre de tuples supprimés ou null si erreur
     */
    private function deleteTuplesOneTable(string $table, ?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // construction de la requête
        $requete = "delete from $table where ";
        foreach ($champs as $key => $value){
            $requete .= "$key=:$key and ";
        }
        // (enlève le dernier and)
        $requete = substr($requete, 0, strlen($requete)-5);   
        return $this->conn->updateBDD($requete, $champs);	        
    }
    
    /**
     * demande de suppression (delete) d'un livre dans la base de données
     * @param array|null $champs
     * @return 1 si la suppression a fonctionné, null si erreur
     */
    private function deleteLivre(?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // création d'une transaction
        $this->conn->beginTransaction();
        try{
            // suppression des informations du livre dans la table livre
            $champsTableLivre = [
                "id" => $champs["Id"]
            ];
            $resultat = $this->deleteTuplesOneTable("livre", $champsTableLivre);
            if($resultat === null){
                throw new Exception("Erreur lors de la suppression dans la table livre");
            }
            // suppression des informations du livre dans la table livres_dvd
            $champsTableLivres_dvd = [
                "id" => $champs["Id"]
            ];
            $resultat = $this->deleteTuplesOneTable("livres_dvd", $champsTableLivres_dvd);
            if($resultat === null){
                throw new Exception("Erreur lors de la suppression dans la table livres_dvd");
            }
            // suppresion des informations du livre dans la table document
            $champsTableDocument = [
                "id" => $champs["Id"]
            ];
            $resultat = $this->deleteTuplesOneTable("document", $champsTableDocument);
            if($resultat === null){
                throw new Exception("Erreur lors de la suppression dans la table document");
            }
            // Application des actions effectuées dans la transaction s'il n'y a pas d'erreur
            $this->conn->commit();
            return 1;
        }catch (Exception $e){
            // Annulation des actions effectuées lors de la transaction s'il y a une erreur
            $this->conn->rollBack();
            return null;
        }	        
    }
    
    /**
     * demande de suppression (delete) d'un dvd dans la base de données
     * @param array|null $champs
     * @return 1 si la suppression a fonctionné, null si erreur
     */
    private function deleteDvd(?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // création d'une transaction
        $this->conn->beginTransaction();
        try{
            // suppression des informations du dvd dans la table dvd
            $champsTableDvd = [
                "id" => $champs["Id"]
            ];
            $resultat = $this->deleteTuplesOneTable("dvd", $champsTableDvd);
            if($resultat === null){
                throw new Exception("Erreur lors de la suppression dans la table dvd");
            }
            // suppression des informations du dvd dans la table livres_dvd
            $champsTableLivres_dvd = [
                "id" => $champs["Id"]
            ];
            $resultat = $this->deleteTuplesOneTable("livres_dvd", $champsTableLivres_dvd);
            if($resultat === null){
                throw new Exception("Erreur lors de la suppression dans la table livres_dvd");
            }
            // suppresion des informations du dvd dans la table document
            $champsTableDocument = [
                "id" => $champs["Id"]
            ];
            $resultat = $this->deleteTuplesOneTable("document", $champsTableDocument);
            if($resultat === null){
                throw new Exception("Erreur lors de la suppression dans la table document");
            }
            // Application des actions effectuées dans la transaction s'il n'y a pas d'erreur
            $this->conn->commit();
            return 1;
        }catch (Exception $e){
            // Annulation des actions effectuées lors de la transaction s'il y a une erreur
            $this->conn->rollBack();
            return null;
        }	        
    }
    
    /**
     * demande de suppression (delete) d'une revue dans la base de données
     * @param array|null $champs
     * @return 1 si la suppression a fonctionné, null si erreur
     */
    private function deleteRevue(?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // création d'une transaction
        $this->conn->beginTransaction();
        try{
            // suppression des informations de la revue dans la table revue
            $champsTableRevue = [
                "id" => $champs["Id"]
            ];
            $resultat = $this->deleteTuplesOneTable("revue", $champsTableRevue);
            if($resultat === null){
                throw new Exception("Erreur lors de la suppression dans la table revue");
            }
            // suppresion des informations de la revue dans la table document
            $champsTableDocument = [
                "id" => $champs["Id"]
            ];
            $resultat = $this->deleteTuplesOneTable("document", $champsTableDocument);
            if($resultat === null){
                throw new Exception("Erreur lors de la suppression dans la table document");
            }
            // Application des actions effectuées dans la transaction s'il n'y a pas d'erreur
            $this->conn->commit();
            return 1;
        }catch (Exception $e){
            // Annulation des actions effectuées lors de la transaction s'il y a une erreur
            $this->conn->rollBack();
            return null;
        }	        
    }
 
    /**
     * récupère toutes les lignes d'une table simple (qui contient juste id et libelle)
     * @param string $table
     * @return array|null
     */
    private function selectTableSimple(string $table) : ?array{
        $requete = "select * from $table order by libelle;";		
        return $this->conn->queryBDD($requete);	    
    }
    
    /**
     * récupère toutes les lignes de la table Livre et les tables associées
     * @return array|null
     */
    private function selectAllLivres() : ?array{
        $requete = "Select l.id, l.ISBN, l.auteur, d.titre, d.image, l.collection, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from livre l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";		
        return $this->conn->queryBDD($requete);
    }	

    /**
     * récupère toutes les lignes de la table DVD et les tables associées
     * @return array|null
     */
    private function selectAllDvd() : ?array{
        $requete = "Select l.id, l.duree, l.realisateur, d.titre, d.image, l.synopsis, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from dvd l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";	
        return $this->conn->queryBDD($requete);
    }	

    /**
     * récupère toutes les lignes de la table Revue et les tables associées
     * @return array|null
     */
    private function selectAllRevues() : ?array{
        $requete = "Select l.id, l.periodicite, d.titre, d.image, l.delaiMiseADispo, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from revue l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";
        return $this->conn->queryBDD($requete);
    }	

    /**
     * récupère tous les exemplaires d'un document
     * @param array|null $champs 
     * @return array|null
     */
    private function selectExemplaires(?array $champs) : ?array{
        if(empty($champs)){
            return null;
        }
        if(!array_key_exists('id', $champs)){
            return null;
        }
        $champNecessaire['id'] = $champs['id'];
        $requete = "Select e.id, e.numero, e.dateAchat, e.photo, e.idEtat, et.libelle ";
        $requete .= "from exemplaire e join document d on e.id=d.id ";
        $requete .= "join etat et on e.idEtat=et.id ";
        $requete .= "where e.id = :id ";
        $requete .= "order by e.dateAchat DESC";
        return $this->conn->queryBDD($requete, $champNecessaire);
    }
    
    /**
     * récupère toutes les commandes d'un livre ou dvd
     * @param array|null $champs 
     * @return array|null
     */
    private function selectCommandeDocument(?array $champs) : ?array{
        if(empty($champs)){
            return null;
        }
        if(!array_key_exists('id', $champs)){
            return null;
        }
        $champNecessaire['id'] = $champs['id'];
        $requete = "Select cd.id, c.dateCommande, c.montant, cd.nbExemplaire, ";
        $requete .= "cd.idLivreDvd, cd.idSuivi, s.libelle ";
        $requete .= "from commandedocument cd join commande c on cd.id=c.id ";
        $requete .= "join suivi s on cd.idSuivi=s.id ";
        $requete .= "where cd.idLivreDvd = :id ";
        $requete .= "order by c.dateCommande DESC";
        return $this->conn->queryBDD($requete, $champNecessaire);
    }
    
    /**
     * récupère tous les abonnements d'une revue
     * @param array|null $champs 
     * @return array|null
     */
    private function selectAbonnement(?array $champs) : ?array{
        if(empty($champs)){
            return null;
        }
        if(!array_key_exists('id', $champs)){
            return null;
        }
        $champNecessaire['id'] = $champs['id'];
        $requete = "Select a.id, c.dateCommande, c.montant, a.dateFinAbonnement, a.idRevue ";
        $requete .= "from abonnement a join commande c on a.id=c.id ";
        $requete .= "where a.idRevue = :id ";
        $requete .= "order by c.dateCommande DESC";
        return $this->conn->queryBDD($requete, $champNecessaire);
    }
    
    /**
     * récupère tous les abonnements se finissant dans moins de 30 jours
     * @param array|null $champs 
     * @return array|null
     */
    private function selectAbonnementsFinissant() : ?array{
        $requete = "Select d.titre, a.dateFinAbonnement ";
        $requete .= "from document d join abonnement a on d.id=a.idRevue ";
        $requete .= "where a.dateFinAbonnement between now() and date_add(now(), interval 30 day) ";
        $requete .= "order by a.dateFinAbonnement ASC";
        return $this->conn->queryBDD($requete);
    }
    
}