<?php
ini_set('display_errors',1);
error_reporting(E_ALL ^E_NOTICE);
# Сохраняем отчет и генерируем ссылку для его просмотра
#include_once "/var/www/html/xhprof/xhprof_lib/utils/xhprof_lib.php";
#include_once "/var/www/html/xhprof/xhprof_lib/utils/xhprof_runs.php";
 
class ControllerextensionmoduleAltermodaOC23 extends Controller {

	//создаем массив для хранения данных о кэше картинок
	public $image_cache = array();
 
 
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
        
         // Профилируемый код
     #$xhprof_runs = new XHProfRuns_Default();
  
        //путь где хранится csv файл для import
        $csvData = 'controller/extension/module/uploads/products.csv';
    
        
        //делаем разбор  файла csv
        $mas = file($csvData);

        for($i = 1; $i<count($mas); $i++){

                //сливаем 2 строки, что бы подтягивало и картинки тоже
               $this->importcsv(trim($mas[$i]));

        }

            //вызываем функцию которая занесет весь кэш в переменную
            $this->CacheImage();

            //проверяем не пустая ли переменная с кэешем, если нет то запускаем 
            //функцию по скачиванию картинок
            if(!empty($this->image_cache)){
            	$this->downloadImage();
            }
            
             # Останавливаем профайлер после выполнения программы
          /*
                $xhprof_data = xhprof_disable();
                $run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_test");
                echo "Report: http://localhost/xhprof/xhprof_html/index.php?run=$run_id&source=xhprof_test";
                echo "\n";
           
           */
              
 
        return true;
    }
    
    
    
    
    //получаем данные с файла csv и работаем с ними
    public function importcsv($mas){
        
        //получаем массив нужных нам данных, для формирования массива по добавлению товара
        $csvExplode = explode(';', $mas);
        
        
        //отправляем массив на добавления в базу товара
        $this->searchID($csvExplode);
        
        return true;
 
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
          
           $imageUrl = $imageMas[0];

           //генерируем имя (без дублей, что бы было, первое значение это id записив в csv)
           $imageName =  $mas[0].'_'.time().'.jpg';

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
            
            'keyword'               =>  "",
            'id'                    => $mas[0],
            'image_url'             => $imageUrl,
            'image_name'            => $imageName,
 

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
               'product_id'     =>  $product_id,
               'cache_id'       =>  $data['id'],
               'image_url'      =>  $data['image_url'],
               'name'           =>  $data['image_name'],
             ];
            
          //передаем массив в модель модуля  
         $this->model_tool_altermodaOC23->modelInsertCacheID($data);

         //заносим кэш инфы картинки
         $this->model_tool_altermodaOC23->insertCacheImage($data);
        }
        
        return true;
    }
 
    //получаем данные о кэше и заносим в массив
    public function CacheImage(){
 		
 		//получаем доступ к модели модуля
        $this->load->model('tool/altermodaOC23');

        //получаем массив данных
        $image =  $this->model_tool_altermodaOC23->getImage();

        //проверяем не пустой ли нам прилетел результат с базы
        if(!empty($image)){
			$this->image_cache = $image;
        }

       return true;
 	}

 	//функция по скачиванию картинок на хост
 	public function downloadImage(){
 		//получаем доступ к модели модуля
        $this->load->model('tool/altermodaOC23');
 		
 		//проверяем есть ли картинки
        if(!empty($this->image_cache)){

        	$image = $this->image_cache;

            for($i=0; $i<count($image); $i++){
            
 				$response = file_get_contents($image[$i]["image_url"]);
     
                file_put_contents('../image/catalog/'.$image[$i]["name"], $response);
            }
 
  		}

  		//удаляем переменную с данными
  		unset($this->image_cache);
 		$this->model_tool_altermodaOC23->deleteCacheImage();

  		return true;
     

 	}
    
    
    #TODO удаляем весь кэш с таблицы (но не в цикле, а после вызова функции) и попробывать скачивать с помощью 
    #curl но это не точно. Так же нужно создать функцию которая будет проверять есть ли такая категория если 
    #есть то получаем ид и прописываем в товаре (делать это все с методо editProduct, что бы не нагружать 
    #сервак) ну или как то так или сразу добавить или при загрузке вытянуть все категории в массив где 
    #будет ключь это ид категории, а значение название и дальше делать поиск по значение если есть такой массив 
    #то берем его ключь и прописываем в добавление товара (назначение категории где ид товара это ключь массива). 
    #Так и нужно сделать !!!
    
 
}
?>