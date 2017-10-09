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

    //заносим сюда кэш инфы о картинки продукта
    public function insertCacheImage($data){
      $this->db->query('INSERT INTO `cache_image` SET `image_url` = "' . $data["image_url"] . '", `name`= "'.$data["name"].'", `product_id` = "' . $data["product_id"] . '"');  
      return true;

    }

     //получаем инфу кэша с таблици
    public function getImage(){
      $query = $this->db->query('SELECT image_url, name  FROM cache_image');
        return $query->rows;
    }

    //очищаем кэш каринок
    public function deleteCacheImage(){
      $this->db->query('TRUNCATE TABLE cache_image');

      return true;
    }
 
}