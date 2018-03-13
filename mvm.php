<?php
function __autoload($class_name) {
	if (file_exists("lib/" . $class_name . '.php')) {
		require_once ("lib/" . $class_name . '.php');
		return;
	}elseif(file_exists("classes/" . $class_name . '.php')){
		require_once ("classes/" . $class_name . '.php');
		return;
	}
}

include("./lib/simple_html_dom.php");	
include("./csv/index.php");

$con = new ConPDO();

$site_id = $con->query('SELECT site_id FROM sites', PDO::FETCH_ASSOC)->fetch()['site_id'];

$product_description = $con->query('SELECT product_description FROM html  WHERE id='.$site_id, PDO::FETCH_ASSOC)->fetch()['product_description'];
$parse_link = $con->query('SELECT parse_link FROM html WHERE id='.$site_id, PDO::FETCH_ASSOC)->fetch()['parse_link'];
$xpath_product_link = $con->query('SELECT xpath_product_link FROM html WHERE id='.$site_id, PDO::FETCH_ASSOC)->fetch()['xpath_product_link'];
$xpath_product_description = $con->query('SELECT xpath_product_description FROM html WHERE id='.$site_id, PDO::FETCH_ASSOC)->fetch()['xpath_product_description'];
$xpath_title = $con->query('SELECT xpath_title FROM html WHERE id='.$site_id, PDO::FETCH_ASSOC)->fetch()['xpath_title'];
$xpath_img = $con->query('SELECT xpath_img FROM html WHERE id='.$site_id, PDO::FETCH_ASSOC)->fetch()['xpath_img'];
$xpath_main_img = $con->query('SELECT xpath_main_img FROM html WHERE id='.$site_id, PDO::FETCH_ASSOC)->fetch()['xpath_main_img'];
$product_category = $con->query('SELECT product_category FROM html WHERE id='.$site_id, PDO::FETCH_ASSOC)->fetch()['product_category'];
$product_url_category = $con->query('SELECT product_url_category FROM html WHERE id='.$site_id, PDO::FETCH_ASSOC)->fetch()['product_url_category'];
$pure_site_link_chk = $con->query('SELECT pure_site_link_chk FROM html WHERE id='.$site_id, PDO::FETCH_ASSOC)->fetch()['pure_site_link_chk'];
$pure_site_link = $con->query('SELECT pure_site_link FROM html WHERE id='.$site_id, PDO::FETCH_ASSOC)->fetch()['pure_site_link'];
$xpath_price = $con->query('SELECT xpath_price FROM html WHERE id='.$site_id, PDO::FETCH_ASSOC)->fetch()['xpath_price'];

Func::cleanDir("tempimage");

if(file_exists(realpath("data.csv"))){
	unlink(realpath("data.csv"));
}
fopen("data.csv", "a");

$csv = new CSV(realpath("data.csv"));

$csv->setCSV(Array("���������~URL ���������~�����~�������~��������~����~URL~�����������~�������~����������~����������~��������� [SEO]~�������� ����� [SEO]~�������� [SEO]~������ ����~�������������~�����~����������~���~��������� ��������~������� ���������~������ �� �����~������~��������"));

$k = 0;
$new_img = Array();
$main_arr = Array();

//MY CODE START

	$html = Func::parseSite($parse_link);
	
	for ($i = 0; $i < $html->getElementById("content")->getElementsByTagName("ul")->item(0)->getElementsByTagName("li")->length; $i++) {
		$main_arr["link"][] = $html->getElementById("content")->getElementsByTagName("ul")->item(0)->getElementsByTagName("li")->item($i)->getElementsByTagName("a")->item(0)->getAttribute("href");	
	}

	for ($i = 0; $i < $html->getElementById("content")->getElementsByTagName("ul")->item(0)->getElementsByTagName("li")->length; $i++) {
		$main_arr["title"][] = strip_tags(preg_replace( "/\r|\n/", "", mb_convert_encoding($html->getElementById("content")->getElementsByTagName("ul")->item(0)->getElementsByTagName("li")->item($i)->getElementsByTagName("div")->item(2)->getElementsByTagName("div")->item(0)->getElementsByTagName("h3")->item(0)->nodeValue, 'windows-1251', 'UTF-8')));
	}
	
	for ($i = 0; $i < $html->getElementById("content")->getElementsByTagName("ul")->item(0)->getElementsByTagName("li")->length; $i++) {
		$int = $html->getElementById("content")->getElementsByTagName("ul")->item(0)->getElementsByTagName("li")->item($i)->getElementsByTagName("div")->item(2)->getElementsByTagName("div")->item(0)->getElementsByTagName("div")->item(0)->getElementsByTagName("span")->item(1)->textContent;
		settype($int, "integer");
		$main_arr["price"][] = $int;
	}
	
	if (!file_exists("tempimage")) {
		mkdir("tempimage", 0777, true);
	}
	
	foreach($main_arr["link"] as $ln){
		$ht = Func::parseSite( $ln );
		$xpath = new DOMXpath($ht);
		$str = preg_replace( "/\r|\n/", "", mb_convert_encoding($xpath->query('//*[@id="content"]/div/div[2]/div/div[3]')->item(0)->nodeValue, 'windows-1251', 'UTF-8'));
		$s = preg_replace( "/: /", "=", $str);
		$s = preg_replace( "/;/", "&", $s);
		$main_arr["product_property"][] = $s;	
		
		for ($i = 0; $i < $xpath->query('//*[@id="content"]/div/div[1]/div/figure/div')->length; $i++) {
			if($xpath->query('//*[@id="content"]/div/div[1]/div/figure')->item(0)->getElementsByTagName("div")->item($i)->getElementsByTagName("a")->item(0)->getAttribute("href") == "") continue;
			
			preg_match("/[a-zA-z\-0-9() ]+.[a-zA-z\-0-9]+$/",$xpath->query('//*[@id="content"]/div/div[1]/div/figure')->item(0)->getElementsByTagName("div")->item($i)->getElementsByTagName("a")->item(0)->getAttribute("href"),$new_img);
			if (copy($xpath->query('//*[@id="content"]/div/div[1]/div/figure')->item(0)->getElementsByTagName("div")->item($i)->getElementsByTagName("a")->item(0)->getAttribute("href"),"tempimage/".$new_img[0])) {//$new_img[0]
				$main_arr["img"][$k][] = $new_img[0];	
			}
		}
		$category = mb_convert_encoding($product_category, 'windows-1251', 'UTF-8'); //��������� ������ "������������ �������/���������� � ��������/��������"
		$url_category = $product_url_category; //URL ���������
		$goods = $main_arr["title"][$k]; //����� "������� Dell Inspiron N411Z"
		$options = ""; //������� "��� �����"
		$description = preg_replace( "/\r|\n/", "", $main_arr["xpath_product_description"][$k]);//str_replace('{$goods}',$goods,mb_convert_encoding(mysql_result($product_description,0), 'windows-1251', 'UTF-8'))
		$price = $main_arr["price"][$k]; //���� "19000"
		$url = ""; //URL "noutbuk-dell-inspiron-n411z"
		$img = ""; //����������� "noutbuk-Dell-Inspiron-N411Z.png[:param:][alt=������� dell][title=������� dell]|noutbuk-Dell-Inspiron-N411Z-oneside.png[:param:][alt=�������"

		if(is_array($main_arr["img"][$k])){
			foreach($main_arr["img"][$k] as $im){
			$img .= $im."[:param:][alt=$goods][title=$goods]|";
			}
			$img = substr($img, 0, -1);
		} else {
			$img .= $main_arr["img"][$k]."[:param:][alt=$goods][title=$goods]";
		}
		
		$articul = ""; //������� "1000A"
		$count = "-1"; //���������� "-1 ��� �� ������"
		$activity = "1"; //���������� 1 ������� 0 ��������
		$title_seo = ""; //��������� [SEO]
		$kay_words = ""; //�������� ����� [SEO]
		$description_seo = ""; //�������� [SEO]
		$old_price = ""; //������ ����
		$reccomend = "0"; //�������������
		$new = "0"; //�����
		$sort = ""; //����������
		$weight = "0,27"; //��� "2,27"
		$bind_articul = ""; //��������� ��������
		$neibor_category = ""; //������� ���������
		$link_goods = ""; //������ �� �����
		$currency = "UAH"; //������
		$propertis = $main_arr["product_property"][$k];
		
		$csv->setCSV(array("$category~$url_category~$goods~$options~$description~$price~$url~$img~$articul~$count~$activity~$title_seo~$kay_words~$description_seo~$old_price~$reccomend~$new~$sort~$weight~$bind_articul~$neibor_category~$link_goods~$currency~$propertis"));
	
		$k++;
	}
	
		Func::printData($main_arr);
		exit();

//MY CODE END
$html = file_get_html($res);



//MY CODE START


//MY CODE END


foreach($html->find($xpath_product_link) as $element) {
	$main_arr["link"][] = $pure_site_link_chk == 1 ? $pure_site_link.$element->href : $element->href;
}

foreach($html->find($xpath_title) as $element) {
	$main_arr["title"][] = $element->plaintext;
}
if($xpath_price == "null"){
	for( $i=0; $i<count($html->find($xpath_product_link)); $i++) {
		$main_arr["price"][] = 0;
	}
}else{
	foreach($html->find($xpath_price) as $element) {
		$main_arr["price"][] = $element->plaintext;
	}
}



foreach($main_arr["link"] as $ln){
	
	$ht = file_get_html( $ln );
	
	if($xpath_product_description == "null"){
		$main_arr["xpath_product_description"][] = $product_description;
	}else{
		foreach($ht->find($xpath_product_description) as $element) {
			$main_arr["xpath_product_description"][] = $element->outertext;
		}
	}
	if($ht->find($xpath_img)){
		foreach($ht->find($xpath_img) as $el) {
		
			if($el->src==false) continue;
			
			//$img_link_src = preg_replace("/node-list/", "node", $el->src);
			
			preg_match("/[a-zA-z\-0-9() ]+.[a-zA-z\-0-9]+$/",$el->src,$new_img);

			if (copy($el->src,"tempimage/".$new_img[0])) {//$new_img[0]
				$main_arr["img"][$k][] = $new_img[0];	//$img_link_src $new_img[0]		
			}
		}
	}else{
		foreach($ht->find($xpath_main_img) as $el) {
		
			if($el->src==false) continue;
			
			//$img_link_src = preg_replace("/node-list/", "node", $el->src);
			
			preg_match("/[a-zA-z\-0-9() ]+.[a-zA-z\-0-9]+$/",$el->src,$new_img);
			if (copy($el->src,"tempimage/".$new_img[0])) {//$new_img[0]
				$main_arr["img"][$k][] = $new_img[0];		
			}			
		}
	}
	
	$category = mb_convert_encoding($product_category, 'windows-1251', 'UTF-8'); //��������� ������ "������������ �������/���������� � ��������/��������"
	$url_category = $product_url_category; //URL ���������
	$goods = mb_convert_encoding($main_arr["title"][$k], 'windows-1251', 'UTF-8'); //����� "������� Dell Inspiron N411Z"
	$options = ""; //������� "��� �����"
	$description = mb_convert_encoding(preg_replace( "/\r|\n/", "", $main_arr["xpath_product_description"][$k] ), 'windows-1251', 'UTF-8');//str_replace('{$goods}',$goods,mb_convert_encoding(mysql_result($product_description,0), 'windows-1251', 'UTF-8'))
	$price = substr($main_arr["price"][$k], 0, -2); //���� "19000"
	$url = ""; //URL "noutbuk-dell-inspiron-n411z"
	$img = ""; //����������� "noutbuk-Dell-Inspiron-N411Z.png[:param:][alt=������� dell][title=������� dell]|noutbuk-Dell-Inspiron-N411Z-oneside.png[:param:][alt=�������"

	if(is_array($main_arr["img"][$k])){
		foreach($main_arr["img"][$k] as $im){
		$img .= $im."[:param:][alt=$goods][title=$goods]|";
		}
		$img = substr($img, 0, -1);
	} else {
		$img .= $main_arr["img"][$k]."[:param:][alt=$goods][title=$goods]";
	}
	
	$articul = ""; //������� "1000A"
	$count = "-1"; //���������� "-1 ��� �� ������"
	$activity = "1"; //���������� 1 ������� 0 ��������
	$title_seo = ""; //��������� [SEO]
	$kay_words = ""; //�������� ����� [SEO]
	$description_seo = ""; //�������� [SEO]
	$old_price = ""; //������ ����
	$reccomend = "0"; //�������������
	$new = "0"; //�����
	$sort = ""; //����������
	$weight = "0,27"; //��� "2,27"
	$bind_articul = ""; //��������� ��������
	$neibor_category = ""; //������� ���������
	$link_goods = ""; //������ �� �����
	$currency = "UAH"; //������

	$pr = array(
		 "PB\/SB" => "������������ ������ / ������� ������",
		 "MACC" => "������� ������",
		 "AB" => "������ ������",
		 "SN\/CP" => "������� ������ / ������������ ����",
		 "MBN" => "������� ������ �����",
		 "White" => "�����",
		 "MOC" => "������� ������ ����",
		 "MA" => "������� ��������",
		 "MC" => "������� ����",
		 "BN\/SBN" => "������ ������ / ������� ������ ������",
		 "BLACK" => "������",
		 "CP" => "������������ ����",
		 "PCF" => "������������ ������",
		 "MACC\/PCF" => "������ ������/��������� ������",
		 "MCF" => "������� ������ ������",
		 "SN" => "������� ������",
		 "SS" => "����������� �����",
		 "BN" => "������ ������",
	);
	
	foreach($pr as $p => $v){
		if(preg_match("/\s".$p."$/",$main_arr["title"][$k])){
			$propertis = "���� ��������=$v"; //��������
		}else{
			$propertis = "";
		}
	}

	$csv->setCSV(array("$category~$url_category~$goods~$options~$description~$price~$url~$img~$articul~$count~$activity~$title_seo~$kay_words~$description_seo~$old_price~$reccomend~$new~$sort~$weight~$bind_articul~$neibor_category~$link_goods~$currency~$propertis"));
	
	$k++;
}

	
Func::printData($main_arr);
$directory = "./tempimage";    // ����� � �������������
$allowed_types=array("jpg", "png", "gif");  //���������� ���� �����������
$file_parts = array();
$ext="";
$title="";
$i=0;
//������� ������� �����
$dir_handle = @opendir($directory) or die("������ ��� �������� ����� !!!");
while ($file = readdir($dir_handle))    //����� �� ������
  {
  if($file=="." || $file == "..") continue;  //���������� ������ �� ������ �����
  $file_parts = explode(".",$file);          //��������� ��� ����� � ��������� ��� � ������
  $ext = strtolower(array_pop($file_parts));   //��������� �������� - ��� ����������
  if(in_array($ext,$allowed_types))
  {
 $i++;
  }
  $images[] = $file;
  }

closedir($dir_handle);  //������� �����

$error = "";
if(isset($_POST['createpdf']))
{

$file_folder = "tempimage/"; // ����� � �������
if(extension_loaded('zip'))
{
if(isset($images) and count($images) > 0)
{
// ��������� ��������� �����
$zip = new ZipArchive(); // ���������� ���������� zip
$zip_name = time().".zip"; // ��� �����
if($zip->open($zip_name, ZIPARCHIVE::CREATE)!==TRUE)
{

$error .= "* Sorry ZIP creation failed at this time";
}
foreach($images as $file)
{
$zip->addFile($file_folder.$file); // ��������� ����� � zip �����
}
$zip->close();
if(file_exists($zip_name))
{
// ����� ���� �� ����������
header('Content-type: application/zip');
header('Content-Disposition: attachment; filename="'.$zip_name.'"');
readfile($zip_name);
// ������� zip ���� ���� �� ����������
unlink($zip_name);
}

}
else
$error .= "* Please select file to zip ";
}
else
$error .= "* You dont have ZIP extension";
}
print_r("<a href='/index.php'>Home</a><br>");
print_r("<a href='/data.csv'>Download data.csv parser file</a><bt>");
print_r("<form name='zips' method='post'><p><input type='submit' name='createpdf' value='Download Images as ZIP' /></p></form>");
?>