<?php
ini_set('display_errors',1);
error_reporting(E_ALL ^E_NOTICE);

class ControllerextensionmoduleAltermodaOC23 extends Controller {
 
 
    public function index() {
        
        $this->load->language('extension/module/altermodaOC23');
        
        $this->document->addStyle('view/stylesheet/altermodaOC23.css');
        $this->document->addScript('view/javascript/jquery/tabs.js');
        $this->document->addScript('view/javascript/jquery/ajaxupload.3.5.js');
        
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('extension/module');

        $this->load->model('setting/setting');
         
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('altermodaOC23', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true)); 
            
        }

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_tab_product'] = $this->language->get('text_tab_product');
        $data['entry_save'] = $this->language->get('entry_save');
        
        $data['entry_upload'] = $this->language->get('entry_upload');
        $data['button_upload'] = $this->language->get('button_upload');
        
        $data['text_tab_author'] = $this->language->get('text_tab_author');
        
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        
       
 
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        }
        else {
            $data['error_warning'] = '';
        }
 

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/altermodaOC23', 'token=' . $this->session->data['token'], true)
        );
        $data['token'] = $this->session->data['token'];

        $data['action'] = $this->url->link('extension/module/altermodaOC23', 'token=' . $this->session->data['token'], true);

        $data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true);
 
        
        // Группы
        $this->load->model('customer/customer_group');
        $data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();
 
        $this->template = 'extension/module/altermodaOC23.tpl';
        $this->children = array(
            'common/header',
            'common/footer' 
        );

        $data['heading_title'] = $this->language->get('heading_title');
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('extension/module/altermodaOC23.tpl', $data));

        //$this->response->setOutput($this->render(), $this->config->get('config_compression'));
    }

    private function validate() {

        if (!$this->user->hasPermission('modify', 'extension/module/altermodaOC23')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;

    }
    
    //получаем нужную информацию из файла и передаем в метод для добавления  нового товара
    public function getNeedData(){
         //путь где хранится csv файл для import
        $csvData = 'controller/extension/module/uploads/products.csv';
        
        //делаем разбор  файла csv
        //$mas =  array_map('str_getcsv', file($csvData));
        $mas = file($csvData);

        for($i = 1; $i<count($mas); $i++){

                //сливаем 2 строки, что бы подтягивало и картинки тоже
               $this->importcsv(trim($mas[$i]));

        }
        
        return true;
    }
    
    
    
    
    //получаем данные с файла csv и работаем с ними
    public function importcsv($mas){
        
        //получаем массив нужных нам данных, для формирования массива по добавлению товара
        $csvExplode = explode(';', $mas);
        
        
        //отправляем массив на добавления в базу товара
        $this->searchID($csvExplode);
        
        return true;
        
        #TODO добавления товара в категорию из файла. Нужно взять название категории из файла 
        #(так как там есть ' и подобные символы надо их экранировать не помню функцию) взять это 
        #имя и иискать в базе если есть такая категория берем ее id и заносим в товар, если 
        #нету то пропускаем + нужно решить проблему почему неправильно делает разбор "," если 
        #есть такой разделитель то почему то переносит на новую строку из-за этого не 
        #угадать и теряется картинка. Нужно как то сделать, что бы на запятую не обращало 
        #внимание и под индексом 6 была ссылка на картинку + протестить на xdebug и xhprof 
        #модуль и по возможности исправить косяки
        
        #TODO надо исправить косяк в методе по загрузке картинки. Из-за него очень сильно большая 
        #нагрузка на сервер идет
  
    }
 
    //делаем поиск в таблице cache_id_product  на id  товара.
    //Если нету то добавляем товар.
    public function searchID($mas){
        
        //получаем доступ к модели модуля
        $this->load->model('tool/altermodaOC23');
        $findID = $this->model_tool_altermodaOC23->modelSearchID($mas[0]);

        //проверяем есть ли картинка
        if(!empty($mas[6])){
          
          //получаем первую ссылку из индекса $m[6] для скачивания  
          $imageMas = explode(',', $mas[6]);  
          
          $image = (!empty($this->downloadImage($imageMas[0]))) ? $this->downloadImage($imageMas[0]): "";

        }else{
           $image = "";
        }
        
        
        //проверяем существует ли цена продажи
        if(!empty($mas[4])){
	  $price = number_format(floatval($mas[4]), 2, '.', '');
        
        }else{
	  $price = 0;
        }
 
        
 
        $data = [
            'model'                 =>  "",
            'sku'                   =>  "",
            'upc'                   =>  "",
            'ean'                   =>  "",
            'jan'                   =>  "",
            'isbn'                  =>  "",
            'mpn'                   =>  "",
            'location'              =>  "",
            'quantity'              =>  0,
            'minimum'               =>  "",
            'subtract'              =>  "",
            'stock_status_id'       =>  "",
            'date_available'        =>  "",
            'manufacturer_id'       =>  "",
            'shipping'              =>  "",
            'price'                 =>  $price,
            'points'                =>  "",
            'weight'                =>  0,
            'weight_class_id'       =>  "",
            'length'                =>  "",
            'width'                 =>  "",
            'height'                =>  "",
            'length_class_id'       =>  "",
            'status'                =>  "",
            'tax_class_id'          =>  "",
            'sort_order'            =>  "",
            'status'                =>  1,
            'image'                 =>  $image,
            'product_description'   =>  [
                $this->config->get('config_language_id') =>[
                    'name'          => $mas[1],
                    'description'   =>  "",
                    'tag'           =>  "",
                    'meta_title'    =>  "",
                    'meta_description'  =>  "",
                    'meta_keyword'  =>  "",
                ],
            ],
            
            'keyword'               =>  "",
            'id'                    => $mas[0],
 

        ];
       
        
        //если нашли id товара то update, если нет то insert
        if(!empty($findID)){
            return true;
        }else{
            $this->insertProduct($data);
        }
        
        

    }
 
   //метод по добавлению нового товара
    public function insertProduct($data){
         
        //подгружаем стандартный метод опенкарт по добавлению нового товара
        $this->load->model('catalog/product');
        $product_id = $this->model_catalog_product->addProduct($data);
        
        //получаем доступ к модели модуля
        $this->load->model('tool/altermodaOC23');
        
        //делаем проверку если товар добавлен то заносим его id  в таблицу cache_id_product
        if(!empty($product_id)){
            $data = [
               'product_id' =>  $product_id,
               'cache_id'       =>  $data['id'],   
            ];
            
          //передаем массив в модель модуля  
         $this->model_tool_altermodaOC23->modelInsertCacheID($data);
        }
        
        return true;
    }
    
    
    
    //функция по скачиванию картинок из моего склада
   function downloadImage($url){
       
        //проверяем есть ли ссылка на картинку
        if(!empty($url)){
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);  
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            $response = curl_exec($ch);
            curl_close($ch);
            
            //создаем имя картинки
            $name = time().'.jpg';
            
            file_put_contents('../image/catalog/'.$name, $response);
            
            return 'catalog/'.$name;
        }else{
            return false;
        }
    }
 
}
?>