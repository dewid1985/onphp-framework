<?php
/****************************************************************************
 *   Copyright (C) 2004-2006 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 2 of the License, or      *
 *   (at your option) any later version.                                    *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	/**
	 * Factory for various Primitives.
	 * 
	 * @ingroup Primitives
	**/
	final class Primitive extends StaticFactory
	{
		public static function spawn($primitive, $name)
		{
			return new $primitive($name);
		}
		
		public static function integer($name)
		{
			return new PrimitiveInteger($name);
		}
		
		public static function identifier($name)
		{
			return new PrimitiveIdentifier($name);
		}
		
		public static function date($name)
		{
			return new PrimitiveDate($name);
		}
		
		public static function string($name)
		{
			return new PrimitiveString($name);
		}
		
		public static function range($name)
		{
			return new PrimitiveRange($name);
		}
		
		public static function choice($name)
		{
			return new PrimitiveList($name);
		}
		
		public static function set($name)
		{
			return new PrimitiveArray($name);
		}

		public static function multiChoice($name)
		{
			return new PrimitiveMultiList($name);
		}
		
		public static function boolean($name)
		{
			return new PrimitiveBoolean($name);
		}
		
		public static function file($name)
		{
			return new PrimitiveFile($name);
		}
		
		public static function image($name)
		{
			return new PrimitiveImage($name);
		}
		
		public static function email($name)
		{
			return new PrimitiveEmail($name);
		}
		
		public static function password($name)
		{
			return new PrimitivePassword($name);
		}
		
		public static function nick($name)
		{
			return new PrimitiveNick($name);
		}
		
		public static function url($name)
		{
			return new PrimitiveUrl($name);
		}
		
		public static function host($name)
		{
			return new PrimitiveHost($name);
		}
	}
?>