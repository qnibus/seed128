Seed128 + CBC + PKCS5
====================

<a href="http://www.kisa.or.kr/" target="_blank">한국인터넷진흥원</a>에서 순수 국내기술로 개발된 암호화 알고리즘을 PHP용으로 전환해 CBC 모드로 운용할 수 있는 클래스를 제공하고자 합니다.

<ul>
    <li>x86/x64 플랫폼 지원</li>
    <li>CBC(Cipher-block chaining 운영모드 지원</li>
    <li>문자셋 지원</li>
</ul>

====================

Usage
====================

<a href="http://docs.cena.co.kr/?mid=textyle&document_srl=15770" target="_blank">Seed 암호화 알고리즘 PHP로 구현 문서 참조</a>

<ol>
  <li>위 링크에서 class.seed.php를 다운로드 합니다.</li>
	<li></li>
</ol>

Delegate, using the contact information processing
<pre>
&lt;?php
include 'class.crypto.php';
 $crypto = new Crypto();
echo $crypto->encrypt('여기는 안반장의 개발 노트입니다');
echo $crypto->decrypt('856ac21e3960225b3e6bf39b084279312485e58b578de7d1d418f6128111a341');
?&gt;
</pre>

====================

Demo
====================

<a href="http://lib.qnibus.com/seed128/" target="_blank">http://lib.qnibus.com/seed128/</a>
