<?php
/**
 * Seed128 + CBC mode + PKCS5 암호화 & 복호화 운용 예제
  * 
 * @package Seed
 * @link http://qnibus.com/blog/how-to-use-seed128-for-php/
 *
 * @author Jong-tae Ahn <andy@qnibus.com>
 * @see http://lib.qnibus.com/seed128/ Demo Site
 * @since 1.0
 */

include 'class.crypto.php';
$crypto = new Crypto();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title> Seed 128 - CBC - PKCS5</title>
	<meta http-equiv="content-type" content="text/html; charset=EUC-KR"/>
	<meta name="generator" content="editplus" />
	<meta name="author" content="qnibus" />
	<meta name="keywords" content="" />
	<meta name="description" content="" />
</head>
<body>
	<h1>Seed128 + CBC + PKCS5</h1>
	<table border="1">
	<tr>
		<th>UserKey</th>
		<td>array(49,-97,101,-52,57,97,49,97,-49,101,98,49,50,-48,55,50)</td>
	</tr>
	<tr>
		<th>IV</th>
		<td>array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16)</td>
	</tr>
	<tr>
		<th>Encrypt</th>
		<td><?php echo $crypto->encrypt('여기는 안반장의 개발 노트입니다'); ?></td>
	</tr>
	<tr>
		<th>Decrypt</th>
		<td><?php echo $crypto->decrypt('4af61b7f11948a99e73ebaa9126fca440df78799b59a1f52c35d87b794a207fd'); ?></td>
	</tr>
	</table>
	
</body>
</html>
