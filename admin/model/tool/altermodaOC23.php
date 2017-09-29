<?php

class ModelToolAltermodaOC23 extends Model {
 
    //функция для поиска в таблице id  добавленого товара
    public function modelSearchID($id){
        $query = $this->db->query("SELECT product_id FROM cache_id_product WHERE cache_id = '$id' ");
        return $query->row;
    }
    
    //после удачного добавления товара в базу, заносим id  товара и cache_id_product товара 
     public function modelInsertCacheID($data){
      $this->db->query('INSERT INTO `cache_id_product` SET product_id = ' . (int)$data["product_id"] . ', `cache_id` = "' . $data["cache_id"] . '"');  
      return true;
    }
 
}