<meta http-equiv='refresh' content='3; url=http://sarybai.lh1.in'>
<meta charset="UTF-8" />

<?php

//В переменную $token нужно вставить токен, который нам прислал @botFather
$token = "5918421735:AAEd3GhHxJgjCdrG8vIxaNyuhVbskPiSRTI";

//Сюда вставляем chat_id
$chat_id = "-703672752";

//Определяем переменные для передачи данных из нашей формы
if ($_POST['act'] == 'order') {
    $present = ($_POST['present']);
    //$phone = ($_POST['phone']);



//Собираем в массив то, что будет передаваться боту
    $arr = array(
        'Мои пожелания:' => $present
       // 'Телефон:' => $phone
    );

//Настраиваем внешний вид сообщения в телеграме
    foreach($arr as $key => $value) {
        $txt .= "<b>".$key."</b> ".$value."%0A";
    };

//Передаем данные боту

 $sendToTelegram = fopen("https://api.telegram.org/bot{$token}/sendMessage?chat_id={$chat_id}&parse_mode=html&text={$txt}","r");
    

//Выводим сообщение об успешной отправке


 if ($sendToTelegram) {
        //alert('Спасибо! Ваша заявка принята. Мы свяжемся с вами в ближайшее время.');
        //echo "<center><h1>Спасибо! Мы записали Ваш e-mail.</h1></center>";
      	

?>
  
  <script language="javascript" type="text/javascript">
    alert('Санта пошел запрягать оленей! Надеемся, ничего не перепутает.');
    window.location = "http://sarybai.lh1.in";
  </script>
  
<?php
    }

//А здесь сообщение об ошибке при отправке
    else {
       // alert('Что-то пошло не так. Попробуйте отправить форму ещё раз.');
       //echo "Ошибка, сообщение не отправлено! Возможно, проблемы на сервере";

?>

    <script language="javascript" type="text/javascript">
    alert('Упс, что-то пошло не так. Возможно проблемы на сервере.');
    window.location = "http://sarybai.lh1.in";
    </script>
  
<?php
	
    }
}

?>




