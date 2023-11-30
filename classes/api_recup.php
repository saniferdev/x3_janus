<?php

Class API{

    public $link;
    public $url;
    public $key;


    public function getRest($url,$key){

        //$date = date("d-m-Y H:i:s",(int)$this->getDate());

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_SSL_VERIFYPEER => 0,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{
            "Receipts":[
                {
                    "receipt_number" : [
                        {
                            "receipt_number":"0124030003473"
                        }
                    ]
                }
            ]
          }',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Token: '.$key,
            'Cookie: cisession=T0%2FSVqUcM%2F0BmvSwkS%2BFdUOFcW46M10FzpG23ut7rSi%2BJV9Ilcv1GT4fuIHg5hLZg3rw4VlYdkQ2h7Jy7DqnpO%2FWXwnyV%2FMEuLblPBep8ZWAq07DR29cIObtHSjzSCHDqRQhJf0S0YNebeLjet5DtljfmOvii1a6h%2BUuZa1nxEBUvJF6HVfKwaxwB6eI4kh3jDSGv%2Fao69XqYbxkZ%2FV3ZtlWdtKaEejdxKNh4fSyX1ZBVCiNZTcRNtX%2BCyeHEeIJkt9ZXghor9wpSRIP9%2FF%2BYBdUO%2Bi9gwfnnQeZqEB2ibXSAsCPBgPtLPwHZc827FnfhT9qMuqCfPgH73%2FPYu8zmxcK%2BzTR58iL1YueYfD3zf9GNObqTgRKwl9XJAfHRvMPf97c27e8f44a52351baf112a8f991ca52cdb245d'
          ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;

    }


    public function getDate(){
        $queryParams    = array();
        $queryOptions   = array("Scrollable" => SQLSRV_CURSOR_KEYSET);
        $query          = "SELECT CAST(dates AS smalldatetime) as datt FROM passerelle WHERE titre ='factures_ventes_stock'";

        $result = sqlsrv_query($this->link, $query, $queryParams, $queryOptions);
        if ($result == FALSE){
          return false;
        }
        elseif (sqlsrv_num_rows($result) == 0) {
          return false;
        }
        else
        {
          $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

          $d   = $row['datt']->format("d-m-Y H:i:s");
          return date("Y-m-d H:i:s",strtotime($d.'-3hours'));
        }
    }

    public function getDO_Piece($receipt_number){
        $queryParams    = array();
        $queryOptions   = array("Scrollable" => SQLSRV_CURSOR_KEYSET);
        $query          = "SELECT * FROM facture_entete WHERE DO_Piece ='$receipt_number'";

        $result = sqlsrv_query($this->link, $query, $queryParams, $queryOptions);
        if ($result == FALSE){
          return false;
        }
        elseif (sqlsrv_num_rows($result) == 0) {
          return 0;
        }
        else
        {
          return 1;
        }
    }

    public function updateDate(){
        $query  = " UPDATE passerelle SET dates = GETDATE() WHERE titre = 'factures_ventes_stock'  ";
        if(sqlsrv_query($this->link, $query)) return true;
        else return var_dump(sqlsrv_errors());
    }

    public function deleteFacture($id){
        $query  = "
                DELETE FROM facture_entete WHERE DO_Piece = '".$id."';
                DELETE FROM facture_ligne WHERE DO_Piece = '".$id."'; ";
        if(sqlsrv_query($this->link, $query)) return true;
        else return var_dump(sqlsrv_errors());
    }

    public function insertDocument_Entete($receipt_number,$created_date,$orderby_code,$shop_code,$orderby_last_name,$orderby_first_name,$orderby_adr1,$orderby_phone,$DO_Provenance){
        
        $query  = "
                SET NOEXEC OFF
                SET ANSI_WARNINGS ON
                SET XACT_ABORT ON
                SET IMPLICIT_TRANSACTIONS OFF
                SET ARITHABORT ON
                SET NOCOUNT ON
                SET QUOTED_IDENTIFIER ON
                SET NUMERIC_ROUNDABORT OFF
                SET CONCAT_NULL_YIELDS_NULL ON
                SET ANSI_NULLS ON
                SET ANSI_PADDING ON
                

                DECLARE @error int = 0;
                DECLARE @ErrorMessage NVARCHAR(4000);  
                DECLARE @ErrorSeverity INT;  
                DECLARE @ErrorState INT;  

                BEGIN

                     BEGIN TRY

                         BEGIN TRANSACTION TR_facture_entete_INS
                            INSERT INTO 
                                facture_entete(
                                    [DO_Type]
                                   ,[DO_Piece]
                                   ,[DO_Date]
                                   ,[DO_Ref]
                                   ,[DO_Tiers]
                                   ,[CO_No]
                                   ,[DO_Souche]
                                   ,[DO_Provenance]
                                   ,[statut]
                                   ,[DO_Coord01]
                                   ,[DO_Coord02]
                                   ,[DO_Coord03]
                                   ,[DO_Coord04]                                
                                )
                                VALUES(
                                    6
                                    ,'".$receipt_number."'
                                    ,'".$created_date."'
                                    ,''
                                    ,'".$orderby_code."'
                                    ,NULL
                                    ,'".$shop_code."'
                                    ,'".$DO_Provenance."'
                                    ,0 
                                    ,'".str_replace("'", "''", $orderby_first_name)."'
                                    ,'".str_replace("'", "''", $orderby_last_name)."'
                                    ,'".str_replace("'", "''", $orderby_adr1)."'
                                    ,'".str_replace("'", "''", $orderby_phone)."'                    
                                );

                        COMMIT TRANSACTION TR_facture_entete_INS
                    END TRY
                    BEGIN CATCH
                        ROLLBACK TRANSACTION TR_facture_entete_INS

                        PRINT 'ROLLBACK TR_facture_entete_INS'
                        SELECT   
                                    @ErrorMessage = ERROR_MESSAGE(),  
                                    @ErrorSeverity = ERROR_SEVERITY(),  
                                    @ErrorState = ERROR_STATE();  

                        RAISERROR (
                            @ErrorMessage,
                            @ErrorSeverity,
                            @ErrorState
                        ); 
                    END CATCH
                END  ";
                
        if(sqlsrv_query($this->link, $query)) return $receipt_number;
        else{
            return $query;
        }
        //echo $query;
    }

    public function insertDocument_Ligne($receipt_number,$created_date,$orderby_code,$ligne,$reference,$quantity,$depot){
        
        $query  = "
                SET NOEXEC OFF
                SET ANSI_WARNINGS ON
                SET XACT_ABORT ON
                SET IMPLICIT_TRANSACTIONS OFF
                SET ARITHABORT ON
                SET NOCOUNT ON
                SET QUOTED_IDENTIFIER ON
                SET NUMERIC_ROUNDABORT OFF
                SET CONCAT_NULL_YIELDS_NULL ON
                SET ANSI_NULLS ON
                SET ANSI_PADDING ON
                

                DECLARE @error int = 0;
                DECLARE @ErrorMessage NVARCHAR(4000);  
                DECLARE @ErrorSeverity INT;  
                DECLARE @ErrorState INT;  

                BEGIN

                     BEGIN TRY
                        BEGIN TRANSACTION TR_facture_ligne_INS
                            INSERT INTO
                                facture_ligne(
                                    [DO_Type]
                                   ,[CT_Num]
                                   ,[DO_Piece]
                                   ,[DO_Date]
                                   ,[DL_Ligne]
                                   ,[DO_Ref]
                                   ,[AR_Ref]
                                   ,[DL_Design]
                                   ,[DL_Qte]
                                   ,[CO_No]
                                   ,[DE_No]
                                   ,[DL_MontantHT]
                                   ,[DL_MontantTTC]
                                   ,[statut]
                                   ,[DL_QteP]
                                )
                                VALUES(
                                    6
                                    ,'".$orderby_code."'
                                    ,'".$receipt_number."'
                                    ,'".$created_date."'
                                    ,'".$ligne."'
                                    ,''
                                    ,'".$reference."'
                                    ,( SELECT TOP 1 AR_Design FROM article WHERE AR_Ref_New = '".$reference."' )
                                    ,'".$quantity."'
                                    ,''
                                     ,'".$depot."'
                                    ,0
                                    ,0
                                    ,0
                                    ,0
                                );
                        COMMIT TRANSACTION TR_facture_ligne_INS
                    END TRY
                    BEGIN CATCH
                        ROLLBACK TRANSACTION TR_facture_ligne_INS

                        PRINT 'ROLLBACK TR_facture_ligne_INS'
                        SELECT   
                                    @ErrorMessage = ERROR_MESSAGE(),  
                                    @ErrorSeverity = ERROR_SEVERITY(),  
                                    @ErrorState = ERROR_STATE();  

                        RAISERROR (
                            @ErrorMessage,
                            @ErrorSeverity,
                            @ErrorState
                        ); 
                    END CATCH
                END  ";
                
        if(sqlsrv_query($this->link, $query)) return $receipt_number;
        else{
            return $query;
        }
        //echo $query;
    }

}
?>