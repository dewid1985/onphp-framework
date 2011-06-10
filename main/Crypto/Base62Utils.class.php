<?php
/***************************************************************************
 *   Copyright (C) 2011 by Sergey S. Sergeev                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Crypto
	**/
	class Base62Utils extends StaticFactory
	{
		protected static $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

		public static function setChars($chars)
		{
			Assert::isEqual(mb_strlen($chars), 62, 'Wrong length');

			Assert::isTrue(
				preg_match('/^[a-z0-9]+$/iu', $chars) !== 0,
				'Wrong pattern matching'
			);

			self::$chars = $chars;
		}

		public static function getChars()
		{
			return self::$chars;
		}

		public static function encode($integer)
		{
			Assert::isPositiveInteger($integer, 'Out of range');
			$magicInt = strlen(self::$chars);

			$string = '';
			do {
				$i = $integer % $magicInt;
				$string = self::$chars[$i] . $string;
				$integer = ($integer - $i) / $magicInt;
			} while ($integer > 0);

			return $string;
		}

		public static function decode($string)
		{
			Assert::isString($string);

			$len = strlen($string);
			Assert::isTrue(
				(PHP_INT_SIZE === 4 && $len > 0 && $len <= 6)
				|| (PHP_INT_SIZE === 8 && $len > 0 && $len <= 11),
				'Wrong code'
			);

			$magicInt = strlen(self::$chars);

			$val = 0;
			$arr = array_flip(str_split(self::$chars));
			for($i = 0; $i < $len; ++$i) {
				$val += $arr[$string[$i]] * pow($magicInt, $len - $i - 1);
			}

			return $val;
		}
	}
?>