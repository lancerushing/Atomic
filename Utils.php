<?php

namespace Atomic;

class Utils {

	/**
	 * Generates a 126 bit random number, and converts to base 64 (21 characters)
	 * basically if PHP/mt_rand had big enough numbers:
	 * base_convert(mt_rand(0, pow(2, 126)), 10, 64);
	 * @return string
	 */
	public static function uuid_create_short() {
		$max = pow(2, 18); // 262144 - 18 bits of randomness can be 3 chars in base 64
		return sprintf('%03s%03s%03s%03s%03s%03s%03s'
			, self::base64_convert(mt_rand(0, $max))
			, self::base64_convert(mt_rand(0, $max))
			, self::base64_convert(mt_rand(0, $max))
			, self::base64_convert(mt_rand(0, $max))
			, self::base64_convert(mt_rand(0, $max))
			, self::base64_convert(mt_rand(0, $max))
			, self::base64_convert(mt_rand(0, $max)));
	}

	public static function random_short() {
		$max = pow(2, 18); // 262144 - 18 bits of randomness can be 3 chars in base 64
		return sprintf('%03s', self::base64_convert(mt_rand(0, $max)));
	}


	/**
	 * "Base 64" converts integers, using . and _ as the 62nd and 63rd indexes.
	 * @param $integer
	 * @return string
	 * @see http://www.yuiblog.com/blog/2010/07/06/in-the-yui-3-gallery-base64-and-y64-encoding/
	 */
	public static function base64_convert($integer) {

		$charList = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ._"; // base 64 chars (URL safe)
		$toBase = 64; // strlen($charList);

		$base64string = '';
		while ($integer > 0) {
			$base64string = $charList[($integer % $toBase)] . $base64string;
			$integer = floor($integer / $toBase);
		}

		return $base64string;
	}
}
