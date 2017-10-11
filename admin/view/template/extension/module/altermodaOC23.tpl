<?php echo $header; ?><?php echo $column_left;
ini_set('display_errors',1);
error_reporting(E_ALL ^E_NOTICE);

?>
 
<div id="content" style="margin-left:50px;">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-category" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a>
      </div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo "Настройка модуля"; ?></h3>
        </div>
        <div class="panel-body">
            <div id="tabs" class="htabs">
              <a href="#tab-product"><?php echo $text_tab_product; ?></a>  
              <a href="#tab-author"><?php echo $text_tab_author; ?></a>
            </div>
        </div>
         <div id="tab-setting">
            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-category" class="form-horizontal">
                 <div id="tab-product">
                    <table class="form">
                      <tr>
                        <td>
                          <?php echo $entry_upload; ?>
                        </td>
                        <td>
                          <a id="button-upload" class="button"><?php echo $button_upload; ?></a>
                          <div class="message">
                            <span id="status" ></span>
                            <ul id="files" ></ul>
                          </div>
                        </td>
                      </tr>
                    </table>
                  </div>
            </form>
            
        </div>
        <div id="tab-author">
            <table>
                 <tr>
                   <td><span style="color: black;margin-left: 30px;font-size: 25px;">
                           <a href="http://isyms.ru/">created by Artur Legusha</a>
                       </span></td>
                  </tr>
            </table>    
        </div>
    </div>
  </div>
   <!-- start animation loading !-->
        <div class="ball"></div>
        <div class="ball1"></div>
        <!-- end animation loading !-->
</div>

<script type="text/javascript"><!--
    $('#tabs a').tabs();
 //--></script>

 <script>
   
    $(function(){
    var btnUpload=$('#button-upload');
    var status=$('#status');
    new AjaxUpload(btnUpload, {
      action: 'controller/extension/module/upload-file.php',
      name: 'uploadfile',
      onSubmit: function(file, ext){
        if (! (ext && /^(csv)$/.test(ext))){
          // extension is not allowed
          status.text('Поддерживаемые форматы csv');
          return false;
        }
         //вкл. анимацию загрузки
        $('.ball').css('display','block');
        $('.ball1').css('display','block');
      },
      onComplete: function(file, response){
        //On completion clear the status
        status.text('');
        //Add uploaded file to list
         if(response==="success"){
            recurAjax(); 
        }
      }
    });
  });

//рекурсивная загрузка товара, если сервер выбил по 503 ошибки то автоматом загружаем заново
function recurAjax(){
  $.ajax({
    url : 'index.php?route=extension/module/altermodaOC23/getNeedData&token=<?=$_GET["token"];?>',
    type : 'post',
    dataType:'text',
    data :{
      good: "good",
    },
    success:function(data){
      console.log(data);
      $('.ball').css('display','none');
      $('.ball1').css('display','none');

    },
    error:function (jqXHR) {
      if (jqXHR.status == 503) {
        //перед началом рекурсии делаем перерыв на 3 сек, что бы серв остыл :)  
        setTimeout(recurAjax(), 3000);
    }else{
      $('.ball').css('display','none');
      $('.ball1').css('display','none');
    }

      
    },
  });
}
 </script>
}


<?php echo $footer; ?>
