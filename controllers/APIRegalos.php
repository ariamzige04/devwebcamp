<?php

namespace Controllers;

use Model\Regalo;
use Model\Registro;

class APIRegalos {

    public static function index() {

        if(!is_admin()) {
            echo json_encode([]);
            return;
        }

        $regalos = Regalo::all();//toma todos los regalos

        //va ir dentificando cual fue el regalo del usuario presencial y lo va ir acumulando en "total"
        foreach($regalos as $regalo) {//itera sobre cada regalo
            $regalo->total = Registro::totalArray(['regalo_id' => $regalo->id, 'paquete_id' => "1"]);
        }//crea una llave nueva llamada total, toma el id del regalo, y (filtra por el paquet id) que eliguio el paqueta 1 (presencial) video 798
        //esto retorna un array completo

        
        echo json_encode($regalos);
        return;
        
    }
}