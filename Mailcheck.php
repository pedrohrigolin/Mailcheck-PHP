<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Mailcheck{

        public function __construct() {}

        public function check($email, $type = false){

            $regex = '/^[a-zA-Z0-9._%+-çÇ]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';

            if($type !== false){

                if($type === 1){
                    $regex = '/^[a-zA-Z0-9._%+-çÇ]+@[a-zA-Z0-9.-]+(.com|.com.br|.br)$/';
                }
                else if($type === 2){
                    $regex = '/^[a-zA-Z0-9._%+-çÇ]+@(gmail.com|outlook.com|outlook.com.br|hotmail.com|hotmail.com.br|live.com|live.com.br|yahoo.com|yahoo.com.br|terra.com|terra.com.br|icloud.com|estudante.ufscar.br|uol.com.br|myyahoo.com|myyahoo.com.br)$/';
                }
                else if(is_array($type)){

                    $regex = '/^[a-zA-Z0-9._%+-çÇ]+@(';

                    foreach($type as $index => $key){
                        $key = preg_replace('/[^a-zA-Z.]/', "", $key);
                        $regex = $regex.$key.'|';
                    }

                    //Removendo o '|' da última posição e colocando o ')$/'.
                    $regex = substr_replace($regex, ")$/", strlen($regex) - 1);
                
                }
                else if(is_string($type)){

                    $type = str_replace(",", "|", $type);
                    $type = preg_replace('/[^a-zA-Z|.]/', "", $type);

                    if(strrpos($type, '|') === strlen($type) - 1){

                        $type = substr_replace($type, "", strlen($type) - 1); 
                        
                    }
                        
                    if(strpos($type, '|') === 0) {
                            
                        $type = substr_replace($type, "", 0, 1);
                        
                    }
               
                    $regex = '/^[a-zA-Z0-9._%+-çÇ]+@('.$type.')$/';

                }
                else{
                    return false;
                }

            }
            
            if (preg_match($regex, $email)) {
                return true;
            } else {
                return false;
            }
            
        }

    }

?>