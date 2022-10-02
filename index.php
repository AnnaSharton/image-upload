<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мультизагрузка</title>
    <style>
        body {padding:30px;}
        p, input[type="file"], input[type="submit"]{margin-bottom:8px;}
        .error {border: 1px solid gray; background: #f59393; padding: 5px;}
        .border {border: 2px solid gray;}
    </style>
</head>
<body>
    <?php
    error_reporting(E_ALL);
    mb_internal_encoding("UTF-8");
        
    //задаю функцию для обрезки и показа фото с 3 параметрами - название файла, ширина, высота
    function showImage($image, $w, $h) {
        $ext=strtolower(pathinfo($image, PATHINFO_EXTENSION));//узнаю расширение файла и перевожу его в нижний регистр если оно окажется PNG/JPEG
        switch ($ext) {
        case 'jpeg': case 'jpg': //если у файла такие расширения
            $im = imagecreatefromjpeg($image);//создаю изображение из файла
            break;
        case 'png':
            $im = imagecreatefrompng($image);
            imageinterlace($im, false);
            break;
        default:
            return false;
        }
        $im2 = imagecrop($im, ['x' => 0, 'y' => 0, 'width' => $w, 'height' => $h]); //обрезаю
        switch ($ext) {
            case 'jpeg': case 'jpg': 
                imagejpeg($im2, $image); //вывожу изображения в браузер
                break;
            case 'png':
                imagesavealpha($im2, true);//чтобы у пнг был белый фон 
                imagepng($im2, $image);
                break;
        }
        echo '<img class="border" src=" '.$image.' " >';
    }

    //вывожу сообщения до формы:
    if (isset($_GET['add'])) {
        if      ($_GET['add']==='error0'){echo '<div class="error">Вы не загрузили файлы</div>';} //не выбраны файлы
        else if ($_GET['add']==='error1'){echo '<div class="error">Вы не выбрали главную фотографию</div>';} //без главной фото
        else if ($_GET['add']==='error2'){echo '<div class="error">Вы не выбрали дополнительные фото</div>';} //без дополнительных фото
    } else {}  
    ?>

  <form class="form" action="" method="post" enctype="multipart/form-data">
        <p>Главное фото</p>
        <input type="file" name="main-file" accept="image/*"> <!--указываю группу допустимых файлов - любые графические--> 
        <p>Другие фото</p> 
        <input type="file" name="files[]" multiple accept="image/*"><br>
        <input type="submit" name="submit" value="Загрузить">
    </form>
    <div>

    <?php 
    
    if (isset($_FILES['main-file']['tmp_name']) and isset($_FILES['files']['tmp_name'])){   
    $error=[]; //создаю массив для ошибок
        foreach ($_FILES['files']['tmp_name'] as $i => $name) { //обхожу массив файлов
            
            if (!is_uploaded_file($_FILES['files']['tmp_name'][$i]) and !is_uploaded_file($_FILES['main-file']['tmp_name']) and isset($_POST['submit'])) {
                $error[]= 1; //заношу в массив какой-нибудь элемент
                header('Location: index.php?add=error0');
            }
            else if (is_uploaded_file($_FILES['files']['tmp_name'][$i]) and !is_uploaded_file($_FILES['main-file']['tmp_name'])){
                $error[]= 2;
                header('Location: index.php?add=error1');
            }
            else if (!is_uploaded_file($_FILES['files']['tmp_name'][$i]) and is_uploaded_file($_FILES['main-file']['tmp_name'])){
                $error[]= 3;
                header('Location: index.php?add=error2');
            }
            else if (count($error)===0) {
                $fileNames = basename($_FILES['files']['name'][$i]); //получаю последний элемент пути как имя мультифайлов
                move_uploaded_file($_FILES['files']['tmp_name'][$i], $fileNames);//перемещаю файл из временной директории в постоянную
                $newArr[]=$fileNames;
                session_start();
                $_SESSION['data1']=$newArr;
                //showImage($fileNames, 150, 150);//вызываю функию показа картинки
                //header('Location: index.php?add=ok');
            } 
            ?> 
            </div>
            <div>  
            <?php
        }
        if (count($error)===0) { // то же для главного файла вне цикла foreach
            $newNameMain = basename($_FILES['main-file']['name']);
            move_uploaded_file($_FILES['main-file']['tmp_name'], $newNameMain);
            // showImage($newNameMain, 300, 300);
            session_start();
            $_SESSION['data']=$newNameMain;
            header('Location: index.php?add=ok');     
        } 
        ?>
        </div>
 
    <?php
    } 
    
    if (isset($_GET['add'])) {
        if ($_GET['add']==='ok'){
            session_start();
            $a=$_SESSION['data'];
            $arr=$_SESSION['data1'];
            showImage($a, 300, 300);
            foreach  ($arr as $v) {
                showImage($v, 150, 150);
            }
        }
    }
    ?>
</body>
</html>