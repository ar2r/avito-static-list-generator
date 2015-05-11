<? set_time_limit(150);
error_reporting(0);
ob_start();?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Expires" content="Fri, Jan 01 1900 00:00:00 GMT">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Cache-Control" content="no-cache">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Lang" content="en">
<meta name="author" content="">
<meta name="description" content="">
<meta name="keywords" content="">
<meta name="creation-date" content="01/01/2009">
<meta name="revisit-after" content="15 days">
<title>Мои объявления на Avito.ru</title>
<link rel="stylesheet" type="text/css" href="my.css">

    <!-- Add jQuery library -->
    <script type="text/javascript" src="lib/jquery-1.9.0.min.js"></script>

    <!-- Add mousewheel plugin (this is optional) -->
    <script type="text/javascript" src="lib/jquery.mousewheel-3.0.6.pack.js"></script>

    <!-- Add fancyBox main JS and CSS files -->
    <script type="text/javascript" src="source/jquery.fancybox.js?v=2.1.4"></script>
    <link rel="stylesheet" type="text/css" href="source/jquery.fancybox.css?v=2.1.4" media="screen" />

    <!-- Add Button helper (this is optional) -->
    <link rel="stylesheet" type="text/css" href="source/helpers/jquery.fancybox-buttons.css?v=1.0.5" />
    <script type="text/javascript" src="source/helpers/jquery.fancybox-buttons.js?v=1.0.5"></script>

    <!-- Add Thumbnail helper (this is optional) -->
    <link rel="stylesheet" type="text/css" href="source/helpers/jquery.fancybox-thumbs.css?v=1.0.7" />
    <script type="text/javascript" src="source/helpers/jquery.fancybox-thumbs.js?v=1.0.7"></script>

    <!-- Add Media helper (this is optional) -->
    <script type="text/javascript" src="source/helpers/jquery.fancybox-media.js?v=1.0.5"></script>

</head>
<body>

<?
include("config.php");
include("phpQuery-onefile.php");
include("s_http.php");
echo "<b>Последнее обновление:".date('Y-m-d H:i:s').". Вопросы по вещам отправляйте на *@mail.ru</b><br><br>";
?>
<table CELLSPACING="0" CELLPADDING="0">
<?
$http = new s_http();
$http->init();
//echo "Load 01";
$http->referer = "http://www.avito.ru";
$http->get('http://www.avito.ru/profile');
$http->data();

$http->referer = "http://www.avito.ru/profile";
if( $http->post('http://www.avito.ru/profile','login='.$LOGIN.'&password='.$PASSWORD.'&submit=logon') ){
    $index = $http->data();
    $document = phpQuery::newDocument($index);
    //get LINKS
    $arPages=array();
    $arPages[] = $index;

    //$pages = $document->find('a[class=page_list-link]');
	$pages = $document->find('a[href*=rossiya?p=]');
    $arPageHrefs=array();
    foreach($pages as $page){
        $page = pq($page);
		$temp_str = "http://www.avito.ru".$page->attr("href");
        $arPageHrefs[ md5($temp_str) ] = $temp_str;
    }
	
	//print_r($arPageHrefs);
	//die();

    foreach($arPageHrefs as $page_href){
        sleep(rand(2,3));
        $http->get($page_href);
        $arPages[] = $http->data();
    }

    // Найти ссылки и заголовки + картинки
    foreach($arPages as $page){
        $document = phpQuery::newDocument($page);
        $divs = $document->find('div[class*=t_p_i profile-item-active]');
        foreach ($divs as $div) {
            $div = pq($div);

            $thumb = $div->find('img[src*=jpg]')->attr('src');

            $link = $div->find('a[title*=Перейти]');
            $item_href = 'http://www.avito.ru/'.$div->find('a[title*=Перейти]')->attr('href');

			sleep(2);
            // Выдираем полную ссылку на картинки
            $http->get($item_href);
            $item_pq = phpQuery::newDocument($http->data());

            $description = $item_pq->find('dd[id=desc_text]')->html();
            $title = $item_pq->find('h1[class*=p_i_ex_title]')->html();

            $arImages = array();
            $item_images = $item_pq->find('div[class*=gallery]')->find('div[class*=ll]');
            foreach($item_images as $ii){
                $pq_img = pq($ii);
                $arImages[] = array(
                    'full'     => $pq_img->find('a')->attr('href'),
                    'thumb' => $pq_img->find('img[class=thumb]')->attr('src')
                );
            }

            $full_one_img = $item_pq->find('td[class*=="big-picture]')->find('img')->attr('src');
            if(!$arImages && $thumb){
                $arImages[] = array(
                    'full'=>$full_one_img,
                    'thumb'=>$thumb,
                );
            }

            $link = str_replace('href="/', 'target="blank" href="http://www.avito.ru/',$link);
            $price = $div->find('p[class=t_p_price]');
            ?>
            <tr>
                <td><div class="price"><?=$price?></div></td>
                <td><?=$link?>
                <i><?=$description?></i>
                <?foreach($arImages as $i){?>
                    <a class="fancybox" rel="gallery1" title="<?=$title?>" href="<?=$i['full']?>"><img src="<?=$i['thumb']?>"></a>&nbsp;
                <?}?>

                </td>


            </tr>
            <?
        }

    }


}
else{
    // Покажем последнюю ошибку
    echo "Load 999";
    echo $http->error();
}
?>
</table>
<?
file_put_contents('index.html', ob_get_contents());
?>
</body>
</html>

