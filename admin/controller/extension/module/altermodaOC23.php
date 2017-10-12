<?php
 
class ControllerextensionmoduleAltermodaOC23 extends Controller {
    
    //временное хранилище
    public $dataMas = array();
 
 
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
         
  
        //делаем разбор  файла csv
        $mas = file('controller/extension/module/uploads/products.csv');
 
        
        //убераем первый элемент массива (имена столбцов), начиная цикл со второго значения
        array_shift($mas);
        foreach($mas as $massiv){
            
            //получаем массив нужных нам данных, для формирования массива по добавлению товара
            $this->dataMas[] = explode(';', trim($massiv));

        }

        //делаем провекру если массив не пустой то заносим данные в базу
        if(!empty($this->dataMas)){
            
            //парсим файл и получаем нужные данные
             $this->importcsv();

            //добавляем новый товар
             $this->insertProduct();
        }
        
        //проверяем не пустая ли переменная с кэшем, если нет то запускаем 
        //функцию по скачиванию картинок
        if(!empty($this->image_cache)){
            $this->downloadImage();
        }
  
        return true;
    }
 
    //делаем поиск в таблице cache_id_product  на id  товара.
    //Если нету то добавляем товар.
    public function importcsv(){
        
        //получаем доступ к модели модуля
        $this->load->model('tool/altermodaOC23');

        //временный массив по хранению данных
        $cacheData = array();

        //делаем проверку есть ли в базе товар.Если есть удаляем из массива
        foreach($this->dataMas as $mas){

            $findID = $this->model_tool_altermodaOC23->modelSearchID($mas[0]);

            //если нашли id товара то удаляем из массива, если нет то заносим данные в новый массив
            if(!empty($findID)){
                unset($mas);
            }elseif(!empty($mas)){
                $cacheData[] = $mas;
            }

        }

        //удаляем массив данных со старого хранилища
        unset($this->dataMas);

        //массив данных для добавления  нового товара
        $data = array();
 
        //формируем массив с данными которые нужно занести в базу
        foreach ($cacheData as $mas){
            //проверяем есть ли картинка
            if(!empty($mas[6])){
              
                //получаем первую ссылку из индекса $m[6] для скачивания  
                $imageMas = explode(',', $mas[6]);  
              
                $imageUrl = $imageMas[0];

                //генерируем имя (без дублей, что бы было, первое значение это id записив в csv)
                $imageName =  $mas[0].'_'.time().'.jpg';

            }else{
                $image = "";
                $imageName = "";
                $imageUrl = "";
            }
            
            
            //проверяем существует ли цена продажи
            if(!empty($mas[4])){
          $price = number_format(floatval($mas[4]), 2, '.', '');
            
            }else{
          $price = 0;
            }
      
            $data[] = [
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
                'image'                 =>  'catalog/'.$imageName,
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
                'product_category'      =>[
                    'category_id'       =>  (!empty($this->findCategory($mas[3]))) ? $this->findCategory($mas[3]) : $this->addCategoryModule($mas[3]),  
                ],
                'product_store'     =>[
                    'store_id'          => $this->config->get('config_store_id'),
                ],
                'keyword'               =>  "",
                'id'                    => $mas[0],
                'image_url'             => $imageUrl,
                'image_name'            => $imageName,
     

            ];

        }

        $this->dataMas = $data;

        return true;
 
    }
 
   //метод по добавлению нового товара
    public function insertProduct(){
         
        //подгружаем стандартный метод опенкарт по добавлению нового товара
        $this->load->model('catalog/product');

        //получаем доступ к модели модуля
        $this->load->model('tool/altermodaOC23');

        //Добавляем новый товар так же добавляем в кэш добавленный товар
        foreach($this->dataMas as $data){

            $product_id = $this->model_catalog_product->addProduct($data);
        
            //делаем проверку если товар добавлен то заносим его id  в таблицу cache_id_product
            if(!empty($product_id)){
                $dat = [
                   'product_id'     =>  $product_id,
                   'cache_id'       =>  $data['id'],
                ];

                //передаем массив в модель модуля  
                $this->model_tool_altermodaOC23->modelInsertCacheID($dat);
    
                //когда товар занесли в базу то скачиваем картинку
                if(!empty($data['image_url']) && !empty($data['image_name'])){
                    $this->downloadImage($data['image_url'],$data['image_name']);
                }
 
            }
        }

        unset($this->dataMas);

        return true;
    }



    //функция по скачиванию картинок на хост
    public function downloadImage($image_url,$name){
        set_time_limit(0);

        $response = file_get_contents($image_url);
        file_put_contents('../image/catalog/'.$name, $response);
 
        return true;
     

    }
    
    //метод по формированию массива для создания категорий
    public function addCategoryModule($name){

        //подключаем модель категории
        $this->load->model('catalog/category');

        if(!empty($name)){
            //формируем массив на добавлении новой категории
            $data = [
                'parent_id'         =>  0,
                'top'               =>  1,
                'column'            =>  1,
                'sort_order'        =>  5,
                'status'            =>  1,
                'category_description'   => [
                    $this->config->get('config_language_id') =>[
                        'name'          => $name,
                        'description'   =>  "",
                        'meta_title'    =>  "",
                        'meta_description'  =>  "",
                        'meta_keyword'  =>  "",
                    ],
                ],
                'parent_id'         =>  0,
                'category_store'    =>[
                    'store_id'      => $this->config->get('config_store_id'),
                ],

             ];

             //получаем ид ново-добавленной категории
             $id = $this->model_catalog_category->addCategory($data);

            return $id;

        }else{
            return 0;
        }

        
    }


    //метод по поиску нужной категории (по имени категории)
    public function findCategory($name){
        
        //подключаем доступ до модели модуля
        $this->load->model('tool/altermodaOC23');

        if(!empty($name)){
            //получаем ид категории по имени
            $findID = $this->model_tool_altermodaOC23->findCategoryModel($name);

            //проверяем ответ
            if(!empty($findID['category_id'])){
                return $findID['category_id'];
            }else{
                return false;
            }

        }else{
            return 0;
        }
 
    }

    
    
 
}
?>