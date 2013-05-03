Seed128 + CBC + PKCS5
====================

SEED는 <a href="http://www.kisa.or.kr/" target="_blank">한국인터넷진흥원</a>에서 순수 국내기술로 개발된 암호화 알고리즘입니다.
이를 PHP용으로 전환해주신 <a href="http://cena.co.kr/mibany" target="_blank">mibany</a>님께 감사드립니다.
저는 이를 이용해 CBC 모드로 운용할 수 있는 클래스와 64bit 환경에서 작동되도록 업데이트하였습니다.
검색을 해보니 이렇게 구현하고자 하시는분들이 계시는 것 같은데 공개된 소스코드는 없는 것 같아서 저같이 고생하지 마시라고 공개하오니 요긴하게 사용하시기 바랍니다.

<ul>
    <li>x86/x64 플랫폼 지원</li>
    <li>CBC(Cipher-block chaining 운영모드 지원</li>
    <li>EUC-KR, UTF-8 문자셋 지원</li>
</ul>

====================

Ready
====================

<a href="http://docs.cena.co.kr/?mid=textyle&document_srl=15770" target="_blank">Seed 암호화 알고리즘 PHP로 구현 문서 참조</a>

<ol>
  <li>위 링크에서 class.seed.php를 다운로드 합니다.</li>
  <li>class.seed.php내 <code>EncRoundKeyUpdate1</code> 함수를 다음과 같이 변경합니다.</li>
  <li>변경할 class.seed.php 파일을 class.crypto.php 파일과 함께 원하는 곳으로 업로드합니다.</li>
  <li>class.crypto.php를 include해서 사용하시면 됩니다.</li>
</ol>

<pre>
private function EncRoundKeyUpdate1(&$K = array(), &$A, &$B, &$C, &$D, $Z)
{
	$T0 = $C;
	$C = ( $C << 8 ) ^ ( $D >> 24   & 0x000000ff );
	$D = ( $D << 8 ) ^ ( $T0 >> 24   & 0x000000ff );

	$T00 = (int) $A + (int) $C - (int) $this->KC[$Z];
	$T00 = $this->ConvertInt($T00);

	$T11 = (int) $B + (int) $this->KC[$Z] - (int) $D;
	$T11 = $this->ConvertInt($T11);

	$K[0] = $this->SS0[$this->GetB0($T00)] ^ $this->SS1[$this->GetB1($T00)] ^ $this->SS2[$this->GetB2($T00)] ^ $this->SS3[$this->GetB3($T00)];
	$K[1] = $this->SS0[$this->GetB0($T11)] ^ $this->SS1[$this->GetB1($T11)] ^ $this->SS2[$this->GetB2($T11)] ^ $this->SS3[$this->GetB3($T11)];
	
	// 64bit에서 정상적인 값을 반영하도록 추가
	$C = (int) $C; 
	if(PHP_INT_SIZE > 4) { 
		$C = $C << 32; 
		$C = $C >> 32; 
	}

	$D = (int) $D; 
	if(PHP_INT_SIZE > 4) { 
		$D = $D << 32; 
		$D = $D >> 32; 
	} 
	$K[0] = (int) $K[0]; 
	if(PHP_INT_SIZE > 4) { 
		$K[0] = $K[0] << 32; 
		$K[0] = $K[0] >> 32; 
	} 

	$K[1] = (int) $K[1]; 
	if(PHP_INT_SIZE > 4) { 
		$K[1] = $K[1] << 32; 
		$K[1] = $K[1] >> 32; 
	} 
}

</pre>

====================

<a href="http://lib.qnibus.com/seed128/" target="_blank">Demo</a>
====================

아래와 같이 암호화 혹은 복호화를 원하는 곳에 넣어서 사용하세요!

<pre>
&lt;?php
include 'class.crypto.php';
$crypto = new Crypto();
echo $crypto->encrypt('여기는 안반장의 개발 노트입니다');
echo $crypto->decrypt('856ac21e3960225b3e6bf39b084279312485e58b578de7d1d418f6128111a341');
?&gt;
</pre>


