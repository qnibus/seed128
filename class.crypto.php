<?php
/**
 * Crypto Class
 * Seed128 + CBC mode + PKCS5 암호화 & 복호화 운용 클래스
 * 본 클래스는 mibany님이 변환하신 class.seed.php가 반드시 있어야 사용하실 수 있습니다.
 * 다운로드는 http://docs.cena.co.kr/textyle/15770 여기서 해주시면 됩니다.
 * 
 * @package Seed
 * @link http://qnibus.com/blog/how-to-use-seed128-for-php/
 *
 * @author Jong-tae Ahn <andy@qnibus.com>
 * @see http://lib.qnibus.com/seed128/ Demo Site
 * @since 1.0
 */

include 'class.seed.php';
class Crypto
{
  private $serverEncoding = 'EUC-KR'; // 서버의 인코딩을 설정하세요
	private $innerEncoding = 'EUC-KR'; // 내부적으로 처리할 인코딩을 설정하세요
	private $block			= 16; // 블록의 사이즈를 설정하세요

	/**
	 * 암호통신에 사용할 키값과 IV(Initialization Vector)를 설정하시고,
	 * 절대로 키가 외부로 유출되지 않도록 유념하시기 바라오며,
	 * 통신할 곳과 키를 공유하셔서 사용하시면 됩니다.
	 * 각각의 바이트 배열은 바이트가 아닌 16진수(0xFE) 형태로 사용하셔도 동일한 결과를 출력합니다.
	 * 각 바이트의 범위는 -128 ~ 127 안에서 랜덤하세 사용하시면 됩니다.
	 */
	private $pbUserKey	= array(49,-97,101,-52,57,97,49,97,-49,101,98,49,50,-48,55,50); // 사용자키
	private $IV				= array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16); // 초기화 벡터

	public function __contruct()
	{
		
	}

	/**
	 * SEED128 + CBC + PKCS#5 암호 운영 모드로 구현한 암호화
	 *
	 * @param string $str
	 *
	 * @return string Encrypted string of hex type
	 */
	public function encrypt($str)
	{
		$str = iconv($this->serverEncoding, $this->innerEncoding, $str);
		$planBytes = array_slice(unpack('c*',$str), 0); // 평문을 바이트 배열로 변환
		if (count($planBytes) == 0) {
			return $str;
		}

		$seed = new Seed();
		$seed->SeedRoundKey($pdwRoundKey, $this->pbUserKey); // 라운드키 생성

		$planBytesLength = count($planBytes);
		$start = 0;
		$end = 0;
		$cipherBlockBytes = array();
		$cbcBlockBytes = array();
		$this->arraycopy($this->IV, 0, $cbcBlockBytes, 0, $this->block); // CBC블록을 IV 바이트로 초기화
		$ret = null;
		while ($end < $planBytesLength) {
			$end = $start + $this->block;
			if ($end > $planBytesLength) {
				$end = $planBytesLength;
			}

			$this->arraycopy($planBytes, $start, $cipherBlockBytes, 0, $end - $start); // 암호블록을 평문 블록으로 대치

			$nPad = $this->block - ($end - $start); // 블록내 바이트 패딩값 계산
			for ($i = ($end - $start); $i < $this->block; $i++) {
				$cipherBlockBytes[$i] = $nPad; // 비어있는 바이트에 패딩 추가
			}

			$this->xor16($cipherBlockBytes, $cbcBlockBytes, $cipherBlockBytes); // CBC운영모드로 새로운 암호화 블록 생성
			$seed->SeedEncrypt($cipherBlockBytes, $pdwRoundKey, $encryptCbcBlockBytes); // 암호블록을 SEED로 암호화
			$this->arraycopy($encryptCbcBlockBytes, 0, $cbcBlockBytes, 0, $this->block); // 다음 블록에서 사용할 CBC블록을 SEED암호 블록으로 대치

			foreach($encryptCbcBlockBytes as $encryptedString) {
				$ret .= bin2hex(chr($encryptedString)); // 암호화된 16진수 스트링 추가 저장
			}
			$start = $end;
		}
		return $ret;
	}


	/**
	 * SEED128 + CBC + PKCS#5 암호 운영 모드로 구현한 복호화
	 *
	 * @param string $str
	 *
	 * @return string Return decrypted string.
	 */
	public function decrypt($str)
	{
		$planBytes = array();
		for ($i = 0; $i < strlen($str); $i += 2) {
			$planBytes[] = $this->convertMinus128(hexdec(substr($str, $i, 2))); // 16진수를 바이트 배열로 변환 
		}
		
		if (count($planBytes) == 0) {
			return $str;
		}

		$seed = new Seed();
		$seed->SeedRoundKey($pdwRoundKey, $this->pbUserKey);

		$planBytesLength = count($planBytes);
		$start = 0;
		$isEnd = false;
		$cipherBlockBytes = array();
		$cbcBlockBytes = array();
		$thisEE = array();
		$this->arraycopy($this->IV, 0, $cbcBlockBytes, 0, $this->block); // CBC블록을 IV 바이트로 초기화

		while (!$isEnd) {
			if ($start + $this->block >= $planBytesLength) {
				$isEnd = true;
			}

			$this->arraycopy($planBytes, $start, $cipherBlockBytes, 0, $this->block); // 암호블록을 평문블록으로 대치
			$seed->SeedDecrypt($cipherBlockBytes, $pdwRoundKey, $ee); // 암호블록을 SEED로 복호화
			$this->xor16($thisEE, $cbcBlockBytes, $ee); // CBC운영모드로 새로운 복호화 블록 생성
			$thisEE = $this->convertMinus128($thisEE);

			$this->arraycopy($thisEE, 0, $planBytes, $start, $this->block); // 평문블록을 생성한 복호화 블록으로 대치
			$this->arraycopy($cipherBlockBytes, 0, $cbcBlockBytes, 0, $this->block); // 다음 블록에서 사용할 CBC블록을 암호 블록으로 대치
			$start += $this->block; // 다음블록의 시작 위치 계산
		}
		$rst = iconv($this->innerEncoding, $this->serverEncoding, call_user_func_array("pack", array_merge(array("c*"), $planBytes))); // 평문블록 바이트 배열을 문자열로 변환
		return $this->pkcs5Unpad($rst); // 패딩처리해서 반환
	}

	/**
	 * Java의 arraycopy 함수를 php로 구현
	 * 원본 배열의 해당 위치부터 시작한 값을 복사할 배열의 위치에 정해진 길이만큼 대치시켜준후, 복사할 배열을 반환
	 *
	 * @param array $src Source array.
	 * @param integer $srcPos Start position of source array.
	 * @param array $dest Destination array.
	 * @param integer $destPos Start position of destination array.
	 * @param integer $length Integer to count the arrays of..
	 *
	 * @return array Return destination source array.
	 */
	public function arraycopy($src, $srcPos, &$dest, $destPos, $length)
	{
		for ($i=$srcPos; $i < $srcPos+$length; $i++) {
			$dest[$destPos] = $src[$i];
			$destPos++;
		}
	}

	/**
	 * XOR 계산식 구현
	 *
	 * @param array $t1
	 * @param array $x1
	 * @param array $x2
	 *
	 * @return array
	 */
	public function xor16(&$t, $x1, $x2)
	{
		$t[0] = $x1[0] ^ $x2[0];
		$t[1] = $x1[1] ^ $x2[1];
		$t[2] = $x1[2] ^ $x2[2];
		$t[3] = $x1[3] ^ $x2[3];
		$t[4] = $x1[4] ^ $x2[4];
		$t[5] = $x1[5] ^ $x2[5];
		$t[6] = $x1[6] ^ $x2[6];
		$t[7] = $x1[7] ^ $x2[7];
		$t[8] = $x1[8] ^ $x2[8];
		$t[9] = $x1[9] ^ $x2[9];
		$t[10] = $x1[10] ^ $x2[10];
		$t[11] = $x1[11] ^ $x2[11];
		$t[12] = $x1[12] ^ $x2[12];
		$t[13] = $x1[13] ^ $x2[13];
		$t[14] = $x1[14] ^ $x2[14];
		$t[15] = $x1[15] ^ $x2[15];
	}

	/**
	 * Bytes값을 Minus 128 표현식으로 변환
	 * 32bit에서 Bytes객체의 8번째 자리수가 1인 경우 음수로 표기
	 * 64bit에서 양수로 표현되기 때문에 정수를 강제로 32bit로 인식하게해 오버플로우 시켜 음수로 표기되도록 변환 시켜줌
	 *
	 * @param mixed[] $bytes Array of bytes or continuous string of hex.
	 *
	 * @return array List of hex lists or string of hex.
	 */
	private function convertMinus128($bytes)
	{
		if(PHP_INT_SIZE > 4) { // 64비트가 아닌 경우 그대로 출력
			return $bytes;
		}

		if (is_array($bytes)) {
			$ret = array();
			foreach($bytes as $val) {
				$ret[] = (($val+128) % 256) -128;
			}
			return $ret;
		}
		return (($bytes+128) % 256) -128;
	}

	/**
	 * Bytes값을 Modulo 256 표현식으로 변환 (현재 사용 안함)
	 *
	 * @param mixed[] $bytes Array of bytes or continuous string of hex.
	 *
	 * @return array List of hex lists or string of hex.
	 */
	private function convertModulo256($hex)
	{
		if (is_array($hex)) {
			$ret = array();
			foreach($hex as $val) {
				$ret[] = ($val % 256) -128;
			}
			return $ret;
		}
		return ($hex % 256) -128;
	}

	/**
	 * Padding의 갯수를 구함 (현재 사용 안함)
	 *
	 * @param array $xx
	 *
	 * @return int
	 */
	private function unPddingCntPKCS7($xx)
	{
		$xxxxx = $xx[count($xx) - 1];
	
		if ($xxxxx > 16) {
			return 0;
		}
	
		if ($xxxxx < 0) {
			return 0;
		}

		for ($i = (16 - $xxxxx); $i < count($xx); $i++) {
			if ($xx[$i] != $xxxxx) {
				return 0;
			}
		}
		return $xxxxx;
	}

	/**
	 * Little Endian인지 체크 (현재 사용 안함)
	 *
	 * @param int $int
	 *
	 * @return boolean
	 */
	private function isLittleEndian($int)
	{
		$p = pack('S', $int); 

		return $int === current(unpack('v', $p));
	}

	/**
	 * Biginteger를 4바이트 바이너리로 unpack (현재 사용 안함)
	 *
	 * @param bigint $unsignedBigEndianInteger
	 *
	 * @return array
	 */
	private function unpackBigInteger($unsignedBigEndianInteger)
	{
		$unpack = unpack('N', $unsignedBigEndianInteger);

		return $unpack;
	}

	/**
	 * 블록사이즈에 맞게 스트링에 패딩 추가 (현재 사용 안함)
	 *
	 * @param string $text Each string of block in CBC mode.
	 * @param integer $blocksize Block size (ex. 16 bytes)
	 *
	 * @return string Return string
	 */
	public function pkcs5Pad($text, $blocksize)  
    { 
        $pad = $blocksize - (strlen ( $text ) % $blocksize);  
        return $text . str_repeat ( chr ( $pad ), $pad );  
    } 

	/**
	 * 스트링에서 패딩을 제거 (현재 사용 안함)
	 *
	 * @param string $text Each string of block in CBC mode.
	 *
	 * @return string Return string
	 */
	public function pkcs5Unpad($text)  
    {  
        $pad = ord ( $text {strlen ( $text ) - 1} );  
        if ($pad > strlen ( $text ))  
            return $text;  
        if (strspn ( $text, chr ( $pad ), strlen ( $text ) - $pad ) != $pad)  
            return $text;  
        return substr ( $text, 0, - 1 * $pad );  
    }
}
